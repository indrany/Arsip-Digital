<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ArsipController; 
use App\Http\Controllers\UserController;

// 1. Rute Default
Route::get('/', function () {
    return redirect()->route('login');
});

// 2. Rute Login
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
    
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

// 3. Rute Terproteksi (Aplikasi Utama)
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [ArsipController::class, 'dashboard'])->name('dashboard');
    
    // --- MODUL PENGIRIMAN BERKAS ---
    // Menampilkan Tabel Riwayat Pengiriman
    Route::get('/pengiriman-berkas', [ArsipController::class, 'pengirimanBerkas'])->name('pengiriman-berkas.index');
    
    // PERBAIKAN: Menampilkan Form Tambah Pengiriman (Menyelesaikan Error Route Not Defined)
    Route::get('/pengiriman-berkas/tambah', [ArsipController::class, 'tambahPengiriman'])->name('pengiriman-berkas.create');
    
    // Menyimpan data pengiriman baru ke database
    Route::post('/pengiriman-berkas/store', [ArsipController::class, 'store'])->name('pengiriman-berkas.store');
    
    // Mencari nomor permohonan saat input
    Route::post('/cari-permohonan', [ArsipController::class, 'cariPermohonan'])->name('cari-permohonan');
    
    
    // --- MODUL PENERIMAAN BERKAS ---
    Route::get('/penerimaan-berkas', [ArsipController::class, 'penerimaanBerkas'])->name('penerimaan-berkas.index');
    
    // Route untuk Scan (Manual Laptop atau API HP)
    Route::post('/penerimaan-berkas/scan', [ArsipController::class, 'scanPermohonan'])->name('penerimaan-berkas.scan-permohonan');
    
    // Cek otomatis scan dari HP (Polling)
    Route::get('/penerimaan-berkas/check-new-scan', [ArsipController::class, 'checkNewScan'])->name('penerimaan-berkas.check-new-scan');
    
    // Tombol Simpan & Konfirmasi Penerimaan
    Route::post('/penerimaan-berkas/konfirmasi-bulk', [ArsipController::class, 'konfirmasiBulk'])->name('penerimaan-berkas.konfirmasi-bulk');


    // --- MODUL LAINNYA ---
    Route::get('/pencarian-berkas', [ArsipController::class, 'pencarianBerkas'])->name('pencarian-berkas.index');
    Route::get('/pinjam-berkas', [ArsipController::class, 'pinjamBerkas'])->name('pinjam-berkas.index');
    
    // Pengaturan User & Logout
    Route::resource('users', UserController::class)->except(['show']); 
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});