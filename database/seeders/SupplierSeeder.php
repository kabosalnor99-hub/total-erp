<?php

// المسار الكامل: database/seeders/SupplierSeeder.php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name'          => 'شركة توتال للأدوات الكهربائية',
                'company_name'  => 'Total Tools International',
                'phone'         => '+249912345678',
                'email'         => 'supply@totaltools.com',
                'address'       => 'القاهرة — مصر',
                'tax_number'    => 'EG-123456789',
                'payment_terms' => 'net_30',
                'rating'        => 5,
                'balance'       => 0,
                'status'        => 'active',
                'notes'         => 'المورد الرئيسي لمنتجات Total',
            ],
            [
                'name'          => 'مؤسسة الخليج للمعدات',
                'company_name'  => 'Gulf Equipment Est.',
                'phone'         => '+249922345678',
                'email'         => 'orders@gulfequip.net',
                'address'       => 'دبي — الإمارات',
                'tax_number'    => 'AE-987654321',
                'payment_terms' => 'net_15',
                'rating'        => 4,
                'balance'       => 0,
                'status'        => 'active',
                'notes'         => 'موردة أدوات يدوية وكهربائية',
            ],
            [
                'name'          => 'شركة النيل للتجارة',
                'company_name'  => null,
                'phone'         => '+249933456789',
                'email'         => 'nile.trade@gmail.com',
                'address'       => 'الخرطوم — السودان',
                'tax_number'    => 'SD-555444333',
                'payment_terms' => 'cash',
                'rating'        => 3,
                'balance'       => 0,
                'status'        => 'active',
                'notes'         => 'مورد محلي — قطع غيار',
            ],
            [
                'name'          => 'مصنع ستانلي للأدوات',
                'company_name'  => 'Stanley Black & Decker',
                'phone'         => '+249944567890',
                'email'         => 'sales@stanley-me.com',
                'address'       => 'الرياض — المملكة العربية السعودية',
                'tax_number'    => 'SA-321654987',
                'payment_terms' => 'net_60',
                'rating'        => 5,
                'balance'       => 0,
                'status'        => 'active',
                'notes'         => 'أدوات احترافية وصناعية',
            ],
            [
                'name'          => 'شركة الأمل للاستيراد',
                'company_name'  => null,
                'phone'         => '+249955678901',
                'email'         => null,
                'address'       => 'أم درمان — السودان',
                'tax_number'    => null,
                'payment_terms' => 'cash',
                'rating'        => 2,
                'balance'       => 0,
                'status'        => 'inactive',
                'notes'         => 'مورد غير نشط حالياً',
            ],
        ];

        foreach ($suppliers as $data) {
            Supplier::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }

        $this->command->info('✅ تم إدراج ' . count($suppliers) . ' موردين تجريبيين');
    }
}
