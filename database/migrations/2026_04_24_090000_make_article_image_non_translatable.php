<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('dashed__articles', function (Blueprint $table) {
            $table->string('image_non_translatable', 1024)->nullable();
        });

        $defaultLocale = \Dashed\DashedCore\Classes\Locales::getFirstLocale()['id'] ?? 'nl';

        DB::table('dashed__articles')
            ->whereNotNull('image')
            ->orderBy('id')
            ->each(function ($article) use ($defaultLocale) {
                $raw = $article->image;
                $decoded = is_string($raw) ? json_decode($raw, true) : $raw;

                $imageValue = null;
                if (is_array($decoded)) {
                    if (! empty($decoded[$defaultLocale])) {
                        $imageValue = $decoded[$defaultLocale];
                    } else {
                        foreach ($decoded as $value) {
                            if (! empty($value)) {
                                $imageValue = $value;

                                break;
                            }
                        }
                    }
                } elseif (is_string($raw) && $decoded === null && $raw !== '') {
                    $imageValue = $raw;
                } elseif (is_string($decoded)) {
                    $imageValue = $decoded;
                }

                if ($imageValue) {
                    DB::table('dashed__articles')
                        ->where('id', $article->id)
                        ->update(['image_non_translatable' => $imageValue]);
                }
            });

        Schema::table('dashed__articles', function (Blueprint $table) {
            $table->dropColumn('image');
        });

        Schema::table('dashed__articles', function (Blueprint $table) {
            $table->renameColumn('image_non_translatable', 'image');
        });
    }

    public function down(): void
    {
    }
};
