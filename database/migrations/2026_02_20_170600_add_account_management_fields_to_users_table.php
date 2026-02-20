<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_super_admin');
            }

            if (!Schema::hasColumn('users', 'preferred_locale')) {
                $table->string('preferred_locale', 10)->nullable()->after('country_code');
            }

            if (!Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone')->default('UTC')->after('preferred_locale');
            }

            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('timezone');
            }
        });

        DB::table('users')
            ->where('email', 'admin@meetrix.pro')
            ->update([
                'is_super_admin' => true,
                'is_active' => true,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'last_login_at')) {
                $table->dropColumn('last_login_at');
            }

            if (Schema::hasColumn('users', 'timezone')) {
                $table->dropColumn('timezone');
            }

            if (Schema::hasColumn('users', 'preferred_locale')) {
                $table->dropColumn('preferred_locale');
            }

            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
