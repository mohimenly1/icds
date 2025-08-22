<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MediaController extends Controller
{
    /**
     * Display the media management page.
     */
    public function index()
    {
        return Inertia::render('Media/Index', [
            'media' => Media::latest()->get(),
        ]);
    }

    /**
     * Store newly created media files in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:20480', // 20MB Max per file
        ]);

        foreach ($validated['files'] as $file) {
            // تحديد نوع الملف بناءً على الامتداد
            $type = in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png']) ? 'image' : 'video';

            // تخزين الملف في مجلد public/storage/media
            $path = $file->store('media', 'public');

            Media::create([
                // استخدام اسم الملف الأصلي مع إزالة الامتداد
                'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'type' => $type,
                'path' => $path,
            ]);
        }

        return Redirect::back()->with('success', 'تم رفع الملفات بنجاح.');
    }

    /**
     * Remove the specified media file from storage.
     */
    public function destroy(Media $media)
    {
        // حذف الملف الفعلي من التخزين
        if (Storage::disk('public')->exists($media->path)) {
            Storage::disk('public')->delete($media->path);
        }

        // حذف السجل من قاعدة البيانات
        $media->delete();

        return Redirect::back()->with('success', 'تم حذف الملف بنجاح.');
    }
}
