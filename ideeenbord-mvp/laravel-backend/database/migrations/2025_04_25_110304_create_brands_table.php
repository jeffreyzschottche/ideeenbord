<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->nullable()->unique();
            $table->string('category');
            $table->string('website_url')->nullable();
            $table->text('intro')->nullable();
            $table->string('intro_short', 160)->nullable();
            $table->string('email')->unique();
            $table->string('logo_path')->nullable();
            $table->json('socials')->nullable();
            $table->boolean('verified')->default(false);
            $table->unsignedInteger('rating_sum')->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->integer('rating')->default(0);
            $table->boolean('has_paid')->default(false);
            $table->string('subscription')->nullable();
            $table->unsignedBigInteger('brand_owner_id')->nullable(); // relatie met user id
            $table->integer('likes')->default(0);
            $table->integer('dislikes')->default(0);
            $table->json('quizzes')->nullable();
            $table->json('giveaways')->nullable();
            $table->foreignId('main_question_id')->nullable()->constrained('main_questions')->nullOnDelete();
            $table->json('ideas')->nullable();
            $table->json('pinned_ideas')->nullable();
            $table->boolean('accepted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
