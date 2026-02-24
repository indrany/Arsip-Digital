<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PinjamBerkas extends Model
{
    use SoftDeletes;
    protected $table = 'pinjam_berkas';
    protected $fillable = [
        'permohonan_id',
        'no_peminjaman',
        'nama_peminjam',
        'divisi_peminjam',
        'petugas_arsip',
        'keterangan',
        'tgl_pinjam',
        'tgl_kembali',
        'status'
    ];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'permohonan_id');
    }
}