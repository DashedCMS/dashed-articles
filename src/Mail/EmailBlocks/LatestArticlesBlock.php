<?php

namespace Dashed\DashedArticles\Mail\EmailBlocks;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Builder\Block;
use Dashed\DashedCore\Mail\EmailBlocks\EmailBlock;

class LatestArticlesBlock extends EmailBlock
{
    public static function key(): string
    {
        return 'latest-articles';
    }

    public static function label(): string
    {
        return __('Laatste artikelen');
    }

    public static function contexts(): array
    {
        return [self::CONTEXT_NEWSLETTER];
    }

    public static function filamentBlock(): Block
    {
        return Block::make(self::key())
            ->label(self::label())
            ->icon('heroicon-o-sparkles')
            ->schema([
                TextInput::make('limit')
                    ->label(__('Aantal artikelen'))
                    ->numeric()
                    ->default(3)
                    ->helperText(__('Wordt gevuld op het moment van verzenden, niet nu.')),
                Select::make('columns')
                    ->label(__('Aantal kolommen'))
                    ->options([1 => '1', 2 => '2', 3 => '3'])
                    ->default(2),
            ]);
    }

    public static function render(array $blockData, array $context): string
    {
        $limit = max(1, min(12, (int) ($blockData['limit'] ?? 3)));

        // De selectie wordt op het moment van verzenden bepaald, één keer voor
        // de hele ronde. Zie CampaignRenderer: rendert de code per ontvanger,
        // dan draait deze query net zo vaak als er ontvangers zijn.
        //
        // visibleArticles() en niet Article::isPublic(): zie het commentaar
        // daar. Zonder de embargodatums en de sitefilter vult deze automatische
        // selectie zich met artikelen die de site zelf nog verbergt, of die van
        // een andere site horen te zijn.
        $articles = ArticlesBlock::visibleArticles($context['siteId'] ?? null)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return ArticlesBlock::renderArticles($articles, (int) ($blockData['columns'] ?? 2), $context);
    }
}
