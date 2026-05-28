<?php

namespace App\Models;

use App\Services\CacheService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'avatar',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ─── العلاقات ────────────────────────────────────────────────

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // ─── الصلاحيات (مع Cache) ────────────────────────────────────

    /**
     * جيب أدوار المستخدم من الـ cache
     */
    public function getCachedRoles(): \Illuminate\Support\Collection
    {
        return Cache::remember(
            CacheService::rolesKey($this->id),
            CacheService::TTL_PERMISSIONS,
            fn() => $this->roles()->with('permissions')->get()
        );
    }

    /**
     * جيب كل صلاحيات المستخدم من الـ cache (set من الأسماء)
     */
    public function getCachedPermissions(): array
    {
        return Cache::remember(
            CacheService::permissionsKey($this->id),
            CacheService::TTL_PERMISSIONS,
            function () {
                // admin له كل شيء → نخزّن علامة خاصة
                if ($this->roles()->where('name', 'admin')->exists()) {
                    return ['__admin__'];
                }

                return $this->roles()
                    ->with('permissions')
                    ->get()
                    ->flatMap(fn($role) => $role->permissions->pluck('name'))
                    ->unique()
                    ->values()
                    ->toArray();
            }
        );
    }

    public function hasRole(string $roleName): bool
    {
        $roles = $this->getCachedRoles();
        return $roles->contains('name', $roleName);
    }

    public function hasPermission(string $permissionName): bool
    {
        $permissions = $this->getCachedPermissions();

        // admin له كل الصلاحيات
        if (in_array('__admin__', $permissions, true)) {
            return true;
        }

        return in_array($permissionName, $permissions, true);
    }

    public function assignRole(string|Role $role): void
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }
        $this->roles()->syncWithoutDetaching([$role->id]);

        // امسح الـ cache فور تغيير الدور
        CacheService::forgetUserPermissions($this->id);
    }

    public function removeRole(string|Role $role): void
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }
        $this->roles()->detach($role->id);

        // امسح الـ cache
        CacheService::forgetUserPermissions($this->id);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function getRoleNameAttribute(): string
    {
        return $this->getCachedRoles()->first()?->display_name ?? '—';
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : asset('images/default-avatar.png');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
