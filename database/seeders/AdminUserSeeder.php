<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@raquelita.com'],
            ['name' => 'Admin', 'password' => bcrypt('Admin123!')]
        );

        // Asigna únicamente 'admin'
        $admin->syncRoles(['admin']);
    }
}
