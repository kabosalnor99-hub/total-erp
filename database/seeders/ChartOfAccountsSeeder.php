<?php

// المسار الكامل: database/seeders/ChartOfAccountsSeeder.php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        // مسح الحسابات الموجودة (للتشغيل مرة واحدة)
        Account::query()->delete();

        $accounts = [

            // ══════════════════════════════════════════════════════
            // 1 — الأصول (Assets)
            // ══════════════════════════════════════════════════════
            ['code'=>'1000','name_ar'=>'الأصول',                     'name_en'=>'Assets',           'type'=>'asset',    'normal_balance'=>'debit',  'parent_code'=>null,  'is_leaf'=>false,'level'=>1],

            // الأصول المتداولة
            ['code'=>'1100','name_ar'=>'الأصول المتداولة',           'name_en'=>'Current Assets',   'type'=>'asset',    'normal_balance'=>'debit',  'parent_code'=>'1000','is_leaf'=>false,'level'=>2],
            ['code'=>'1001','name_ar'=>'الصندوق النقدي',              'name_en'=>'Cash',             'type'=>'asset',    'normal_balance'=>'debit',  'parent_code'=>'1100','is_leaf'=>true, 'level'=>3],
            ['code'=>'1002','name_ar'=>'الحساب البنكي',               'name_en'=>'Bank Account',     'type'=>'asset',    'normal_balance'=>'debit',  'parent_code'=>'1100','is_leaf'=>true, 'level'=>3],
            ['code'=>'1101','name_ar'=>'ذمم مدينة — عملاء',           'name_en'=>'Accounts Receivable','type'=>'asset', 'normal_balance'=>'debit',  'parent_code'=>'1100','is_leaf'=>true, 'level'=>3],
            ['code'=>'1102','name_ar'=>'أوراق قبض',                   'name_en'=>'Notes Receivable', 'type'=>'asset',   'normal_balance'=>'debit',  'parent_code'=>'1100','is_leaf'=>true, 'level'=>3],
            ['code'=>'1201','name_ar'=>'المخزون السلعي',               'name_en'=>'Inventory',        'type'=>'asset',   'normal_balance'=>'debit',  'parent_code'=>'1100','is_leaf'=>true, 'level'=>3],
            ['code'=>'1301','name_ar'=>'مصروفات مقدمة',               'name_en'=>'Prepaid Expenses', 'type'=>'asset',   'normal_balance'=>'debit',  'parent_code'=>'1100','is_leaf'=>true, 'level'=>3],

            // الأصول الثابتة
            ['code'=>'1500','name_ar'=>'الأصول الثابتة',             'name_en'=>'Fixed Assets',     'type'=>'asset',    'normal_balance'=>'debit',  'parent_code'=>'1000','is_leaf'=>false,'level'=>2],
            ['code'=>'1501','name_ar'=>'أثاث ومعدات',                 'name_en'=>'Furniture & Equipment','type'=>'asset','normal_balance'=>'debit', 'parent_code'=>'1500','is_leaf'=>true, 'level'=>3],
            ['code'=>'1502','name_ar'=>'أجهزة كمبيوتر',               'name_en'=>'Computers',        'type'=>'asset',   'normal_balance'=>'debit',  'parent_code'=>'1500','is_leaf'=>true, 'level'=>3],
            ['code'=>'1503','name_ar'=>'سيارات ومركبات',               'name_en'=>'Vehicles',         'type'=>'asset',   'normal_balance'=>'debit',  'parent_code'=>'1500','is_leaf'=>true, 'level'=>3],
            ['code'=>'1599','name_ar'=>'مجمع إهلاك الأصول',           'name_en'=>'Accumulated Depreciation','type'=>'asset','normal_balance'=>'credit','parent_code'=>'1500','is_leaf'=>true,'level'=>3],

            // ══════════════════════════════════════════════════════
            // 2 — الخصوم (Liabilities)
            // ══════════════════════════════════════════════════════
            ['code'=>'2000','name_ar'=>'الخصوم',                     'name_en'=>'Liabilities',       'type'=>'liability','normal_balance'=>'credit','parent_code'=>null,  'is_leaf'=>false,'level'=>1],

            // الخصوم المتداولة
            ['code'=>'2100','name_ar'=>'الخصوم المتداولة',           'name_en'=>'Current Liabilities','type'=>'liability','normal_balance'=>'credit','parent_code'=>'2000','is_leaf'=>false,'level'=>2],
            ['code'=>'2001','name_ar'=>'ذمم دائنة — موردون',         'name_en'=>'Accounts Payable',  'type'=>'liability','normal_balance'=>'credit','parent_code'=>'2100','is_leaf'=>true, 'level'=>3],
            ['code'=>'2002','name_ar'=>'أوراق دفع',                   'name_en'=>'Notes Payable',     'type'=>'liability','normal_balance'=>'credit','parent_code'=>'2100','is_leaf'=>true, 'level'=>3],
            ['code'=>'2003','name_ar'=>'رواتب مستحقة الدفع',          'name_en'=>'Salaries Payable',  'type'=>'liability','normal_balance'=>'credit','parent_code'=>'2100','is_leaf'=>true, 'level'=>3],
            ['code'=>'2004','name_ar'=>'ضريبة القيمة المضافة',        'name_en'=>'VAT Payable',       'type'=>'liability','normal_balance'=>'credit','parent_code'=>'2100','is_leaf'=>true, 'level'=>3],
            ['code'=>'2005','name_ar'=>'دفعات مقدمة من عملاء',        'name_en'=>'Customer Deposits', 'type'=>'liability','normal_balance'=>'credit','parent_code'=>'2100','is_leaf'=>true, 'level'=>3],

            // الخصوم طويلة الأجل
            ['code'=>'2500','name_ar'=>'الخصوم طويلة الأجل',         'name_en'=>'Long-term Liabilities','type'=>'liability','normal_balance'=>'credit','parent_code'=>'2000','is_leaf'=>false,'level'=>2],
            ['code'=>'2501','name_ar'=>'قروض بنكية طويلة الأجل',     'name_en'=>'Long-term Loans',   'type'=>'liability','normal_balance'=>'credit','parent_code'=>'2500','is_leaf'=>true, 'level'=>3],

            // ══════════════════════════════════════════════════════
            // 3 — حقوق الملكية (Equity)
            // ══════════════════════════════════════════════════════
            ['code'=>'3000','name_ar'=>'حقوق الملكية',               'name_en'=>'Equity',            'type'=>'equity',   'normal_balance'=>'credit','parent_code'=>null,  'is_leaf'=>false,'level'=>1],
            ['code'=>'3001','name_ar'=>'رأس المال',                   'name_en'=>'Capital',           'type'=>'equity',   'normal_balance'=>'credit','parent_code'=>'3000','is_leaf'=>true, 'level'=>2],
            ['code'=>'3002','name_ar'=>'الأرباح المدورة',              'name_en'=>'Retained Earnings', 'type'=>'equity',   'normal_balance'=>'credit','parent_code'=>'3000','is_leaf'=>true, 'level'=>2],
            ['code'=>'3003','name_ar'=>'مسحوبات صاحب العمل',          'name_en'=>'Owner Drawings',    'type'=>'equity',   'normal_balance'=>'debit', 'parent_code'=>'3000','is_leaf'=>true, 'level'=>2],

            // ══════════════════════════════════════════════════════
            // 4 — الإيرادات (Revenue)
            // ══════════════════════════════════════════════════════
            ['code'=>'4000','name_ar'=>'الإيرادات',                  'name_en'=>'Revenue',           'type'=>'revenue',  'normal_balance'=>'credit','parent_code'=>null,  'is_leaf'=>false,'level'=>1],
            ['code'=>'4001','name_ar'=>'إيرادات المبيعات',            'name_en'=>'Sales Revenue',     'type'=>'revenue',  'normal_balance'=>'credit','parent_code'=>'4000','is_leaf'=>true, 'level'=>2],
            ['code'=>'4002','name_ar'=>'إيرادات مبيعات POS',         'name_en'=>'POS Sales Revenue', 'type'=>'revenue',  'normal_balance'=>'credit','parent_code'=>'4000','is_leaf'=>true, 'level'=>2],
            ['code'=>'4003','name_ar'=>'مرتجعات المبيعات',            'name_en'=>'Sales Returns',     'type'=>'revenue',  'normal_balance'=>'debit', 'parent_code'=>'4000','is_leaf'=>true, 'level'=>2],
            ['code'=>'4004','name_ar'=>'إيرادات أخرى',               'name_en'=>'Other Revenue',     'type'=>'revenue',  'normal_balance'=>'credit','parent_code'=>'4000','is_leaf'=>true, 'level'=>2],

            // ══════════════════════════════════════════════════════
            // 5 — المصروفات (Expenses)
            // ══════════════════════════════════════════════════════
            ['code'=>'5000','name_ar'=>'المصروفات',                   'name_en'=>'Expenses',          'type'=>'expense',  'normal_balance'=>'debit', 'parent_code'=>null,  'is_leaf'=>false,'level'=>1],

            // تكلفة المبيعات
            ['code'=>'5001','name_ar'=>'تكلفة البضاعة المباعة',       'name_en'=>'COGS',              'type'=>'expense',  'normal_balance'=>'debit', 'parent_code'=>'5000','is_leaf'=>true, 'level'=>2],
            ['code'=>'5101','name_ar'=>'مشتريات البضاعة',              'name_en'=>'Purchases',         'type'=>'expense',  'normal_balance'=>'debit', 'parent_code'=>'5000','is_leaf'=>true, 'level'=>2],
            ['code'=>'5102','name_ar'=>'مصروفات الشحن والاستيراد',    'name_en'=>'Freight & Import',  'type'=>'expense',  'normal_balance'=>'debit', 'parent_code'=>'5000','is_leaf'=>true, 'level'=>2],

            // المصروفات التشغيلية
            ['code'=>'5200','name_ar'=>'المصروفات التشغيلية',          'name_en'=>'Operating Expenses','type'=>'expense',  'normal_balance'=>'debit', 'parent_code'=>'5000','is_leaf'=>false,'level'=>2],
            ['code'=>'5201','name_ar'=>'مصاريف الرواتب',               'name_en'=>'Salaries Expense',  'type'=>'expense',  'normal_balance'=>'debit', 'parent_code'=>'5200','is_leaf'=>true, 'level'=>3],
            ['code'=>'5202','name_ar'=>'مصاريف الإيجار',               'name_en'=>'Rent Expense',      'type'=>'expense',  'normal_balance'=>'debit', 'parent_code'=>'5200','is_leaf'=>true, 'level'=>3],
            ['code'=>'5203','name_ar'=>'مصاريف الكهرباء والمياه',      'name_en'=>'Utilities',         'type'=>'expense',  'normal_balance'=>'debit', 'parent_code'=>'5200','is_leaf'=>true, 'level'=>3],
            ['code'=>'5204','name_ar'=>'مصاريف الاتصالات',             'name_en'=>'Communications',    'type'=>'expense',  'normal_balance'=>'debit', 'parent_code'=>'5200','is_leaf'=>true, 'level'=>3],
            ['code'=>'5205','name_ar'=>'مصاريف الصيانة',               'name_en'=>'Maintenance',       'type'=>'expense',  'normal_balance'=>'debit', 'parent_code'=>'5200','is_leaf'=>true, 'level'=>3],
            ['code'=>'5206','name_ar'=>'مصاريف التسويق والإعلان',     'name_en'=>'Marketing',         'type'=>'expense',  'normal_balance'=>'debit', 'parent_code'=>'5200','is_leaf'=>true, 'level'=>3],
            ['code'=>'5207','name_ar'=>'مصاريف النقل والمواصلات',     'name_en'=>'Transportation',    'type'=>'expense',  'normal_balance'=>'debit', 'parent_code'=>'5200','is_leaf'=>true, 'level'=>3],
            ['code'=>'5208','name_ar'=>'مصاريف القرطاسية والمطبوعات', 'name_en'=>'Stationery',        'type'=>'expense',  'normal_balance'=>'debit', 'parent_code'=>'5200','is_leaf'=>true, 'level'=>3],
            ['code'=>'5209','name_ar'=>'مصاريف التأمين',               'name_en'=>'Insurance',         'type'=>'expense',  'normal_balance'=>'debit', 'parent_code'=>'5200','is_leaf'=>true, 'level'=>3],
            ['code'=>'5210','name_ar'=>'مصاريف الإهلاك',               'name_en'=>'Depreciation',      'type'=>'expense',  'normal_balance'=>'debit', 'parent_code'=>'5200','is_leaf'=>true, 'level'=>3],
            ['code'=>'5299','name_ar'=>'مصاريف أخرى متنوعة',          'name_en'=>'Miscellaneous',     'type'=>'expense',  'normal_balance'=>'debit', 'parent_code'=>'5200','is_leaf'=>true, 'level'=>3],
        ];

        // إنشاء map لربط الكود بالـ ID
        $codeToId = [];

        foreach ($accounts as $data) {
            $parentId = null;
            if ($data['parent_code'] && isset($codeToId[$data['parent_code']])) {
                $parentId = $codeToId[$data['parent_code']];
            }

            $account = Account::create([
                'code'                 => $data['code'],
                'name_ar'              => $data['name_ar'],
                'name_en'              => $data['name_en'],
                'type'                 => $data['type'],
                'normal_balance'       => $data['normal_balance'],
                'parent_id'            => $parentId,
                'level'                => $data['level'],
                'is_leaf'              => $data['is_leaf'],
                'is_active'            => true,
                'opening_balance'      => 0,
                'opening_balance_type' => $data['normal_balance'],
            ]);

            $codeToId[$data['code']] = $account->id;
        }

        $this->command->info('✅ تم إنشاء ' . count($accounts) . ' حساباً في دليل الحسابات.');
    }
}
