<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permohonan extends Model
{
    use HasFactory;

    // Paksa nama tabel dengan P kapital sesuai file migrasi tadi
    protected $table = 'Permohonan'; 

    // no_permohonan adalah Primary Key
    protected $primaryKey = 'no_permohonan';
    public $incrementing = false; 
    protected $keyType = 'string';

    public $timestamps = true; 

    protected $fillable = [
        'no_permohonan',
        'tanggal_permohonan',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'status_berkas',
        'tanggal_diterima',
    ];
}