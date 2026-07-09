<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_special_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('type', 80);
            $table->string('severity', 40)->default('info');
            $table->string('message', 255);
            $table->text('details')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['booking_id', 'resolved_at'], 'booking_special_cases_booking_resolved_index');
            $table->index(['type', 'resolved_at'], 'booking_special_cases_type_resolved_index');
            $table->index(['severity', 'created_at'], 'booking_special_cases_severity_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_special_cases');
    }
};
