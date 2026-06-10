<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Wrap the existing non-translatable author `content` (a plain list of
     * builder blocks) into the locale-keyed structure Spatie's HasTranslations
     * expects, now that `content` is part of ArticleAuthor::$translatable.
     */
    public function up(): void
    {
        $defaultLocale = \Dashed\DashedCore\Classes\Locales::getFirstLocale()['id'] ?? 'nl';

        DB::table('dashed__article_authors')
            ->whereNotNull('content')
            ->orderBy('id')
            ->each(function ($author) use ($defaultLocale) {
                $raw = $author->content;
                $decoded = is_string($raw) ? json_decode($raw, true) : $raw;

                if (! is_array($decoded)) {
                    return;
                }

                // Already locale-keyed (e.g. {"nl": [...], "en": [...]}) -> leave as-is.
                if (! array_is_list($decoded)) {
                    return;
                }

                DB::table('dashed__article_authors')
                    ->where('id', $author->id)
                    ->update([
                        'content' => json_encode([$defaultLocale => $decoded]),
                    ]);
            });
    }

    public function down(): void
    {
    }
};
