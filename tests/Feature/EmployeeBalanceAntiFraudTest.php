<?php

namespace Tests\Feature;

use App\Models\RechargeRequest;
use App\Models\User;
use App\Services\Staff\RechargeRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeBalanceAntiFraudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->seed(\Database\Seeders\MockSubscriberSeeder::class);
    }

    public function test_employee_can_approve_bank_transfer_and_credit_their_balance(): void
    {
        $employeeA = User::create([
            'name' => 'أحمد علي (موظف أ)',
            'username' => 'employee_a',
            'email' => 'emp_a@netapp.com',
            'phone' => '0910000001',
            'password' => bcrypt('password'),
            'type' => 'employee',
            'status' => \App\Enums\UserStatus::Active,
        ]);
        $employeeA->assignRole('employee');

        $rechargeRequest = RechargeRequest::create([
            'contract_number' => 'TEST-100001',
            'subscriber_name' => 'خالد محمد عبد الله',
            'package_id' => 'PKG-70',
            'package_name' => 'ميقا 70G',
            'amount' => 70.00,
            'payment_method' => 'bank_transfer',
            'status' => 'pending',
        ]);

        $service = app(RechargeRequestService::class);
        $service->approve($rechargeRequest, $employeeA, 'تم التحقق من الوصل بنجاح');

        $this->assertDatabaseHas('recharge_requests', [
            'id' => $rechargeRequest->id,
            'status' => 'approved',
            'processed_by' => $employeeA->id,
        ]);

        $this->assertDatabaseHas('payments', [
            'contract_number' => 'TEST-100001',
            'amount' => 70.00,
            'processed_by' => $employeeA->id,
            'gateway' => 'bank_transfer',
        ]);

        $response = $this->actingAs($employeeA)->get('/staff/my-balance');
        $response->assertStatus(200);
        $response->assertSee('70.00');
        $response->assertSee('أحمد علي (موظف أ)');
    }

    public function test_anti_fraud_blocks_second_employee_from_claiming_same_transaction(): void
    {
        $employeeA = User::create([
            'name' => 'أحمد علي (موظف أ)',
            'username' => 'emp_a_dup',
            'email' => 'emp_a_dup@netapp.com',
            'phone' => '0910000002',
            'password' => bcrypt('password'),
            'type' => 'employee',
            'status' => \App\Enums\UserStatus::Active,
        ]);
        $employeeA->assignRole('employee');

        $employeeB = User::create([
            'name' => 'سالم محمود (موظف ب)',
            'username' => 'emp_b_dup',
            'email' => 'emp_b_dup@netapp.com',
            'phone' => '0910000003',
            'password' => bcrypt('password'),
            'type' => 'employee',
            'status' => \App\Enums\UserStatus::Active,
        ]);
        $employeeB->assignRole('employee');

        $rechargeRequest = RechargeRequest::create([
            'contract_number' => 'TEST-100002',
            'subscriber_name' => 'سالم مفتاح علي',
            'package_id' => 'PKG-150',
            'package_name' => 'ميقا 150G',
            'amount' => 150.00,
            'payment_method' => 'bank_transfer',
            'status' => 'pending',
        ]);

        $service = app(RechargeRequestService::class);
        $service->approve($rechargeRequest, $employeeA, 'تم الشحن من قبل الموظف أ');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/تزوير/u');

        $service->approve($rechargeRequest->fresh(), $employeeB, 'محاولة شحن مكررة من الموظف ب');
    }
}
