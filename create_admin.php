<?php
// ضع هذا الملف في جذر المشروع ثم شغّله مرة واحدة:
// railway run php create_admin.php
// أو: php create_admin.php
// بعد التشغيل احذفه فوراً لأسباب أمنية

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->boot();

use App\Models\User;
use App\Models\Role;

$email = 'admin123';  // الإيميل
$password = 'password';  // كلمة المرور

// إنشاء المستخدم أو تحديثه إن كان موجوداً
$user = User::updateOrCreate(
    ['email' => $email],
    [
        'name'     => 'مدير النظام',
        'email'    => $email,
        'password' => bcrypt($password),
        'status'   => 'active',
    ]
);

// إسناد دور المدير العام
$adminRole = Role::where('name', 'admin')->first();
if ($adminRole) {
    $user->roles()->syncWithoutDetaching([$adminRole->id]);
    echo "✅ تم إنشاء المستخدم وإسناد دور المدير العام\n";
} else {
    echo "✅ تم إنشاء المستخدم (لم يُعثر على دور admin)\n";
}

echo "📧 الإيميل: {$email}\n";
echo "🔑 كلمة المرور: {$password}\n";
echo "⚠️  احذف هذا الملف الآن!\n";
