<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Les anciennes catégories saisies en texte libre deviennent de vraies catégories,
        // les articles restants sont rangés dans « Non classé ».
        DB::table('articles')->whereNull('article_category_id')->orderBy('id')->each(function (object $article): void {
            $name = filled($article->category) ? trim($article->category) : 'Non classé';

            DB::table('articles')->where('id', $article->id)->update([
                'article_category_id' => $this->categoryId($name),
            ]);
        });

        // Une catégorie contenant des articles ne peut plus être supprimée sans réaffectation.
        // L'ancienne colonne texte « category » est reprise ci-dessus et entrait en conflit avec la relation category().
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['article_category_id']);
            $table->dropColumn('category');
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->foreign('article_category_id')->references('id')->on('article_categories')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['article_category_id']);
            $table->string('category')->nullable()->after('slug');
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->foreign('article_category_id')->references('id')->on('article_categories')->nullOnDelete();
        });
    }

    private function categoryId(string $name): int
    {
        $existing = DB::table('article_categories')->where('name', $name)->value('id');

        if ($existing) {
            return $existing;
        }

        $base = Str::slug($name) ?: 'categorie';
        $slug = $base;
        $counter = 2;

        while (DB::table('article_categories')->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return DB::table('article_categories')->insertGetId([
            'name' => $name,
            'slug' => $slug,
            'section' => 'news',
            'title' => $name,
            'is_active' => true,
            'position' => (int) DB::table('article_categories')->max('position') + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
