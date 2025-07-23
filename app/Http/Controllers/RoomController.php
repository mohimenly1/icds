<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class RoomController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(['floor_id' => 'required|exists:floors,id', 'room_number' => 'required|string|max:255', 'name' => 'nullable|string|max:255']);
        Room::create($request->all());
        return Redirect::back()->with('success', 'تمت إضافة الغرفة بنجاح.');
    }

    public function update(Request $request, Room $room)
    {
        $request->validate(['floor_id' => 'required|exists:floors,id', 'room_number' => 'required|string|max:255', 'name' => 'nullable|string|max:255']);
        $room->update($request->all());
        return Redirect::back()->with('success', 'تم تحديث الغرفة بنجاح.');
    }

    public function destroy(Room $room)
    {
        // تم تحديث الشرط ليفحص وجود أي أطباء في الغرفة
        if ($room->doctors()->count() > 0) {
            return Redirect::back()->with('error', 'لا يمكن حذف الغرفة لأنها مشغولة من قبل طبيب أو أكثر.');
        }
        
        $room->delete();
        return Redirect::back()->with('success', 'تم حذف الغرفة بنجاح.');
    }
}
