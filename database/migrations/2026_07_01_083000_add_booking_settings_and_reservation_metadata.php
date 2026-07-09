<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('reservation_fee_percent', 5, 2)->nullable()->after('estimated_price');
            $table->unsignedInteger('reservation_fee_amount')->nullable()->after('reservation_fee_percent');
            $table->timestamp('reservation_fee_paid_at')->nullable()->after('reservation_fee_amount');
            $table->foreignId('reservation_fee_marked_paid_by')->nullable()->after('reservation_fee_paid_at')->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable()->after('cancelled_by');

            $table->index(['reservation_fee_paid_at', 'status'], 'bookings_fee_paid_status_index');
            $table->index(['cancelled_at', 'status'], 'bookings_cancelled_status_index');
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->string('room_number')->nullable()->after('location');
            $table->string('floor')->nullable()->after('room_number');
            $table->text('arrival_instructions')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropColumn(['room_number', 'floor', 'arrival_instructions']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_cancelled_status_index');
            $table->dropIndex('bookings_fee_paid_status_index');
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropConstrainedForeignId('reservation_fee_marked_paid_by');
            $table->dropColumn([
                'reservation_fee_percent',
                'reservation_fee_amount',
                'reservation_fee_paid_at',
                'cancellation_reason',
            ]);
        });

        Schema::dropIfExists('booking_settings');
    }
};
