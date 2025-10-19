<?php

namespace App\Console\Commands;

use App\Jobs\ImportPagbankTransactionsJob;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestImportPagbankTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-pagbank
                            {startDate : Data inicial (YYYY-MM-DD)}
                            {endDate : Data final (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispara em fila a importação Pagbank. Separar argumento com espaço';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // ImportPagbankTransactionsJob::dispatch('2025-09-03')->delay(now()->addMinutes(2));
        // Emite mensagem de retorno.
        // $this->info('Tarefa agendada com sucesso.');

        $startDate = $this->argument('startDate');
        $endDate   = $this->argument('endDate');

        // Validação básica
        if (!strtotime($startDate) || !strtotime($endDate)) {
            $this->error('Formato de data inválido. Use YYYY-MM-DD');

            return;
        }

        // Cria múltiplas datas
        $dates = $this->generateDateRange($startDate, $endDate);

        // Faz loop na lista de datas
        foreach ($dates as $date) {
            ImportPagbankTransactionsJob::dispatch($date)->onQueue('pagbank');
            $this->info('Tarefa agendada com sucesso: ' . $date);
        }
    }

    // Cria lista de datas, a partir de duas datas: início e fim
    private function generateDateRange(string $startDate, string $endDate): mixed
    {
        $dates = [];
        $start = Carbon::parse($startDate);
        $end   = Carbon::parse($endDate);

        while ($start <= $end) {
            $dates[] = $start->format('Y-m-d');
            $start->addDay();
        }

        return $dates;
    }
}
