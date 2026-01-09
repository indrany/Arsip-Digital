<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PinjamBerkas extends Model
{
    use SoftDeletes;
    protected $table = 'pinjam_berkas';
    protected $guarded = [];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'permohonan_id');
    }
}