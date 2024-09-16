<?php

namespace Database\Seeders;

use App\Models\{BankAccount, Transaction};
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Inicia o gerador de dados fake.
        $faker = Faker::create('pt_BR');

        // Recuperar todas as empresas cadastradas.
        $bankAccounts = BankAccount::all()->pluck('id');

        $qde_documentos = 10; // Qde de documentos a criar.
        $data_inicio    = '2024-01-01';  // Faz calculo a partir da data atual: '-5 month' / '-2 year' / '-5 days'...  /// Data exata '2022-01-01'.
        $data_fim       = '-1 days';  // Faz calculo a partir da data atual: '+3 month' / '+2 year' / '+5 days'...  /// Data exata '2024-12-31'.

        $documentos = [];

        foreach ($bankAccounts as $item) {
            for ($count = 1; $count <= $qde_documentos; $count++) {
                $documentos[] = [
                    'bank_account_id' => $item,
                    'description'     => $faker->sentence(3),
                    'type'            => $faker->randomElement(['credit', 'debit']),
                    'amount'          => $faker->randomFloat(2, 17.45, 862.13),
                    'date'            => $faker->dateTimeBetween($data_inicio, $data_fim),
                ];
            }
        }

        if (count($documentos) > 0) {
            // Abre uma transaction para salvar no BC os dados fake criado
            DB::transaction(function () use ($documentos) {

                // Limpar a tabela de contas bancárias para garantir um novo estado inicial.
                Transaction::query()->delete();

                foreach ($documentos as $item) {
                    // Salva no BD documento criado.
                    Transaction::create($item);
                }
            });
            //dd('Documentos criados com sucesso!');
        } else {
            dump('Nenhum documento criado');
        }

        //dd($documentos);
    }
}
