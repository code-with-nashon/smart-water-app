<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\WaterReading;
use App\Models\Meter; // Import the Meter model
use Carbon\Carbon;
use Illuminate\Console\Command;

class DailyWaterReadingsSeeder extends Seeder
{
    public function run(?Command $command = null)
    {
        $output = $command ?? $this->command;

        // Get ALL meter IDs from the new 'meters' table
        $allMeterIds = Meter::pluck('meter_id'); // Changed this line to fetch from Meter model

        // Get all regular users (excluding admin)
        $users = User::where('role', 'user')->get();

        // Check if there are any meters or users to generate data for
        if ($allMeterIds->isEmpty() || $users->isEmpty()) {
            $output->info('No meters or users found. Skipping daily water readings generation.');
            return;
        }

        $output->info('Generating daily water readings...');

        $datesToGenerate = [
            Carbon::today()->subDay(), // Yesterday
            Carbon::today(),           // Today
        ];

        $firstUser = $users->first();
        $firstMeter = $allMeterIds->first(); // Use first meter from allMeterIds for forced leak

        foreach ($users as $user) {
            foreach ($allMeterIds as $meterId) { // Iterate through allMeterIds
                foreach ($datesToGenerate as $date) {
                    // Check if a reading already exists for this meter, user, and date
                    $exists = WaterReading::where('user_id', $user->id)
                                        ->where('meter_id', $meterId)
                                        ->whereDate('reading_at', $date)
                                        ->exists();

                    if (!$exists) {
                        $consumption = rand(50, 400); // Default normal consumption

                        // Forced leak for the first user and first meter for today/yesterday (for consistent testing)
                        if ($user->id === $firstUser->id && $meterId === $firstMeter) {
                            if ($date->isToday()) {
                                $consumption = rand(501, 1000); // Today's consumption for leak
                            } elseif ($date->isYesterday()) {
                                $consumption = rand(10, 99); // Yesterday's consumption for leak
                            }
                        } else {
                            // Original random chance for other meters/users
                            if (rand(1, 10) === 1) { // 10% chance of a leak value
                                $consumption = rand(501, 1000);
                            }
                        }

                        WaterReading::create([
                            'user_id' => $user->id,
                            'meter_id' => $meterId,
                            'consumption_liters' => $consumption,
                            'reading_at' => $date->copy()->addHours(rand(8, 20))->addMinutes(rand(0, 59))->addSeconds(rand(0, 59)),
                        ]);
                        $output->info("Generated reading for Meter: {$meterId}, User: {$user->id}, Date: {$date->toDateString()}");
                    } else {
                        $output->info("Reading already exists for Meter: {$meterId}, User: {$user->id}, Date: {$date->toDateString()}. Skipping.");
                    }
                }
            }
        }
        $output->info('Daily water readings generation complete.');
    }
}