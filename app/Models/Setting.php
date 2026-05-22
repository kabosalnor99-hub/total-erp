<?php

// المسار: app/Models/Setting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label_ar',
        'label_en',
        'description_ar',
        'description_en',
        'is_public',
        'is_editable',
        'sort_order',
    ];

    protected $casts = [
        'is_public'   => 'boolean',
        'is_editable' => 'boolean',
        'sort_order'  => 'integer',
    ];

    // -------------------------------------------------------
    // Static Helpers
    // -------------------------------------------------------

    /**
     * Get a setting value by key with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = Cache::rememberForever("setting_{$key}", function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, mixed $value): void
    {
        static::where('key', $key)->update(['value' => $value]);
        Cache::forget("setting_{$key}");
    }

    /**
     * Get all settings grouped by group name.
     */
    public static function getAllGrouped(): array
    {
        return Cache::remember('all_settings_grouped', 3600, function () {
            return static::orderBy('group')->orderBy('sort_order')
                ->get()
                ->groupBy('group')
                ->toArray();
        });
    }

    /**
     * Flush all settings cache.
     */
    public static function flushCache(): void
    {
        Cache::flush();
    }

    // -------------------------------------------------------
    // Internal
    // -------------------------------------------------------

    /**
     * Cast value to the appropriate PHP type.
     */
    protected static function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($value, true),
            'float'   => (float) $value,
            default   => $value,
        };
    }

    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------

    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeEditable($query)
    {
        return $query->where('is_editable', true);
    }

    // -------------------------------------------------------
    // Accessors
    // -------------------------------------------------------

    /**
     * Return the label based on current app locale.
     */
    public function getLabelAttribute(): string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->label_ar : $this->label_en;
    }

    /**
     * Return the description based on current app locale.
     */
    public function getDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->description_ar : $this->description_en;
    }

    /**
     * Return the typed value.
     */
    public function getTypedValueAttribute(): mixed
    {
        return static::castValue($this->value, $this->type);
    }
}
