<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!\Dashed\DashedCore\Models\Customsetting::get('article_overview_page_id')) {
            $page = new \Dashed\DashedPages\Models\Page();
            $page->setTranslation('name', 'nl', 'Artikelen');
            $page->setTranslation('slug', 'nl', 'articles');
            $page->setTranslation('content', 'nl', [
               [
                   'data' => [],
                   'type' => 'all-articles',
               ]
            ]);
            $page->save();

            \Dashed\DashedCore\Models\Customsetting::set('article_overview_page_id', $page->id);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('metadata');
    }
};
