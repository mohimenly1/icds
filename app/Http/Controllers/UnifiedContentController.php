<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\UnifiedContentSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class UnifiedContentController extends Controller
{
    /**
     * Display the unified content management page.
     */
    public function index()
    {
        return Inertia::render('UnifiedContent/Index', [
            'contentSets' => UnifiedContentSet::with('media')->get(),
            'media' => Media::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:unified_content_sets,name',
            'media_ids' => 'required|array',
            'media_ids.*' => 'exists:media,id',
        ]);

        DB::transaction(function () use ($validated) {
            $set = UnifiedContentSet::create(['name' => $validated['name']]);
            $set->media()->sync($validated['media_ids']);
        });

        return Redirect::back()->with('success', 'تم إنشاء مجموعة المحتوى الموحد بنجاح.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UnifiedContentSet $unifiedContentSet)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:unified_content_sets,name,' . $unifiedContentSet->id,
            'media_ids' => 'required|array',
            'media_ids.*' => 'exists:media,id',
        ]);

        DB::transaction(function () use ($unifiedContentSet, $validated) {
            $unifiedContentSet->update(['name' => $validated['name']]);
            $unifiedContentSet->media()->sync($validated['media_ids']);
        });

        return Redirect::back()->with('success', 'تم تحديث مجموعة المحتوى بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UnifiedContentSet $unifiedContentSet)
    {
        // Note: The database migration sets `unified_content_set_id` to null on delete,
        // so screens will automatically disconnect from this set.
        $unifiedContentSet->delete();

        return Redirect::back()->with('success', 'تم حذف مجموعة المحتوى بنجاح.');
    }
}
