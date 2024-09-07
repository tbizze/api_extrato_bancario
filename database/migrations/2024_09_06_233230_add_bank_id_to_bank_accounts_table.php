<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            // Chave estrangeira: Banco.
            $table->foreignId('bank_id')->after('company_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            // Remove a chave estrangeira.
            $table->dropForeign(['bank_id']);

            // Remove a coluna.
            $table->dropColumn('bank_id');
        });
    }
};
