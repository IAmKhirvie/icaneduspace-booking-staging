<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->string('image_url')->nullable()->after('description');
            $table->json('gallery')->nullable()->after('image_url');
            $table->string('address')->nullable()->after('location');
            $table->json('amenities')->nullable()->after('gallery');
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'gallery', 'address', 'amenities']);
        });
    }
};
