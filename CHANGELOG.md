# Changelog

All notable changes to `dashed-articles` will be documented in this file.

## v4.2.0 - 2026-06-10

### Added
- Het contentblok (`content`) van een auteur is nu vertaalbaar. `content` is toegevoegd aan `ArticleAuthor::$translatable` zodat de automatische vertaling (DeepL/AI) de blokken meeneemt, gelijk aan `Article`. Voorheen werden alleen `name` en `slug` van een auteur vertaald.

### Changed
- Bestaande auteur-content (een platte lijst builder-blokken) wordt via de migratie `2026_06_10_090000_make_author_content_translatable` omgezet naar de locale-keyed structuur (`{"<locale>": [...]}`) die Spatie's `HasTranslations` verwacht. Reeds locale-keyed waarden blijven ongemoeid.

## v4.1.0 - 2026-05-07

### Added
- "Sorteren"-header-action op `ListArticleCategories` via `Dashed\DashedCore\Filament\Concerns\HasNestableSortingAction`. Artikelcategorieën kunnen via slepen genested en geordend worden. Vereist `dashed-core` v4.6.0+.

## v4.0.12 - 2026-04-27

- `DashedArticlesServiceProvider::bootingPackage()` registreert de "Artikelen" navigatiegroep via `cms()->registerNavigationGroup('Artikelen', 20)` zodat consumer projecten zonder dashed-articles geen lege groep meer in de Filament-sidebar krijgen. Vereist dashed-core v4.2.0+.

## v4.0.11 - 2026-04-24

- Afbeelding van artikelen is niet langer vertaalbaar. De `image`-kolom op `dashed__articles` is omgezet van JSON naar een enkele string. Bestaande waarden worden via de migratie `2026_04_24_090000_make_article_image_non_translatable` bewaard door de waarde van de eerste locale (met fallback op de eerste niet-lege locale) over te nemen.

## 1.0.0 - 202X-XX-XX

- initial release
