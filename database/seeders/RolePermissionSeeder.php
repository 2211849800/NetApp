<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seeds the initial RBAC roles and permissions.
 *
 * Roles: admin, supervisor, employee, subscriber
 * Permissions are organized by group (recharges, complaints, packages, etc.)
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ──────────────────────────────────────────
        // Create Permissions (grouped for clarity)
        // ──────────────────────────────────────────

        $permissions = [
            // Recharges
            ['name' => 'view_recharges', 'display_name' => 'View Recharges', 'group' => 'recharges'],
            ['name' => 'approve_recharges', 'display_name' => 'Approve Recharges', 'group' => 'recharges'],
            ['name' => 'reject_recharges', 'display_name' => 'Reject Recharges', 'group' => 'recharges'],

            // Complaints
            ['name' => 'view_complaints', 'display_name' => 'View Complaints', 'group' => 'complaints'],
            ['name' => 'manage_complaints', 'display_name' => 'Manage Complaints', 'group' => 'complaints'],

            // Packages
            ['name' => 'view_packages', 'display_name' => 'View Packages', 'group' => 'packages'],
            ['name' => 'manage_packages', 'display_name' => 'Manage Packages', 'group' => 'packages'],

            // Products
            ['name' => 'view_products', 'display_name' => 'View Products', 'group' => 'products'],
            ['name' => 'manage_products', 'display_name' => 'Manage Products', 'group' => 'products'],

            // Offers
            ['name' => 'view_offers', 'display_name' => 'View Offers', 'group' => 'offers'],
            ['name' => 'manage_offers', 'display_name' => 'Manage Offers', 'group' => 'offers'],

            // Subscribers
            ['name' => 'view_subscribers', 'display_name' => 'View Subscribers', 'group' => 'subscribers'],
            ['name' => 'manage_subscribers', 'display_name' => 'Manage Subscribers', 'group' => 'subscribers'],

            // Payments
            ['name' => 'view_payments', 'display_name' => 'View Payments', 'group' => 'payments'],

            // Bank Accounts
            ['name' => 'manage_bank_accounts', 'display_name' => 'Manage Bank Accounts', 'group' => 'bank_accounts'],

            // Employees
            ['name' => 'view_employees', 'display_name' => 'View Employees', 'group' => 'employees'],
            ['name' => 'manage_employees', 'display_name' => 'Manage Employees', 'group' => 'employees'],

            // Roles
            ['name' => 'view_roles', 'display_name' => 'View Roles', 'group' => 'roles'],
            ['name' => 'manage_roles', 'display_name' => 'Manage Roles', 'group' => 'roles'],

            // Subscription Requests
            ['name' => 'view_subscription_requests', 'display_name' => 'View Subscription Requests', 'group' => 'subscription_requests'],
            ['name' => 'manage_subscription_requests', 'display_name' => 'Manage Subscription Requests', 'group' => 'subscription_requests'],

            // Transfer Requests
            ['name' => 'view_transfer_requests', 'display_name' => 'View Transfer Requests', 'group' => 'transfer_requests'],
            ['name' => 'manage_transfer_requests', 'display_name' => 'Manage Transfer Requests', 'group' => 'transfer_requests'],

            // Audit Logs
            ['name' => 'view_audit_logs', 'display_name' => 'View Audit Logs', 'group' => 'audit_logs'],

            // Notifications
            ['name' => 'send_notifications', 'display_name' => 'Send Notifications', 'group' => 'notifications'],

            // Dashboard
            ['name' => 'view_dashboard', 'display_name' => 'View Dashboard', 'group' => 'dashboard'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                $permissionData,
            );
        }

        // ──────────────────────────────────────────
        // Create Roles
        // ──────────────────────────────────────────

        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrator',
                'description' => 'Full system access. All permissions granted implicitly.',
                'is_system' => true,
            ],
        );

        $supervisorRole = Role::firstOrCreate(
            ['name' => 'supervisor'],
            [
                'display_name' => 'Supervisor',
                'description' => 'Oversees employees and manages operational workflows.',
                'is_system' => true,
            ],
        );

        $employeeRole = Role::firstOrCreate(
            ['name' => 'employee'],
            [
                'display_name' => 'Employee',
                'description' => 'Handles subscriber requests and daily operations.',
                'is_system' => true,
            ],
        );

        $subscriberRole = Role::firstOrCreate(
            ['name' => 'subscriber'],
            [
                'display_name' => 'Subscriber',
                'description' => 'ISP customer with access to subscriber features.',
                'is_system' => true,
            ],
        );

        // ──────────────────────────────────────────
        // Assign Permissions to Roles
        //
        // Admin gets ALL permissions implicitly via HasRoles trait,
        // so we don't assign them here.
        // ──────────────────────────────────────────

        $supervisorPermissions = Permission::whereIn('name', [
            'view_recharges', 'approve_recharges', 'reject_recharges',
            'view_complaints', 'manage_complaints',
            'view_packages',
            'view_products',
            'view_offers',
            'view_subscribers', 'manage_subscribers',
            'view_payments',
            'view_employees',
            'view_subscription_requests', 'manage_subscription_requests',
            'view_transfer_requests', 'manage_transfer_requests',
            'view_audit_logs',
            'send_notifications',
            'view_dashboard',
        ])->pluck('id');

        $supervisorRole->permissions()->syncWithoutDetaching($supervisorPermissions);

        $employeePermissions = Permission::whereIn('name', [
            'view_recharges', 'approve_recharges', 'reject_recharges',
            'view_complaints', 'manage_complaints',
            'view_packages',
            'view_products',
            'view_offers',
            'view_subscribers', 'manage_subscribers',
            'view_payments',
            'view_subscription_requests', 'manage_subscription_requests',
            'view_transfer_requests', 'manage_transfer_requests',
            'view_dashboard',
        ])->pluck('id');

        $employeeRole->permissions()->syncWithoutDetaching($employeePermissions);

        // Subscribers don't need role-based permissions;
        // their access is controlled by user type middleware.
    }
}
