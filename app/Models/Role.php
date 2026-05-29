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
        'name',
        'display_name',
        'guard_name',
        'description',
    ];

    // ─── Cache ───────────────────────────────────────────────────────

    /**
     * جلب كل الأدوار من الـ cache
     * يُستخدم في صفحة إدارة المستخدمين والصلاحيات
     */
    public static function cachedAll(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            CacheService::rolesListKey(),
            CacheService::TTL_ROLES,
            fn () => self::with('permissions')
                ->orderBy('name')
                ->get()
        );
    }

    /**
     * مسح الـ cache تلقائياً عند أي تعديل على الأدوار أو صلاحياتها
     */
    protected static function booted(): void
    {
        $flush = function (self $role) {
            CacheService::forgetRoles();
            // أي تغيير في الدور يؤثر على صلاحيات كل مستخدميه
            $role->users()->pluck('id')->each(
                fn ($id) => CacheService::forgetUserPermissions($id)
            );
        };

        static::created(fn (self $role) => CacheService::forgetRoles());
        static::updated($flush);
        static::deleted($flush);
    }

    // ─── العلاقات ────────────────────────────────────────────────

    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function givePermissionTo(string|Permission $permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->firstOrFail();
        }
        $this->permissions()->syncWithoutDetaching([$permission->id]);
    }

    public function syncPermissions(array $permissionNames): void
    {
        $ids = Permission::whereIn('name', $permissionNames)->pluck('id');
        $this->permissions()->sync($ids);

        // مسح صلاحيات كل مستخدمي هذا الدور من الـ cache
        $this->users()->pluck('id')->each(
            fn ($id) => CacheService::forgetUserPermissions($id)
        );
        CacheService::forgetRoles();
    }
}
