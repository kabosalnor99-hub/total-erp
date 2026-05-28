<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * CacheService — مركز إدارة الـ cache في النظام
 *
 * المبدأ:
 *   - كل نوع بيانات له key محدد ومدة TTL مناسبة
 *   - عند أي تعديل على البيانات → invalidate الـ cache فوراً
 *   - لا نحذف كل الـ cache دفعةً واحدة (خطر) → نحذف بالـ tags أو بالـ keys
 */
class CacheService
{
    // ─── TTL ثوابت (بالثواني) ───────────────────────────────────────────
    const TTL_PERMISSIONS   = 3600;     // الصلاحيات: ساعة كاملة
    const TTL_SETTINGS      = 86400;    // الإعدادات: 24 ساعة
    const TTL_CATEGORIES    = 1800;     // الفئات: 30 دقيقة
    const TTL_WAREHOUSES    = 1800;     // المستودعات: 30 دقيقة
    const TTL_ROLES         = 3600;     // الأدوار: ساعة
    const TTL_DASHBOARD     = 300;      // Dashboard: 5 دقائق
    const TTL_STATS         = 300;      // إحصائيات المنتجات: 5 دقائق
    const TTL_NOTIFICATIONS = 60;       // إشعارات: دقيقة واحدة (تتغير كثيراً)
    const TTL_REPORT        = 600;      // التقارير: 10 دقائق

    // ─── Keys ────────────────────────────────────────────────────────────
    public static function permissionsKey(int $userId): string
    {
        return "user_permissions_{$userId}";
    }

    public static function rolesKey(int $userId): string
    {
        return "user_roles_{$userId}";
    }

    public static function categoriesKey(): string
    {
        return 'active_categories';
    }

    public static function warehousesKey(): string
    {
        return 'active_warehouses';
    }

    public static function rolesListKey(): string
    {
        return 'roles_list';
    }

    public static function dashboardKey(): string
    {
        return 'dashboard_stats';
    }

    public static function productStatsKey(): string
    {
        return 'product_stats';
    }

    public static function notificationsKey(int $userId): string
    {
        return "notifications_recent_{$userId}";
    }

    public static function unreadCountKey(int $userId): string
    {
        return "notifications_unread_{$userId}";
    }

    public static function settingsGroupKey(string $group): string
    {
        return "settings_group_{$group}";
    }

    // ─── Invalidation Methods ────────────────────────────────────────────

    /** عند تغيير صلاحيات/دور مستخدم معين */
    public static function forgetUserPermissions(int $userId): void
    {
        Cache::forget(self::permissionsKey($userId));
        Cache::forget(self::rolesKey($userId));
    }

    /** عند تغيير صلاحيات دور كامل → امسح كل المستخدمين */
    public static function forgetAllPermissions(): void
    {
        // نجيب كل المستخدمين النشطين ونمسح cache كل واحد
        $userIds = \App\Models\User::pluck('id');
        foreach ($userIds as $id) {
            Cache::forget(self::permissionsKey($id));
            Cache::forget(self::rolesKey($id));
        }
    }

    /** عند إضافة/تعديل/حذف فئة */
    public static function forgetCategories(): void
    {
        Cache::forget(self::categoriesKey());
    }

    /** عند إضافة/تعديل/حذف مستودع */
    public static function forgetWarehouses(): void
    {
        Cache::forget(self::warehousesKey());
    }

    /** عند إضافة/تعديل/حذف دور */
    public static function forgetRoles(): void
    {
        Cache::forget(self::rolesListKey());
    }

    /** عند أي تغيير في المبيعات/المخزون */
    public static function forgetDashboard(): void
    {
        Cache::forget(self::dashboardKey());
    }

    /** عند إضافة/حذف/تعديل منتج */
    public static function forgetProductStats(): void
    {
        Cache::forget(self::productStatsKey());
        Cache::forget(self::dashboardKey()); // لأن Dashboard يعرض إحصائيات المنتجات
    }

    /** عند قراءة/حذف إشعار */
    public static function forgetNotifications(int $userId): void
    {
        Cache::forget(self::notificationsKey($userId));
        Cache::forget(self::unreadCountKey($userId));
    }

    /** عند تغيير الإعدادات */
    public static function forgetSettings(string $group = null): void
    {
        if ($group) {
            Cache::forget(self::settingsGroupKey($group));
        } else {
            // امسح كل مجموعات الإعدادات
            foreach (['general', 'company', 'invoice', 'pos', 'hr', 'accounting', 'notifications'] as $g) {
                Cache::forget(self::settingsGroupKey($g));
            }
            Cache::forget('all_settings_grouped');
        }
    }
}

