<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BerkasPermohonan extends Model
{
    protected $table = 'berkas_permohonan';

    protected $fillable = [
        'permohonan_id',
        'nama_berkas',
        'file_path'
    ];
}