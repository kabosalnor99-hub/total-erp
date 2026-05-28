<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\CacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class WarmCache extends Command
{
    protected $signature   = 'cache:warm';
    protected $description = 'تسخين الـ cache بعد الـ deployment (صلاحيات، فئات، مستودعات، أدوار)';

    public function handle(): int
    {
        $this->info('🔥 بدء تسخين الـ cache...');

        // 1. الفئات النشطة
        Cache::forget(CacheService::categoriesKey());
        Category::cachedActive();
        $this->line('  ✅ الفئات');

        // 2. المستودعات النشطة
        Cache::forget(CacheService::warehousesKey());
        Warehouse::cachedActive();
        $this->line('  ✅ المستودعات');

        // 3. الأدوار
        Cache::forget(CacheService::rolesListKey());
        Role::cachedAll();
        $this->line('  ✅ الأدوار');

        // 4. صلاحيات كل المستخدمين النشطين
        $users = User::where('status', 'active')->get();
        foreach ($users as $user) {
            Cache::forget(CacheService::permissionsKey($user->id));
            Cache::forget(CacheService::rolesKey($user->id));
            $user->getCachedPermissions();
        }
        $this->line("  ✅ صلاحيات {$users->count()} مستخدم");

        $this->info('✨ اكتمل تسخين الـ cache بنجاح.');

        return self::SUCCESS;
    }
}

