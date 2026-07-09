<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->json('equipment_requests')->nullable()->after('format');
            $table->text('equipment_notes')->nullable()->after('equipment_requests');
            $table->json('snack_beverage_requests')->nullable()->after('equipment_notes');
            $table->text('snack_beverage_notes')->nullable()->after('snack_beverage_requests');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'equipment_requests',
                'equipment_notes',
                'snack_beverage_requests',
                'snack_beverage_notes',
            ]);
        });
    }
};
