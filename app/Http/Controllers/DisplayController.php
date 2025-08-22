<?php

namespace App\Http\Controllers;

use App\Models\Screen;
use App\Models\UnifiedContentSet;
use Inertia\Inertia;

class DisplayController extends Controller
{
    /**
     * Show the public display for a specific screen.
     */
    public function show(Screen $screen)
    {
        $mediaItems = collect();

        if ($screen->is_unified_content && $screen->unified_content_set_id) {
            // إذا كان المحتوى موحدًا، قم بجلب الوسائط من المجموعة المحددة
            $contentSet = UnifiedContentSet::with('media')->find($screen->unified_content_set_id);
            if ($contentSet) {
                $mediaItems = $contentSet->media;
            }
        } else {
            // وإلا، قم بجلب الوسائط المخصصة للشاشة
            $mediaItems = $screen->media;
        }

        $media = $mediaItems->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'type' => $item->type,
                'path' => '/storage/' . $item->path,
            ];
        });

        return Inertia::render('Display/Index', [
            'screen' => $screen,
            'media' => $media,
        ]);
    }
}
