<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan; // Pastikan model Permohonan di-import
use Carbon\Carbon;

class PenerimaanBerkasController extends Controller
{
    /**
     * Menampilkan halaman utama penerimaan berkas
     */
    public function index()
    {
        // Ambil data yang siap diterima (tabel kiri)
        // Sesuaikan 'SIAP_DITERIMA' dengan status di database Anda
        $list_siap_diterima = Permohonan::where('status_berkas', 'SIAP_DITERIMA')->get();

        return view('arsip.penerimaan_berkas', compact('list_siap_diterima'));
    }

    /**
     * Memproses scan barcode dari komputer (Scanner kabel/manual)
     */
    public function scanPermohonan(Request $request)
    {
        $request->validate([
            'nomor_permohonan' => 'required'
        ]);

        $permohonan = Permohonan::where('no_permohonan', $request->nomor_permohonan)
                                ->first();

        if (!$permohonan) {
            return response()->json(['message' => 'Data tidak ditemukan.'], 404);
        }

        // Jika ditemukan, kembalikan data untuk dipindah ke tabel kanan secara visual
        return response()->json([
            'success' => true,
            'data' => $permohonan
        ]);
    }

    /**
     * KODE KRITIS: Mengecek apakah ada data baru yang masuk dari scan HP
     * Fungsi ini dipanggil oleh JavaScript setInterval di View
     */
    public function checkNewScan()
    {
        // Cari berkas yang statusnya 'DITERIMA_SCAN'
        // Asumsi: Aplikasi HP Anda mengubah status berkas menjadi 'DITERIMA_SCAN' saat sukses scan
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
     * Menyimpan dan mengonfirmasi semua berkas yang sudah di-scan (Simpan Massal)
     */
    public function konfirmasiBulk(Request $request)
    {
        $list_nomor = $request->nomor_permohonan_list;

        if (!$list_nomor || count($list_nomor) == 0) {
            return response()->json(['message' => 'Tidak ada berkas untuk dikonfirmasi.'], 400);
        }

        // Update status semua nomor yang dipilih menjadi 'DITERIMA'
        Permohonan::whereIn('no_permohonan', $list_nomor)
            ->update([
                'status_berkas' => 'DITERIMA',
                'tanggal_diterima' => Carbon::now()
            ]);

        return response()->json([
            'success' => true,
            'message' => count($list_nomor) . ' Berkas berhasil dikonfirmasi dan disimpan.'
        ]);
    }
}