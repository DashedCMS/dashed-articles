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
        Schema::table('dashed__articles', function (Blueprint $table) {
            $table->json('image')
                ->nullable();
            $table->json('excerpt')
                ->nullable();
        });

        foreach(\Dashed\DashedArticles\Models\Article::all() as $article){
            foreach(\Dashed\DashedCore\Classes\Locales::getLocales() as $locale){
                app()->setLocale($locale['id']);
                $article->setTranslation('excerpt', $locale['id'], $article->contentBlocks['excerpt'] ?? '');
                $article->setTranslation('image', $locale['id'], $article->contentBlocks['main_image'] ?? '');
            }
            $article->save();
        }
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
