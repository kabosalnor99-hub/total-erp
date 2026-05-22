<?php

// المسار: app/Http/Controllers/SettingController.php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:settings.view'])->only('index');
        $this->middleware(['auth', 'permission:settings.edit'])->only(['update', 'updateGroup']);
        $this->middleware(['auth', 'permission:settings.backup'])->only(['backup', 'clearCache']);
    }

    // -------------------------------------------------------
    // Display settings page grouped by category
    // -------------------------------------------------------

    public function index(): View
    {
        $groups = [
            'general'       => __('settings.group_general'),
            'company'       => __('settings.group_company'),
            'invoice'       => __('settings.group_invoice'),
            'pos'           => __('settings.group_pos'),
            'hr'            => __('settings.group_hr'),
            'accounting'    => __('settings.group_accounting'),
            'notifications' => __('settings.group_notifications'),
        ];

        $settings = Setting::orderBy('group')->orderBy('sort_order')->get()->groupBy('group');

        return view('settings.index', compact('groups', 'settings'));
    }

    // -------------------------------------------------------
    // Update a single setting key
    // -------------------------------------------------------

    public function update(Request $request, string $key): RedirectResponse
    {
        $setting = Setting::where('key', $key)->firstOrFail();

        if (!$setting->is_editable) {
            return back()->with('error', __('settings.not_editable'));
        }

        $value = $request->input('value');

        // Handle file upload
        if ($request->hasFile('value') && $setting->type === 'file') {
            $path  = $request->file('value')->store('settings', 'public');
            $value = $path;
        }

        // Handle boolean toggle
        if ($setting->type === 'boolean') {
            $value = $request->has('value') ? '1' : '0';
        }

        Setting::set($key, $value);

        return back()->with('success', __('settings.updated_success'));
    }

    // -------------------------------------------------------
    // Update all settings in a group at once
    // -------------------------------------------------------

    public function updateGroup(Request $request, string $group): RedirectResponse
    {
        $settings = Setting::group($group)->editable()->get();

        foreach ($settings as $setting) {
            $value = $request->input($setting->key);

            if ($request->hasFile($setting->key) && $setting->type === 'file') {
                // Delete old file if exists
                if ($setting->value && Storage::disk('public')->exists($setting->value)) {
                    Storage::disk('public')->delete($setting->value);
                }
                $path  = $request->file($setting->key)->store('settings', 'public');
                $value = $path;
            } elseif ($setting->type === 'boolean') {
                $value = $request->has($setting->key) ? '1' : '0';
            }

            if ($value !== null) {
                Setting::set($setting->key, $value);
            }
        }

        return back()->with('success', __('settings.group_updated', ['group' => $group]));
    }

    // -------------------------------------------------------
    // Clear application cache
    // -------------------------------------------------------

    public function clearCache(): RedirectResponse
    {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');
        Setting::flushCache();

        return back()->with('success', __('settings.cache_cleared'));
    }

    // -------------------------------------------------------
    // Run database backup
    // -------------------------------------------------------

    public function backup(): RedirectResponse
    {
        try {
            Artisan::call('erp:backup-database');
            $output = Artisan::output();
            return back()->with('success', __('settings.backup_success') . ' - ' . trim($output));
        } catch (\Exception $e) {
            return back()->with('error', __('settings.backup_failed') . ': ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------
    // API: Get public settings as JSON (used by JS)
    // -------------------------------------------------------

    public function publicSettings(): \Illuminate\Http\JsonResponse
    {
        $settings = Setting::public()->editable()->get()
            ->mapWithKeys(fn($s) => [$s->key => $s->typed_value]);

        return response()->json($settings);
    }
}
