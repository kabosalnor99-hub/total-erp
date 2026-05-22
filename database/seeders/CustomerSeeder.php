<?php

// المسار الكامل: database/seeders/CustomerSeeder.php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();

        $customers = [
            [
                'name'           => 'شركة الخرطوم للمقاولات',
                'phone'          => '0912345678',
                'phone_alt'      => '0912345679',
                'email'          => 'info@khartoum-contracting.sd',
                'address'        => 'الخرطوم بحري، شارع الصناعة',
                'type'           => 'company',
                'company_name'   => 'شركة الخرطوم للمقاولات المحدودة',
                'tax_number'     => 'TAX-2024-001',
                'classification' => 'vip',
                'credit_limit'   => 500000.00,
                'balance'        => 0,
                'is_active'      => true,
                'notes'          => 'عميل VIP — يتعامل بالجملة',
                'created_by'     => $admin?->id,
            ],
            [
                'name'           => 'مؤسسة النيل للكهرباء',
                'phone'          => '0923456789',
                'phone_alt'      => null,
                'email'          => 'nile.electric@gmail.com',
                'address'        => 'أمدرمان، السوق العربي',
                'type'           => 'company',
                'company_name'   => 'مؤسسة النيل للخدمات الكهربائية',
                'tax_number'     => 'TAX-2024-002',
                'classification' => 'vip',
                'credit_limit'   => 300000.00,
                'balance'        => 15000.00,
                'is_active'      => true,
                'notes'          => 'يفضل التواصل عبر الواتساب',
                'created_by'     => $admin?->id,
            ],
            [
                'name'           => 'أحمد محمد علي',
                'phone'          => '0911111111',
                'phone_alt'      => null,
                'email'          => null,
                'address'        => 'الخرطوم، حي الرياض',
                'type'           => 'individual',
                'company_name'   => null,
                'tax_number'     => null,
                'classification' => 'regular',
                'credit_limit'   => 50000.00,
                'balance'        => 5000.00,
                'is_active'      => true,
                'notes'          => null,
                'created_by'     => $admin?->id,
            ],
            [
                'name'           => 'شركة الديم للإنشاءات',
                'phone'          => '0934567890',
                'phone_alt'      => '0934567891',
                'email'          => 'aldaym.construction@outlook.com',
                'address'        => 'الخرطوم، المنطقة الصناعية',
                'type'           => 'company',
                'company_name'   => 'شركة الديم للإنشاءات والمقاولات',
                'tax_number'     => 'TAX-2024-004',
                'classification' => 'regular',
                'credit_limit'   => 200000.00,
                'balance'        => 0,
                'is_active'      => true,
                'notes'          => null,
                'created_by'     => $admin?->id,
            ],
            [
                'name'           => 'محمود إبراهيم حسن',
                'phone'          => '0922222222',
                'phone_alt'      => null,
                'email'          => null,
                'address'        => 'بحري، الحاج يوسف',
                'type'           => 'individual',
                'company_name'   => null,
                'tax_number'     => null,
                'classification' => 'regular',
                'credit_limit'   => 10000.00,
                'balance'        => 2500.00,
                'is_active'      => true,
                'notes'          => null,
                'created_by'     => $admin?->id,
            ],
            [
                'name'           => 'مجموعة التميز التجارية',
                'phone'          => '0945678901',
                'phone_alt'      => '0945678902',
                'email'          => 'tamayoz.group@sd.com',
                'address'        => 'الخرطوم، الدرجة الأولى',
                'type'           => 'company',
                'company_name'   => 'مجموعة التميز للتجارة والاستيراد',
                'tax_number'     => 'TAX-2024-006',
                'classification' => 'vip',
                'credit_limit'   => 1000000.00,
                'balance'        => 0,
                'is_active'      => true,
                'notes'          => 'عميل كبير — شروط دفع خاصة 60 يوم',
                'created_by'     => $admin?->id,
            ],
            [
                'name'           => 'عثمان عبد الله جمعة',
                'phone'          => '0933333333',
                'phone_alt'      => null,
                'email'          => null,
                'address'        => 'أمدرمان، الثورة',
                'type'           => 'individual',
                'company_name'   => null,
                'tax_number'     => null,
                'classification' => 'inactive',
                'credit_limit'   => 0,
                'balance'        => 8000.00,
                'is_active'      => false,
                'notes'          => 'حساب متوقف — مديونية قديمة',
                'created_by'     => $admin?->id,
            ],
            [
                'name'           => 'مصنع الأمانة للبلاستيك',
                'phone'          => '0956789012',
                'phone_alt'      => null,
                'email'          => 'amanah.factory@gmail.com',
                'address'        => 'الخرطوم، المنطقة الصناعية الشمالية',
                'type'           => 'company',
                'company_name'   => 'مصنع الأمانة للبلاستيك والتعبئة',
                'tax_number'     => 'TAX-2024-008',
                'classification' => 'regular',
                'credit_limit'   => 150000.00,
                'balance'        => 0,
                'is_active'      => true,
                'notes'          => null,
                'created_by'     => $admin?->id,
            ],
        ];

        foreach ($customers as $data) {
            Customer::create($data);
        }

        $this->command->info('✅ CustomerSeeder: تم إنشاء ' . count($customers) . ' عميل تجريبي.');
    }
}
