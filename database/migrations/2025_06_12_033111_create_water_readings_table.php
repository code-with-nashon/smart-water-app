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
        Schema::create('water_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('meter_id', 255); // Ensure explicit length matches meters table
            $table->double('consumption_liters');
            $table->timestamp('reading_at');
            $table->timestamps();

            // Explicitly add an index to user_id (good practice)
            $table->index('user_id');

            // Add foreign key constraint for meter_id
            $table->foreign('meter_id')->references('meter_id')->on('meters')->onDelete('cascade');
            // Explicitly add an index to meter_id in water_readings for performance
            $table->index('meter_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('water_readings');
    }
};