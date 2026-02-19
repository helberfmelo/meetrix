<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SchedulingPage;
use App\Models\AvailabilityRule;
use App\Models\AppointmentType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Admin User
        $user = User::create([
            'name' => 'Helber Melo',
            'email' => 'admin@meetrix.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // 2. Create a Scheduling Page
        $page = SchedulingPage::create([
            'user_id' => $user->id,
            'slug' => 'helber',
            'title' => 'Helber\'s Calendar',
            'intro_text' => 'Book a time to meet with me.',
            'is_active' => true,
            'config' => [
                'theme' => 'light',
                'color' => '#4f46e5',
            ],
        ]);

        // 3. Define Availability (Mon-Fri, 9am-5pm)
        AvailabilityRule::create([
            'scheduling_page_id' => $page->id,
            'days_of_week' => [1, 2, 3, 4, 5], // Mon-Fri
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'timezone' => 'America/Sao_Paulo',
        ]);

        // 4. Create Appointment Types
        AppointmentType::create([
            'scheduling_page_id' => $page->id,
            'name' => 'Quick Chat',
            'duration_minutes' => 15,
            'price' => 0,
            'currency' => 'BRL',
            'is_active' => true,
        ]);

        AppointmentType::create([
            'scheduling_page_id' => $page->id,
            'name' => 'Consultation',
            'duration_minutes' => 60,
            'price' => 150.00,
            'currency' => 'BRL',
            'is_active' => true,
        ]);

        $this->command->info('Database seeded with test data!');
        $this->command->info('User: admin@meetrix.test / password');
        $this->command->info('Page: /helber');
    }
}
