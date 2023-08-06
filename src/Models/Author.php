<?php

namespace Dashed\DashedArticles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class Author extends Model
{
    use SoftDeletes;
    use HasTranslations;
    use LogsActivity;

    protected static $logFillable = true;

    protected $table = 'dashed__article_authors';

    protected $fillable = [
        'name',
        'slug',
        'image',
    ];

    public $translatable = [
        'name',
        'slug',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
