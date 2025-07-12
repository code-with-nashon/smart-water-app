<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meter extends Model
{
    use HasFactory;

    protected $fillable = [
        'meter_id',
        'location',
        'installation_date',
        'notes',
    ];

    protected $casts = [
        'installation_date' => 'date',
    ];

    // Define a relationship to the User model
    // A meter can be associated with multiple water readings, each by a user.
    // For simplicity in the anomaly report, we'll try to get the first user associated with this meter.
    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            WaterReading::class,
            'meter_id', // Foreign key on water_readings table
            'id',       // Foreign key on users table
            'meter_id', // Local key on meters table
            'user_id'   // Local key on water_readings table
        );
    }
}