<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * php artisan pdf:install-arabic-font
 *
 * يُنزِّل خط Noto Naskh Arabic ويسجّله في DomPDF
 * شغّله مرة واحدة بعد النشر أو في post-deploy script
 */
class InstallArabicFont extends Command
{
    protected $signature   = 'pdf:install-arabic-font';
    protected $description = 'تثبيت خط Noto Naskh Arabic في DomPDF لدعم العربية في PDF';

    // روابط الخطوط (GitHub Noto Fonts)
    private array $fonts = [
        'NotoNaskhArabic-Regular' => 'https://github.com/googlefonts/noto-fonts/raw/main/hinted/ttf/NotoNaskhArabic/NotoNaskhArabic-Regular.ttf',
        'NotoNaskhArabic-Bold'    => 'https://github.com/googlefonts/noto-fonts/raw/main/hinted/ttf/NotoNaskhArabic/NotoNaskhArabic-Bold.ttf',
    ];

    public function handle(): int
    {
        $fontDir = storage_path('fonts');

        if (! is_dir($fontDir)) {
            mkdir($fontDir, 0755, true);
            $this->info("📁 أُنشئ مجلد: {$fontDir}");
        }

        // ── 1. تنزيل ملفات TTF ─────────────────────────────────
        foreach ($this->fonts as $name => $url) {
            $dest = "{$fontDir}/{$name}.ttf";

            if (file_exists($dest)) {
                $this->line("  ✓ موجود مسبقاً: {$name}.ttf");
                continue;
            }

            $this->info("⬇  جاري تحميل {$name}.ttf ...");
            $data = @file_get_contents($url);

            if (! $data) {
                $this->error("✗ فشل التحميل: {$url}");
                return self::FAILURE;
            }

            file_put_contents($dest, $data);
            $this->info("  ✓ تم حفظ: {$name}.ttf");
        }

        // ── 2. تسجيل الخط في DomPDF عبر load_font.php ─────────
        $loadFontScript = base_path('vendor/dompdf/dompdf/load_font.php');

        if (! file_exists($loadFontScript)) {
            // fallback: كتابة font_metrics.json يدوياً
            $this->warn('⚠  load_font.php غير موجود، سيُستخدم font cache مباشرة.');
            $this->writeFontCache($fontDir);
        } else {
            $this->runLoadFont($loadFontScript, $fontDir);
        }

        $this->newLine();
        $this->info('✅ تم تثبيت خط Noto Naskh Arabic بنجاح!');
        $this->line('   استخدمه في blade: font-family: "NotoNaskhArabic"');

        return self::SUCCESS;
    }

    // ── تشغيل load_font.php الرسمي من dompdf ──────────────────
    private function runLoadFont(string $script, string $fontDir): void
    {
        $regular = "{$fontDir}/NotoNaskhArabic-Regular.ttf";
        $bold    = "{$fontDir}/NotoNaskhArabic-Bold.ttf";

        foreach ([
            ['NotoNaskhArabic', 'normal', '400', $regular],
            ['NotoNaskhArabic', 'bold',   '700', $bold],
        ] as [$family, $style, $weight, $file]) {
            $cmd = sprintf(
                'php %s %s %s %s %s 2>&1',
                escapeshellarg($script),
                escapeshellarg($family),
                escapeshellarg($style),
                escapeshellarg($weight),
                escapeshellarg($file)
            );

            exec($cmd, $output, $code);

            if ($code !== 0) {
                $this->warn("  ⚠ load_font: {$family}/{$style} — " . implode(' ', $output));
            } else {
                $this->info("  ✓ مسجَّل: {$family} / {$style}");
            }
        }
    }

    // ── كتابة font_metrics.json يدوياً (fallback) ─────────────
    private function writeFontCache(string $fontDir): void
    {
        $cacheFile = "{$fontDir}/dompdf_font_family_cache.php";

        // اقرأ الـ cache الحالي إن وُجد
        $cache = [];
        if (file_exists($cacheFile)) {
            $cache = include $cacheFile;
            if (! is_array($cache)) {
                $cache = [];
            }
        }

        $cache['noto naskh arabic'] = [
            'normal'      => "{$fontDir}/NotoNaskhArabic-Regular.ttf",
            'bold'        => "{$fontDir}/NotoNaskhArabic-Bold.ttf",
            'italic'      => "{$fontDir}/NotoNaskhArabic-Regular.ttf",
            'bold_italic' => "{$fontDir}/NotoNaskhArabic-Bold.ttf",
        ];

        $export = "<?php return " . var_export($cache, true) . ";\n";
        file_put_contents($cacheFile, $export);

        $this->info("  ✓ تم كتابة font cache: {$cacheFile}");
    }
}
