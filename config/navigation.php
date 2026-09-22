<?php

/**
 * Sidebar navigation items grouped by permission.
 * Visibility is controlled in Blade; route access is enforced by middleware.
 */
return [
    'items' => [
        [
            'label' => 'لوحة التحكم',
            'route' => 'dashboard.redirect',
            'permission' => 'view_dashboard',
            'icon' => 'dashboard',
        ],
        [
            'label' => 'باقات الإنترنت',
            'route' => 'admin.packages.index',
            'permission' => 'manage_packages',
            'roles' => ['admin'],
            'icon' => 'packages',
        ],
        [
            'label' => 'باقات IPTV',
            'route' => 'admin.iptv-packages.index',
            'permission' => 'manage_packages',
            'roles' => ['admin'],
            'icon' => 'tv',
        ],
        [
            'label' => 'المنتجات',
            'route' => 'admin.products.index',
            'permission' => 'manage_products',
            'roles' => ['admin'],
            'icon' => 'products',
        ],
        [
            'label' => 'العروض',
            'route' => 'admin.offers.index',
            'permission' => 'manage_offers',
            'roles' => ['admin'],
            'icon' => 'offers',
        ],
        [
            'label' => 'الحسابات البنكية',
            'route' => 'admin.bank-accounts.index',
            'permission' => 'manage_bank_accounts',
            'roles' => ['admin'],
            'icon' => 'bank',
        ],
        [
            'label' => 'الموظفين',
            'route' => 'admin.employees.index',
            'permission' => 'manage_employees',
            'roles' => ['admin'],
            'icon' => 'employees',
        ],
        [
            'label' => 'الأدوار',
            'route' => 'admin.roles.index',
            'permission' => 'manage_roles',
            'roles' => ['admin'],
            'icon' => 'roles',
        ],
        [
            'label' => 'الصلاحيات',
            'route' => 'admin.permissions.index',
            'permission' => 'manage_roles',
            'roles' => ['admin'],
            'icon' => 'key',
        ],
        [
            'label' => 'طلبات الشحن',
            'route' => 'staff.recharges.index',
            'permission' => 'view_recharges',
            'roles' => ['employee', 'supervisor'],
            'icon' => 'recharges',
        ],
        [
            'label' => 'سجل المدفوعات',
            'route' => 'staff.payments.index',
            'permission' => 'view_payments',
            'roles' => ['employee', 'supervisor', 'admin'],
            'icon' => 'payments',
        ],
        [
            'label' => 'الشكاوى',
            'route' => 'staff.complaints.index',
            'permission' => 'view_complaints',
            'roles' => ['employee', 'supervisor', 'admin'],
            'icon' => 'complaints',
        ],
        [
            'label' => 'المشتركين',
            'route' => 'staff.subscribers.index',
            'permission' => 'view_subscribers',
            'roles' => ['employee', 'supervisor', 'admin'],
            'icon' => 'subscribers',
        ],
        [
            'label' => 'طلبات الاشتراك',
            'route' => 'staff.subscription-requests.index',
            'permission' => 'view_subscription_requests',
            'roles' => ['employee', 'supervisor'],
            'icon' => 'subscription',
        ],
        [
            'label' => 'سجل العمليات',
            'route' => 'admin.audit-logs.index',
            'permission' => 'view_audit_logs',
            'roles' => ['admin', 'supervisor'],
            'icon' => 'audit',
        ],
        [
            'label' => 'الملف الشخصي',
            'route' => 'admin.profile.index',
            'permission' => 'view_dashboard',
            'icon' => 'user',
        ],
    ],
];
