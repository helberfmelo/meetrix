<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('billing_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->enum('source', ['subscription', 'booking', 'manual'])->default('subscription');
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->string('external_reference')->nullable()->index();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 3)->default('BRL');
            $table->string('coupon_code')->nullable();
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'created_at']);
            $table->index(['source', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_transactions');
    }
};
