<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_free_entry')->default(false)->after('is_paid');
            $table->string('banner_position', 20)->nullable()->after('image_path');
            $table->string('flyer_position', 20)->nullable()->after('banner_position');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['is_free_entry', 'banner_position', 'flyer_position']);
        });
    }
};
