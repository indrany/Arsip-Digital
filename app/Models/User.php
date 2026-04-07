<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'name',         
        'password',
        'nama_lengkap', 
        'role',         
        'is_active',   
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
    public function getRoleDisplayAttribute()
    {
    if ($this->role === 'INTELTUSKIM') {
        return 'INTALTUSKIM';
    }
    return $this->role;
    }
}