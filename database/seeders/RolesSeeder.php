<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesSeeder extends Seeder {
    public function run(): void {
        $admin = Role::firstOrCreate(['name'=>'admin']); //administrador
        $employee = Role::firstOrCreate(['name'=>'employee']); //empleado
        $secretary = Role::firstOrCreate(['name'=>'secretary']); //secretaria

        foreach (['users.manage','orders.view','orders.create','orders.assign','inventory.view','inventory.edit'] as $p) {
            Permission::firstOrCreate(['name'=>$p]); //Control de acceso posterior a asignación de roles
        }
        //Control de acceso segun rol asignado
        $admin->givePermissionTo(Permission::all());
        $employee->givePermissionTo(['orders.view','orders.create']);
        $secretary->givePermissionTo(['orders.view']);

        $user = User::firstOrCreate(
            ['email'=>'admin@raquelita.com'],
            ['name'=>'Admin Raquel López','password'=>Hash::make('Admin@12345'),'email_verified_at'=>now()]
        );
        $user->assignRole('admin');
    }
}

