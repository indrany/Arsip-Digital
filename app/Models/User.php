<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'name',         // Ini adalah kolom Username kamu
        'password',
        'nama_lengkap', 
        'role',         
        'is_active',   
    ];

    /**
     * Atribut yang harus disembunyikan.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting atribut (Email dihapus dari sini).
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}