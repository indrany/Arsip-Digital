<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan; 
use Illuminate\Support\Facades\DB; 

class PengirimanBerkasController extends Controller
{
    public function index()
    {
        // Jika Anda belum membuat model PengirimanBerkas, kita gunakan query langsung dulu
        $riwayat_berkas = Permohonan::where('status_berkas', 'DIKIRIM')
                                    ->orderBy('updated_at', 'desc')->get();
        
        return view('auth.Dashboard.pengiriman_berkas', compact('riwayat_berkas'));
    }

    public function create()
    {
        return view('auth.Dashboard.pengiriman_berkas_create');
    }

    public function cariPermohonan(Request $request)
    {
        $request->validate(['nomor_permohonan' => 'required|string']);
        $nomor = $request->nomor_permohonan;
        
        // Mencari data di database berdasarkan nomor permohonan
        $permohonan = Permohonan::where('no_permohonan', $nomor)->first();

        if ($permohonan) {
            return response()->json([
                'success' => true,
                'data' => [
                    'no_permohonan' => $permohonan->no_permohonan,
                    // Gunakan string langsung jika kolom di DB bukan objek Carbon/Date
                    'tanggal_permohonan' => $permohonan->tanggal_permohonan, 
                    'nama' => $permohonan->nama, // Sesuai kolom 'nama' di migrasi Anda
                    'tempat_lahir' => $permohonan->tempat_lahir ?? '-',
                    'tanggal_lahir' => $permohonan->tanggal_lahir ?? '-'
                ]
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Nomor Permohonan ' . $nomor . ' tidak ditemukan.'
        ], 404);
    }

    public function store(Request $request)
    {
        $nomor_permohonan_list = $request->nomor_permohonan_list;

        if (empty($nomor_permohonan_list)) {
            return response()->json(['success' => false, 'message' => 'Daftar permohonan kosong.'], 400);
        }

        // Proses Update Status Berkas di Database
        Permohonan::whereIn('no_permohonan', $nomor_permohonan_list)
            ->update(['status_berkas' => 'DIKIRIM']);

        return response()->json([
            'success' => true,
            'message' => 'Berkas berhasil dikirim ke bagian Penerimaan!',
            // Pastikan nama route ini sudah ada di web.php Anda
            'redirect_url' => route('penerimaan-berkas.index') 
        ]);
    }
}