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
            ],
            [
                'id' => 2,
                'name' => 'Permite criar eventos',
                'slug' => Str::slug('Permite criar eventos'),
            ],
            [
                'id' => 3,
                'name' => 'Permite realizar logout',
                'slug' => Str::slug('Permite realizar logout'),
            ],
            [
                'id' => 4,
                'name' => 'Permite realizar pagamentos',
                'slug' => Str::slug('Permite realizar pagamentos'),
            ],
            [
                'id' => 5,
                'name' => 'Permite mostrar os eventos que o usuario pagou',
                'slug' => Str::slug('Permite mostrar os eventos que o usuario pagou'),
            ]
        ];

        foreach ($permissions as $permission)
        {
            Permission::create($permission);
        }
    }
}
