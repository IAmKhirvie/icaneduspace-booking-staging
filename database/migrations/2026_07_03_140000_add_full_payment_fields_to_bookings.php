<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_scope', 40)->nullable()->after('payment_method');
            $table->timestamp('full_payment_paid_at')->nullable()->after('reservation_fee_marked_paid_by');
            $table->foreignId('full_payment_marked_paid_by')->nullable()->after('full_payment_paid_at')->constrained('users')->nullOnDelete();

            $table->index(['payment_scope', 'status'], 'bookings_payment_scope_status_index');
            $table->index(['full_payment_paid_at', 'status'], 'bookings_full_payment_paid_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_full_payment_paid_status_index');
            $table->dropIndex('bookings_payment_scope_status_index');
            $table->dropConstrainedForeignId('full_payment_marked_paid_by');
            $table->dropColumn(['payment_scope', 'full_payment_paid_at']);
        });
    }
};
