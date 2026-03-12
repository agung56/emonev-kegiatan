<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'nip' => '12345', // Login pakai 12345
            'name' => 'Administrator KPU',
            'email' => 'admin@kpu.web.id',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);
    }
}
