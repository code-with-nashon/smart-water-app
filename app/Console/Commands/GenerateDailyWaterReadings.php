<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\DailyWaterReadingsSeeder;

class GenerateDailyWaterReadings extends Command
{
    protected $signature = 'water:generate-daily-readings';

    protected $description = 'Generates daily water readings for all active meters and users.';

    public function handle()
    {
        $this->info('Starting daily water readings generation...');

        // Instantiate the seeder and pass the current command instance to its run method
        $seeder = new DailyWaterReadingsSeeder();
        $seeder->run($this); // Pass $this (the command instance) to the seeder's run method

        $this->info('Daily water readings generation finished.');

        return Command::SUCCESS;
    }
}