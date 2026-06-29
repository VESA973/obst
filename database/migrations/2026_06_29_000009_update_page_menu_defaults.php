<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('page_settings')) {
            return;
        }

        DB::table('page_settings')
            ->whereIn('page_key', ['actions', 'research'])
            ->update([
                'show_in_menu' => false,
                'updated_at' => now(),
            ]);

        DB::table('page_settings')
            ->where('page_key', 'public')
            ->whereIn('menu_label', ['Sante des femmes', 'Santé des femmes'])
            ->update([
                'menu_label' => 'Santé de la femme',
                'eyebrow' => 'Santé de la femme',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('page_settings')) {
            return;
        }

        DB::table('page_settings')
            ->whereIn('page_key', ['actions', 'research'])
            ->update([
                'show_in_menu' => true,
                'updated_at' => now(),
            ]);
    }
};
