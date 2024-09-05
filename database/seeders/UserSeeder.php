<?php

namespace Database\Seeders;

use App\Models\{Company, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::create(['name' => 'Empresa Exemplo', 'cnpj' => '03488844000111']);
        //dd($company);

        // User::factory()->create([
        //     'name'  => 'Test User',
        //     'email' => 'test@test.com',
        // ]);

        // Criar superusuário
        User::create([
            'name'         => 'Super Admin',
            'email'        => 'admin@test.com',
            'password'     => Hash::make('password'),
            'company_id'   => $company->id,
            'is_superuser' => true,
        ]);

        // Criar um usuário comum
        User::create([
            'name'         => 'Usuário Comum',
            'email'        => 'user@test.com',
            'password'     => Hash::make('password'),
            'company_id'   => $company->id,
            'is_superuser' => false,
        ]);
    }
}
