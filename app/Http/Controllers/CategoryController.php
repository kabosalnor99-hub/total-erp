<?php

// المسار الكامل: app/Http/Controllers/CategoryController.php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Services\CacheService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    // ─── قائمة الفئات ────────────────────────────────────────────────

    public function index(): View
    {
        $categories = Category::with('parent', 'children')
            ->root()
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name_ar')
            ->get();

        return view('categories.index', compact('categories'));
    }

    // ─── حفظ فئة جديدة ───────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name_ar'    => 'required|string|max:100',
            'name_en'    => 'nullable|string|max:100',
            'parent_id'  => 'nullable|exists:categories,id',
            'icon'       => 'nullable|string|max:50',
            'color'      => 'nullable|string|size:7',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'name_ar.required' => 'اسم الفئة بالعربية مطلوب',
        ]);

        $category = Category::create($data);

        ActivityLog::record('created', "إضافة فئة: {$category->name_ar}", $category);

        return back()->with('success', 'تم إضافة الفئة بنجاح.');
    }

    // ─── تحديث فئة ───────────────────────────────────────────────────

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'name_ar'    => 'required|string|max:100',
            'name_en'    => 'nullable|string|max:100',
            'parent_id'  => 'nullable|exists:categories,id',
            'icon'       => 'nullable|string|max:50',
            'color'      => 'nullable|string|size:7',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'boolean',
        ]);

        // منع أن تكون الفئة أباً لنفسها
        if (isset($data['parent_id']) && $data['parent_id'] == $category->id) {
            return back()->with('error', 'لا يمكن للفئة أن تكون أباً لنفسها.');
        }

        $category->update($data);

        ActivityLog::record('updated', "تعديل فئة: {$category->name_ar}", $category);

        return back()->with('success', 'تم تحديث الفئة بنجاح.');
    }

    // ─── حذف فئة ─────────────────────────────────────────────────────

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف فئة تحتوي على منتجات.');
        }

        if ($category->children()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف فئة تحتوي على فئات فرعية.');
        }

        ActivityLog::record('deleted', "حذف فئة: {$category->name_ar}", $category);
        $category->delete(); // ← booted() يمسح الـ cache تلقائياً

        return back()->with('success', 'تم حذف الفئة بنجاح.');
    }
}
