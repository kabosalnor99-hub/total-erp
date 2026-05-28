<?php

// المسار: app/Http/Controllers/NotificationController.php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // -------------------------------------------------------
    // List all notifications for authenticated user
    // -------------------------------------------------------

    public function index(Request $request): View
    {
        $query = Notification::forUser(auth()->id())
            ->orderByDesc('created_at');

        if ($request->filter === 'unread') {
            $query->unread();
        }

        if ($request->type) {
            $query->ofType($request->type);
        }

        $notifications = $query->paginate(20);
        $unreadCount   = Notification::forUser(auth()->id())->unread()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    // -------------------------------------------------------
    // Mark a single notification as read
    // -------------------------------------------------------

    public function markRead(int $id): RedirectResponse|JsonResponse
    {
        $notification = Notification::where('user_id', auth()->id())
            ->findOrFail($id);

        $notification->markAsRead();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        if ($notification->url) {
            return redirect($notification->url);
        }

        return back()->with('success', __('notifications.marked_read'));
    }

    // -------------------------------------------------------
    // Mark all unread notifications as read
    // -------------------------------------------------------

    public function markAllRead(): JsonResponse|RedirectResponse
    {
        Notification::forUser(auth()->id())
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', __('notifications.all_marked_read'));
    }

    // -------------------------------------------------------
    // Delete a notification
    // -------------------------------------------------------

    public function destroy(int $id): JsonResponse|RedirectResponse
    {
        Notification::where('user_id', auth()->id())->findOrFail($id)->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', __('notifications.deleted'));
    }

    // -------------------------------------------------------
    // Delete all read notifications
    // -------------------------------------------------------

    public function clearRead(): RedirectResponse
    {
        Notification::forUser(auth()->id())->where('is_read', true)->delete();

        return back()->with('success', __('notifications.cleared'));
    }

    // -------------------------------------------------------
    // API: Get recent unread notifications (for dropdown)
    // -------------------------------------------------------

    public function recent(): JsonResponse
    {
        $notifications = Notification::forUser(auth()->id())
            ->recent(10)
            ->get()
            ->map(fn($n) => [
                'id'         => $n->id,
                'title'      => $n->title,
                'body'       => $n->body,
                'icon'       => $n->icon,
                'color'      => $n->color,
                'color_class'=> $n->color_class,
                'url'        => $n->url,
                'is_read'    => $n->is_read,
                'time'       => $n->created_at->diffForHumans(),
            ]);

        $unreadCount = Notification::forUser(auth()->id())->unread()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ]);
    }
}
