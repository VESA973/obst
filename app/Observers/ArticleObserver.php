<?php

namespace App\Observers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Support\NewsBlocks;
use App\Support\ResponsiveImage;

/**
 * Invalide les caches qui dépendent des articles : badges de la navigation admin
 * et blocs de la rubrique Actualités. Supprime aussi les miniatures devenues inutiles.
 */
class ArticleObserver
{
    public function created(Article $article): void
    {
        ArticleCategory::forgetNavigationCache();
        NewsBlocks::flush();
    }

    public function updated(Article $article): void
    {
        if ($article->wasChanged('article_category_id')) {
            ArticleCategory::forgetNavigationCache();
        }

        if ($article->wasChanged('image_path')) {
            ResponsiveImage::forget($article->getOriginal('image_path'));
        }

        NewsBlocks::flush();
    }

    public function deleted(Article $article): void
    {
        ArticleCategory::forgetNavigationCache();
        ResponsiveImage::forget($article->image_path);
        NewsBlocks::flush();
    }
}
