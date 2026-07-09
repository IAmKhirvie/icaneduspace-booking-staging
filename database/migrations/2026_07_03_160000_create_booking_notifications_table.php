<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('audience', 40);
            $table->string('channel', 40)->default('mail');
            $table->string('notification_type');
            $table->string('recipient')->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->string('status', 40)->default('pending');
            $table->text('error')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'created_at'], 'booking_notifications_booking_created_index');
            $table->index(['user_id', 'read_at', 'created_at'], 'booking_notifications_user_read_created_index');
            $table->index(['audience', 'status', 'created_at'], 'booking_notifications_audience_status_created_index');
            $table->index(['channel', 'status', 'created_at'], 'booking_notifications_channel_status_created_index');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['status', 'reservation_fee_paid_at', 'starts_at'], 'bookings_status_reservation_paid_starts_index');
            $table->index(['status', 'full_payment_paid_at', 'starts_at'], 'bookings_status_full_paid_starts_index');
            $table->index(['cancelled_at', 'starts_at'], 'bookings_cancelled_starts_index');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_cancelled_starts_index');
            $table->dropIndex('bookings_status_full_paid_starts_index');
            $table->dropIndex('bookings_status_reservation_paid_starts_index');
        });

        Schema::dropIfExists('booking_notifications');
    }
};
