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
            if (!Schema::hasColumn('users', 'account_mode')) {
                $table->string('account_mode', 32)->default('scheduling_only')->after('country_code');
            }

            if (!Schema::hasColumn('users', 'region')) {
                $table->string('region', 16)->default('BR')->after('account_mode');
            }

            if (!Schema::hasColumn('users', 'currency')) {
                $table->string('currency', 3)->default('BRL')->after('region');
            }

            if (!Schema::hasColumn('users', 'platform_fee_percent')) {
                $table->decimal('platform_fee_percent', 5, 2)->default(0)->after('currency');
            }
        });

        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'account_mode')) {
                $table->string('account_mode', 32)->default('scheduling_only')->after('currency');
            }

            if (!Schema::hasColumn('tenants', 'platform_fee_percent')) {
                $table->decimal('platform_fee_percent', 5, 2)->default(0)->after('account_mode');
            }
        });

        DB::table('users')->select(['id', 'country_code', 'region', 'currency'])->orderBy('id')->chunkById(200, function ($users) {
            foreach ($users as $user) {
                $region = match (strtoupper((string) $user->country_code)) {
                    'BR' => 'BR',
                    'US', 'CA', 'AU' => 'USD',
                    default => 'EUR',
                };

                $currency = match ($region) {
                    'BR' => 'BRL',
                    'USD' => 'USD',
                    default => 'EUR',
                };

                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'account_mode' => 'scheduling_only',
                        'region' => $region,
                        'currency' => $currency,
                        'platform_fee_percent' => 0,
                    ]);
            }
        });

        DB::table('tenants')->select(['id', 'region', 'currency'])->orderBy('id')->chunkById(200, function ($tenants) {
            foreach ($tenants as $tenant) {
                $region = strtoupper((string) $tenant->region);
                $resolvedRegion = match ($region) {
                    'BR' => 'BR',
                    'US', 'USA', 'CA', 'AU', 'USD' => 'USD',
                    default => 'EUR',
                };

                $currency = match ($resolvedRegion) {
                    'BR' => 'BRL',
                    'USD' => 'USD',
                    default => 'EUR',
                };

                DB::table('tenants')
                    ->where('id', $tenant->id)
                    ->update([
                        'region' => $resolvedRegion,
                        'currency' => $currency,
                        'account_mode' => 'scheduling_only',
                        'platform_fee_percent' => 0,
                    ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'platform_fee_percent')) {
                $table->dropColumn('platform_fee_percent');
            }

            if (Schema::hasColumn('tenants', 'account_mode')) {
                $table->dropColumn('account_mode');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'platform_fee_percent')) {
                $table->dropColumn('platform_fee_percent');
            }

            if (Schema::hasColumn('users', 'currency')) {
                $table->dropColumn('currency');
            }

            if (Schema::hasColumn('users', 'region')) {
                $table->dropColumn('region');
            }

            if (Schema::hasColumn('users', 'account_mode')) {
                $table->dropColumn('account_mode');
            }
        });
    }
};
