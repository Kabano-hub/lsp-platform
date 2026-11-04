<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bibliographic_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenant_libraries')->cascadeOnDelete();
            $table->string('isbn');
            $table->string('title');
            $table->string('author');
            $table->string('metadata_format')->default('MARC21');
            $table->json('marc21_json');
            $table->decimal('price', 8, 2)->default(0);
            $table->unsignedInteger('available_copies')->default(0);
            $table->unsignedInteger('search_hits')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bibliographic_resources');
    }
};
