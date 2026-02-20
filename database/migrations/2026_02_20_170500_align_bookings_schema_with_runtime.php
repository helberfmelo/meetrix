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
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'appointment_type_id')) {
                $table->foreignId('appointment_type_id')
                    ->nullable()
                    ->after('scheduling_page_id')
                    ->constrained('appointment_types')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('bookings', 'customer_phone')) {
                $table->string('customer_phone')->nullable()->after('customer_email');
            }

            if (!Schema::hasColumn('bookings', 'customer_timezone')) {
                $table->string('customer_timezone')->default('UTC')->after('customer_phone');
            }

            if (!Schema::hasColumn('bookings', 'customer_data')) {
                $table->json('customer_data')->nullable()->after('customer_timezone');
            }

            if (!Schema::hasColumn('bookings', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('amount_paid');
            }

            if (!Schema::hasColumn('bookings', 'meeting_link')) {
                $table->string('meeting_link')->nullable()->after('cancellation_reason');
            }
        });

        if (Schema::hasColumn('bookings', 'meta') && Schema::hasColumn('bookings', 'customer_data')) {
            DB::table('bookings')
                ->whereNull('customer_data')
                ->whereNotNull('meta')
                ->update([
                    'customer_data' => DB::raw('meta'),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('bookings', 'appointment_type_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropForeign(['appointment_type_id']);
                $table->dropColumn('appointment_type_id');
            });
        }

        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'customer_phone')) {
                $table->dropColumn('customer_phone');
            }

            if (Schema::hasColumn('bookings', 'customer_timezone')) {
                $table->dropColumn('customer_timezone');
            }

            if (Schema::hasColumn('bookings', 'customer_data')) {
                $table->dropColumn('customer_data');
            }

            if (Schema::hasColumn('bookings', 'cancellation_reason')) {
                $table->dropColumn('cancellation_reason');
            }

            if (Schema::hasColumn('bookings', 'meeting_link')) {
                $table->dropColumn('meeting_link');
            }
        });
    }
};
