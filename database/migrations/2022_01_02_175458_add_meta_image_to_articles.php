<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMetaImageToArticles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dashed__articles', function (Blueprint $table) {
            $table->string('meta_image')->nullable()->after('meta_description');
        });

        foreach (\Dashed\DashedArticles\Models\Article::get() as $article) {
            $newContent = [];
            foreach (\Dashed\DashedCore\Classes\Locales::getLocales() as $locale) {
                $newBlocks = [];
                foreach (json_decode($article->getTranslation('content', $locale['id']), true) ?: [] as $block) {
                    $newBlocks[\Illuminate\Support\Str::orderedUuid()->toString()] = [
                        'type' => $block['type'],
                        'data' => $block['data'],
                    ];
                }
                $newContent[$locale['id']] = $newBlocks;
            }

            $media = \Illuminate\Support\Facades\DB::table('media')
                ->where('model_type', 'Dashed\Dashed\Models\Article')
                ->where('model_id', $article->id)
                ->where('collection_name', 'meta-image-' . $locale['id'])
                ->first();

            if ($media) {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists("/dashed/uploads/$media->id/$media->file_name")) {
                    try {
                        \Illuminate\Support\Facades\Storage::disk('public')->copy("/dashed/uploads/$media->id/$media->file_name", "/dashed/articles/meta-images/$media->file_name");
                    } catch (Exception $exception) {
                    }
                    $article->setTranslation('meta_image', $locale['id'], "/dashed/articles/meta-images/$media->file_name");
                }
            }

            $article->content = $newContent;
            $article->save();
        }
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
}
