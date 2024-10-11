<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar lista de bancos.
        $banks = [
            ['bank_number' => '290', 'bank_name' => 'Santander'],
            ['bank_number' => '033', 'bank_name' => 'PagBank'],
            // ['bank_number' => '237', 'bank_name' => 'Bradesco'],
            // ['bank_number' => '001', 'bank_name' => 'Banco do Brasil'],
            // ['bank_number' => '341', 'bank_name' => 'Itaú Unibanco'],
            // ['bank_number' => '104', 'bank_name' => 'Caixa Econômica Federal'],
            // ['bank_number' => '260', 'bank_name' => 'Nubank'],
            // ['bank_number' => '077', 'bank_name' => 'Banco Inter'],
            // ['bank_number' => '623', 'bank_name' => 'Banco Pan'],
            // ['bank_number' => '422', 'bank_name' => 'Banco Safra']
        ];

        // Armazena no BD os bancos listados.
        foreach ($banks as $item) {
            Bank::create($item);
        }
    }
}
