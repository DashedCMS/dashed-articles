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
        foreach (\Dashed\DashedArticles\Models\Article::withTrashed()->get() as $model) {
            $model->site_id = json_encode([$model->site_id]);
            $model->save();
        }

        Schema::table('dashed__articles', function (Blueprint $table) {
            $table->renameColumn('site_id', 'site_ids');
        });

        Schema::table('dashed__articles', function (Blueprint $table) {
            $table->json('site_ids')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('array', function (Blueprint $table) {
            //
        });
    }
};
