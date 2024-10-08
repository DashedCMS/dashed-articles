<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('article_likes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('article_id')
                ->constrained('dashed__articles')
                ->cascadeOnDelete();
            $table->ipAddress('ip');
            $table->boolean('like');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
