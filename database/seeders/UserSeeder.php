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
        User::create([
            'name' => 'Michael',
            'email' => 'info@wmemtipp.de',
            'password' => Hash::make('book24Stats!'),
        ]);
    }
}
