<?php

// المسار الكامل: app/Http/Controllers/WarehouseController.php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    // ─── قائمة المستودعات ────────────────────────────────────────────

    public function index(): View
    {
        $warehouses = Warehouse::withCount('stockMovements')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('warehouses.index', compact('warehouses'));
    }

    // ─── حفظ مستودع جديد ─────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'code'         => 'nullable|string|max:20|unique:warehouses,code',
            'location'     => 'nullable|string|max:255',
            'manager_name' => 'nullable|string|max:100',
            'phone'        => 'nullable|string|max:20',
            'is_default'   => 'boolean',
            'notes'        => 'nullable|string',
        ], [
            'name.required' => 'اسم المستودع مطلوب',
            'code.unique'   => 'كود المستودع مستخدم مسبقاً',
        ]);

        // إذا هذا هو الافتراضي، أزل الافتراضي من البقية
        if (!empty($data['is_default'])) {
            Warehouse::where('is_default', true)->update(['is_default' => false]);
        }

        $warehouse = Warehouse::create($data);

        ActivityLog::record('created', "إضافة مستودع: {$warehouse->name}", $warehouse);

        return back()->with('success', 'تم إضافة المستودع بنجاح.');
    }

    // ─── تحديث مستودع ────────────────────────────────────────────────

    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'code'         => "nullable|string|max:20|unique:warehouses,code,{$warehouse->id}",
            'location'     => 'nullable|string|max:255',
            'manager_name' => 'nullable|string|max:100',
            'phone'        => 'nullable|string|max:20',
            'is_active'    => 'boolean',
            'is_default'   => 'boolean',
            'notes'        => 'nullable|string',
        ]);

        if (!empty($data['is_default'])) {
            Warehouse::where('is_default', true)
                     ->where('id', '!=', $warehouse->id)
                     ->update(['is_default' => false]);
        }

        $warehouse->update($data);

        ActivityLog::record('updated', "تعديل مستودع: {$warehouse->name}", $warehouse);

        return back()->with('success', 'تم تحديث المستودع بنجاح.');
    }

    // ─── حذف مستودع ──────────────────────────────────────────────────

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        if ($warehouse->stockMovements()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف مستودع له حركات مخزون.');
        }

        if ($warehouse->is_default) {
            return back()->with('error', 'لا يمكن حذف المستودع الافتراضي.');
        }

        ActivityLog::record('deleted', "حذف مستودع: {$warehouse->name}", $warehouse);
        $warehouse->delete();

        return back()->with('success', 'تم حذف المستودع بنجاح.');
    }

    // ─── تقرير حركة مخزون المستودع ───────────────────────────────────

    public function movements(Warehouse $warehouse, Request $request): View
    {
        $movements = StockMovement::with('product', 'user')
            ->where('warehouse_id', $warehouse->id)
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->type))
            ->when($request->filled('from'), fn($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'),   fn($q) => $q->whereDate('created_at', '<=', $request->to))
            ->latest()
            ->paginate(20)->withQueryString();

        return view('warehouses.movements', compact('warehouse', 'movements'));
    }
}
