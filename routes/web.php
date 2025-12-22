<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ArsipController; 
use App\Http\Controllers\UserController;
use Illuminate\Http\Request; 

// 1. Rute Default (Landing)
Route::get('/', function () {
    return redirect()->route('login');
});

// 2. Rute Login (Akses oleh Tamu Saja)
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
    
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

// 3. Rute Terproteksi (Hanya untuk Pengguna yang sudah Login)
Route::middleware('auth')->group(function () {

    // --- DASHBOARD ---
    Route::get('/dashboard', [ArsipController::class, 'dashboard'])->name('dashboard');

    // --- MODUL PENGIRIMAN BERKAS ---
    Route::get('/pengiriman-berkas', [ArsipController::class, 'pengirimanBerkas'])->name('pengiriman-berkas.index');
    Route::get('/pengiriman-berkas/tambah', [ArsipController::class, 'create'])->name('pengiriman-berkas.create');

    /**
     * PERBAIKAN DI SINI:
     * 1. Nama rute diubah menjadi 'cari-permohonan' agar sesuai dengan route() di Blade.
     * 2. Metode diubah menjadi POST karena di Script AJAX Anda menggunakan type: "POST".
     */
    Route::post('/pengiriman-berkas/cari-permohonan', [ArsipController::class, 'cariPermohonan'])->name('cari-permohonan');

    Route::post('/pengiriman-berkas/store', [ArsipController::class, 'store'])->name('pengiriman-berkas.store');

    // --- MODUL PENERIMAAN BERKAS (ALUR SCAN OTOMATIS) ---
    Route::get('/penerimaan-berkas', [ArsipController::class, 'penerimaanBerkas'])->name('penerimaan-berkas.index');
    
    // Pencarian data saat komputer melakukan scan
    Route::post('/penerimaan-berkas/scan-permohonan', [ArsipController::class, 'scanPermohonan'])->name('penerimaan-berkas.scan-permohonan');
    
    // Polling data baru dari HP
    Route::get('/penerimaan-berkas/check-new', [ArsipController::class, 'checkNewScan'])->name('penerimaan-berkas.check-new');
    
    // Simpan data scan bulk
    Route::post('/penerimaan-berkas/konfirmasi-bulk', [ArsipController::class, 'konfirmasiBulk'])->name('penerimaan-berkas.konfirmasi-bulk');

    // --- MODUL NAVIGASI LAINNYA ---
    Route::get('/pencarian-berkas', [ArsipController::class, 'pencarianBerkas'])->name('pencarian-berkas.index');
    Route::get('/pencarian-berkas/search', [ArsipController::class, 'searchAction'])->name('pencarian-berkas.search');
    Route::get('/pinjam-berkas', [ArsipController::class, 'pinjamBerkas'])->name('pinjam-berkas.index');
    
    // --- MANAJEMEN PENGGUNA ---
    Route::resource('users', UserController::class)->except(['show']); 

    // --- LOGOUT ---
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});