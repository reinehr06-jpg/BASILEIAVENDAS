<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasterUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::firstOrCreate(
            ['email' => 'master@basileiavendas.com'],
            [
                'name' => 'Admin Master',
                'password' => \Illuminate\Support\Facades\Hash::make('Master@2026'),
                'perfil' => 'master',
            ]
        );
    }
}
