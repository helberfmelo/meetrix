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
        $attributes = [
            'name' => 'Master Admin',
            'password' => \Illuminate\Support\Facades\Hash::make('MeetrixMaster2026Sovereign!#'),
            'is_super_admin' => true,
            'email_verified_at' => now(),
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'is_active')) {
            $attributes['is_active'] = true;
        }

        \App\Models\User::updateOrCreate(['email' => 'admin@meetrix.pro'], $attributes);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
