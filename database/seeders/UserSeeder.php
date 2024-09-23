<?php

namespace Database\Seeders;

use App\Models\{Company, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar uma empresa
        $company = Company::create([
            'name' => 'FinSync Company',
            'cnpj' => '',
        ]);

        // Criar superusuário
        User::factory()->create([
            'name'              => 'Super Admin',
            'email'             => 'admin@test',
            'password'          => Hash::make('123'),
            'company_id'        => $company->id,
            'is_superuser'      => true,
            'email_verified_at' => now(),
            'remember_token'    => Str::random(10),
        ]);

        // Criar um usuário comum
        // User::factory()->create([
        //     'name'         => 'Usuário Comum',
        //     'email'        => 'user@test',
        //     'password'     => Hash::make('123'),
        //     'company_id'   => $company->id,
        //     'is_superuser' => false,
        // ]);
    }
}
