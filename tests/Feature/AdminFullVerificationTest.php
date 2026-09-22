<?php

namespace Tests\Feature;

use App\Enums\RecordStatus;
use App\Enums\UserStatus;
use App\Models\AuditLog;
use App\Models\BankAccount;
use App\Models\Complaint;
use App\Models\IptvPackage;
use App\Models\Offer;
use App\Models\Package;
use App\Models\Product;
use App\Models\RechargeRequest;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\StaffUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminFullVerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $employee;
    private User $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(StaffUserSeeder::class);

        $this->admin = User::where('email', 'admin@netapp.com')->firstOrFail();
        $this->employee = User::where('email', 'employee@example.com')->firstOrFail();
        $this->supervisor = User::where('email', 'supervisor@example.com')->firstOrFail();
    }

    public function test_admin_dashboard_loads_with_live_database_statistics(): void
    {
        Package::create([
            'name' => 'باقة سرعة 100',
            'price' => 100.00,
            'duration_days' => 30,
            'status' => 'active',
        ]);

        Complaint::create([
            'subject' => 'شكوى انقطاع',
            'status' => 'open',
        ]);

        RechargeRequest::create([
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('لوحة تحكم المدير');
    }

    public function test_admin_dashboard_renders_cleanly_on_empty_database_state(): void
    {
        Package::query()->forceDelete();
        Complaint::query()->delete();
        RechargeRequest::query()->delete();

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('0');
    }

    public function test_package_crud_validation_and_audit_logging(): void
    {
        $failResponse = $this->actingAs($this->admin)
            ->from(route('admin.packages.create'))
            ->post(route('admin.packages.store'), [
                'name' => '',
                'price' => -10,
            ]);
        $failResponse->assertRedirect(route('admin.packages.create'));
        $failResponse->assertSessionHasErrors(['name', 'price']);

        $createResponse = $this->actingAs($this->admin)->post(route('admin.packages.store'), [
            'name' => 'باقة الألعاب المميزة',
            'description' => 'باقة مخصصة للألعاب بسرعة فائقة',
            'price' => 200.00,
            'data_allowance' => '200 GB',
            'duration_days' => 30,
            'status' => 'active',
        ]);
        $createResponse->assertRedirect(route('admin.packages.index'));
        $this->assertDatabaseHas('packages', ['name' => 'باقة الألعاب المميزة']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'PACKAGE_CREATED']);

        $package = Package::where('name', 'باقة الألعاب المميزة')->firstOrFail();

        $this->actingAs($this->admin)->get(route('admin.packages.show', $package))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.packages.edit', $package))->assertOk();

        $updateResponse = $this->actingAs($this->admin)->put(route('admin.packages.update', $package), [
            'name' => 'باقة الألعاب المحدثة',
            'price' => 220.00,
            'duration_days' => 30,
            'status' => 'active',
        ]);
        $updateResponse->assertRedirect(route('admin.packages.index'));
        $this->assertEquals('باقة الألعاب المحدثة', $package->fresh()->name);
        $this->assertDatabaseHas('audit_logs', ['action' => 'PACKAGE_UPDATED']);

        $this->actingAs($this->admin)->patch(route('admin.packages.toggle-status', $package))->assertRedirect();
        $this->assertEquals(RecordStatus::Inactive, $package->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'PACKAGE_DISABLED']);

        $this->actingAs($this->admin)->patch(route('admin.packages.toggle-status', $package))->assertRedirect();
        $this->assertEquals(RecordStatus::Active, $package->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'PACKAGE_ACTIVATED']);
    }

    public function test_product_crud_image_upload_and_toggle_status(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('router.jpg', 600, 400);

        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'راوتر هواوي AX3',
            'description' => 'راوتر واي فاي 6 متطور',
            'price' => 280.00,
            'is_available' => 1,
            'status' => 'active',
            'image' => $image,
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', ['name' => 'راوتر هواوي AX3']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'PRODUCT_CREATED']);

        $product = Product::where('name', 'راوتر هواوي AX3')->firstOrFail();
        $this->assertNotNull($product->image_path);
        Storage::disk('public')->assertExists($product->image_path);

        $this->actingAs($this->admin)->get(route('admin.products.edit', $product))->assertOk();

        $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
            'name' => 'راوتر هواوي AX3 Pro',
            'price' => 320.00,
            'is_available' => 1,
            'status' => 'active',
        ])->assertRedirect(route('admin.products.index'));

        $this->assertEquals('راوتر هواوي AX3 Pro', $product->fresh()->name);
        $this->assertDatabaseHas('audit_logs', ['action' => 'PRODUCT_UPDATED']);

        $this->actingAs($this->admin)->patch(route('admin.products.toggle-status', $product))->assertRedirect();
        $this->assertEquals(RecordStatus::Inactive, $product->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'PRODUCT_DISABLED']);
    }

    public function test_offer_crud_and_date_range_validation(): void
    {
        $invalidResp = $this->actingAs($this->admin)
            ->from(route('admin.offers.create'))
            ->post(route('admin.offers.store'), [
                'name' => 'عرض الصيف',
                'starts_at' => '2026-08-01',
                'ends_at' => '2026-07-01',
                'discount_percent' => 20.00,
                'status' => 'active',
            ]);
        $invalidResp->assertRedirect(route('admin.offers.create'));
        $invalidResp->assertSessionHasErrors('ends_at');

        $validResp = $this->actingAs($this->admin)->post(route('admin.offers.store'), [
            'name' => 'عرض الخريف 2026',
            'description' => 'خصم 30% على الاشتراكات السنوية',
            'starts_at' => '2026-09-01',
            'ends_at' => '2026-10-01',
            'discount_percent' => 30.00,
            'status' => 'active',
        ]);
        $validResp->assertRedirect(route('admin.offers.index'));
        $this->assertDatabaseHas('offers', ['name' => 'عرض الخريف 2026']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'OFFER_CREATED']);

        $offer = Offer::where('name', 'عرض الخريف 2026')->firstOrFail();

        $this->actingAs($this->admin)->get(route('admin.offers.show', $offer))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.offers.edit', $offer))->assertOk();

        $this->actingAs($this->admin)->patch(route('admin.offers.toggle-status', $offer))->assertRedirect();
        $this->assertEquals(RecordStatus::Inactive, $offer->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'OFFER_DEACTIVATED']);
    }

    public function test_iptv_package_crud_and_status_toggle(): void
    {
        $resp = $this->actingAs($this->admin)->post(route('admin.iptv-packages.store'), [
            'name' => 'باقة سينما برو',
            'description' => 'أحدث الأفلام والمسلسلات بجودة 4K',
            'price' => 60.00,
            'duration_days' => 30,
            'status' => 'active',
        ]);

        $resp->assertRedirect(route('admin.iptv-packages.index'));
        $this->assertDatabaseHas('iptv_packages', ['name' => 'باقة سينما برو']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'IPTV_PACKAGE_CREATED']);

        $iptv = IptvPackage::where('name', 'باقة سينما برو')->firstOrFail();

        $this->actingAs($this->admin)->get(route('admin.iptv-packages.edit', $iptv))->assertOk();

        $this->actingAs($this->admin)->patch(route('admin.iptv-packages.toggle-status', $iptv))->assertRedirect();
        $this->assertEquals(RecordStatus::Inactive, $iptv->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'IPTV_PACKAGE_STATUS_CHANGED']);
    }

    public function test_bank_account_crud_and_account_number_masking_in_audit(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.bank-accounts.store'), [
            'bank_name' => 'مصرف الجمهورية',
            'account_name' => 'شركة ميقا للاتصالات والتقنية',
            'account_number' => '112233445566',
            'transfer_instructions' => 'يرجى كتابة رقم العقد في وصف التحويل',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.bank-accounts.index'));
        $this->assertDatabaseHas('bank_accounts', ['account_number' => '112233445566']);

        $audit = AuditLog::where('action', 'BANK_ACCOUNT_CREATED')->latest()->first();
        $this->assertNotNull($audit);
        $this->assertNotEquals('112233445566', $audit->new_values['account_number']);
        $this->assertStringContainsString('****', $audit->new_values['account_number']);

        $bank = BankAccount::where('account_number', '112233445566')->firstOrFail();

        $this->actingAs($this->admin)->patch(route('admin.bank-accounts.toggle-status', $bank))->assertRedirect();
        $this->assertEquals(RecordStatus::Inactive, $bank->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'BANK_ACCOUNT_DISABLED']);
    }

    public function test_employee_creation_status_transitions_and_password_reset(): void
    {
        $createResp = $this->actingAs($this->admin)->post(route('admin.employees.store'), [
            'name' => 'عمر المختار',
            'username' => 'omar_mukhtar',
            'phone' => '0917778899',
            'email' => 'omar@example.com',
            'password' => 'secret_pass_123',
            'password_confirmation' => 'secret_pass_123',
            'role' => 'employee',
            'status' => 'active',
        ]);

        $createResp->assertRedirect(route('admin.employees.index'));
        $this->assertDatabaseHas('users', ['username' => 'omar_mukhtar']);

        $employee = User::where('username', 'omar_mukhtar')->firstOrFail();

        $this->assertTrue(Hash::check('secret_pass_123', $employee->password));
        $creationAudit = AuditLog::where('action', 'EMPLOYEE_CREATED')->where('entity_id', $employee->id)->first();
        $this->assertNotNull($creationAudit);
        $this->assertArrayNotHasKey('password', $creationAudit->new_values ?? []);

        $this->actingAs($this->admin)->get(route('admin.employees.show', $employee))->assertOk();

        $updateResp = $this->actingAs($this->admin)->put(route('admin.employees.update', $employee), [
            'name' => 'عمر المختار المحدث',
            'username' => 'omar_mukhtar',
            'phone' => '0917778899',
            'email' => 'omar@example.com',
            'role' => 'supervisor',
            'status' => 'active',
        ]);
        $updateResp->assertRedirect(route('admin.employees.index'));
        $this->assertTrue($employee->fresh()->hasRole('supervisor'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'EMPLOYEE_UPDATED']);

        $this->actingAs($this->admin)->patch(route('admin.employees.deactivate', $employee), ['confirm' => 'yes'])->assertRedirect();
        $this->assertEquals(UserStatus::Inactive, $employee->fresh()->status);
        $this->assertFalse($employee->fresh()->is_active);

        $this->post('/logout');
        $loginDeactivated = $this->post('/login', [
            'login' => 'omar_mukhtar',
            'password' => 'secret_pass_123',
        ]);
        $loginDeactivated->assertSessionHasErrors();
        $this->assertGuest();

        $this->actingAs($this->admin)->patch(route('admin.employees.suspend', $employee), ['confirm' => 'yes'])->assertRedirect();
        $this->assertEquals(UserStatus::Suspended, $employee->fresh()->status);

        $this->post('/logout');
        $loginSuspended = $this->post('/login', [
            'login' => 'omar_mukhtar',
            'password' => 'secret_pass_123',
        ]);
        $loginSuspended->assertSessionHasErrors();
        $this->assertGuest();

        $this->actingAs($this->admin)->patch(route('admin.employees.activate', $employee))->assertRedirect();
        $this->assertEquals(UserStatus::Active, $employee->fresh()->status);
        $this->assertTrue($employee->fresh()->is_active);

        $this->actingAs($this->admin)->get(route('admin.employees.reset-password-form', $employee))->assertOk();

        $resetResp = $this->actingAs($this->admin)->post(route('admin.employees.reset-password', $employee), [
            'password' => 'new_secure_pass_456',
            'password_confirmation' => 'new_secure_pass_456',
        ]);
        $resetResp->assertRedirect(route('admin.employees.show', $employee));

        $resetAudit = AuditLog::where('action', 'PASSWORD_RESET')->where('entity_id', $employee->id)->first();
        $this->assertNotNull($resetAudit);
        $this->assertEmpty($resetAudit->new_values);

        $this->post('/logout');
        $loginSuccess = $this->post('/login', [
            'login' => 'omar_mukhtar',
            'password' => 'new_secure_pass_456',
        ]);
        $loginSuccess->assertRedirect(route('supervisor.dashboard'));
        $this->assertAuthenticatedAs($employee);
    }

    public function test_roles_and_permissions_management_and_admin_immutability(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();
        $employeeRole = Role::where('name', 'employee')->firstOrFail();

        $blockedResp = $this->actingAs($this->admin)->put(route('admin.roles.update', $adminRole), [
            'permissions' => ['view_dashboard'],
        ]);
        $blockedResp->assertSessionHas('error');

        $updateResp = $this->actingAs($this->admin)->put(route('admin.roles.update', $employeeRole), [
            'permissions' => ['view_dashboard', 'view_complaints'],
        ]);
        $updateResp->assertRedirect(route('admin.roles.index'));
        $this->assertTrue($employeeRole->fresh()->permissions->contains('name', 'view_complaints'));
    }

    public function test_audit_logs_search_and_filter(): void
    {
        AuditLog::create([
            'user_id' => $this->admin->id,
            'action' => 'TEST_SECURITY_ACTION',
            'entity_type' => 'SecurityEntity',
            'entity_id' => 999,
            'result' => 'success',
            'ip_address' => '192.168.1.100',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.audit-logs.index', [
            'search' => 'TEST_SECURITY_ACTION',
        ]));

        $response->assertOk();
        $response->assertSee('TEST_SECURITY_ACTION');
    }

    public function test_admin_can_update_profile_and_change_password(): void
    {
        $this->actingAs($this->admin)->get(route('admin.profile.index'))->assertOk();

        $this->actingAs($this->admin)->put(route('admin.profile.update'), [
            'name' => 'مدير النظام الرئيسي',
            'username' => 'admin_updated',
            'phone' => '0910009999',
            'email' => 'admin_updated@netapp.com',
        ])->assertRedirect(route('admin.profile.index'));

        $this->assertEquals('admin_updated', $this->admin->fresh()->username);

        $changePassResp = $this->actingAs($this->admin)->put(route('admin.profile.change-password'), [
            'current_password' => 'password',
            'password' => 'new_admin_password_789',
            'password_confirmation' => 'new_admin_password_789',
        ]);
        $changePassResp->assertRedirect(route('admin.profile.index'));

        $this->post('/logout');
        $loginResp = $this->post('/login', [
            'login' => 'admin_updated',
            'password' => 'new_admin_password_789',
        ]);
        $loginResp->assertRedirect(route('admin.dashboard'));
    }

    public function test_guest_is_redirected_from_all_admin_endpoints(): void
    {
        $routes = [
            route('admin.dashboard'),
            route('admin.packages.index'),
            route('admin.products.index'),
            route('admin.offers.index'),
            route('admin.iptv-packages.index'),
            route('admin.bank-accounts.index'),
            route('admin.employees.index'),
            route('admin.roles.index'),
            route('admin.permissions.index'),
            route('admin.audit-logs.index'),
            route('admin.profile.index'),
        ];

        foreach ($routes as $route) {
            $this->get($route)->assertRedirect(route('login'));
        }
    }

    public function test_employee_is_forbidden_from_all_admin_management_endpoints(): void
    {
        $forbiddenRoutes = [
            route('admin.dashboard'),
            route('admin.packages.index'),
            route('admin.packages.create'),
            route('admin.products.index'),
            route('admin.products.create'),
            route('admin.offers.index'),
            route('admin.offers.create'),
            route('admin.iptv-packages.index'),
            route('admin.bank-accounts.index'),
            route('admin.employees.index'),
            route('admin.employees.create'),
            route('admin.roles.index'),
            route('admin.permissions.index'),
            route('admin.audit-logs.index'),
            route('admin.profile.index'),
        ];

        foreach ($forbiddenRoutes as $route) {
            $this->actingAs($this->employee)->get($route)->assertForbidden();
        }
    }

    public function test_non_existent_entity_returns_404(): void
    {
        $this->actingAs($this->admin)->get('/admin/packages/99999')->assertNotFound();
        $this->actingAs($this->admin)->get('/admin/products/99999')->assertNotFound();
        $this->actingAs($this->admin)->get('/admin/offers/99999')->assertNotFound();
        $this->actingAs($this->admin)->get('/admin/iptv-packages/99999/edit')->assertNotFound();
        $this->actingAs($this->admin)->get('/admin/bank-accounts/99999/edit')->assertNotFound();
        $this->actingAs($this->admin)->get('/admin/employees/99999')->assertNotFound();
    }
}