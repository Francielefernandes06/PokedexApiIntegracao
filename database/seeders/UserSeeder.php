<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Exemplo Usuário',
            'email' => 'exemplo@usuario.com',
            'password' => Hash::make('senha123'),
            'pontuation' => 1000,
            'role' => 'user',
        ]);


        User::create([
            'name' => 'Outro Usuário',
            'email' => 'outro@usuario.com',
            'password' => Hash::make('outrasenha'),
            'pontuation' => 1000,
            'role' => 'user',
        ]);
    }
}
