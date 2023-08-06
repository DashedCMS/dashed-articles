<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dashed__article_categories', function (Blueprint $table) {
            $table->id();

            $table->json('name');
            $table->json('slug');

            $table->timestamps();
        });

        Schema::table('dashed__articles', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('slug')
                ->constrained('dashed__article_categories')
            ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('articles', function (Blueprint $table) {
            //
        });
    }
};
