<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RakLoker extends Model
{
    protected $table = 'rak_loker';
    protected $fillable = ['no_lemari', 'kode_rak', 'kapasitas', 'terisi', 'status'];
}