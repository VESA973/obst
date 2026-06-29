<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_settings', function (Blueprint $table) {
            $table->id();
            $table->string('page_key')->unique();
            $table->string('menu_label')->nullable();
            $table->string('eyebrow')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('hero_image_path')->nullable();
            $table->string('title_size')->default('normal');
            $table->boolean('show_in_menu')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_settings');
    }
};
