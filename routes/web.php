<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

// Pastikan Anda mengimpor Controller yang baru (contoh)
use App\Http\Controllers\ArsipController; 
use App\Http\Controllers\UserController;


// Rute Default/Root akan mengarah ke Login
Route::get('/', function () {
    return redirect()->route('login');
});

// --- Rute Login (Akses oleh Tamu Saja) ---
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
    
    // Rute POST yang mengirimkan data login ke LoginController
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});


// --- Rute Terproteksi (Hanya untuk Pengguna Terotentikasi) ---
Route::middleware('auth')->group(function () {

    // Rute Dashboard
    Route::get('/dashboard', function () {
        // Memanggil View Dashboard yang sudah Anda buat
        return view('auth.Dashboard.index'); 
    })->name('dashboard');

    // ==========================================================
    // >>> PENAMBAHAN ROUTE BARU DI SINI <<<
    // ==========================================================

    // 1. Rute Manajemen Arsip (Contoh)
    // Menggunakan Controller khusus untuk logika Arsip
    Route::get('/arsip', [ArsipController::class, 'index'])->name('arsip.index');
    
    // 2. Rute Manajemen Pengguna (Contoh)
    // Menggunakan Controller khusus untuk logika Pengguna
    Route::resource('users', UserController::class)->except(['show']); // Contoh Resource Route
    
    // ==========================================================

    // Rute Log Out (Menggunakan POST untuk keamanan)
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});