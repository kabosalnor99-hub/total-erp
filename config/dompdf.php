<?php

// config/dompdf.php

return [

    'show_warnings'    => false,
    'public_path'      => null,
    'convert_entities' => true,

    'options' => [

        /*
         * الخط الافتراضي — Noto Naskh Arabic يدعم العربية الكاملة
         * (بعد تشغيل: php artisan pdf:install-arabic-font)
         */
        'defaultFont' => 'noto naskh arabic',

        'isRemoteEnabled'        => true,
        'isHtml5ParserEnabled'   => true,
        'isFontSubsettingEnabled'=> true,

        'defaultMediaType' => 'print',

        'dpi'             => 110,
        'fontHeightRatio' => 1.1,

        'isPhpEnabled'        => false,
        'isJavascriptEnabled' => false,

        'debugPng'             => false,
        'debugKeepTemp'        => false,
        'debugCss'             => false,
        'debugLayout'          => false,
        'debugLayoutLines'     => false,
        'debugLayoutBlocks'    => false,
        'debugLayoutInline'    => false,
        'debugLayoutPaddingBox'=> false,

        'pdfBackend'    => 'CPDF',
        'pdflibLicense' => '',
        'logOutputFile' => storage_path('logs/dompdf.htm'),

        'chroot' => realpath(base_path()),

        'allowed_protocols' => [
            'file://' => ['rules' => []],
            'http://'  => ['rules' => []],
            'https://' => ['rules' => []],
        ],

        'artifactPathValidation' => null,

        'adminUsername' => 'user',
        'adminPassword' => '',

        /*
         * مجلد الخطوط — نفس المكان الذي يحفظ فيه artisan command الخطوط
         */
        'fontDir'   => storage_path('fonts'),
        'fontCache' => storage_path('fonts'),

        'tempDir' => sys_get_temp_dir(),
        'rootDir' => realpath(base_path('vendor/dompdf/dompdf')),
    ],
];
