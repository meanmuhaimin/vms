<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitors', function (Blueprint $table): void {
            $table->string('phone_number', 20)->nullable()->change();
            $table->string('email_address')->nullable()->change();
            $table->string('full_name')->nullable()->change();
            $table->string('company_name')->nullable()->change();
            $table->binary('encrypted_id_number')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table): void {
            $table->string('phone_number', 20)->nullable(false)->change();
            $table->string('email_address')->nullable(false)->change();
            $table->string('full_name')->nullable(false)->change();
            $table->string('company_name')->nullable()->change();
            $table->binary('encrypted_id_number')->nullable(false)->change();
        });
    }
};
