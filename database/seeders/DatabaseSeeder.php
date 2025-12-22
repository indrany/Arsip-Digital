<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
{
    // Akun Login
    \App\Models\User::create([
        'name' => 'Admin',
        'email' => 'admin@gmail.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
    ]);

    // Data Permohonan untuk Dites
    \App\Models\Permohonan::create([
        'no_permohonan' => '02345677744',
        'tanggal_permohonan' => now(),
        'nama' => 'Contoh Pemohon Real',
        'status_berkas' => 'TERDAFTAR'
    ]);
}
}