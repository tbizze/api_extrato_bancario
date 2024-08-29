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
        Schema::create('santander_tokens', function (Blueprint $table) {
            $table->id();
            $table->text('access_token')->nullable();
            $table->integer('expires_in')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->string('not_before_policy')->nullable();
            $table->string('session_state')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('santander_tokens');
    }
};
