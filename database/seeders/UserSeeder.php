<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Michael',
            'email' => 'info@wmemtipp.de',
            'password' => \Illuminate\Support\Facades\Hash::make('book24Stats!'),
        ]);
    }
}
