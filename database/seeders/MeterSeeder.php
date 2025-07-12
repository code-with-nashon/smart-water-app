<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Meter;

class MeterSeeder extends Seeder
{
    public function run()
    {
        // Clear existing meters to avoid duplicates if re-run
        Meter::truncate();

        Meter::create([
            'meter_id' => 'SMW001',
            'location' => 'Main Building - Unit A',
            'installation_date' => '2023-01-15',
            'notes' => 'Initial meter for testing purposes.'
        ]);

        Meter::create([
            'meter_id' => 'SMW002',
            'location' => 'Apartment Complex - Building B, Unit 10',
            'installation_date' => '2023-03-01',
            'notes' => 'New installation, high traffic area.'
        ]);

        Meter::create([
            'meter_id' => 'SMW003',
            'location' => 'Commercial Property - Shop #5',
            'installation_date' => '2024-06-20',
            'notes' => 'Commercial meter, expected high consumption.'
        ]);

        Meter::create([
            'meter_id' => 'SMW004',
            'location' => 'Residential House - 123 Oak St',
            'installation_date' => '2024-01-10',
            'notes' => 'Standard residential meter.'
        ]);

        $this->command->info('Meters seeded successfully!');
    }
}