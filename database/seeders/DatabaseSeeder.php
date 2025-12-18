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
    // Buat User agar bisa LOGIN kembali
    \App\Models\User::create([
        'name' => 'Admin',
        'email' => 'admin@gmail.com',
        'password' => bcrypt('password123'),
    ]);

    // Buat Data Contoh agar bisa di-SCAN
    \App\Models\Permohonan::create([
        'no_permohonan' => '02345677744',
        'tanggal_permohonan' => '2025-12-10',
        'nama' => 'Ahmad Budi Santoso',
        'tempat_lahir' => 'Jakarta',
        'tanggal_lahir' => '1990-01-01',
        'status_berkas' => 'SIAP_DITERIMA'
    ]);
}
}