<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dashed__article_drafts', function (Blueprint $table) {
            $table->foreignId('content_cluster_id')->nullable()->after('id')->constrained('dashed__content_clusters')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('dashed__article_drafts', function (Blueprint $table) {
            $table->dropForeignIdFor(\Dashed\DashedArticles\Models\ContentCluster::class, 'content_cluster_id');
            $table->dropColumn('content_cluster_id');
        });
    }
};
