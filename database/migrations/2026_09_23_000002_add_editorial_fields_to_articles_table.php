<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // Statut éditorial : draft, published ou scheduled.
            $table->string('status', 20)->default('published')->after('slug')->index();
            $table->string('meta_title', 70)->nullable()->after('body');
            $table->string('meta_description', 160)->nullable()->after('meta_title');
            $table->foreignId('user_id')->nullable()->after('article_category_id')->constrained()->nullOnDelete();
        });

        // Statut déduit des anciens champs is_published / published_at.
        DB::table('articles')->where('is_published', false)->update(['status' => 'draft']);
        DB::table('articles')->where('is_published', true)->where('published_at', '>', now())->update(['status' => 'scheduled']);

        // Le contenu devient du HTML (éditeur riche) : conversion du texte brut existant.
        DB::table('articles')->whereNotNull('body')->orderBy('id')->each(function (object $article): void {
            if ($article->body !== strip_tags($article->body)) {
                return;
            }

            $paragraphs = preg_split('/\R{2,}/', trim($article->body)) ?: [];
            $html = collect($paragraphs)
                ->map(fn (string $paragraph) => '<p>'.nl2br(e(trim($paragraph)), false).'</p>')
                ->implode('');

            DB::table('articles')->where('id', $article->id)->update(['body' => $html]);
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['status', 'meta_title', 'meta_description']);
        });
    }
};
