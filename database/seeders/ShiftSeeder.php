<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Shift::create([
            'name' => 'Shift 1',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'expected_arrival_time' => '09:00:00',
            'late_threshold_minutes' => 5,
        ]);

        Shift::create([
            'name' => 'Shift 2',
            'start_time' => '17:00:00',
            'end_time' => '00:00:00',
            'expected_arrival_time' => '17:00:00',
            'late_threshold_minutes' => 5,
        ]);
    }
}
