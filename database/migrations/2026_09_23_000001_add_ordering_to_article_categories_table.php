<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('article_categories', function (Blueprint $table) {
            // Permet de masquer une catégorie sans la supprimer.
            $table->boolean('is_active')->default(true)->after('image_path');
            // Ordre d'affichage (glisser-déposer dans l'administration).
            $table->unsignedInteger('position')->default(0)->after('is_active');
        });

        // Les catégories existantes sont numérotées par ordre alphabétique.
        DB::table('article_categories')->orderBy('name')->pluck('id')
            ->each(fn (int $id, int $index) => DB::table('article_categories')
                ->where('id', $id)
                ->update(['position' => $index + 1]));
    }

    public function down(): void
    {
        Schema::table('article_categories', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'position']);
        });
    }
};
