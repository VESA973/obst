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
            $table->string('section')->default('news')->after('slug');
            $table->string('title')->nullable()->after('section');
            $table->text('description')->nullable()->after('title');
        });

        DB::table('article_categories')->update([
            'section' => 'news',
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('article_categories', function (Blueprint $table) {
            $table->dropColumn(['section', 'title', 'description']);
        });
    }
};
