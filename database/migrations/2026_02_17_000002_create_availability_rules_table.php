<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availability_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduling_page_id')->constrained()->onDelete('cascade');
            $table->json('days_of_week'); // e.g. [1, 2, 3, 4, 5]
            $table->time('start_time');
            $table->time('end_time');
            $table->json('breaks')->nullable(); // e.g. [{"start": "12:00", "end": "13:00"}]
            $table->string('timezone')->default('UTC');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availability_rules');
    }
};
