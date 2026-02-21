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
        if (Schema::hasTable('webhook_events')) {
            return;
        }

        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 32)->default('stripe');
            $table->string('event_id', 191);
            $table->string('event_type', 120);
            $table->string('status', 32)->default('processed');
            $table->timestamp('processed_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'event_id']);
            $table->index(['provider', 'event_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
    }
};

