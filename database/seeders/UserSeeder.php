<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $developer = [
            'name' => 'Desenvolvedor',
            'email' => 'dev@zehticket.com.br',
            'password' => Hash::make('dev102030'),
        ];

        $user = User::create($developer);
        $user->roles()->attach([1]);
    }
}