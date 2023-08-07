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
        Schema::create('dashed__articles', function (Blueprint $table) {
            $table->id();

            $table->json('name');
            $table->json('slug');
            $table->json('content')->nullable();
            $table->json('blocks')->nullable();
            $table->string('site_id');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dashed', function (Blueprint $table) {
            //
        });
    }
};
