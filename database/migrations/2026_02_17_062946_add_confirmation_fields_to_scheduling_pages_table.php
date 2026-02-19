<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scheduling_pages', function (Blueprint $table) {
            $table->text('confirmation_message')->nullable();
            $table->string('redirect_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('scheduling_pages', function (Blueprint $table) {
            $table->dropColumn(['confirmation_message', 'redirect_url']);
        });
    }
};
