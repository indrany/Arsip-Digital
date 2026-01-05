<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permohonan extends Model
{
    use HasFactory;

    protected $table = 'permohonan'; // Kembali ke tabel awal

    // WAJIB mendaftarkan kolom baru di sini agar tidak ditolak sistem
    protected $fillable = [
        'no_permohonan', 
        'no_pengirim', 
        'tanggal_permohonan', 
        'tanggal_terbit',
        'nama', 
        'tempat_lahir',    // Ditambahkan
        'tanggal_lahir',   // Ditambahkan
        'jenis_kelamin',   // Ditambahkan
        'no_telp',         // Ditambahkan
        'jenis_permohonan', 
        'jenis_paspor', 
        'tujuan_paspor', 
        'no_paspor',       // Ditambahkan
        'alur_terakhir', 
        'status_berkas',
        'lokasi_arsip'     // Ditambahkan
    ];
}