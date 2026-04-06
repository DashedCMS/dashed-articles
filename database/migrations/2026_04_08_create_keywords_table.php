<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashed__keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keyword_research_id')->constrained('dashed__keyword_researches')->cascadeOnDelete();
            $table->string('keyword');
            $table->string('type')->default('secondary'); // primary, secondary, long_tail, lsi, question
            $table->string('search_intent')->default('informational'); // informational, commercial, transactional, navigational
            $table->string('difficulty')->default('medium'); // easy, medium, hard
            $table->string('volume_indication')->default('medium'); // low, medium, high
            $table->string('status')->default('new'); // new, approved, blacklisted
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashed__keywords');
    }
};
