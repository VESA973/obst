<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Photos de la page « L'association », gérées dans Filament (Contenus > Page L'association).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('association_page_images', function (Blueprint $table) {
            $table->id();
            // Emplacement : hero, mission, history, actions (une image) ou gallery (plusieurs).
            $table->string('slot', 20)->index();
            $table->string('path');
            $table->string('alt', 200);
            $table->string('caption', 300)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('association_page_images');
    }
};
