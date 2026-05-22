<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // ─── 1. إنشاء الأدوار ────────────────────────────────────────

        $roles = [
            ['name' => 'admin',          'display_name' => 'مدير عام'],
            ['name' => 'finance_manager','display_name' => 'مدير مالي'],
            ['name' => 'stock_manager',  'display_name' => 'مدير مخزون'],
            ['name' => 'sales',          'display_name' => 'موظف مبيعات'],
            ['name' => 'hr',             'display_name' => 'موظف HR'],
            ['name' => 'accountant',     'display_name' => 'محاسب'],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(['name' => $roleData['name']], $roleData);
        }

        // ─── 2. إنشاء الصلاحيات ─────────────────────────────────────

        $permissions = [
            // المستخدمون
            ['name' => 'users.view',    'display_name' => 'عرض المستخدمين',    'module' => 'users'],
            ['name' => 'users.create',  'display_name' => 'إضافة مستخدم',      'module' => 'users'],
            ['name' => 'users.edit',    'display_name' => 'تعديل مستخدم',      'module' => 'users'],
            ['name' => 'users.delete',  'display_name' => 'حذف مستخدم',        'module' => 'users'],

            // المنتجات
            ['name' => 'products.view',   'display_name' => 'عرض المنتجات',   'module' => 'products'],
            ['name' => 'products.create', 'display_name' => 'إضافة منتج',     'module' => 'products'],
            ['name' => 'products.edit',   'display_name' => 'تعديل منتج',     'module' => 'products'],
            ['name' => 'products.delete', 'display_name' => 'حذف منتج',       'module' => 'products'],

            // الفواتير
            ['name' => 'invoices.view',   'display_name' => 'عرض الفواتير',   'module' => 'invoices'],
            ['name' => 'invoices.create', 'display_name' => 'إنشاء فاتورة',   'module' => 'invoices'],
            ['name' => 'invoices.edit',   'display_name' => 'تعديل فاتورة',   'module' => 'invoices'],
            ['name' => 'invoices.delete', 'display_name' => 'حذف فاتورة',     'module' => 'invoices'],

            // العملاء
            ['name' => 'customers.view',   'display_name' => 'عرض العملاء',   'module' => 'customers'],
            ['name' => 'customers.create', 'display_name' => 'إضافة عميل',    'module' => 'customers'],
            ['name' => 'customers.edit',   'display_name' => 'تعديل عميل',    'module' => 'customers'],
            ['name' => 'customers.delete', 'display_name' => 'حذف عميل',      'module' => 'customers'],

            // المخزون
            ['name' => 'stock.view',    'display_name' => 'عرض المخزون',      'module' => 'stock'],
            ['name' => 'stock.adjust',  'display_name' => 'تسوية المخزون',    'module' => 'stock'],

            // المشتريات
            ['name' => 'purchases.view',   'display_name' => 'عرض المشتريات', 'module' => 'purchases'],
            ['name' => 'purchases.create', 'display_name' => 'إنشاء أمر شراء','module' => 'purchases'],
            ['name' => 'purchases.approve','display_name' => 'اعتماد طلب شراء','module' => 'purchases'],

            // الموارد البشرية
            ['name' => 'hr.view',       'display_name' => 'عرض الموظفين',     'module' => 'hr'],
            ['name' => 'hr.create',     'display_name' => 'إضافة موظف',       'module' => 'hr'],
            ['name' => 'hr.edit',       'display_name' => 'تعديل موظف',       'module' => 'hr'],
            ['name' => 'hr.delete',     'display_name' => 'حذف موظف',         'module' => 'hr'],
            ['name' => 'payroll.run',   'display_name' => 'تشغيل الرواتب',    'module' => 'hr'],

            // المحاسبة
            ['name' => 'accounts.view',    'display_name' => 'عرض الحسابات',   'module' => 'accounting'],
            ['name' => 'accounts.create',  'display_name' => 'إضافة حساب',     'module' => 'accounting'],
            ['name' => 'journals.view',    'display_name' => 'عرض القيود',     'module' => 'accounting'],
            ['name' => 'journals.create',  'display_name' => 'إضافة قيد',      'module' => 'accounting'],
            ['name' => 'journals.approve', 'display_name' => 'اعتماد قيود',    'module' => 'accounting'],

            // التقارير
            ['name' => 'reports.view',  'display_name' => 'عرض التقارير',     'module' => 'reports'],
            ['name' => 'reports.export','display_name' => 'تصدير التقارير',   'module' => 'reports'],

            // الإعدادات
            ['name' => 'settings.view', 'display_name' => 'عرض الإعدادات',    'module' => 'settings'],
            ['name' => 'settings.edit', 'display_name' => 'تعديل الإعدادات',  'module' => 'settings'],

            // POS
            ['name' => 'pos.access',    'display_name' => 'الوصول لنقطة البيع','module' => 'pos'],
        ];

        foreach ($permissions as $permData) {
            Permission::firstOrCreate(['name' => $permData['name']], $permData);
        }

        // ─── 3. تعيين الصلاحيات للأدوار ─────────────────────────────

        // المدير المالي
        $financeManager = Role::where('name', 'finance_manager')->first();
        $financeManager->syncPermissions([
            'accounts.view', 'accounts.create',
            'journals.view', 'journals.create', 'journals.approve',
            'reports.view',  'reports.export',
            'invoices.view', 'customers.view',
            'purchases.view',
        ]);

        // مدير المخزون
        $stockManager = Role::where('name', 'stock_manager')->first();
        $stockManager->syncPermissions([
            'products.view', 'products.create', 'products.edit',
            'stock.view',    'stock.adjust',
            'purchases.view', 'purchases.create', 'purchases.approve',
            'reports.view',
        ]);

        // موظف المبيعات
        $sales = Role::where('name', 'sales')->first();
        $sales->syncPermissions([
            'invoices.view', 'invoices.create', 'invoices.edit',
            'customers.view', 'customers.create', 'customers.edit',
            'products.view',
            'pos.access',
        ]);

        // موظف HR
        $hr = Role::where('name', 'hr')->first();
        $hr->syncPermissions([
            'hr.view', 'hr.create', 'hr.edit',
        ]);

        // محاسب
        $accountant = Role::where('name', 'accountant')->first();
        $accountant->syncPermissions([
            'accounts.view', 'accounts.create',
            'journals.view', 'journals.create',
            'reports.view',  'reports.export',
            'payroll.run',
        ]);

        // ─── 4. إنشاء مستخدم المدير العام ───────────────────────────

        $admin = User::firstOrCreate(
            ['email' => 'admin@total-erp.sd'],
            [
                'name'     => 'مدير النظام',
                'email'    => 'admin@total-erp.sd',
                'phone'    => '0900000000',
                'password' => Hash::make('Admin@123'),
                'status'   => 'active',
            ]
        );

        $adminRole = Role::where('name', 'admin')->first();
        $admin->assignRole($adminRole);

        $this->command->info('✅ تم إنشاء الأدوار والصلاحيات ومستخدم المدير العام بنجاح.');
        $this->command->info('   البريد: admin@total-erp.sd | كلمة المرور: Admin@123');
    }
}
