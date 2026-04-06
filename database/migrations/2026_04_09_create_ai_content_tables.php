<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('dashed__article_drafts')) {
            Schema::create('dashed__article_drafts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_cluster_id')->nullable()->constrained('dashed__content_clusters')->nullOnDelete();
                $table->string('keyword');
                $table->string('locale', 10)->default('nl');
                $table->text('instruction')->nullable();
                $table->string('status')->default('pending');
                $table->text('progress_message')->nullable();
                $table->text('error_message')->nullable();
                $table->json('content_plan')->nullable();
                $table->json('article_content')->nullable();
                $table->nullableMorphs('subject');
                $table->unsignedBigInteger('applied_by')->nullable();
                $table->timestamp('applied_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('dashed__keyword_researches')) {
            Schema::create('dashed__keyword_researches', function (Blueprint $table) {
                $table->id();
                $table->string('seed_keyword');
                $table->string('locale', 10)->default('nl');
                $table->string('status')->default('pending');
                $table->text('progress_message')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('dashed__keywords')) {
            Schema::create('dashed__keywords', function (Blueprint $table) {
                $table->id();
                $table->foreignId('keyword_research_id')->constrained('dashed__keyword_researches')->cascadeOnDelete();
                $table->string('keyword');
                $table->string('type')->default('secondary');
                $table->string('search_intent')->default('informational');
                $table->string('difficulty')->default('medium');
                $table->string('volume_indication')->default('medium');
                $table->string('status')->default('new');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('dashed__content_clusters')) {
            Schema::create('dashed__content_clusters', function (Blueprint $table) {
                $table->id();
                $table->foreignId('keyword_research_id')->nullable()->constrained('dashed__keyword_researches')->nullOnDelete();
                $table->string('name');
                $table->string('theme')->nullable();
                $table->string('content_type')->default('blog');
                $table->text('description')->nullable();
                $table->string('status')->default('planned');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('dashed__content_cluster_keyword')) {
            Schema::create('dashed__content_cluster_keyword', function (Blueprint $table) {
                $table->foreignId('content_cluster_id')->constrained('dashed__content_clusters')->cascadeOnDelete();
                $table->foreignId('keyword_id')->constrained('dashed__keywords')->cascadeOnDelete();
                $table->primary(['content_cluster_id', 'keyword_id']);
            });
        }

        // Add content_cluster_id to existing article_drafts table if missing
        if (Schema::hasTable('dashed__article_drafts') && ! Schema::hasColumn('dashed__article_drafts', 'content_cluster_id')) {
            Schema::table('dashed__article_drafts', function (Blueprint $table) {
                $table->foreignId('content_cluster_id')->nullable()->after('id')->constrained('dashed__content_clusters')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dashed__content_cluster_keyword');
        Schema::dropIfExists('dashed__content_clusters');
        Schema::dropIfExists('dashed__keywords');
        Schema::dropIfExists('dashed__keyword_researches');
        Schema::dropIfExists('dashed__article_drafts');
    }
};
