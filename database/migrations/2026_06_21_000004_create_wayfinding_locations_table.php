<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wayfinding_locations', function (Blueprint $table): void {
            $table->string('location_id', 50)->primary();
            $table->string('display_name');
            $table->string('building_sector', 100);
            $table->string('floor_label', 50);
            $table->string('map_asset_path');
            $table->json('route_steps');
            $table->boolean('is_public')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wayfinding_locations');
    }
};
