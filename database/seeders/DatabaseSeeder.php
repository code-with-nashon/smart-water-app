<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB; // NEW: Import DB facade

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // NEW: Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Call the MeterSeeder to populate the meters table
        $this->call(MeterSeeder::class);

        // Create default users (admin and test user) if they don't exist
        // Using updateOrCreate to prevent duplicates if seed is run multiple times without fresh
        User::updateOrCreate(
            ['email' => 'admin.smartwater@gmail.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'daily_consumption_alert_threshold' => 1000, // Default for admin, can be higher
            ]
        );

        User::updateOrCreate(
            ['email' => 'user.smartwater@gmail.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'role' => 'user',
                'daily_consumption_alert_threshold' => 100, // Default for regular user
            ]
        );

        // Ensure Fanuel exists for specific testing
        User::updateOrCreate(
            ['email' => 'fanuel@example.com'], // Assuming this is Fanuel's email
            [
                'name' => 'Fanuel',
                'password' => bcrypt('password'), // Or a specific password for Fanuel
                'role' => 'user',
                'daily_consumption_alert_threshold' => 45, // As seen in your dashboard screenshot
            ]
        );


        // Call the DailyWaterReadingsSeeder to populate water readings
        $this->call(DailyWaterReadingsSeeder::class);

        // NEW: Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}