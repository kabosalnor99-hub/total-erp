<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    // ─── عرض قائمة المستخدمين ────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = User::with('roles')->latest();

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($w) => $w->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%"));
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', fn($w) => $w->where('name', $request->role));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
    }

    // ─── عرض مستخدم واحد ─────────────────────────────────────────────

    public function show(User $user): View
    {
        $user->load('roles');
        $logs = ActivityLog::where('user_id', $user->id)->latest()->limit(20)->get();
        return view('users.show', compact('user', 'logs'));
    }

    // ─── نموذج إنشاء مستخدم ──────────────────────────────────────────

    public function create(): View
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    // ─── حفظ مستخدم جديد ─────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'nullable|email|unique:users,email',
            'phone'    => 'nullable|string|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'role_id'  => 'required|exists:roles,id',
            'avatar'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status'   => 'required|in:active,inactive',
        ], [
            'name.required'     => 'الاسم مطلوب',
            'email.unique'      => 'البريد الإلكتروني مستخدم مسبقاً',
            'phone.unique'      => 'رقم الهاتف مستخدم مسبقاً',
            'password.min'      => 'كلمة المرور 8 أحرف على الأقل',
            'password.confirmed'=> 'كلمة المرور غير متطابقة',
            'role_id.required'  => 'يجب تحديد الدور',
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create($data);
        $user->assignRole(Role::find($data['role_id']));

        ActivityLog::record('created', "إنشاء مستخدم: {$user->name}", $user);

        return redirect()->route('users.index')
            ->with('success', 'تم إنشاء المستخدم بنجاح.');
    }

    // ─── نموذج تعديل مستخدم ──────────────────────────────────────────

    public function edit(User $user): View
    {
        $roles = Role::all();
        $user->load('roles');
        return view('users.edit', compact('user', 'roles'));
    }

    // ─── تحديث مستخدم ────────────────────────────────────────────────

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => ['nullable', 'email', Rule::unique('users')->ignore($user->id)],
            'phone'   => ['nullable', 'string', Rule::unique('users')->ignore($user->id)],
            'role_id' => 'required|exists:roles,id',
            'avatar'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status'  => 'required|in:active,inactive',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $old = $user->only(['name', 'email', 'phone', 'status']);
        $user->update($data);

        // تحديث الدور
        $user->roles()->sync([$data['role_id']]);

        ActivityLog::record('updated', "تعديل مستخدم: {$user->name}", $user, $old, $user->fresh()->only(['name', 'email', 'phone', 'status']));

        return redirect()->route('users.index')
            ->with('success', 'تم تحديث المستخدم بنجاح.');
    }

    // ─── حذف مستخدم ──────────────────────────────────────────────────

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك حذف حسابك الخاص.');
        }

        ActivityLog::record('deleted', "حذف مستخدم: {$user->name}", $user);
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'تم حذف المستخدم بنجاح.');
    }

    // ─── الملف الشخصي ────────────────────────────────────────────────

    public function profile(): View
    {
        $user = auth()->user()->load('roles');
        return view('users.profile', compact('user'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $data = $request->validate([
            'name'   => 'required|string|max:100',
            'email'  => ['nullable', 'email', Rule::unique('users')->ignore($user->id)],
            'phone'  => ['nullable', 'string', Rule::unique('users')->ignore($user->id)],
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return back()->with('success', 'تم تحديث الملف الشخصي بنجاح.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ], [
            'current_password.required' => 'كلمة المرور الحالية مطلوبة',
            'password.min'              => 'كلمة المرور الجديدة 8 أحرف على الأقل',
            'password.confirmed'        => 'كلمة المرور غير متطابقة',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'كلمة المرور الحالية غير صحيحة.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        ActivityLog::record('password_changed', 'تغيير كلمة المرور');

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح.');
    }
}
