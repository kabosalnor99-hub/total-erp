<?php

// المسار الكامل: database/seeders/ProductSeeder.php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // ─── الفئات الرئيسية لتوتال الكلاكلة ──────────────────────────

        $cats = [
            [
                'name_ar'    => 'أدوات كهربائية',
                'name_en'    => 'Power Tools',
                'icon'       => 'fas fa-bolt',
                'color'      => '#F59E0B',
                'sort_order' => 1,
                'children'   => ['مثاقب كهربائية', 'مناشير كهربائية', 'جلاخات'],
            ],
            [
                'name_ar'    => 'أدوات يدوية',
                'name_en'    => 'Hand Tools',
                'icon'       => 'fas fa-tools',
                'color'      => '#3B82F6',
                'sort_order' => 2,
                'children'   => ['مفكات', 'مطارق', 'مقاسات وشرائط'],
            ],
            [
                'name_ar'    => 'معدات',
                'name_en'    => 'Equipment',
                'icon'       => 'fas fa-cogs',
                'color'      => '#6366F1',
                'sort_order' => 3,
                'children'   => ['ضواغط هواء', 'مولدات كهربائية'],
            ],
            [
                'name_ar'    => 'قطع غيار',
                'name_en'    => 'Spare Parts',
                'icon'       => 'fas fa-puzzle-piece',
                'color'      => '#10B981',
                'sort_order' => 4,
                'children'   => [],
            ],
        ];

        $categoryIds = [];

        foreach ($cats as $catData) {
            $children = $catData['children'];
            unset($catData['children']);

            $parent = Category::firstOrCreate(
                ['name_ar' => $catData['name_ar']],
                $catData
            );

            $categoryIds[$catData['name_ar']] = $parent->id;

            foreach ($children as $childName) {
                $child = Category::firstOrCreate(
                    ['name_ar' => $childName, 'parent_id' => $parent->id],
                    ['name_ar' => $childName, 'parent_id' => $parent->id, 'is_active' => true]
                );
                $categoryIds[$childName] = $child->id;
            }
        }

        // ─── المستودع الافتراضي ────────────────────────────────────────

        $warehouse = Warehouse::firstOrCreate(
            ['name' => 'المستودع الرئيسي'],
            [
                'name'         => 'المستودع الرئيسي',
                'code'         => 'WH-01',
                'location'     => 'الكلاكلة، الخرطوم',
                'manager_name' => 'مدير المستودع',
                'is_default'   => true,
                'is_active'    => true,
            ]
        );

        // ─── قائمة المنتجات التجريبية ──────────────────────────────────

        $products = [
            // أدوات كهربائية
            [
                'sku'            => 'TL-00001',
                'barcode'        => '6281234560001',
                'name_ar'        => 'مثقاب كهربائي توتال 850W',
                'name_en'        => 'Total Electric Drill 850W',
                'category_id'    => $categoryIds['مثاقب كهربائية'],
                'brand'          => 'Total',
                'unit'           => 'قطعة',
                'purchase_price' => 180.00,
                'sale_price'     => 250.00,
                'reorder_point'  => 5,
                'type'           => 'power_tools',
                'quantity'       => 30,
            ],
            [
                'sku'            => 'TL-00002',
                'barcode'        => '6281234560002',
                'name_ar'        => 'مثقاب مطرقة توتال 1050W',
                'name_en'        => 'Total Hammer Drill 1050W',
                'category_id'    => $categoryIds['مثاقب كهربائية'],
                'brand'          => 'Total',
                'unit'           => 'قطعة',
                'purchase_price' => 280.00,
                'sale_price'     => 380.00,
                'reorder_point'  => 3,
                'type'           => 'power_tools',
                'quantity'       => 15,
            ],
            [
                'sku'            => 'TL-00003',
                'barcode'        => '6281234560003',
                'name_ar'        => 'جلاخة زاوية توتال 125mm 900W',
                'name_en'        => 'Total Angle Grinder 125mm 900W',
                'category_id'    => $categoryIds['جلاخات'],
                'brand'          => 'Total',
                'unit'           => 'قطعة',
                'purchase_price' => 150.00,
                'sale_price'     => 210.00,
                'reorder_point'  => 5,
                'type'           => 'power_tools',
                'quantity'       => 25,
            ],
            [
                'sku'            => 'TL-00004',
                'barcode'        => '6281234560004',
                'name_ar'        => 'منشار دائري توتال 185mm 1200W',
                'name_en'        => 'Total Circular Saw 185mm 1200W',
                'category_id'    => $categoryIds['مناشير كهربائية'],
                'brand'          => 'Total',
                'unit'           => 'قطعة',
                'purchase_price' => 320.00,
                'sale_price'     => 450.00,
                'reorder_point'  => 3,
                'type'           => 'power_tools',
                'quantity'       => 10,
            ],
            [
                'sku'            => 'TL-00005',
                'barcode'        => '6281234560005',
                'name_ar'        => 'مفك كهربائي توتال 12V',
                'name_en'        => 'Total Cordless Screwdriver 12V',
                'category_id'    => $categoryIds['أدوات كهربائية'],
                'brand'          => 'Total',
                'unit'           => 'قطعة',
                'purchase_price' => 220.00,
                'sale_price'     => 310.00,
                'reorder_point'  => 4,
                'type'           => 'power_tools',
                'quantity'       => 20,
            ],

            // أدوات يدوية
            [
                'sku'            => 'TL-00006',
                'barcode'        => '6281234560006',
                'name_ar'        => 'طقم مفكات توتال 6 قطع',
                'name_en'        => 'Total Screwdriver Set 6pcs',
                'category_id'    => $categoryIds['مفكات'],
                'brand'          => 'Total',
                'unit'           => 'طقم',
                'purchase_price' => 35.00,
                'sale_price'     => 55.00,
                'reorder_point'  => 10,
                'type'           => 'hand_tools',
                'quantity'       => 50,
            ],
            [
                'sku'            => 'TL-00007',
                'barcode'        => '6281234560007',
                'name_ar'        => 'مطرقة توتال 500 جرام',
                'name_en'        => 'Total Hammer 500g',
                'category_id'    => $categoryIds['مطارق'],
                'brand'          => 'Total',
                'unit'           => 'قطعة',
                'purchase_price' => 25.00,
                'sale_price'     => 40.00,
                'reorder_point'  => 15,
                'type'           => 'hand_tools',
                'quantity'       => 60,
            ],
            [
                'sku'            => 'TL-00008',
                'barcode'        => '6281234560008',
                'name_ar'        => 'شريط قياس توتال 5 متر',
                'name_en'        => 'Total Measuring Tape 5m',
                'category_id'    => $categoryIds['مقاسات وشرائط'],
                'brand'          => 'Total',
                'unit'           => 'قطعة',
                'purchase_price' => 15.00,
                'sale_price'     => 25.00,
                'reorder_point'  => 20,
                'type'           => 'hand_tools',
                'quantity'       => 80,
            ],

            // معدات
            [
                'sku'            => 'TL-00009',
                'barcode'        => '6281234560009',
                'name_ar'        => 'ضاغط هواء توتال 50 لتر 2HP',
                'name_en'        => 'Total Air Compressor 50L 2HP',
                'category_id'    => $categoryIds['ضواغط هواء'],
                'brand'          => 'Total',
                'unit'           => 'قطعة',
                'purchase_price' => 850.00,
                'sale_price'     => 1150.00,
                'reorder_point'  => 2,
                'type'           => 'equipment',
                'quantity'       => 8,
            ],
            [
                'sku'            => 'TL-00010',
                'barcode'        => '6281234560010',
                'name_ar'        => 'مولد كهربائي توتال 2500W',
                'name_en'        => 'Total Generator 2500W',
                'category_id'    => $categoryIds['مولدات كهربائية'],
                'brand'          => 'Total',
                'unit'           => 'قطعة',
                'purchase_price' => 1200.00,
                'sale_price'     => 1650.00,
                'reorder_point'  => 2,
                'type'           => 'equipment',
                'quantity'       => 5,
            ],

            // قطع غيار
            [
                'sku'            => 'TL-00011',
                'barcode'        => '6281234560011',
                'name_ar'        => 'قرص جلخ 125mm للجلاخة',
                'name_en'        => 'Grinding Disc 125mm',
                'category_id'    => $categoryIds['قطع غيار'],
                'brand'          => 'Total',
                'unit'           => 'قطعة',
                'purchase_price' => 3.50,
                'sale_price'     => 6.00,
                'reorder_point'  => 50,
                'type'           => 'spare_parts',
                'quantity'       => 200,
            ],
            [
                'sku'            => 'TL-00012',
                'barcode'        => '6281234560012',
                'name_ar'        => 'مجموعة مثاقب HSS توتال 13 قطعة',
                'name_en'        => 'Total HSS Drill Bits Set 13pcs',
                'category_id'    => $categoryIds['قطع غيار'],
                'brand'          => 'Total',
                'unit'           => 'طقم',
                'purchase_price' => 45.00,
                'sale_price'     => 70.00,
                'reorder_point'  => 10,
                'type'           => 'spare_parts',
                'quantity'       => 35,
            ],
            // منتج حرج للتجربة (مخزون منخفض)
            [
                'sku'            => 'TL-00013',
                'barcode'        => '6281234560013',
                'name_ar'        => 'ورق صنفرة توتال 240 حبة',
                'name_en'        => 'Total Sandpaper 240 Grit',
                'category_id'    => $categoryIds['قطع غيار'],
                'brand'          => 'Total',
                'unit'           => 'ورقة',
                'purchase_price' => 0.50,
                'sale_price'     => 1.00,
                'reorder_point'  => 100,
                'type'           => 'spare_parts',
                'quantity'       => 30,  // أقل من reorder_point — مخزون حرج
            ],
        ];

        // ─── إنشاء المنتجات مع حركات المخزون الابتدائية ──────────────

        foreach ($products as $productData) {
            $qty = $productData['quantity'];
            unset($productData['quantity']);

            // حساب هامش الربح
            $productData['profit_margin'] = Product::calcProfitMargin(
                $productData['purchase_price'],
                $productData['sale_price']
            );

            $productData['is_active']  = true;
            $productData['created_by'] = 1; // المدير

            $product = Product::firstOrCreate(
                ['sku' => $productData['sku']],
                $productData
            );

            // إضافة مخزون ابتدائي إن لم يكن موجوداً
            if ($product->wasRecentlyCreated && $qty > 0) {
                StockMovement::record(
                    $product->id,
                    $warehouse->id,
                    'in',
                    $qty,
                    [
                        'notes'     => 'مخزون ابتدائي — بيانات تجريبية',
                        'unit_cost' => $product->purchase_price,
                        'user_id'   => 1,
                    ]
                );
            }
        }

        $this->command->info('✅ تم إنشاء ' . count($products) . ' منتج تجريبي مع مخزونهم الابتدائي.');
    }
}
