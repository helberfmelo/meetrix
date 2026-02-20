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
            $table->foreignId('scheduling_page_id')->constrained('scheduling_pages')->onDelete('cascade');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('status')->default('pending'); // confirmed, cancelled, reshcheduled
            $table->json('meta')->nullable(); // Answers to form questions
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
