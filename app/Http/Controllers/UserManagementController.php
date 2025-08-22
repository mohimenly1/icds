<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    /**
     * Display the user management page.
     */
    public function index()
    {
        return Inertia::render('Users/Index', [
            'users' => User::with('roles')->get(),
            'roles' => Role::all()->pluck('name'),
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => 'required|array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->syncRoles($request->roles);

        return Redirect::back()->with('success', 'تم إنشاء المستخدم بنجاح.');
    }

    /**
     * Update the roles for a specific user.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        // استخدام syncRoles لمزامنة الأدوار الجديدة للمستخدم
        $user->syncRoles($request->roles);

        return Redirect::back()->with('success', 'تم تحديث صلاحيات المستخدم بنجاح.');
    }
}
