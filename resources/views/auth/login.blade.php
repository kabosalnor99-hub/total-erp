<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول — Zenix ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { primary: { DEFAULT: '#00838F', dark: '#005F6B', light: '#00A8B8' } } } }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * { font-family: 'Tajawal', sans-serif; }

        .bg-logo {
            background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNTM2IiBoZWlnaHQ9IjY5MSI+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0iIzAwNkY3QiIvPjwvc3ZnPg==');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-color: #004F5A;
        }

        .login-panel {
            animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .input-field:focus {
            box-shadow: 0 0 0 3px rgba(0,131,143,0.15);
        }

        .bg-overlay {
            background: linear-gradient(135deg,
                rgba(0,63,71,0.88) 0%,
                rgba(0,131,143,0.75) 50%,
                rgba(0,95,107,0.90) 100%
            );
        }

        /* القسم الأيسر خلفية بلون الثيم */
        .left-panel-bg {
            background: linear-gradient(160deg, #f0fafa 0%, #e0f4f6 40%, #c8ecef 100%);
        }

        .mazen-logo-container {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #005F6B 0%, #00838F 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(0,131,143,0.30);
            flex-shrink: 0;
        }
    </style>
</head>

<body class="min-h-screen flex overflow-hidden" x-data="{ showPass: false }">

    <!-- ════ الجانب الأيمن: خلفية الشعار (desktop فقط) ════ -->
    <div class="hidden lg:flex flex-1 relative overflow-hidden">
        <div class="absolute inset-0 bg-logo"></div>
        <div class="absolute inset-0 bg-overlay"></div>
        <div class="relative z-10 flex flex-col justify-end p-12 w-full">
            <h2 class="text-white text-4xl font-black leading-tight mb-3">
                مرحباً بك في<br>
                <span class="text-white/75">Zenix ERP</span>
            </h2>
            <p class="text-white/55 text-base leading-relaxed max-w-xs mb-8">
                نظام إدارة موارد متكامل لمتابعة المبيعات والمخزون والمحاسبة
            </p>
            <div class="flex flex-col gap-3 mb-10">
                <div class="flex items-center gap-3 text-white/70 text-sm">
                    <span class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center flex-shrink-0">
                        <i class="fa fa-chart-line text-xs"></i>
                    </span>
                    متابعة المبيعات والتقارير لحظياً
                </div>
                <div class="flex items-center gap-3 text-white/70 text-sm">
                    <span class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center flex-shrink-0">
                        <i class="fa fa-boxes-stacked text-xs"></i>
                    </span>
                    إدارة المخزون والمستودعات
                </div>
                <div class="flex items-center gap-3 text-white/70 text-sm">
                    <span class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center flex-shrink-0">
                        <i class="fa fa-calculator text-xs"></i>
                    </span>
                    محاسبة ومالية متكاملة
                </div>
            </div>

            <!-- معلومات Mazen Tech مع الشعار -->
            <div class="pt-8 border-t border-white/15">
                <div class="flex items-center gap-4">
                    <!-- شعار Mazen Tech -->
                    <div class="mazen-logo-container">
                        <svg viewBox="0 0 100 100" width="34" height="34" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 22 L20 55 Q20 62 27 68 L34 74 L34 40 Z" fill="#e8e8e8"/>
                            <path d="M34 40 L52 58 L76 28 L76 38 Q76 45 70 51 L52 70 L34 52 Z" fill="url(#lg)"/>
                            <path d="M63 60 Q63 54 67 49 L73 43 L73 60 Q73 67 67 72 L63 76 Z" fill="#e8e8e8"/>
                            <defs>
                                <linearGradient id="lg" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#8ecfd4"/>
                                    <stop offset="50%" stop-color="#e5e5e5"/>
                                    <stop offset="100%" stop-color="#8ecfd4"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                    <!-- النصوص -->
                    <div>
                        <p class="text-white/45 text-xs mb-0.5">تطوير وتصميم</p>
                        <p class="text-white text-base font-bold leading-none mb-1.5">Mazen Tech</p>
                        <a href="https://wa.me/249126438664"
                           target="_blank"
                           class="inline-flex items-center gap-1.5 text-white/55 hover:text-white/90 text-xs transition-colors">
                            <i class="fa-brands fa-whatsapp text-green-400 text-sm"></i>
                            0126438664
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ════ الجانب الأيسر: نموذج تسجيل الدخول ════ -->
    <div class="w-full lg:w-[480px] flex flex-col min-h-screen relative left-panel-bg">

        <!-- خلفية الموبايل: الشعار كاملاً -->
        <div class="lg:hidden absolute inset-0 bg-logo"></div>
        <div class="lg:hidden absolute inset-0 bg-overlay"></div>

        <!-- المحتوى -->
        <div class="relative z-10 flex flex-col flex-1 justify-center p-6 sm:p-10 login-panel">

            <!-- شعار Mazen Tech للموبايل فقط -->
            <div class="mb-8 flex justify-center lg:hidden">
                <div class="mazen-logo-container w-20 h-20 rounded-2xl">
                    <svg viewBox="0 0 100 100" width="48" height="48" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 22 L20 55 Q20 62 27 68 L34 74 L34 40 Z" fill="#e8e8e8"/>
                        <path d="M34 40 L52 58 L76 28 L76 38 Q76 45 70 51 L52 70 L34 52 Z" fill="url(#lg2)"/>
                        <path d="M63 60 Q63 54 67 49 L73 43 L73 60 Q73 67 67 72 L63 76 Z" fill="#e8e8e8"/>
                        <defs>
                            <linearGradient id="lg2" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#8ecfd4"/>
                                <stop offset="50%" stop-color="#e5e5e5"/>
                                <stop offset="100%" stop-color="#8ecfd4"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
            </div>

            <!-- بطاقة النموذج -->
            <div class="bg-white/92 backdrop-blur-sm lg:backdrop-blur-none lg:bg-white rounded-2xl shadow-xl lg:shadow-md p-7 sm:p-8 border border-primary/10 lg:border-gray-100">

                <!-- العنوان -->
                <div class="mb-7">
                    <!-- شعار صغير بجانب العنوان في الديسكتوب -->
                    <div class="hidden lg:flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg,#005F6B,#00838F);">
                            <svg viewBox="0 0 100 100" width="22" height="22" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 22 L20 55 Q20 62 27 68 L34 74 L34 40 Z" fill="#e8e8e8"/>
                                <path d="M34 40 L52 58 L76 28 L76 38 Q76 45 70 51 L52 70 L34 52 Z" fill="#b0dde0"/>
                                <path d="M63 60 Q63 54 67 49 L73 43 L73 60 Q73 67 67 72 L63 76 Z" fill="#e8e8e8"/>
                            </svg>
                        </div>
                        <span class="text-primary font-bold text-sm">Zenix ERP</span>
                    </div>
                    <h1 class="text-gray-900 text-2xl font-black">تسجيل الدخول</h1>
                    <p class="text-gray-500 text-sm mt-1">أدخل بياناتك للوصول إلى النظام</p>
                </div>

                <!-- رسالة الخطأ -->
                <div class="bg-red-50 border border-red-200 rounded-xl p-3.5 mb-5 text-sm text-red-700 flex items-center gap-2.5 hidden">
                    <i class="fa fa-circle-exclamation text-red-500 flex-shrink-0"></i>
                    <span>بيانات الدخول غير صحيحة</span>
                </div>

                <!-- النموذج -->
                <form method="POST" action="#" class="space-y-5">

                    <!-- البريد أو الهاتف -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            البريد الإلكتروني أو رقم الهاتف
                        </label>
                        <div class="relative">
                            <span class="absolute top-1/2 -translate-y-1/2 right-3.5 text-gray-400 pointer-events-none">
                                <i class="fa fa-user text-sm"></i>
                            </span>
                            <input
                                type="text"
                                name="login"
                                placeholder="أدخل البريد أو الهاتف"
                                autofocus
                                class="input-field w-full pr-10 pl-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition"
                            >
                        </div>
                    </div>

                    <!-- كلمة المرور -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            كلمة المرور
                        </label>
                        <div class="relative">
                            <span class="absolute top-1/2 -translate-y-1/2 right-3.5 text-gray-400 pointer-events-none">
                                <i class="fa fa-lock text-sm"></i>
                            </span>
                            <input
                                :type="showPass ? 'text' : 'password'"
                                name="password"
                                placeholder="أدخل كلمة المرور"
                                class="input-field w-full pr-10 pl-10 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition"
                            >
                            <button type="button" @click="showPass = !showPass"
                                class="absolute top-1/2 -translate-y-1/2 left-3.5 text-gray-400 hover:text-primary transition-colors">
                                <i :class="showPass ? 'fa-eye-slash' : 'fa-eye'" class="fa text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <!-- تذكرني -->
                    <div class="flex items-center gap-2.5">
                        <input type="checkbox" name="remember" id="remember"
                            class="w-4 h-4 accent-primary rounded cursor-pointer">
                        <label for="remember" class="text-sm text-gray-600 cursor-pointer select-none">تذكرني</label>
                    </div>

                    <!-- زر الدخول -->
                    <button type="submit"
                        class="w-full bg-primary hover:bg-primary-dark active:scale-[0.98] text-white font-bold py-3.5 rounded-xl transition-all duration-200 shadow-sm hover:shadow-lg text-sm">
                        <i class="fa fa-right-to-bracket ml-2"></i>
                        دخول إلى النظام
                    </button>
                </form>
            </div>

            <!-- Footer -->
            <div class="mt-6 text-center space-y-1">
                <p class="text-white/50 lg:text-gray-400 text-xs">
                    جميع الحقوق محفوظة &copy; 2025
                </p>
                <p class="text-white/40 lg:text-primary/50 text-xs">
                    Powered by
                    <span class="font-bold text-white/65 lg:text-primary">Mazen Tech</span>
                    &nbsp;·&nbsp;
                    <a href="https://wa.me/249126438664"
                       target="_blank"
                       class="inline-flex items-center gap-1 hover:text-white lg:hover:text-primary transition-colors">
                        <i class="fa-brands fa-whatsapp text-green-400 text-xs"></i>
                        0126438664
                    </a>
                </p>
            </div>

        </div>
    </div>

</body>
</html>
