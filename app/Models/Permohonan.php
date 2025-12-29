<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permohonan extends Model
{
    protected $table = 'permohonan';

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
        'alur_terakhir',
        'lokasi_arsip',
        'status_berkas',

        // 🔥 TAMBAHKAN INI
        'no_pengirim'
    ];
}
