<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Place;
use App\Models\Transportation;
use App\Models\Route;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        $bromo = Place::create([
            'name' => 'Gunung Bromo',
            'description' => 'Wisata gunung di Jawa Timur',
            'category' => 'wisata',
            'location' => 'Probolinggo',
            'latitude' => -7.9425,
            'longitude' => 112.9531
        ]);

        $bus = Transportation::create([
            'type' => 'bus',
            'name' => 'Bus AKDP'
        ]);

        Route::create([
            'place_id' => $bromo->id,
            'transportation_id' => $bus->id,
            'start_point' => 'Terminal Arjosari, Malang',
            'end_point' => 'Cemoro Lawang',
            'price' => 30000,
            'duration' => 120,
            'notes' => 'Naik bus jurusan Probolinggo, lalu lanjut travel ke Bromo.'
        ]);
    }
}

