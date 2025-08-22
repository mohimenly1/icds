<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // إنشاء الصلاحيات
        Permission::create(['name' => 'manage doctors']);
        Permission::create(['name' => 'manage clinic structure']);
        Permission::create(['name' => 'manage schedules']);
        Permission::create(['name' => 'manage screens']); // صلاحية جديدة

        // إنشاء دور "مدير الشاشات" وإعطائه الصلاحية الخاصة به فقط
        $screenManagerRole = Role::create(['name' => 'Screen-Manager'])
            ->givePermissionTo('manage screens');

        // إنشاء دور "المدير العام" وإعطائه جميع الصلاحيات
        $superAdminRole = Role::create(['name' => 'Super-Admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        // إعطاء دور "المدير العام" للمستخدم الأول في النظام (اختياري)
        $user = User::find(1);
        if ($user) {
            $user->assignRole($superAdminRole);
        }
    }
}
