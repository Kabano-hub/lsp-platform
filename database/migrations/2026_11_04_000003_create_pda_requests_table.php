<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pda_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenant_libraries')->cascadeOnDelete();
            $table->foreignId('resource_id')->constrained('bibliographic_resources')->cascadeOnDelete();
            $table->string('user_email');
            $table->string('course_code');
            $table->string('status')->default('NEW');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pda_requests');
    }
};
