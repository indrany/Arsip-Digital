<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengirimanBerkas; 
use App\Models\Permohonan; 
use Illuminate\Support\Facades\DB; 
use Carbon\Carbon; 
use Illuminate\Support\Facades\Auth;

class ArsipController extends Controller
{
    /**
     * Tampilkan halaman Dashboard.
     */
    public function dashboard()
    {
        $data['current_page'] = 'dashboard'; 
        return view('auth.Dashboard.index', $data); 
    }

    // --- MODUL PENGIRIMAN BERKAS ---

    public function pengirimanBerkas()
    {
        $riwayat_berkas = [
            (object)['no_pengirim' => '0234', 'tanggal_pengirim' => '08-12-2025', 'tanggal_diterima' => null, 'jumlah_berkas' => 2],
            (object)['no_pengirim' => '0235', 'tanggal_pengirim' => '10-12-2025', 'tanggal_diterima' => '15-12-2025', 'jumlah_berkas' => 1],
        ];
        $data['current_page'] = 'pengiriman-berkas'; 
        $data['riwayat_berkas'] = $riwayat_berkas;
        return view('auth.Dashboard.pengiriman_berkas', $data); 
    }
    
    public function create()
    {
        $data['current_page'] = 'pengiriman-berkas'; 
        return view('auth.Dashboard.pengiriman_berkas_create', $data); 
    }

    public function cariPermohonan(Request $request)
    {
        $nomor_permohonan = $request->nomor_permohonan;
        if (empty($nomor_permohonan)) {
            return response()->json(['message' => 'Nomor Permohonan wajib diisi.'], 400);
        }

        // Cari di database nyata menggunakan model Permohonan
        $data_permohonan = Permohonan::where('no_permohonan', $nomor_permohonan)->first();

        if ($data_permohonan) {
             return response()->json(['message' => 'Data ditemukan.', 'data' => $data_permohonan]);
        } else {
             return response()->json(['message' => 'Data tidak ditemukan.'], 404);
        }
    }

    public function store(Request $request)
    {
        return response()->json([
            'message' => 'Pengiriman berhasil disimpan!',
            'redirect_url' => route('pengiriman-berkas.index') 
        ]);
    }
    
    // --- MODUL PENERIMAAN BERKAS ---

    public function penerimaanBerkas()
{
    // PERBAIKAN: Ambil data SIAP_DITERIMA (tabel kiri) 
    // DAN data yang sedang di-scan HP/Tinker (tabel kanan)
    $list_siap_diterima = Permohonan::where('status_berkas', 'SIAP_DITERIMA')->get();
    
    // Tambahkan ini agar data yang sudah di-scan HP tetap ada saat halaman di-refresh
    $list_sudah_scan = Permohonan::where('status_berkas', 'DITERIMA_SCAN')->get();

    $data['current_page'] = 'penerimaan-berkas';
    $data['list_siap_diterima'] = $list_siap_diterima; 
    $data['list_sudah_scan'] = $list_sudah_scan; // Kirim data ini ke View

    return view('arsip.penerimaan_berkas', $data); 
}

    /**
     * AJAX: Scan Berkas Individual (Scanner Kabel)
     */
    public function scanPermohonan(Request $request)
    {
        $nomorPermohonan = $request->input('nomor_permohonan');
        
        $permohonan = Permohonan::where('no_permohonan', $nomorPermohonan)->first();

        if ($permohonan) {
            return response()->json([
                'success' => true,
                'data' => $permohonan
            ]);
        } 
        
        return response()->json(['success' => false, 'message' => 'Berkas tidak ditemukan.'], 404);
    }

    /**
     * AJAX: POLLING HP (Mengecek data yang di-scan melalui HP)
     * Aplikasi HP harus mengubah status_berkas menjadi 'DITERIMA_SCAN'
     */
    public function checkNewScan()
    {
        // Cari data yang baru saja di-scan oleh aplikasi HP
        $newData = Permohonan::where('status_berkas', 'DITERIMA_SCAN')->get();

        if ($newData->count() > 0) {
            return response()->json([
                'hasNewData' => true,
                'data_list' => $newData
            ]);
        }

        return response()->json(['hasNewData' => false]);
    }

    /**
     * AJAX: Simpan Konfirmasi Akhir (Tombol Hijau)
     */
    public function konfirmasiBulk(Request $request)
    {
        $nomorPermohonanList = $request->input('nomor_permohonan_list');
        
        if (empty($nomorPermohonanList)) {
            return response()->json(['message' => 'Daftar berkas tidak valid.'], 400);
        }

        try {
            DB::beginTransaction();

            // Update status menjadi DITERIMA (Selesai)
            Permohonan::whereIn('no_permohonan', $nomorPermohonanList)
                      ->update([
                          'status_berkas' => 'DITERIMA',
                          'tanggal_diterima' => Carbon::now()
                      ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => count($nomorPermohonanList) . ' berkas berhasil dikonfirmasi.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal simpan database.'], 500);
        }
    }
    
    // --- MODUL LAIN ---
    
    public function pencarianBerkas()
    {
        $data['current_page'] = 'pencarian-berkas';
        return view('auth.Dashboard.pencarian_berkas', $data); 
    }
    
    public function pinjamBerkas()
    {
        $data['current_page'] = 'pinjam-berkas';
        return view('auth.Dashboard.pinjam_berkas', $data); 
    }
}