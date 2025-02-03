<?php

namespace Dashed\DashedArticles\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dashed\DashedCore\Models\Concerns\IsVisitable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Dashed\DashedCore\Models\Concerns\HasCustomBlocks;

class ArticleAuthor extends Model
{
    use HasCustomBlocks;
    use IsVisitable;
    use SoftDeletes;

    protected static $logFillable = true;

    protected $table = 'dashed__article_authors';

    protected $fillable = [
        'name',
        'slug',
        'content',
        'image',
    ];

    public $translatable = [
        'name',
        'slug',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'site_ids' => 'array',
        'content' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'author_id')
            ->orderBy('order');
    }

    public static function canHaveParent(): bool
    {
        return false;
    }
}
