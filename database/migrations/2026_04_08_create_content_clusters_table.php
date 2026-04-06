<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashed__content_clusters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keyword_research_id')->nullable()->constrained('dashed__keyword_researches')->nullOnDelete();
            $table->string('name');
            $table->string('theme')->nullable();
            $table->string('content_type')->default('blog'); // blog, landing_page, category, faq, product, other
            $table->text('description')->nullable();
            $table->string('status')->default('planned'); // planned, in_progress, done
            $table->timestamps();
        });

        Schema::create('dashed__content_cluster_keyword', function (Blueprint $table) {
            $table->foreignId('content_cluster_id')->constrained('dashed__content_clusters')->cascadeOnDelete();
            $table->foreignId('keyword_id')->constrained('dashed__keywords')->cascadeOnDelete();
            $table->primary(['content_cluster_id', 'keyword_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashed__content_cluster_keyword');
        Schema::dropIfExists('dashed__content_clusters');
    }
};
