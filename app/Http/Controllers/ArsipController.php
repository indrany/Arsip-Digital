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
        $list_siap_diterima = Permohonan::where('status_berkas', 'SIAP_DITERIMA')->get();
        $list_sudah_scan = Permohonan::where('status_berkas', 'DITERIMA_SCAN')->get();

        $data['current_page'] = 'penerimaan-berkas';
        $data['list_siap_diterima'] = $list_siap_diterima; 
        $data['list_sudah_scan'] = $list_sudah_scan; 

        return view('arsip.penerimaan_berkas', $data); 
    }

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

    public function checkNewScan()
    {
        $newData = Permohonan::where('status_berkas', 'DITERIMA_SCAN')->get();

        if ($newData->count() > 0) {
            return response()->json([
                'hasNewData' => true,
                'data_list' => $newData
            ]);
        }

        return response()->json(['hasNewData' => false]);
    }

    public function konfirmasiBulk(Request $request)
    {
        $nomorPermohonanList = $request->input('nomor_permohonan_list');
        
        if (empty($nomorPermohonanList)) {
            return response()->json(['message' => 'Daftar berkas tidak valid.'], 400);
        }

        try {
            DB::beginTransaction();

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
    
    // --- MODUL PENCARIAN BERKAS (PERBAIKAN LOKASI VIEW) ---
    
    public function pencarianBerkas()
    {
        $data['current_page'] = 'pencarian-berkas';
        $data['results'] = null; 
        
        // Hapus 'auth.Dashboard.' cukup tulis nama filenya saja
        return view('pencarian_berkas', $data); 
    }

    public function searchAction(Request $request)
{
    $data['current_page'] = 'pencarian-berkas';
    $query = $request->input('nomor_permohonan');

    // Pastikan nama model 'Permohonan' sudah benar
    $data['results'] = Permohonan::where('no_permohonan', 'LIKE', '%' . $query . '%')
                                ->orWhere('nama_pemohon', 'LIKE', '%' . $query . '%')
                                ->get();
    
    $data['query_text'] = $query;

    // AKTIFKAN INI JIKA NAMA MASIH KOSONG:
    // dd($data['results']->toArray()); 

    return view('pencarian_berkas', $data);
}
    
    // --- MODUL PINJAM BERKAS ---

    public function pinjamBerkas()
    {
        $data['current_page'] = 'pinjam-berkas';
        // Pastikan file ini juga ada di folder views
        return view('pinjam_berkas', $data); 
    }
}