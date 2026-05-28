<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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

    public function posSessions()
    {
        return $this->hasMany(PosSession::class);
    }

    // ─── الصلاحيات ───────────────────────────────────────────────

    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function hasPermission(string $permissionName): bool
    {
        // المدير العام لديه كل الصلاحيات
        if ($this->hasRole('admin')) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', fn($q) => $q->where('name', $permissionName))
            ->exists();
    }

    public function assignRole(string|Role $role): void
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }
        $this->roles()->syncWithoutDetaching([$role->id]);
    }

    public function removeRole(string|Role $role): void
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }
        $this->roles()->detach($role->id);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function getRoleNameAttribute(): string
    {
        return $this->roles->first()?->display_name ?? '—';
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
