<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['user_id', 'starts_at'], 'bookings_user_starts_index');
            $table->index(['user_id', 'status', 'starts_at'], 'bookings_user_status_starts_index');
            $table->index(['status', 'starts_at'], 'bookings_status_starts_index');
            $table->index(['starts_at', 'ends_at', 'status'], 'bookings_calendar_range_index');
            $table->index(['classroom_id', 'booking_date', 'status', 'user_id'], 'bookings_room_date_status_user_index');
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->index(['is_active', 'name'], 'classrooms_active_name_index');
            $table->index('name', 'classrooms_name_index');
        });

        Schema::table('service_packages', function (Blueprint $table) {
            $table->index(['is_active', 'base_price'], 'service_packages_active_price_index');
            $table->index('base_price', 'service_packages_price_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('name', 'users_name_index');
        });

        Schema::table('activity_log', function (Blueprint $table) {
            $table->index(['subject_type', 'subject_id', 'created_at'], 'activity_subject_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex('activity_subject_created_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_name_index');
        });

        Schema::table('service_packages', function (Blueprint $table) {
            $table->dropIndex('service_packages_price_index');
            $table->dropIndex('service_packages_active_price_index');
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropIndex('classrooms_name_index');
            $table->dropIndex('classrooms_active_name_index');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_room_date_status_user_index');
            $table->dropIndex('bookings_calendar_range_index');
            $table->dropIndex('bookings_status_starts_index');
            $table->dropIndex('bookings_user_status_starts_index');
            $table->dropIndex('bookings_user_starts_index');
        });
    }
};
