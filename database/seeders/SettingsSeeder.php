<?php

// المسار: database/seeders/SettingsSeeder.php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [

            // ================================================================
            // GROUP: general
            // ================================================================
            [
                'key'            => 'app_name',
                'value'          => 'توتال الكلاكلة',
                'type'           => 'string',
                'group'          => 'general',
                'label_ar'       => 'اسم التطبيق',
                'label_en'       => 'Application Name',
                'is_public'      => true,
                'is_editable'    => true,
                'sort_order'     => 1,
            ],
            [
                'key'            => 'app_locale',
                'value'          => 'ar',
                'type'           => 'string',
                'group'          => 'general',
                'label_ar'       => 'اللغة الافتراضية',
                'label_en'       => 'Default Language',
                'is_public'      => true,
                'is_editable'    => true,
                'sort_order'     => 2,
            ],
            [
                'key'            => 'timezone',
                'value'          => 'Africa/Khartoum',
                'type'           => 'string',
                'group'          => 'general',
                'label_ar'       => 'المنطقة الزمنية',
                'label_en'       => 'Timezone',
                'is_public'      => false,
                'is_editable'    => true,
                'sort_order'     => 3,
            ],
            [
                'key'            => 'currency',
                'value'          => 'SDG',
                'type'           => 'string',
                'group'          => 'general',
                'label_ar'       => 'العملة',
                'label_en'       => 'Currency',
                'is_public'      => true,
                'is_editable'    => true,
                'sort_order'     => 4,
            ],
            [
                'key'            => 'currency_symbol',
                'value'          => 'ج.س',
                'type'           => 'string',
                'group'          => 'general',
                'label_ar'       => 'رمز العملة',
                'label_en'       => 'Currency Symbol',
                'is_public'      => true,
                'is_editable'    => true,
                'sort_order'     => 5,
            ],
            [
                'key'            => 'date_format',
                'value'          => 'Y-m-d',
                'type'           => 'string',
                'group'          => 'general',
                'label_ar'       => 'صيغة التاريخ',
                'label_en'       => 'Date Format',
                'is_public'      => true,
                'is_editable'    => true,
                'sort_order'     => 6,
            ],

            // ================================================================
            // GROUP: company
            // ================================================================
            [
                'key'            => 'company_name_ar',
                'value'          => 'توتال الكلاكلة',
                'type'           => 'string',
                'group'          => 'company',
                'label_ar'       => 'اسم الشركة (عربي)',
                'label_en'       => 'Company Name (Arabic)',
                'is_public'      => true,
                'is_editable'    => true,
                'sort_order'     => 1,
            ],
            [
                'key'            => 'company_name_en',
                'value'          => 'Total Al-Kalaklah',
                'type'           => 'string',
                'group'          => 'company',
                'label_ar'       => 'اسم الشركة (إنجليزي)',
                'label_en'       => 'Company Name (English)',
                'is_public'      => true,
                'is_editable'    => true,
                'sort_order'     => 2,
            ],
            [
                'key'            => 'company_address',
                'value'          => 'الكلاكلة — الخرطوم، السودان',
                'type'           => 'string',
                'group'          => 'company',
                'label_ar'       => 'عنوان الشركة',
                'label_en'       => 'Company Address',
                'is_public'      => true,
                'is_editable'    => true,
                'sort_order'     => 3,
            ],
            [
                'key'            => 'company_phone',
                'value'          => '+249-912-000000',
                'type'           => 'string',
                'group'          => 'company',
                'label_ar'       => 'هاتف الشركة',
                'label_en'       => 'Company Phone',
                'is_public'      => true,
                'is_editable'    => true,
                'sort_order'     => 4,
            ],
            [
                'key'            => 'company_email',
                'value'          => 'info@total-kalaklah.sd',
                'type'           => 'string',
                'group'          => 'company',
                'label_ar'       => 'البريد الإلكتروني',
                'label_en'       => 'Company Email',
                'is_public'      => true,
                'is_editable'    => true,
                'sort_order'     => 5,
            ],
            [
                'key'            => 'company_logo',
                'value'          => null,
                'type'           => 'file',
                'group'          => 'company',
                'label_ar'       => 'شعار الشركة',
                'label_en'       => 'Company Logo',
                'is_public'      => true,
                'is_editable'    => true,
                'sort_order'     => 6,
            ],
            [
                'key'            => 'company_tax_number',
                'value'          => '',
                'type'           => 'string',
                'group'          => 'company',
                'label_ar'       => 'الرقم الضريبي',
                'label_en'       => 'Tax Registration Number',
                'is_public'      => false,
                'is_editable'    => true,
                'sort_order'     => 7,
            ],

            // ================================================================
            // GROUP: invoice
            // ================================================================
            [
                'key'            => 'invoice_prefix',
                'value'          => 'INV',
                'type'           => 'string',
                'group'          => 'invoice',
                'label_ar'       => 'بادئة رقم الفاتورة',
                'label_en'       => 'Invoice Number Prefix',
                'is_public'      => false,
                'is_editable'    => true,
                'sort_order'     => 1,
            ],
            [
                'key'            => 'invoice_next_number',
                'value'          => '1000',
                'type'           => 'integer',
                'group'          => 'invoice',
                'label_ar'       => 'رقم الفاتورة التالي',
                'label_en'       => 'Next Invoice Number',
                'is_public'      => false,
                'is_editable'    => true,
                'sort_order'     => 2,
            ],
            [
                'key'            => 'invoice_due_days',
                'value'          => '30',
                'type'           => 'integer',
                'group'          => 'invoice',
                'label_ar'       => 'مدة الاستحقاق الافتراضية (يوم)',
                'label_en'       => 'Default Due Days',
                'is_public'      => false,
                'is_editable'    => true,
                'sort_order'     => 3,
            ],
            [
                'key'            => 'invoice_vat_rate',
                'value'          => '0',
                'type'           => 'float',
                'group'          => 'invoice',
                'label_ar'       => 'نسبة ضريبة القيمة المضافة (%)',
                'label_en'       => 'VAT Rate (%)',
                'description_ar' => 'أدخل 0 لتعطيل الضريبة',
                'description_en' => 'Enter 0 to disable VAT',
                'is_public'      => true,
                'is_editable'    => true,
                'sort_order'     => 4,
            ],
            [
                'key'            => 'invoice_footer_note',
                'value'          => 'شكراً لتعاملكم معنا — توتال الكلاكلة',
                'type'           => 'string',
                'group'          => 'invoice',
                'label_ar'       => 'ملاحظة أسفل الفاتورة',
                'label_en'       => 'Invoice Footer Note',
                'is_public'      => false,
                'is_editable'    => true,
                'sort_order'     => 5,
            ],

            // ================================================================
            // GROUP: pos
            // ================================================================
            [
                'key'            => 'pos_receipt_prefix',
                'value'          => 'RCP',
                'type'           => 'string',
                'group'          => 'pos',
                'label_ar'       => 'بادئة رقم الإيصال',
                'label_en'       => 'Receipt Number Prefix',
                'is_public'      => false,
                'is_editable'    => true,
                'sort_order'     => 1,
            ],
            [
                'key'            => 'pos_allow_credit_sales',
                'value'          => '1',
                'type'           => 'boolean',
                'group'          => 'pos',
                'label_ar'       => 'السماح بالبيع الآجل من الكاشير',
                'label_en'       => 'Allow Credit Sales at POS',
                'is_public'      => false,
                'is_editable'    => true,
                'sort_order'     => 2,
            ],
            [
                'key'            => 'pos_receipt_footer',
                'value'          => 'شكراً لتسوقكم من توتال الكلاكلة',
                'type'           => 'string',
                'group'          => 'pos',
                'label_ar'       => 'رسالة أسفل الإيصال',
                'label_en'       => 'Receipt Footer Message',
                'is_public'      => false,
                'is_editable'    => true,
                'sort_order'     => 3,
            ],
            [
                'key'            => 'pos_print_auto',
                'value'          => '1',
                'type'           => 'boolean',
                'group'          => 'pos',
                'label_ar'       => 'طباعة تلقائية بعد كل بيع',
                'label_en'       => 'Auto Print After Each Sale',
                'is_public'      => false,
                'is_editable'    => true,
                'sort_order'     => 4,
            ],

            // ================================================================
            // GROUP: hr
            // ================================================================
            [
                'key'            => 'hr_work_days_per_month',
                'value'          => '26',
                'type'           => 'integer',
                'group'          => 'hr',
                'label_ar'       => 'أيام العمل في الشهر',
                'label_en'       => 'Work Days Per Month',
                'is_public'      => false,
                'is_editable'    => true,
                'sort_order'     => 1,
            ],
            [
                'key'            => 'hr_annual_leave_days',
                'value'          => '21',
                'type'           => 'integer',
                'group'          => 'hr',
                'label_ar'       => 'أيام الإجازة السنوية',
                'label_en'       => 'Annual Leave Days',
                'is_public'      => false,
                'is_editable'    => true,
                'sort_order'     => 2,
            ],
            [
                'key'            => 'hr_payroll_day',
                'value'          => '25',
                'type'           => 'integer',
                'group'          => 'hr',
                'label_ar'       => 'يوم صرف الراتب من الشهر',
                'label_en'       => 'Payroll Day of Month',
                'is_public'      => false,
                'is_editable'    => true,
                'sort_order'     => 3,
            ],

            // ================================================================
            // GROUP: notifications
            // ================================================================
            [
                'key'            => 'notify_low_stock',
                'value'          => '1',
                'type'           => 'boolean',
                'group'          => 'notifications',
                'label_ar'       => 'تنبيه عند نقص المخزون',
                'label_en'       => 'Notify on Low Stock',
                'is_public'      => false,
                'is_editable'    => true,
                'sort_order'     => 1,
            ],
            [
                'key'            => 'notify_overdue_invoices',
                'value'          => '1',
                'type'           => 'boolean',
                'group'          => 'notifications',
                'label_ar'       => 'تنبيه عند تأخر سداد الفواتير',
                'label_en'       => 'Notify on Overdue Invoices',
                'is_public'      => false,
                'is_editable'    => true,
                'sort_order'     => 2,
            ],
            [
                'key'            => 'notify_backup_success',
                'value'          => '1',
                'type'           => 'boolean',
                'group'          => 'notifications',
                'label_ar'       => 'تنبيه بعد اكتمال النسخ الاحتياطي',
                'label_en'       => 'Notify After Backup Success',
                'is_public'      => false,
                'is_editable'    => true,
                'sort_order'     => 3,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('✅ Settings seeded successfully (' . count($settings) . ' settings).');
    }
}
