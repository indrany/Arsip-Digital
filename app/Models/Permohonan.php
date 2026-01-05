<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permohonan extends Model
{
    use HasFactory;

    protected $table = 'permohonan'; // Kembali ke tabel awal

    protected $fillable = [
        'no_permohonan',
        'tanggal_permohonan',
        'tanggal_terbit',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'no_telp',
        'jenis_permohonan',
        'jenis_paspor',
        'tujuan_paspor',
        'no_paspor',
        'status_berkas',
        'lokasi_arsip',
        'no_pengirim'
    ];
}