<?php

// config/dompdf.php
// نسخ هذا الملف إلى: config/dompdf.php

return [

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */

    'show_warnings'   => false,
    'public_path'     => null,
    'convert_entities'=> true,

    'options' => [

        /*
         * الخط الافتراضي — DejaVu Sans يدعم العربية في dompdf
         */
        'defaultFont' => 'dejavu sans',

        /*
         * ضروري لتحميل الصور عبر URL (مثل الشعار)
         */
        'isRemoteEnabled' => true,

        /*
         * ضروري لعرض HTML5 بشكل صحيح
         */
        'isHtml5ParserEnabled' => true,

        /*
         * تقليل حجم الملف
         */
        'isFontSubsettingEnabled' => true,

        /*
         * يحسن دعم Unicode والنص العربي
         */
        'defaultMediaType' => 'print',

        'dpi'                  => 96,
        'fontHeightRatio'      => 1.1,
        'isPhpEnabled'         => false,
        'isJavascriptEnabled'  => false,
        'debugPng'             => false,
        'debugKeepTemp'        => false,
        'debugCss'             => false,
        'debugLayout'          => false,
        'debugLayoutLines'     => false,
        'debugLayoutBlocks'    => false,
        'debugLayoutInline'    => false,
        'debugLayoutPaddingBox'=> false,

        'pdfBackend'         => 'CPDF',
        'pdflibLicense'      => '',
        'logOutputFile'      => storage_path('logs/dompdf.htm'),

        'chroot'             => realpath(base_path()),
        'allowed_protocols'  => [
            'file://' => ['rules' => []],
            'http://'  => ['rules' => []],
            'https://' => ['rules' => []],
        ],

        'artifactPathValidation' => null,

        'adminUsername' => 'user',
        'adminPassword' => '',

        'fontDir'   => storage_path('fonts'),
        'fontCache' => storage_path('fonts'),

        'tempDir' => sys_get_temp_dir(),

        'rootDir' => realpath(base_path('vendor/dompdf/dompdf')),
    ],
];
