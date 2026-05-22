<?php

// المسار: app/Models/Notification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title_ar',
        'title_en',
        'body_ar',
        'body_en',
        'icon',
        'color',
        'url',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data'     => 'array',
        'is_read'  => 'boolean',
        'read_at'  => 'datetime',
    ];

    // -------------------------------------------------------
    // Relationships
    // -------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderByDesc('created_at')->limit($limit);
    }

    // -------------------------------------------------------
    // Accessors
    // -------------------------------------------------------

    public function getTitleAttribute(): string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->title_ar : $this->title_en;
    }

    public function getBodyAttribute(): string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->body_ar : $this->body_en;
    }

    public function getColorClassAttribute(): string
    {
        return match ($this->color) {
            'red'    => 'text-red-500 bg-red-50',
            'green'  => 'text-green-500 bg-green-50',
            'yellow' => 'text-yellow-500 bg-yellow-50',
            'teal'   => 'text-teal-500 bg-teal-50',
            default  => 'text-blue-500 bg-blue-50',
        };
    }

    // -------------------------------------------------------
    // Actions
    // -------------------------------------------------------

    /**
     * Mark this notification as read.
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Create a new notification for a user.
     */
    public static function notify(
        int $userId,
        string $type,
        string $titleAr,
        string $titleEn,
        string $bodyAr,
        string $bodyEn,
        string $url = null,
        array $data = [],
        string $icon = 'bell',
        string $color = 'blue'
    ): static {
        return static::create([
            'user_id'  => $userId,
            'type'     => $type,
            'title_ar' => $titleAr,
            'title_en' => $titleEn,
            'body_ar'  => $bodyAr,
            'body_en'  => $bodyEn,
            'url'      => $url,
            'data'     => $data,
            'icon'     => $icon,
            'color'    => $color,
        ]);
    }

    /**
     * Notify all users with a given role.
     */
    public static function notifyRole(
        string $roleName,
        string $type,
        string $titleAr,
        string $titleEn,
        string $bodyAr,
        string $bodyEn,
        string $url = null,
        array $data = [],
        string $icon = 'bell',
        string $color = 'blue'
    ): void {
        $users = User::whereHas('roles', fn($q) => $q->where('name', $roleName))->get();

        foreach ($users as $user) {
            static::notify(
                $user->id, $type,
                $titleAr, $titleEn,
                $bodyAr, $bodyEn,
                $url, $data, $icon, $color
            );
        }
    }
}
