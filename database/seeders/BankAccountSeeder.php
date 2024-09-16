<?php

namespace Database\Seeders;

use App\Models\{Bank, BankAccount, Company};
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Inicia o gerador de dados fake.
        $faker = Faker::create('pt_BR');

        // Criar uma empresa
        Company::create([
            'name' => $faker->company(),
            'cnpj' => $faker->cnpj(false),
        ]);

        // Recuperar todas as empresas cadastradas.
        $companies = Company::all();

        // Criar lista de bancos.
        $banks = [
            [
                'bank_name'   => 'Banco Santander',
                'bank_number' => '033',
            ],
            [
                'bank_name'   => 'PagBank',
                'bank_number' => '105',
            ],
        ];

        // Armazena no BD os bancos listados.
        foreach ($banks as $item) {
            Bank::create($item);
        }

        // Gerar contas bancárias para cada empresa.
        foreach ($companies as $item) {
            $x = BankAccount::create([
                'bank_name'      => 'Banco ' . $faker->company(),
                'account_agency' => $faker->randomNumber(5),
                'account_number' => $faker->randomNumber(9),
                'company_id'     => $item->id,
                'bank_id'        => $this->getRandom(Bank::class),
            ]);
        }

        // Limpar a tabela de contas bancárias para garantir um novo estado inicial.
        //BankAccount::truncate();

        // Limpar a tabela de transações para garantir um novo estado inicial.
        // Neste caso, não é necessário pois as transações são criadas automaticamente
        // quando um extrato é solicitado.

        // Gerar transações para cada conta bancária.
        // Aqui você poderia adicionar transações aleatórias para cada conta.
        // Exemplos:
        // BankAccount::all()->each(function ($account) use ($faker) {
        //     $account->transactions()->saveMany(
        //         Transaction::factory(rand(1, 10))->make()
        //     );
        // });

        // Para simplificar, vamos apenas gerar uma transação para cada conta.
        // BankAccount::all()->each(function ($account) use ($faker) {
        //     $account->transactions()->save(
        //         Transaction::factory()->make()
        //     );
        // });

    }

    private function getRandom($model)
    {
        $random = $model::all()->random(1)->pluck('id');

        return $random[0];
    }
}
