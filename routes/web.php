<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ArsipController; 
use App\Http\Controllers\UserController;
use App\Http\Controllers\PinjamBerkasController;

// 1. Rute Default - Ubah dari login ke register
Route::get('/', function () {
    return redirect()->route('register');
});
// 2. Rute Login & Registrasi
Route::middleware('guest')->group(function () {
    Route::get('/login', function () { return view('auth.login'); })->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');

    // Tambahkan ini untuk memproses pendaftaran
    Route::get('/register', function () { return view('auth.register'); })->name('register');
    Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register'])->name('register.post');
});
// 3. Rute Terproteksi (Aplikasi Utama)
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [ArsipController::class, 'dashboard'])->name('dashboard');
    
    // --- MODUL PENGIRIMAN BERKAS (LOKET) ---
    Route::get('/pengiriman-berkas', [ArsipController::class, 'pengirimanBerkas'])->name('pengiriman-berkas.index');
    Route::get('/pengiriman-berkas/tambah', [ArsipController::class, 'tambahPengiriman'])->name('pengiriman-berkas.create');
    Route::post('/pengiriman-berkas/store', [ArsipController::class, 'store'])->name('pengiriman-berkas.store');

    // Mengambil daftar berkas per batch (AJAX Modal)
    Route::get('/arsip/list-berkas/{no_pengirim}', [ArsipController::class, 'listBerkas'])->name('arsip.list-berkas');
    
    // Rute cetak pengantar
    Route::get('/arsip/cetak-pengantar/{no_pengirim}', [ArsipController::class, 'cetakPengantar'])->name('arsip.cetak-pengantar');
    
    
    // --- MODUL PENERIMAAN BERKAS (ARSIP) ---
    Route::get('/penerimaan-berkas', [ArsipController::class, 'penerimaanBerkas'])->name('penerimaan-berkas.index');
    
    // AJX: Mengambil daftar berkas khusus untuk batch yang dipilih
    Route::get('/penerimaan-berkas/items/{no_pengirim}', [ArsipController::class, 'getBatchItems'])->name('penerimaan-berkas.get-items');
    
    // AJAX: Ambil detail permohonan untuk POP-UP saat scan barcode
    Route::get('/penerimaan-berkas/get-detail/{nomor}', [ArsipController::class, 'getDetail'])->name('penerimaan-berkas.get-detail');
    
    // Proses update status per berkas (setelah konfirmasi pop-up)
    Route::post('/penerimaan-berkas/scan', [ArsipController::class, 'scanPermohonan'])->name('penerimaan-berkas.scan-permohonan');
    
    // Proses simpan permanen seluruh batch
    Route::post('/penerimaan-berkas/konfirmasi-bulk', [ArsipController::class, 'konfirmasiBulk'])->name('penerimaan-berkas.konfirmasi-bulk');


    // --- MODUL PENCARIAN BERKAS ---
    Route::get('/pencarian-berkas', [ArsipController::class, 'pencarianBerkas'])->name('pencarian-berkas.index');
    Route::get('/pencarian-berkas/search', [ArsipController::class, 'searchAction'])->name('pencarian-berkas.search');
    
    // --- MODUL PINJAM BERKAS (SUDAH DIRAPIKAN) ---
    // Rute ini menangkap Tampilan Utama DAN Hasil Pencarian
    Route::get('/pinjam-berkas', [PinjamBerkasController::class, 'index'])->name('pinjam-berkas.index');
    
    // Simpan Data Baru dari Modal
    Route::post('/pinjam-berkas/store', [PinjamBerkasController::class, 'store'])->name('pinjam-berkas.store');
    
    // Update Divisi via AJAX Dropdown (Opsional, jika masih ingin digunakan)
    Route::post('/pinjam-berkas/update-divisi/{id}', [PinjamBerkasController::class, 'updateDivisi'])->name('pinjam-berkas.update-divisi');
    
    // Aksi Persetujuan & Selesai
    Route::post('/pinjam-berkas/approve/{id}', [PinjamBerkasController::class, 'approve'])->name('pinjam-berkas.approve');
    Route::post('/pinjam-berkas/reject/{id}', [PinjamBerkasController::class, 'reject'])->name('pinjam-berkas.reject');
    Route::post('/pinjam-berkas/complete/{id}', [PinjamBerkasController::class, 'complete'])->name('pinjam-berkas.complete');
    
    // Rute untuk tombol Cek Detail di dalam Modal Pinjam
    Route::get('/cari-permohonan/{no}', [PinjamBerkasController::class, 'cariPermohonan'])->name('pinjam-berkas.cari-permohonan');
    // Pengaturan User & Logout
    Route::resource('users', UserController::class)->except(['show']); 
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});