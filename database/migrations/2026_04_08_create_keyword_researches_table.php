<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashed__keyword_researches', function (Blueprint $table) {
            $table->id();
            $table->string('seed_keyword');
            $table->string('locale', 10)->default('nl');
            $table->string('status')->default('pending'); // pending, running, done, failed
            $table->text('progress_message')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashed__keyword_researches');
    }
};
