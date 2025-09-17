<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'mecanico', 'secretaria'] as $name) {
            Role::firstOrCreate(['name' => $name]);
        }
    }
}
