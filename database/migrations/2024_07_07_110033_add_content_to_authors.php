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
        Schema::table('dashed__article_authors', function (Blueprint $table) {
            $table->json('content')
                ->nullable();
            $table->json('site_ids')
                ->nullable();
            $table->dateTime('start_date')
                ->nullable();
            $table->dateTime('end_date')
                ->nullable();
            $table->integer('order')
                ->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_modules', function (Blueprint $table) {
            //
        });
    }
};
