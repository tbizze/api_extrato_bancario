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
            $table->string('client_id')->nullable()->after('bank_id');
            $table->string('client_secret')->nullable()->after('client_id');
            $table->string('certificate_path')->nullable()->after('client_secret');
            $table->string('key_path')->nullable()->after('certificate_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn('client_id');
            $table->dropColumn('client_secret');
            $table->dropColumn('certificate_path');
            $table->dropColumn('key_path');
        });
    }
};
