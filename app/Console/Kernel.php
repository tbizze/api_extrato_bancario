<?php

namespace App\Console;

use App\Jobs\ImportBankTransactionsJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        // Agendar o Job executar: XXXXX, às 08:00
        // weekdays -> de segunda a sexta | daily -> diário.
        $schedule->job(new ImportBankTransactionsJob())
            ->daily()
            ->at('08:00');

        //Agendar o Job executar a cada minuto.
        //$schedule->job(new ImportBankTransactionsJob())->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
