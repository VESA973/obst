<?php

namespace App\Observers;

use App\Models\ArticleCategory;
use App\Support\NewsBlocks;

/**
 * Vide le cache de la navigation admin et des Actualités dès qu'une catégorie change.
 */
class ArticleCategoryObserver
{
    public function saved(ArticleCategory $category): void
    {
        ArticleCategory::forgetNavigationCache();
        NewsBlocks::flush();
    }

    public function deleted(ArticleCategory $category): void
    {
        ArticleCategory::forgetNavigationCache();
        NewsBlocks::flush();
    }
}
