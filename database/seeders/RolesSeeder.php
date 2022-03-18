<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'id' => 1,
                'name' => 'Desenvolvedor',
                'slug' => Str::slug('Desenvolvedor'),
            ],
            [
                'id' => 2,
                'name' => 'Usuario',
                'slug' => Str::slug('Usuario'),
            ]
        ];

        foreach ($roles as $role)
        {
            $role_created = Role::create($role);

            if ($role['id'] === 1)
            {
                $permissions = Permission::get()->pluck('id');
                $role_created->permissions()->attach($permissions);
            }
        }

        return true;
    }
}