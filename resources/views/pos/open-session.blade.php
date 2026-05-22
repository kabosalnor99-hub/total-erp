{{-- المسار الكامل: resources/views/pos/open-session.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فتح جلسة — نقطة البيع | توتال الكلاكلة</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{
            min-height:100vh;display:flex;align-items:center;justify-content:center;
            background:linear-gradient(135deg,#1A2E35 0%,#0F1E24 100%);
            font-family:'Cairo',sans-serif;direction:rtl;
        }
        .card{
            background:#1F3540;border:1px solid #2D4A57;border-radius:20px;
            padding:40px 36px;width:100%;max-width:420px;
            box-shadow:0 20px 60px rgba(0,0,0,.5);
        }
        .logo{text-align:center;margin-bottom:32px}
        .logo-icon{
            width:72px;height:72px;background:#00838F;border-radius:18px;
            display:inline-flex;align-items:center;justify-content:center;
            font-size:32px;margin-bottom:12px;
        }
        .logo h1{font-size:20px;font-weight:800;color:#E8F4F6}
        .logo p{font-size:13px;color:#7EADB8;margin-top:4px}

        h2{font-size:16px;font-weight:700;color:#E8F4F6;margin-bottom:20px;
           display:flex;align-items:center;gap:8px}
        h2 .dot{width:8px;height:8px;background:#00838F;border-radius:50%;display:inline-block}

        .label{display:block;font-size:12px;color:#7EADB8;margin-bottom:6px}
        .input{
            width:100%;background:#0F1E24;border:1.5px solid #2D4A57;
            border-radius:10px;padding:12px 16px;color:#E8F4F6;
            font-size:16px;font-family:inherit;outline:none;
            transition:border-color .2s;text-align:center;font-weight:700;
        }
        .input:focus{border-color:#00838F}
        .input::placeholder{color:#7EADB8;font-weight:400}

        .hint{font-size:11px;color:#7EADB8;margin-top:6px;text-align:center}

        .btn{
            width:100%;margin-top:24px;padding:14px;
            background:#00838F;color:#fff;border:none;border-radius:10px;
            font-size:16px;font-weight:700;font-family:inherit;cursor:pointer;
            transition:background .2s;display:flex;align-items:center;
            justify-content:center;gap:8px;
        }
        .btn:hover{background:#005F6B}

        .info-box{
            background:rgba(0,131,143,.1);border:1px solid rgba(0,131,143,.3);
            border-radius:10px;padding:12px 16px;margin-bottom:20px;
        }
        .info-row{display:flex;justify-content:space-between;font-size:12px;padding:3px 0}
        .info-row .label-i{color:#7EADB8}
        .info-row .val{color:#4FB3C0;font-weight:600}

        .back{
            display:block;text-align:center;margin-top:16px;
            color:#7EADB8;font-size:12px;text-decoration:none;
            transition:color .2s;
        }
        .back:hover{color:#4FB3C0}

        @if(session('error'))
        .alert{background:rgba(229,57,53,.15);border:1px solid rgba(229,57,53,.4);
               border-radius:10px;padding:10px 14px;color:#EF9A9A;font-size:13px;
               margin-bottom:16px;text-align:center}
        @endif
    </style>
</head>
<body>
<div class="card">
    <div class="logo">
        <div class="logo-icon">🔧</div>
        <h1>توتال الكلاكلة</h1>
        <p>نقطة البيع (POS)</p>
    </div>

    @if(session('error'))
        <div class="alert">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif

    <div class="info-box">
        <div class="info-row">
            <span class="label-i">الكاشير</span>
            <span class="val">{{ auth()->user()->name }}</span>
        </div>
        <div class="info-row">
            <span class="label-i">التاريخ</span>
            <span class="val">{{ now()->format('Y/m/d') }}</span>
        </div>
        <div class="info-row">
            <span class="label-i">الوقت</span>
            <span class="val">{{ now()->format('H:i') }}</span>
        </div>
    </div>

    <h2><span class="dot"></span> فتح جلسة جديدة</h2>

    <form method="POST" action="{{ route('pos.session.open') }}">
        @csrf
        <label class="label" for="opening_balance">رصيد الصندوق الافتتاحي (ج.س)</label>
        <input
            id="opening_balance"
            name="opening_balance"
            type="number"
            min="0"
            step="0.01"
            class="input"
            placeholder="0.00"
            value="{{ old('opening_balance', 0) }}"
            autofocus
        >
        <p class="hint">أدخل المبلغ النقدي الموجود في الصندوق الآن</p>

        <button type="submit" class="btn">
            <span>🟢</span>
            فتح الجلسة والدخول للكاشير
        </button>
    </form>

    <a href="{{ route('dashboard') }}" class="back">← العودة للوحة التحكم</a>
</div>
</body>
</html>
