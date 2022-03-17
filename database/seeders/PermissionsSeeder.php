<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            [
                'id' => 1,
                'name' => 'Permite verificar usuario logado',
                'slug' => Str::slug('Permite verificar usuario logado'),
            ]
        ];

        foreach ($permissions as $permission)
        {
            Permission::create($permission);
        }

        return true;
    }
}
