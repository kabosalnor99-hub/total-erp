<?php

// المسار الكامل: database/seeders/AdminUserSeeder.php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * إنشاء مستخدم المدير العام الافتراضي
     * يُشغَّل بعد RolesAndPermissionsSeeder
     */
    public function run(): void
    {
        // ─── المدير العام ─────────────────────────────────────────────
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
        if ($adminRole) {
            $admin->assignRole($adminRole);
        }

        // ─── مستخدم تجريبي: مدير مالي ────────────────────────────────
        $finance = User::firstOrCreate(
            ['email' => 'finance@total-erp.sd'],
            [
                'name'     => 'المدير المالي',
                'email'    => 'finance@total-erp.sd',
                'phone'    => '0911111111',
                'password' => Hash::make('Finance@123'),
                'status'   => 'active',
            ]
        );

        $financeRole = Role::where('name', 'finance_manager')->first();
        if ($financeRole) {
            $finance->assignRole($financeRole);
        }

        // ─── مستخدم تجريبي: موظف مبيعات ─────────────────────────────
        $sales = User::firstOrCreate(
            ['email' => 'sales@total-erp.sd'],
            [
                'name'     => 'موظف المبيعات',
                'email'    => 'sales@total-erp.sd',
                'phone'    => '0922222222',
                'password' => Hash::make('Sales@123'),
                'status'   => 'active',
            ]
        );

        $salesRole = Role::where('name', 'sales')->first();
        if ($salesRole) {
            $sales->assignRole($salesRole);
        }

        // ─── مستخدم تجريبي: مدير مخزون ──────────────────────────────
        $stock = User::firstOrCreate(
            ['email' => 'stock@total-erp.sd'],
            [
                'name'     => 'مدير المخزون',
                'email'    => 'stock@total-erp.sd',
                'phone'    => '0933333333',
                'password' => Hash::make('Stock@123'),
                'status'   => 'active',
            ]
        );

        $stockRole = Role::where('name', 'stock_manager')->first();
        if ($stockRole) {
            $stock->assignRole($stockRole);
        }

        $this->command->info('✅ تم إنشاء المستخدمين التجريبيين:');
        $this->command->table(
            ['الاسم', 'البريد', 'كلمة المرور', 'الدور'],
            [
                ['مدير النظام',    'admin@total-erp.sd',   'Admin@123',   'مدير عام'],
                ['المدير المالي',  'finance@total-erp.sd', 'Finance@123', 'مدير مالي'],
                ['موظف المبيعات', 'sales@total-erp.sd',   'Sales@123',   'موظف مبيعات'],
                ['مدير المخزون',  'stock@total-erp.sd',   'Stock@123',   'مدير مخزون'],
            ]
        );
    }
}
