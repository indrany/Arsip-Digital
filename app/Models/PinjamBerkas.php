<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PinjamBerkas extends Model
{
    protected $table = 'pinjam_berkas';
    protected $guarded = [];

    public function permohonan()
    {
        // Menghubungkan ID ke tabel permohonan
        return $this->belongsTo(Permohonan::class, 'permohonan_id');
    }
}