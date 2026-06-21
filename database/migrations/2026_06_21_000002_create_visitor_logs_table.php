<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_logs', function (Blueprint $table): void {
            $table->uuid('log_id')->primary();
            $table->uuid('visitor_id');
            $table->string('host_employee_id', 100)->index();
            $table->string('assigned_location_id', 50)->index();
            $table->string('verification_channel', 30);
            $table->string('status', 30)->index();
            $table->timestamp('checkin_submit_time')->nullable();
            $table->timestamp('desk_release_time')->nullable();
            $table->timestamp('host_approval_time')->nullable();
            $table->timestamp('checkout_time')->nullable();

            $table->foreign('visitor_id')
                ->references('visitor_id')
                ->on('visitors')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
    }
};
