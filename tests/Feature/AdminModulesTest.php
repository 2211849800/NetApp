<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\IptvPackage;
use App\Models\Offer;
use App\Models\Package;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\StaffUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(StaffUserSeeder::class);
    }

    private function admin(): User
    {
        return User::where('email', 'admin@netapp.com')->firstOrFail();
    }

    private function employee(): User
    {
        return User::where('email', 'employee@example.com')->firstOrFail();
    }

    public function test_admin_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->admin())
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('لوحة تحكم المدير');
    }

    public function test_packages_crud_and_toggle_status(): void
    {
        $admin = $this->admin();

        // Create
        $response = $this->actingAs($admin)->post(route('admin.packages.store'), [
            'name' => 'باقة التوفير 50G',
            'description' => 'باقة سرعة 20 ميجا',
            'price' => 75.00,
            'data_allowance' => '50 GB',
            'duration_days' => 30,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.packages.index'));
        $this->assertDatabaseHas('packages', ['name' => 'باقة التوفير 50G']);

        $package = Package::where('name', 'باقة التوفير 50G')->firstOrFail();

        // Show & Edit
        $this->actingAs($admin)->get(route('admin.packages.show', $package))->assertOk();
        $this->actingAs($admin)->get(route('admin.packages.edit', $package))->assertOk();

        // Update
        $this->actingAs($admin)->put(route('admin.packages.update', $package), [
            'name' => 'باقة التوفير 100G',
            'price' => 120.00,
            'duration_days' => 30,
            'status' => 'active',
        ])->assertRedirect(route('admin.packages.index'));

        // Toggle status
        $this->actingAs($admin)->patch(route('admin.packages.toggle-status', $package))->assertRedirect();
        $this->assertEquals('inactive', $package->fresh()->status->value);
    }

    public function test_products_crud_and_toggle_status(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'راوتر هواوي 5G',
            'description' => 'راوتر يدعم جميع الشبكات',
            'price' => 350.00,
            'is_available' => 1,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $product = Product::where('name', 'راوتر هواوي 5G')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.products.show', $product))->assertOk();
        $this->actingAs($admin)->patch(route('admin.products.toggle-status', $product))->assertRedirect();
        $this->assertEquals('inactive', $product->fresh()->status->value);
    }

    public function test_offers_crud_and_toggle_status(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post(route('admin.offers.store'), [
            'name' => 'عرض بداية السنة',
            'description' => 'خصم 25%',
            'starts_at' => now()->format('Y-m-d'),
            'ends_at' => now()->addMonth()->format('Y-m-d'),
            'discount_percent' => 25.00,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.offers.index'));
        $offer = Offer::where('name', 'عرض بداية السنة')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.offers.show', $offer))->assertOk();
        $this->actingAs($admin)->patch(route('admin.offers.toggle-status', $offer))->assertRedirect();
        $this->assertEquals('inactive', $offer->fresh()->status->value);
    }

    public function test_iptv_packages_crud_and_toggle_status(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post(route('admin.iptv-packages.store'), [
            'name' => 'باقة IPTV VIP',
            'description' => 'شاملة القنوات الرياضية',
            'price' => 50.00,
            'duration_days' => 30,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.iptv-packages.index'));
        $iptv = IptvPackage::where('name', 'باقة IPTV VIP')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.iptv-packages.edit', $iptv))->assertOk();
        $this->actingAs($admin)->patch(route('admin.iptv-packages.toggle-status', $iptv))->assertRedirect();
        $this->assertEquals('inactive', $iptv->fresh()->status->value);
    }

    public function test_bank_accounts_crud_and_toggle_status(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post(route('admin.bank-accounts.store'), [
            'bank_name' => 'مصرف أمان',
            'account_name' => 'شركة ميقا للاتصالات',
            'account_number' => '123456789',
            'transfer_instructions' => 'إرفاق إيصال السداد',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.bank-accounts.index'));
        $bank = BankAccount::where('account_number', '123456789')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.bank-accounts.edit', $bank))->assertOk();
        $this->actingAs($admin)->patch(route('admin.bank-accounts.toggle-status', $bank))->assertRedirect();
        $this->assertEquals('inactive', $bank->fresh()->status->value);
    }

    public function test_roles_and_permissions_view_and_update(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.roles.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.permissions.index'))->assertOk();

        $employeeRole = Role::where('name', 'employee')->firstOrFail();

        $response = $this->actingAs($admin)->put(route('admin.roles.update', $employeeRole), [
            'permissions' => ['view_dashboard', 'view_subscribers'],
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $this->assertTrue($employeeRole->fresh()->permissions->contains('name', 'view_subscribers'));
    }

    public function test_audit_logs_view_and_show(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.audit-logs.index'))->assertOk();
    }

    public function test_admin_profile_update_and_password_change(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.profile.index'))->assertOk();

        $this->actingAs($admin)->put(route('admin.profile.update'), [
            'name' => 'مدير النظام المعدل',
            'username' => 'admin_super',
            'phone' => '0920000000',
            'email' => 'admin_super@netapp.com',
        ])->assertRedirect(route('admin.profile.index'));

        $this->assertDatabaseHas('users', [
            'username' => 'admin_super',
            'phone' => '0920000000',
        ]);
    }

    public function test_employee_is_forbidden_from_admin_modules(): void
    {
        $employee = $this->employee();

        $this->actingAs($employee)->get(route('admin.packages.index'))->assertForbidden();
        $this->actingAs($employee)->get(route('admin.products.index'))->assertForbidden();
        $this->actingAs($employee)->get(route('admin.offers.index'))->assertForbidden();
        $this->actingAs($employee)->get(route('admin.bank-accounts.index'))->assertForbidden();
        $this->actingAs($employee)->get(route('admin.roles.index'))->assertForbidden();
        $this->actingAs($employee)->get(route('admin.audit-logs.index'))->assertForbidden();
    }
}
