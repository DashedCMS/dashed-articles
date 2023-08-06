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
        Schema::create('dashed__article_authors', function (Blueprint $table) {
            $table->id();

            $table->json('name');
            $table->json('slug');
            $table->string('image')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('dashed__articles', function (Blueprint $table) {
            $table->foreignId('author_id')->nullable()->after('slug')->constrained('dashed__article_authors');
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
