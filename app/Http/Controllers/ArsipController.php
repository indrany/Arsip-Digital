<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ArsipController extends Controller
{
    /**
     * Menampilkan daftar semua arsip/dokumen.
     */
    public function index()
    {
        // 1. (Opsional) Logika untuk mengambil data dari database akan ditambahkan di sini.
        
        // 2. Mengembalikan view (tampilan) daftar arsip.
        // Panggil view yang akan Anda buat di resources/views/arsip/index.blade.php
        return view('arsip.index');
    }
    
    // Anda bisa menambahkan method lain seperti create, store, edit, update, destroy di bawah ini.
}