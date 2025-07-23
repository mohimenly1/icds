<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class FloorController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255', 'level' => 'required|integer|unique:floors,level']);
        Floor::create($request->all());
        return Redirect::back()->with('success', 'تمت إضافة الطابق بنجاح.');
    }

    public function update(Request $request, Floor $floor)
    {
        $request->validate(['name' => 'required|string|max:255', 'level' => 'required|integer|unique:floors,level,' . $floor->id]);
        $floor->update($request->all());
        return Redirect::back()->with('success', 'تم تحديث الطابق بنجاح.');
    }

    public function destroy(Floor $floor)
    {
        if ($floor->rooms()->count() > 0) {
            return Redirect::back()->with('error', 'لا يمكن حذف الطابق لأنه يحتوي على غرف.');
        }
        $floor->delete();
        return Redirect::back()->with('success', 'تم حذف الطابق بنجاح.');
    }
}
