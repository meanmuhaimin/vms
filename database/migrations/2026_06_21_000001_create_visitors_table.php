<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table): void {
            $table->uuid('visitor_id')->primary();
            $table->string('phone_number', 20)->index();
            $table->string('email_address');
            $table->string('full_name');
            $table->string('company_name')->nullable();
            $table->string('id_doc_type', 20);
            $table->binary('encrypted_id_number');
            $table->timestamp('created_at')->useCurrent();

            $table->index('email_address');
            $table->index('id_doc_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
