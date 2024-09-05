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
        //
        // Criar superusuário
        User::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@test.com',
            'password' => Hash::make('password'),
            //'company_id' => $company->id,
            'is_superuser' => true,
        ]);

        // Criar um usuário comum
        User::create([
            'name'     => 'Usuário Comum',
            'email'    => 'user@test.com',
            'password' => Hash::make('password'),
            //'company_id' => $company->id,
            'is_superuser' => false,
        ]);
    }
}
