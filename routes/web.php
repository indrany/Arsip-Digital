<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ArsipController; 
use App\Http\Controllers\UserController;
use App\Http\Controllers\PinjamBerkasController;

// 1. RUTE PUBLIC
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', function () { return view('auth.login'); })->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

// 2. RUTE TERPROTEKSI (HARUS LOGIN)
Route::middleware('auth')->group(function () {
    
    // LOGOUT
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // DASHBOARD
    Route::get('/dashboard', [ArsipController::class, 'dashboard'])->name('dashboard');

    // --- MODUL PENGIRIMAN BERKAS (LOKET) ---
    Route::get('/pengiriman-berkas', [ArsipController::class, 'pengirimanBerkas'])->name('pengiriman-berkas.index');
    Route::get('/pengiriman-berkas/tambah', [ArsipController::class, 'tambahPengiriman'])->name('pengiriman-berkas.create');
    Route::post('/pengiriman-berkas/store', [ArsipController::class, 'store'])->name('pengiriman-berkas.store');
    Route::get('/arsip/list-berkas/{no_pengirim}', [ArsipController::class, 'listBerkas'])->name('arsip.list-berkas');
    Route::get('/arsip/cetak-pengantar/{no_pengirim}', [ArsipController::class, 'cetakPengantar'])->name('arsip.cetak-pengantar');
    
    // TAMBAHKAN BARIS INI UNTUK AUTO-FILL SAAT INPUT NO PERMOHONAN
    Route::get('/get-permohonan-detail/{nomor}', [ArsipController::class, 'getPermohonanDetail'])->name('get-permohonan-detail');

    // --- MODUL PENERIMAAN BERKAS (ARSIP) ---
    Route::get('/penerimaan-berkas', [ArsipController::class, 'penerimaanBerkas'])->name('penerimaan-berkas.index');
    Route::get('/penerimaan-berkas/items/{no_pengirim}', [ArsipController::class, 'getBatchItems'])->name('penerimaan-berkas.get-items');
    Route::get('/penerimaan-berkas/get-detail/{nomor}', [ArsipController::class, 'getDetail'])->name('penerimaan-berkas.get-detail');
    Route::post('/penerimaan-berkas/scan', [ArsipController::class, 'scanPermohonan'])->name('penerimaan-berkas.scan-permohonan');
    Route::post('/penerimaan-berkas/konfirmasi-bulk', [ArsipController::class, 'konfirmasiBulk'])->name('penerimaan-berkas.konfirmasi-bulk');

    // --- MODUL PENCARIAN BERKAS ---
    Route::get('/pencarian-berkas', [ArsipController::class, 'pencarianBerkas'])->name('pencarian-berkas.index');
    Route::get('/pencarian-berkas/search', [ArsipController::class, 'searchAction'])->name('pencarian-berkas.search');

    // --- MODUL PINJAM BERKAS ---
    Route::get('/pinjam-berkas', [PinjamBerkasController::class, 'index'])->name('pinjam-berkas.index');
    Route::post('/pinjam-berkas/store', [PinjamBerkasController::class, 'store'])->name('pinjam-berkas.store');
    Route::post('/pinjam-berkas/approve/{id}', [PinjamBerkasController::class, 'approve'])->name('pinjam-berkas.approve');
    Route::post('/pinjam-berkas/reject/{id}', [PinjamBerkasController::class, 'reject'])->name('pinjam-berkas.reject');
    Route::post('/pinjam-berkas/complete/{id}', [PinjamBerkasController::class, 'complete'])->name('pinjam-berkas.complete');
    Route::get('/cari-permohonan/{no}', [PinjamBerkasController::class, 'cariPermohonan'])->name('pinjam-berkas.cari-permohonan');
    Route::get('/pinjam-berkas/cetak/{id}', [PinjamBerkasController::class, 'cetak'])->name('pinjam-berkas.cetak');
    Route::get('/pinjam-berkas/cetak-kembali/{id}', [PinjamBerkasController::class, 'cetakKembali'])->name('pinjam-berkas.cetak-kembali');
    
    // --- MANAJEMEN USER ---
    Route::resource('users', UserController::class)->except(['show']);
    Route::patch('/users/{user}/update-status', [UserController::class, 'updateStatus'])->name('users.update-status');

    // --- MODUL MASTER RAK LOKER (TAMBAHAN REVISI) ---
    Route::get('/rak-loker', [ArsipController::class, 'rakIndex'])->name('rak-loker.index');
    Route::post('/rak-loker/store', [ArsipController::class, 'rakStore'])->name('rak-loker.store');
    Route::delete('/rak-loker/{id}', [ArsipController::class, 'rakDestroy'])->name('rak-loker.destroy');
    
    // --- MODUL PEMUSNAHAN ARSIP ---
    Route::middleware(['auth'])->group(function () {
        Route::get('/pemusnahan-arsip', [ArsipController::class, 'pemusnahanIndex'])->name('pemusnahan.index');
        Route::get('/pemusnahan-arsip/hitung', [ArsipController::class, 'hitungDokumen'])->name('pemusnahan.hitung');
        Route::get('/pemusnahan-arsip/cetak/{id}', [ArsipController::class, 'cetakPemusnahan'])->name('pemusnahan.cetak');
        Route::get('/pemusnahan-arsip/detail/{id}', [App\Http\Controllers\ArsipController::class, 'getDetailPemusnahan'])->name('pemusnahan.detail');
        Route::post('/pemusnahan-arsip/reject/{id}', [ArsipController::class, 'tolakPemusnahan'])->name('pemusnahan.reject');
        Route::post('/pemusnahan-arsip/upload/{id}', [ArsipController::class, 'upload'])->name('pemusnahan.upload');
        
        // Route untuk TIKIM (Simpan Data & Upload Susulan)
        Route::post('/pemusnahan-arsip/simpan', [ArsipController::class, 'simpanPemusnahan'])->name('pemusnahan.store');
        Route::post('/pemusnahan-arsip/upload/{id}', [ArsipController::class, 'uploadPDF'])->name('pemusnahan.upload');
        
        // Route untuk ADMIN (Persetujuan)
        Route::post('/pemusnahan-arsip/setujui/{id}', [ArsipController::class, 'setujuiPemusnahan'])->name('pemusnahan.approve');
    });
});

