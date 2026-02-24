<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permohonan extends Model
{
    use HasFactory;

    protected $table = 'permohonan';

    protected $fillable = [
        'no_permohonan', 
        'rak_id',
        'no_pengirim', 
        'tgl_pengirim', 
        'asal_unit',
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
        'status_berkas',
        'lokasi_arsip'
    ];
}
