<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan;
use Carbon\Carbon;

class PenerimaanBerkasController extends Controller
{
    public function index() {
        // Mengambil data yang masih dalam proses pengiriman
        $list_semua = Permohonan::where('status_berkas', 'DIKIRIM')->get();
        
        // Mengambil data yang sudah di-scan/diterima hari ini untuk kolom kanan
        $list_sudah_scan = Permohonan::where('status_berkas', 'DITERIMA')
                            ->whereDate('updated_at', Carbon::today())
                            ->get();
    
        return view('penerimaan.index', compact('list_semua', 'list_sudah_scan'));
    }

    public function scanPermohonan(Request $request)
    {
        $nomor = $request->nomor_permohonan;
        
        // Cari data tanpa mengunci status secara ketat agar lebih fleksibel
        $permohonan = Permohonan::where('no_permohonan', $nomor)->first();

        if ($permohonan) {
            // Ubah status menjadi DITERIMA agar masuk ke tabel sebelah kanan
            $permohonan->update([
                'status_berkas' => 'DITERIMA',
                'updated_at' => Carbon::now()
            ]);

            return response()->json([
                'success' => true,
                'data' => $permohonan
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Nomor permohonan tidak ditemukan.'
        ], 404);
    }

    public function checkNewScan()
    {
        // Mencari data yang baru saja diupdate menjadi DITERIMA (dari HP atau PC lain)
        // Kita gunakan pengecekan updated_at dalam 10 detik terakhir
        $hasNew = Permohonan::where('status_berkas', 'DITERIMA')
                            ->where('updated_at', '>=', Carbon::now()->subSeconds(10))
                            ->exists();

        return response()->json([
            'has_new' => $hasNew // Key ini harus 'has_new' sesuai JavaScript di View
        ]);
    }

    public function konfirmasiBulk(Request $request)
    {
        // Karena scan permohonan sudah mengubah status ke DITERIMA secara otomatis,
        // Fungsi ini bisa digunakan untuk finalisasi atau pencetakan tanda terima massal.
        return response()->json([
            'success' => true,
            'message' => 'Semua berkas telah berhasil diperbarui.'
        ]);
    }
}