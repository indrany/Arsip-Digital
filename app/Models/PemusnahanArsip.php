<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemusnahanArsip extends Model
{
    protected $table = 'pemusnahan_arsip';
    protected $fillable = [
        'no_berita_acara', 'tgl_pemusnahan', 'filter_mulai', 
        'filter_selesai', 'jumlah_dokumen', 'jumlah_manual', 'file_pdf', 
        'status', 'daftar_id_permohonan'
    ];

    // Otomatis ubah JSON string ke Array saat dipanggil
    protected $casts = [
        'daftar_id_permohonan' => 'array',
    ];
}