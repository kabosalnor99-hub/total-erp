<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\CacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $query = Notification::forUser(auth()->id())->orderByDesc('created_at');

        if ($request->filter === 'unread') {
            $query->unread();
        }
        if ($request->type) {
            $query->ofType($request->type);
        }

        $notifications = $query->paginate(20);

        // عدد غير المقروء من الـ cache
        $unreadCount = $this->getUnreadCount();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markRead(int $id): RedirectResponse|JsonResponse
    {
        $notification = Notification::where('user_id', auth()->id())->findOrFail($id);
        $notification->markAsRead();

        CacheService::forgetNotifications(auth()->id());

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        if ($notification->url) {
            return redirect($notification->url);
        }

        return back()->with('success', __('notifications.marked_read'));
    }

    public function markAllRead(): JsonResponse|RedirectResponse
    {
        Notification::forUser(auth()->id())->unread()
            ->update(['is_read' => true, 'read_at' => now()]);

        CacheService::forgetNotifications(auth()->id());

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', __('notifications.all_marked_read'));
    }

    public function destroy(int $id): JsonResponse|RedirectResponse
    {
        Notification::where('user_id', auth()->id())->findOrFail($id)->delete();

        CacheService::forgetNotifications(auth()->id());

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', __('notifications.deleted'));
    }

    public function clearRead(): RedirectResponse
    {
        Notification::forUser(auth()->id())->where('is_read', true)->delete();

        CacheService::forgetNotifications(auth()->id());

        return back()->with('success', __('notifications.cleared'));
    }

    /**
     * API: جيب آخر الإشعارات من الـ cache
     */
    public function recent(): JsonResponse
    {
        $userId = auth()->id();

        $notifications = Cache::remember(
            CacheService::notificationsKey($userId),
            CacheService::TTL_NOTIFICATIONS,
            function () use ($userId) {
                return Notification::forUser($userId)
                    ->recent(10)
                    ->get()
                    ->map(fn($n) => [
                        'id'          => $n->id,
                        'title'       => $n->title,
                        'body'        => $n->body,
                        'icon'        => $n->icon,
                        'color'       => $n->color,
                        'color_class' => $n->color_class,
                        'url'         => $n->url,
                        'is_read'     => $n->is_read,
                        'time'        => $n->created_at->diffForHumans(),
                    ]);
            }
        );

        $unreadCount = $this->getUnreadCount();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ]);
    }

    // ─── Helper ──────────────────────────────────────────────────────

    private function getUnreadCount(): int
    {
        $userId = auth()->id();

        return Cache::remember(
            CacheService::unreadCountKey($userId),
            CacheService::TTL_NOTIFICATIONS,
            fn() => Notification::forUser($userId)->unread()->count()
        );
    }
}
