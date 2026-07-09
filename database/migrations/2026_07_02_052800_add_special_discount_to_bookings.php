<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('special_discount_percent', 5, 2)->nullable()->after('estimated_price');
            $table->unsignedInteger('special_discount_amount')->nullable()->after('special_discount_percent');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'special_discount_percent',
                'special_discount_amount',
            ]);
        });
    }
};
