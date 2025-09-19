<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $m = User::firstOrCreate(
            ['email' => 'mecanico@raquelita.com'],
            ['name' => 'Mecánico Demo', 'password' => bcrypt('Mecanico123!')]
        );
        $m->syncRoles(['mecanico']);

        $s = User::firstOrCreate(
            ['email' => 'secretaria@raquelita.com'],
            ['name' => 'Secretaria Demo', 'password' => bcrypt('Secretaria123!')]
        );
        $s->syncRoles(['secretaria']);
    }
}
