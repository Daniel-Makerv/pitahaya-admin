<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();


        User::create([
            'name' => 'Pitamex',
            'email' => 'Pitamex@pitahayaorg.com',
            'password' => Hash::make('eu437C*
            '),
        ]);

        User::create([
            'name' => 'gerencia',
            'email' => 'gerencia@pitajayaorg',
            'password' => Hash::make("0K4'um=8&"),
        ]);


        User::create([
            'name' => 'gerencia',
            'email' => 'ema.juve.17@gmail.com',
            'password' => Hash::make("C$56g6$"),
        ]);

        User::create([
            'name' => 'gerencia',
            'email' => 'Omegajmayala@gmail.com',
            'password' => Hash::make("J4.c36?"),
        ]);

        $this->call([
            TypeFormSeeder::class,
        ]);
    }
}
