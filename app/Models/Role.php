<?php

namespace App\Models;

use App\Services\CacheService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'display_name', 'guard_name', 'description',
    ];

    // ─── العلاقات ────────────────────────────────────────────────

    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    // ─── Cache ───────────────────────────────────────────────────

    /**
     * جيب كل الأدوار من الـ cache (تُستخدم في create/edit user)
     */
    public static function cachedAll(): \Illuminate\Support\Collection
    {
        return Cache::remember(
            CacheService::rolesListKey(),
            CacheService::TTL_ROLES,
            fn() => static::all()
        );
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function givePermissionTo(string|Permission $permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->firstOrFail();
        }
        $this->permissions()->syncWithoutDetaching([$permission->id]);

        // امسح صلاحيات كل مستخدمي هذا الدور
        CacheService::forgetAllPermissions();
    }

    public function syncPermissions(array $permissionNames): void
    {
        $ids = Permission::whereIn('name', $permissionNames)->pluck('id');
        $this->permissions()->sync($ids);

        // امسح صلاحيات كل المستخدمين
        CacheService::forgetAllPermissions();
    }

    // ─── إلغاء الـ cache تلقائياً ────────────────────────────────

    protected static function booted(): void
    {
        static::saved(function () {
            CacheService::forgetRoles();
            CacheService::forgetAllPermissions();
        });
        static::deleted(function () {
            CacheService::forgetRoles();
            CacheService::forgetAllPermissions();
        });
    }
}
