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
        foreach (\Dashed\DashedArticles\Models\Article::withTrashed()->get() as $article) {
            $customBlock = new \Dashed\DashedCore\Models\CustomBlock;
            $customBlock->blocks = $article->blocks;
            $customBlock->blockable_type = \Dashed\DashedArticles\Models\Article::class;
            $customBlock->blockable_id = $article->id;
            $customBlock->save();
        }

        Schema::dropColumns('dashed__articles', ['blocks']);
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
