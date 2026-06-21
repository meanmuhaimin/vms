<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_ledger', function (Blueprint $table): void {
            $table->bigIncrements('otp_id');
            $table->string('phone_number', 20)->index();
            $table->string('otp_token_hash', 64);
            $table->timestamp('expiration_time')->index();
            $table->integer('attempts_count')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_ledger');
    }
};
