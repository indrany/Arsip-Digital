<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ArsipController; 
use App\Http\Controllers\UserController;
use App\Http\Controllers\PinjamBerkasController;

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
    Route::get('/pengiriman-berkas', [ArsipController::class, 'pengirimanBerkas'])->name('pengiriman-berkas.index');
    Route::get('/pengiriman-berkas/tambah', [ArsipController::class, 'tambahPengiriman'])->name('pengiriman-berkas.create');
    Route::post('/pengiriman-berkas/store', [ArsipController::class, 'store'])->name('pengiriman-berkas.store');

    Route::get(
        '/arsip/list-berkas/{no_pengirim}',
        [ArsipController::class, 'listBerkas']
    )->name('arsip.list-berkas');
// PERBAIKAN: Ubah ke GET agar sinkron dengan AJAX pencarian
Route::get('/cari-permohonan', [ArsipController::class, 'cariPermohonan'])->name('cari-permohonan');
    
    
    // --- MODUL PENERIMAAN BERKAS ---
    Route::get('/penerimaan-berkas', [ArsipController::class, 'penerimaanBerkas'])->name('penerimaan-berkas.index');
    
    // Route untuk Scan (Manual Laptop atau API HP)
    Route::post('/penerimaan-berkas/scan', [ArsipController::class, 'scanPermohonan'])->name('penerimaan-berkas.scan-permohonan');
    
    // Cek otomatis scan dari HP (Polling)
    Route::get('/penerimaan-berkas/check-new-scan', [ArsipController::class, 'checkNewScan'])->name('penerimaan-berkas.check-new-scan');
    
    // Tombol Simpan & Konfirmasi Penerimaan
    Route::post('/penerimaan-berkas/konfirmasi-bulk', [ArsipController::class, 'konfirmasiBulk'])->name('penerimaan-berkas.konfirmasi-bulk');

    // Halaman utama pencarian (Form Kosong)
    Route::get('/pencarian-berkas', [ArsipController::class, 'pencarianBerkas'])->name('pencarian-berkas.index');

    // Proses pencarian (Hasil Cari) - Tambahkan rute ini
    Route::get('/pencarian-berkas/search', [ArsipController::class, 'searchAction'])->name('pencarian-berkas.search');
    
    Route::middleware('auth')->group(function () {
        // Tampilan Utama
        Route::get('/pinjam-berkas', [PinjamBerkasController::class, 'index'])->name('pinjam-berkas.index');
    
        // Simpan Data Baru
        Route::post('/pinjam-berkas/store', [PinjamBerkasController::class, 'store'])->name('pinjam-berkas.store');
    
        // --- TAMBAHKAN BARIS INI (Update Divisi via AJAX) ---
        Route::post('/pinjam-berkas/update-divisi/{id}', [PinjamBerkasController::class, 'updateDivisi'])->name('pinjam-berkas.update-divisi');
    
        // Aksi Tombol Status
        Route::post('/pinjam-berkas/approve/{id}', [PinjamBerkasController::class, 'approve'])->name('pinjam-berkas.approve');
        Route::post('/pinjam-berkas/reject/{id}', [PinjamBerkasController::class, 'reject'])->name('pinjam-berkas.reject');
        Route::post('/pinjam-berkas/complete/{id}', [PinjamBerkasController::class, 'complete'])->name('pinjam-berkas.complete');
    });
    // Pengaturan User & Logout
    Route::resource('users', UserController::class)->except(['show']); 
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});