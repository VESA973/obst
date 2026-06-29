<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('description');
            $table->boolean('is_paid')->default(false)->after('registration_url');
            $table->unsignedInteger('registration_capacity')->nullable()->after('is_paid');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'is_paid', 'registration_capacity']);
        });
    }
};
