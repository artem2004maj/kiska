<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            EmployeeSeeder::class,
            ServiceSeeder::class,
            // TestimonialsSeeder::class,  ← если сделаешь позже
        ]);
    }
}