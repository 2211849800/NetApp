<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Complaint;
use App\Models\MockSubscriber;
use App\Models\Payment;
use App\Models\RechargeRequest;
use App\Models\SubscriptionRequest;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\StaffUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(StaffUserSeeder::class);
        $this->employee = User::where('email', 'employee@example.com')->firstOrFail();
        $this->employee->load('roles.permissions');
    }

    private function seedMockSubscriber(array $overrides = []): MockSubscriber
    {
        return MockSubscriber::create(array_merge([
            'contract_number' => 'TEST-100001',
            'name' => 'خالد محمد عبد الله',
            'phone' => '0912345678',
            'status' => 'ACTIVE',
            'current_package_id' => 'PKG-100',
            'current_package_name' => 'ميقا 100G',
            'current_package_price' => 100,
            'data_allowance_gb' => 100,
            'used_data_gb' => 20,
            'expires_at' => now()->addDays(10),
            'borrowing_status' => 'AVAILABLE',
        ], $overrides));
    }

    public function test_employee_dashboard_shows_live_counters(): void
    {
        RechargeRequest::create(['status' => 'pending']);
        Complaint::create(['subject' => 'انقطاع', 'status' => 'open']);
        SubscriptionRequest::create([
            'applicant_name' => 'مواطن',
            'phone' => '0910000001',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->employee)->get(route('employee.dashboard'));

        $response->assertOk();
        $response->assertSee('طلبات الشحن المعلقة');
        $response->assertSee('التذاكر المفتوحة');
        $response->assertSee('العمليات المنجزة اليوم');
    }

    public function test_subscriber_lookup_uses_provider_not_static_arrays(): void
    {
        $this->seedMockSubscriber();

        $response = $this->actingAs($this->employee)->get(route('staff.subscribers.index', [
            'q' => 'TEST-100001',
            'contract' => 'TEST-100001',
        ]));

        $response->assertOk();
        $response->assertSee('خالد محمد عبد الله');
        $response->assertSee('ميقا 100G');
        $response->assertSee('TEST-100001');
        $response->assertSee('تنفيذ الشحن');
    }

    public function test_employee_can_recharge_subscriber_and_write_audit_and_payment(): void
    {
        $this->seedMockSubscriber();

        $response = $this->actingAs($this->employee)->post(route('staff.subscribers.recharge'), [
            'contract_number' => 'TEST-100001',
            'package_id' => 'PKG-150',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('mock_subscribers', [
            'contract_number' => 'TEST-100001',
            'current_package_id' => 'PKG-150',
            'status' => 'ACTIVE',
        ]);
        $this->assertDatabaseHas('payments', [
            'contract_number' => 'TEST-100001',
            'gateway' => 'cash',
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'SUBSCRIBER_RECHARGED',
        ]);
    }

    public function test_employee_can_approve_recharge_request(): void
    {
        $this->seedMockSubscriber(['status' => 'EXPIRED', 'used_data_gb' => 100]);

        $request = RechargeRequest::create([
            'contract_number' => 'TEST-100001',
            'subscriber_name' => 'خالد محمد عبد الله',
            'package_id' => 'PKG-70',
            'package_name' => 'ميقا 70G',
            'amount' => 70,
            'payment_method' => 'lypay',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->employee)
            ->post(route('staff.recharges.approve', $request), ['notes' => 'تم التحقق']);

        $response->assertRedirect(route('staff.recharges.index'));
        $this->assertDatabaseHas('recharge_requests', ['id' => $request->id, 'status' => 'approved']);
        $this->assertDatabaseHas('mock_subscribers', ['contract_number' => 'TEST-100001', 'status' => 'ACTIVE']);
        $this->assertDatabaseHas('payments', ['recharge_request_id' => $request->id, 'gateway' => 'lypay']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'RECHARGE_REQUEST_APPROVED']);
    }

    public function test_employee_can_reject_recharge_request(): void
    {
        $request = RechargeRequest::create([
            'contract_number' => 'TEST-100001',
            'package_id' => 'PKG-70',
            'status' => 'pending',
        ]);

        $this->actingAs($this->employee)
            ->post(route('staff.recharges.reject', $request), ['notes' => 'إيصال غير واضح'])
            ->assertRedirect(route('staff.recharges.index'));

        $this->assertDatabaseHas('recharge_requests', ['id' => $request->id, 'status' => 'rejected']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'RECHARGE_REQUEST_REJECTED']);
    }

    public function test_employee_can_approve_subscription_request_and_create_user(): void
    {
        $request = SubscriptionRequest::create([
            'applicant_name' => 'يوسف علي',
            'national_id' => '119900123456',
            'phone' => '0911112233',
            'city' => 'طرابلس',
            'package_id' => 'PKG-100',
            'package_name' => 'ميقا 100G',
            'status' => 'pending',
        ]);

        $this->actingAs($this->employee)
            ->post(route('staff.subscription-requests.approve', $request))
            ->assertRedirect(route('staff.subscription-requests.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('subscription_requests', ['id' => $request->id, 'status' => 'approved']);
        $this->assertDatabaseHas('users', [
            'name' => 'يوسف علي',
            'phone' => '0911112233',
            'type' => 'subscriber',
        ]);
        $this->assertTrue(MockSubscriber::query()->where('phone', '0911112233')->exists());
        $this->assertDatabaseHas('audit_logs', ['action' => 'SUBSCRIPTION_REQUEST_APPROVED']);
    }

    public function test_complaints_page_is_database_backed(): void
    {
        Complaint::create([
            'subject' => 'انقطاع الإشارة',
            'subscriber_name' => 'طارق الزوي',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->employee)->get(route('staff.complaints.index'));

        $response->assertOk();
        $response->assertSee('انقطاع الإشارة');
        $response->assertSee('طارق الزوي');
        $response->assertDontSee('TK-2025-0158');
    }

    public function test_employee_can_update_complaint_status(): void
    {
        $complaint = Complaint::create([
            'subject' => 'ضعف السرعة',
            'status' => 'open',
        ]);

        $this->actingAs($this->employee)
            ->put(route('staff.complaints.update', $complaint), [
                'status' => 'resolved',
                'internal_notes' => 'تم ضبط الراوتر',
                'staff_reply' => 'تم الحل، يرجى إعادة التشغيل.',
                'priority' => 'medium',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'status' => 'resolved',
            'staff_reply' => 'تم الحل، يرجى إعادة التشغيل.',
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'COMPLAINT_RESOLVED']);
    }

    public function test_payments_page_filters_real_records(): void
    {
        Payment::create([
            'reference' => 'PAY-LYPAY-1',
            'subscriber_name' => 'سالم مفتاح علي',
            'contract_number' => 'TEST-100002',
            'gateway' => 'lypay',
            'amount' => 150,
            'status' => 'completed',
            'paid_at' => now(),
        ]);
        Payment::create([
            'reference' => 'PAY-CASH-1',
            'gateway' => 'cash',
            'amount' => 70,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->employee)->get(route('staff.payments.index', [
            'gateway' => 'lypay',
            'status' => 'completed',
        ]));

        $response->assertOk();
        $response->assertSee('PAY-LYPAY-1');
        $response->assertDontSee('PAY-CASH-1');
        $response->assertSee('سالم مفتاح علي');
    }

    public function test_recharges_placeholder_is_removed(): void
    {
        $this->actingAs($this->employee)
            ->get(route('staff.recharges.index'))
            ->assertOk()
            ->assertDontSee('هذه الصفحة placeholder')
            ->assertSee('طلبات الشحن');
    }
}
