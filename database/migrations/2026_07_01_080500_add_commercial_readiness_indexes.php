<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['contact', 'starts_at'], 'bookings_contact_starts_index');
            $table->index(['customer_name', 'starts_at'], 'bookings_customer_starts_index');
        });

        Schema::table('booking_notes', function (Blueprint $table) {
            $table->index(['booking_id', 'created_at'], 'booking_notes_booking_created_index');
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->index(['user_id', 'last_activity'], 'sessions_user_activity_index');
        });
    }

    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropIndex('sessions_user_activity_index');
        });

        Schema::table('booking_notes', function (Blueprint $table) {
            $table->dropIndex('booking_notes_booking_created_index');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_customer_starts_index');
            $table->dropIndex('bookings_contact_starts_index');
        });
    }
};
