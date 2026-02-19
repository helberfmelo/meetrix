<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduling_page_id')->constrained()->onDelete('cascade');
            $table->foreignId('appointment_type_id')->constrained()->onDelete('cascade');
            $table->dateTime('start_at'); // UTC
            $table->dateTime('end_at'); // UTC
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->string('customer_timezone')->default('UTC');
            $table->json('customer_data')->nullable(); // Answers to form questions
            $table->string('status')->default('pending'); // pending, confirmed, cancelled, rejected
            $table->text('cancellation_reason')->nullable();
            $table->string('meeting_link')->nullable(); // Zoom/Meet link
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
