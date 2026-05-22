<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول — توتال الكلاكلة</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { primary: { DEFAULT: '#00838F', dark: '#005F6B' } } } }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>* { font-family: 'Tajawal', sans-serif; }</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-primary to-primary-dark flex items-center justify-center p-4">

    <div class="w-full max-w-md" x-data="{ showPass: false }">

        {{-- البطاقة --}}
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">

            {{-- الرأس --}}
            <div class="bg-primary px-8 py-10 text-center">
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <span class="text-primary font-black text-3xl">T</span>
                </div>
                <h1 class="text-white text-2xl font-bold">توتال الكلاكلة</h1>
                <p class="text-white/70 text-sm mt-1">نظام إدارة الموارد المؤسسية</p>
            </div>

            {{-- النموذج --}}
            <div class="px-8 py-8">
                <h2 class="text-gray-800 text-xl font-bold mb-6 text-center">تسجيل الدخول</h2>

                @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-xl p-3 mb-4 text-sm text-red-700 flex items-center gap-2">
                    <i class="fa fa-circle-exclamation"></i>
                    {{ $errors->first() }}
                </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                    @csrf

                    {{-- البريد أو الهاتف --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            البريد الإلكتروني أو رقم الهاتف
                        </label>
                        <div class="relative">
                            <span class="absolute top-1/2 -translate-y-1/2 right-3 text-gray-400">
                                <i class="fa fa-user"></i>
                            </span>
                            <input
                                type="text"
                                name="login"
                                value="{{ old('login') }}"
                                placeholder="أدخل البريد أو الهاتف"
                                autofocus
                                class="w-full pr-10 pl-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition @error('login') border-red-400 @enderror"
                            >
                        </div>
                    </div>

                    {{-- كلمة المرور --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            كلمة المرور
                        </label>
                        <div class="relative">
                            <span class="absolute top-1/2 -translate-y-1/2 right-3 text-gray-400">
                                <i class="fa fa-lock"></i>
                            </span>
                            <input
                                :type="showPass ? 'text' : 'password'"
                                name="password"
                                placeholder="أدخل كلمة المرور"
                                class="w-full pr-10 pl-10 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition @error('password') border-red-400 @enderror"
                            >
                            <button type="button" @click="showPass = !showPass"
                                class="absolute top-1/2 -translate-y-1/2 left-3 text-gray-400 hover:text-gray-600">
                                <i :class="showPass ? 'fa-eye-slash' : 'fa-eye'" class="fa"></i>
                            </button>
                        </div>
                    </div>

                    {{-- تذكرني --}}
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="remember" id="remember"
                            class="w-4 h-4 accent-primary rounded">
                        <label for="remember" class="text-sm text-gray-600">تذكرني</label>
                    </div>

                    {{-- زر الدخول --}}
                    <button type="submit"
                        class="w-full bg-primary hover:bg-primary-dark text-white font-bold py-3.5 rounded-xl transition-all duration-200 shadow-md hover:shadow-lg text-sm">
                        <i class="fa fa-right-to-bracket ml-2"></i>
                        دخول
                    </button>
                </form>
            </div>
        </div>

        {{-- Footer --}}
        <p class="text-center text-white/60 text-xs mt-6">
            توتال الكلاكلة &copy; {{ date('Y') }} — جميع الحقوق محفوظة
        </p>
    </div>

</body>
</html>
