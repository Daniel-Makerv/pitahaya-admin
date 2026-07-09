<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        User::firstOrCreate(
            [
                'email' => 'Investigapitamex@gmail.com',
            ],
            [
                'name' => 'Investigapitamex',
                'password' => Hash::make('F89BSSc0'),
            ]
        );

        User::firstOrCreate(
            [
                'email' => 'Ingespinoza91@gmail.com',
            ],
            [
                'name' => 'Ingespinoza91',
                'password' => Hash::make('dJkwZjQV'),
            ]
        );
    }
}
