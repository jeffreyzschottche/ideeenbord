<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brand_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->enum('status', ['processing', 'completed', 'failed'])->default('processing');
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->json('metrics')->nullable();   // computed analytics
            $table->json('ai')->nullable();        // AI-generated narrative sections
            $table->text('error')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index(['brand_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_reports');
    }
};
