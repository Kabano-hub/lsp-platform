<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demand_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenant_libraries')->cascadeOnDelete();
            $table->foreignId('resource_id')->constrained('bibliographic_resources')->cascadeOnDelete();
            $table->unsignedInteger('aps_score');
            $table->decimal('estimated_roi_months', 8, 1);
            $table->string('recommendation');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demand_forecasts');
    }
};
