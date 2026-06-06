<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AiService
{
    private string $apiKey;
    private string $model    = 'gemini-1.5-flash';
    private string $baseUrl  = 'https://generativelanguage.googleapis.com/v1beta';
    private int    $timeout  = 20;

    // ─── Persona ثابتة للنظام ────────────────────────────────────────
    private string $systemPersona = "أنت مساعد ذكي متخصص لنظام إدارة توتال السودان لمعدات الورش.
تجيب باللغة العربية بأسلوب مختصر ومهني.
لا تخترع أرقاماً — استخدم فقط البيانات المقدمة لك في كل طلب.
عند غياب البيانات أخبر المستخدم بذلك بدلاً من التخمين.
";

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
    }

    // ═══════════════════════════════════════════════════════════════════
    //  الواجهات العامة
    // ═══════════════════════════════════════════════════════════════════

    /**
     * سؤال عام مع cache (5 دقائق) — للبيانات التي لا تتغير كثيراً
     */
    public function ask(string $prompt, array $context = [], int $maxTokens = 1000): string
    {
        $fullPrompt = $this->buildPrompt($prompt, $context);
        $cacheKey   = 'ai_resp_' . md5($fullPrompt);

        return Cache::remember($cacheKey, 300, fn () => $this->callApi($fullPrompt, $maxTokens));
    }

    /**
     * سؤال بدون cache — للبيانات اللحظية (المخزون، المبيعات، الكاشير)
     */
    public function askFresh(string $prompt, array $context = [], int $maxTokens = 1000): string
    {
        return $this->callApi($this->buildPrompt($prompt, $context), $maxTokens);
    }

    /**
     * محادثة متعددة الأدوار (chat history)
     *
     * @param  array  $messages  [['role' => 'user|model', 'text' => '...']]
     */
    public function chat(array $messages, array $context = [], int $maxTokens = 1200): string
    {
        $contents = [];

        // حقن السياق في أول رسالة مستخدم
        if (! empty($context)) {
            $contextBlock = "\n[بيانات النظام المتاحة]:\n"
                . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $messages[0]['text'] = $contextBlock . "\n\n" . $messages[0]['text'];
        }

        foreach ($messages as $msg) {
            $contents[] = [
                'role'  => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $msg['text']]],
            ];
        }

        return $this->callApiRaw($contents, $maxTokens);
    }

    /**
     * تحليل ملف / نص طويل (مثل تقرير) مع streaming context
     */
    public function analyze(string $text, string $instruction, int $maxTokens = 1500): string
    {
        $prompt = $this->systemPersona
            . "\n[التعليمة]: " . $instruction
            . "\n\n[النص المراد تحليله]:\n" . $text;

        return $this->callApi($prompt, $maxTokens);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  Specialized helpers — يستخدمها كل Controller مباشرة
    // ═══════════════════════════════════════════════════════════════════

    /**
     * تنبيه ذكي للمخزون
     *
     * @param  array  $products  [['name' => '...', 'qty' => n, 'reorder' => n]]
     */
    public function inventoryAlert(array $products): string
    {
        return $this->askFresh(
            'بناءً على بيانات المخزون التالية، اعطني ملخصاً نصياً موجزاً لا يتجاوز 3 أسطر '
            . 'يوضح أهم المشاكل وأولوية التدخل.',
            ['products' => $products],
            400
        );
    }

    /**
     * تحليل أداء المبيعات
     *
     * @param  array  $salesData  ['today' => n, 'lastWeek' => n, 'topProducts' => [...]]
     */
    public function salesInsight(array $salesData): string
    {
        return $this->ask(
            'حلل بيانات المبيعات وقدم 3 ملاحظات رئيسية وتوصية واحدة قابلة للتنفيذ.',
            ['sales' => $salesData],
            500
        );
    }

    /**
     * مساعد الفواتير المتأخرة
     *
     * @param  array  $overdueList  [['customer' => '...', 'amount' => n, 'days' => n]]
     */
    public function overdueReminder(array $overdueList): string
    {
        return $this->ask(
            'رتّب هؤلاء العملاء حسب الأولوية للمتابعة، مع سبب مختصر لكل ترتيب.',
            ['overdue_invoices' => $overdueList],
            600
        );
    }

    /**
     * توقع احتياجات الشراء
     *
     * @param  array  $stockData   حالة المخزون الحالية
     * @param  array  $salesTrend  متوسط المبيعات اليومية
     */
    public function purchaseForecast(array $stockData, array $salesTrend): string
    {
        return $this->ask(
            'بناءً على المخزون الحالي واتجاهات المبيعات، اقترح قائمة شراء أولوية لضمان '
            . 'عدم انقطاع المنتجات الأكثر حركة خلال الأسبوعين القادمين.',
            ['stock' => $stockData, 'sales_trend' => $salesTrend],
            700
        );
    }

    /**
     * تلخيص التقارير المالية
     */
    public function summarizeFinancials(array $financialData): string
    {
        return $this->ask(
            'لخّص هذه البيانات المالية في فقرة واحدة واضحة للإدارة، مع إبراز أي مؤشرات تستدعي الانتباه.',
            $financialData,
            600
        );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  الطبقة الداخلية — API calls
    // ═══════════════════════════════════════════════════════════════════

    private function callApi(string $prompt, int $maxTokens): string
    {
        $contents = [
            ['role' => 'user', 'parts' => [['text' => $prompt]]],
        ];

        return $this->callApiRaw($contents, $maxTokens);
    }

    private function callApiRaw(array $contents, int $maxTokens): string
    {
        if (empty($this->apiKey)) {
            Log::warning('AiService: GEMINI_API_KEY is not set.');
            return 'خدمة الذكاء الاصطناعي غير مُهيأة. يرجى التواصل مع المسؤول.';
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post(
                    "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}",
                    [
                        'system_instruction' => [
                            'parts' => [['text' => $this->systemPersona]],
                        ],
                        'contents'            => $contents,
                        'generationConfig'    => [
                            'maxOutputTokens' => $maxTokens,
                            'temperature'     => 0.3,
                            'topP'            => 0.85,
                        ],
                        'safetySettings' => [
                            ['category' => 'HARM_CATEGORY_HARASSMENT',        'threshold' => 'BLOCK_NONE'],
                            ['category' => 'HARM_CATEGORY_HATE_SPEECH',        'threshold' => 'BLOCK_NONE'],
                            ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',  'threshold' => 'BLOCK_NONE'],
                            ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',  'threshold' => 'BLOCK_NONE'],
                        ],
                    ]
                );

            if ($response->failed()) {
                Log::error('Gemini API HTTP error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return $this->friendlyError($response->status());
            }

            $text = $response->json('candidates.0.content.parts.0.text');

            if (empty($text)) {
                Log::warning('Gemini API returned empty response', ['body' => $response->json()]);
                return 'لم يتمكن الذكاء الاصطناعي من توليد رد في الوقت الحالي.';
            }

            return trim($text);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('AiService: Connection timeout — ' . $e->getMessage());
            return 'انتهت مهلة الاتصال بخدمة الذكاء الاصطناعي. يرجى المحاولة مجدداً.';

        } catch (\Exception $e) {
            Log::error('AiService: Unexpected error — ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return 'حدث خطأ غير متوقع في خدمة الذكاء الاصطناعي.';
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    //  Helpers
    // ═══════════════════════════════════════════════════════════════════

    private function buildPrompt(string $userPrompt, array $context): string
    {
        $prompt = $userPrompt;

        if (! empty($context)) {
            $prompt .= "\n\n[البيانات المتاحة]:\n"
                . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        return $prompt;
    }

    private function friendlyError(int $status): string
    {
        return match (true) {
            $status === 400 => 'طلب غير صالح. يرجى التحقق من المدخلات.',
            $status === 401 => 'مفتاح API غير صحيح. يرجى مراجعة الإعدادات.',
            $status === 429 => 'تم تجاوز الحد الأقصى للطلبات. يرجى الانتظار قليلاً.',
            $status >= 500  => 'خادم الذكاء الاصطناعي غير متاح مؤقتاً.',
            default         => 'تعذّر الاتصال بالذكاء الاصطناعي (خطأ ' . $status . ').',
        };
    }
}
