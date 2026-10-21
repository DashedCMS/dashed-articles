<?php

namespace Dashed\DashedArticles\Classes\Seo;

use Illuminate\Support\Str;
use Spatie\SchemaOrg\Person;
use Spatie\SchemaOrg\Schema;
use Spatie\SchemaOrg\BaseType;
use Dashed\DashedArticles\Models\ArticleAuthor;

/**
 * The author as a Person, and the author page as a ProfilePage around that
 * Person. The Person carries the @id of the author page, so the author
 * reference in every Article resolves to the same entity.
 */
class PersonSchema
{
    public static function person(ArticleAuthor $author): Person
    {
        $url = ArticleSchema::absolute((string) $author->getUrl(app()->getLocale(), false));

        $person = Schema::person()
            ->setProperty('@id', $url . '#person')
            ->name(ArticleSchema::plainText($author->name))
            ->url($url);

        $images = ArticleSchema::imageUrls([$author->image]);
        if ($images !== []) {
            $person->image($images[0]);
        }

        $description = Str::limit(ArticleSchema::plainText($author->getPlainContent()), 300, '');
        if ($description !== '') {
            $person->description($description);
        }

        return $person;
    }

    public static function profilePage(ArticleAuthor $author): BaseType
    {
        $url = ArticleSchema::absolute((string) $author->getUrl(app()->getLocale(), false));

        return Schema::profilePage()
            ->setProperty('@id', $url . '#profilepage')
            ->url($url)
            ->name(ArticleSchema::plainText($author->name))
            ->mainEntity(static::person($author))
            ->dateCreated($author->created_at)
            ->dateModified($author->updated_at ?: $author->created_at);
    }
}
