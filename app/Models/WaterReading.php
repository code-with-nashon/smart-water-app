<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaterReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'meter_id', // This is the string ID, e.g., 'SMW001'
        'consumption_liters',
        'reading_at',
    ];

    protected $casts = [
        'reading_at' => 'datetime',
    ];

    /**
     * Get the user that owns the water reading.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the meter that the water reading belongs to.
     */
    public function meter()
    {
        return $this->belongsTo(Meter::class, 'meter_id', 'meter_id');
        // Foreign key 'meter_id' in water_readings table,
        // local key 'meter_id' in meters table (the unique string ID)
    }
}