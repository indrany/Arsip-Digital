<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengirimanBatch extends Model
{
    use HasFactory;

    protected $table = 'pengiriman_batch';

    protected $fillable = [
        'no_pengirim',
        'tgl_pengirim',
        'tanggal_diterima',
        'jumlah_berkas',
        'status'
    ];
}
