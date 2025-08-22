<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Screen;
use App\Models\UnifiedContentSet; // 1. إضافة استيراد جديد
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ScreenController extends Controller
{
    /**
     * Display the screens management page.
     */
    public function index()
    {
        return Inertia::render('Screens/Index', [
            'screens' => Screen::with('media')->get(),
            'media' => Media::all(),
            // 2. إرسال بيانات مجموعات المحتوى الموحد
            'unifiedContentSets' => UnifiedContentSet::all(),
        ]);
    }

    /**
     * Store a newly created screen in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
            'is_unified_content' => 'required|boolean',
            'unified_content_set_id' => 'nullable|required_if:is_unified_content,true|exists:unified_content_sets,id',
            'media_ids' => 'nullable|required_if:is_unified_content,false|array',
            'media_ids.*' => 'exists:media,id',
        ]);

        $screen = Screen::create([
            'name' => $validated['name'],
            'location' => $validated['location'],
            'is_active' => $validated['is_active'],
            'is_unified_content' => $validated['is_unified_content'],
            'unified_content_set_id' => $validated['is_unified_content'] ? $validated['unified_content_set_id'] : null,
            'unique_key' => (string) Str::uuid(),
        ]);

        if (!$screen->is_unified_content && isset($validated['media_ids'])) {
            $screen->media()->sync($validated['media_ids']);
        }

        return Redirect::back()->with('success', 'تم إنشاء الشاشة بنجاح.');
    }

    /**
     * Update the specified screen in storage.
     */
    public function update(Request $request, Screen $screen)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
            'is_unified_content' => 'required|boolean',
            'unified_content_set_id' => 'nullable|required_if:is_unified_content,true|exists:unified_content_sets,id',
            'media_ids' => 'nullable|required_if:is_unified_content,false|array',
            'media_ids.*' => 'exists:media,id',
        ]);
        
        $validated['unified_content_set_id'] = $validated['is_unified_content'] ? $validated['unified_content_set_id'] : null;
        $screen->update($validated);

        if ($screen->is_unified_content) {
            $screen->media()->detach(); // إزالة الوسائط المخصصة عند تفعيل المحتوى الموحد
        } elseif (isset($validated['media_ids'])) {
            $screen->media()->sync($validated['media_ids']);
        } else {
            $screen->media()->detach();
        }

        return Redirect::back()->with('success', 'تم تحديث الشاشة بنجاح.');
    }

    /**
     * Remove the specified screen from storage.
     */
    public function destroy(Screen $screen)
    {
        $screen->delete();
        return Redirect::back()->with('success', 'تم حذف الشاشة بنجاح.');
    }
}
