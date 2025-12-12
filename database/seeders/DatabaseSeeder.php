<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// Tidak perlu use UserSeeder karena berada di namespace yang sama

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Panggil UserSeeder
        $this->call([
            UserSeeder::class, 
        ]);
    }
}