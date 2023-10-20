<?php

namespace Database\Seeders;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FavoritesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::first();
        $secondUser = User::find(2);


        Favorite::create([
            'user_id' => $user->id,
            'pokemon_id' => 14,
            'date_added' => now(),
        ]);


        Favorite::create([
            'user_id' => $user->id,
            'pokemon_id' => 29,
            'date_added' => now(),
        ]);

        Favorite::create([
            'user_id' => $secondUser->id,
            'pokemon_id' => 33,
            'date_added' => now(), 
        ]);
    }
}
