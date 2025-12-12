<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pengecekan agar data tidak diduplikasi
        if (User::where('email', 'admin@imigrasi.com')->doesntExist()) {
            
            User::create([
                'name' => 'Admin Utama',
                'email' => 'admin@imigrasi.com',
                // Password yang di-hash: 'password'
                'password' => Hash::make('password'), 
            ]);

            $this->command->info('✅ Pengguna "Admin Utama" berhasil ditambahkan.');
        } else {
            $this->command->warn('⚠️ Pengguna "Admin Utama" sudah ada, lewati penambahan data.');
        }
    }
}