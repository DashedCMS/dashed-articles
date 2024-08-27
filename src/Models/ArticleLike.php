<?php

namespace Dashed\DashedArticles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ArticleLike extends Model
{
    use HasFactory;

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public static function markAs(bool $status, int $articleId): void
    {
        self::updateOrCreate(
            [
                'article_id' => $articleId,
                'ip' => request()->ip(),
            ],
            [
                'like' => $status,
            ]
        );
    }

    public static function remove(int $articleId): void
    {
        self::where('ip', request()->ip())->where('article_id', $articleId)->delete();
    }
}
