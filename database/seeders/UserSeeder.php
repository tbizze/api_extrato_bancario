<?php

namespace Database\Seeders;

use App\Models\{Company, User};
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Iniciar o gerador de dados fake.
        $faker = Faker::create('pt_BR');

        // Criar uma empresa
        $company = Company::create([
            'name' => 'FinSync Company',
            'cnpj' => $faker->cnpj(false),
        ]);

        // Criar superusuário
        User::factory()->create([
            'name'         => 'Super Admin',
            'email'        => 'admin@test',
            'password'     => Hash::make('123'),
            'company_id'   => $company->id,
            'is_superuser' => true,
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
