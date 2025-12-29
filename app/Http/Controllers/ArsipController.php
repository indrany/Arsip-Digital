<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan; 
use Illuminate\Support\Facades\DB; 
use Carbon\Carbon; 

class ArsipController extends Controller
{
    // --- 1. DASHBOARD ---
    public function dashboard() {
        $data['current_page'] = 'dashboard'; 
        return view('auth.dashboard.index', $data); 
    }

    // --- 2. MODUL PENGIRIMAN BERKAS ---
    public function pengirimanBerkas() {
        $data['current_page'] = 'pengiriman-berkas';
        $data['riwayat'] = DB::table('pengiriman_batch')
                            ->orderBy('created_at', 'desc')
                            ->get();
        return view('arsip.riwayat_pengiriman', $data);
    }

    public function tambahPengiriman() {
        $data['current_page'] = 'pengiriman-berkas';
        return view('auth.dashboard.pengiriman_berkas', $data);
    }

    public function cariPermohonan(Request $request) {
        $nomor = $request->no_permohonan;
        $permohonan = Permohonan::where('no_permohonan', $nomor)->first();

        if ($permohonan) {
            return response()->json(['success' => true, 'data' => $permohonan]);
        }
        return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
    }

    public function store(Request $request) {
        $dataList = $request->input('nomor_permohonan_list');
        if (empty($dataList)) return response()->json(['success' => false], 400);

        try {
            DB::beginTransaction();
            $noBatch = rand(1000, 9999);
            DB::table('pengiriman_batch')->insert([
                'no_pengirim' => $noBatch,
                'tgl_pengirim' => now(),
                'jumlah_berkas' => count($dataList),
                'status' => 'Diajukan',
                'created_at' => now()
            ]);

            foreach ($dataList as $item) {
                Permohonan::updateOrCreate(
                    ['no_permohonan' => $item['no_permohonan']], 
                    ['nama' => $item['nama'], 'status_berkas' => 'SIAP_DITERIMA', 'updated_at' => now()]
                );
            }
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // --- 3. MODUL PENERIMAAN BERKAS ---
    public function penerimaanBerkas() {
        $data['current_page'] = 'penerimaan-berkas';
        $data['list_semua'] = Permohonan::whereIn('status_berkas', ['SIAP_DITERIMA', 'DITERIMA'])->get();
        $data['list_sudah_scan'] = Permohonan::where('status_berkas', 'DITERIMA')
                                             ->whereDate('updated_at', Carbon::today())
                                             ->get();
        return view('arsip.penerimaan_berkas', $data);
    }

    public function scanPermohonan(Request $request) {
        $nomor = $request->nomor_permohonan;
        $permohonan = Permohonan::where('no_permohonan', $nomor)->first();

        if ($permohonan) {
            $permohonan->update(['status_berkas' => 'DITERIMA', 'updated_at' => now()]);
            return response()->json(['success' => true, 'data' => $permohonan]);
        }
        return response()->json(['success' => false], 404);
    }

    public function checkNewScan() {
        $hasNew = Permohonan::where('status_berkas', 'DITERIMA')
                            ->where('updated_at', '>=', now()->subSeconds(10))
                            ->exists();
        return response()->json(['has_new' => $hasNew]);
    }

    public function konfirmasiBulk(Request $request) {
        Permohonan::where('status_berkas', 'DITERIMA')
                  ->whereDate('updated_at', Carbon::today())
                  ->update(['status_berkas' => 'DITERIMA OLEH ARSIP', 'updated_at' => now()]);
        return response()->json(['success' => true]);
    }

    // --- 4. MODUL PENCARIAN & PINJAM ---
    public function pencarianBerkas() {
        $data['current_page'] = 'pencarian-berkas';
        $data['results'] = null;
        return view('pencarian_berkas', $data);
    }

    public function searchAction(Request $request) {
        $data['current_page'] = 'pencarian-berkas';
        $query = $request->input('nomor_permohonan');
        $data['results'] = Permohonan::where('no_permohonan', 'LIKE', "%$query%")
                                    ->orWhere('nama', 'LIKE', "%$query%")
                                    ->get();
        return view('pencarian_berkas', $data);
    }

    public function pinjamBerkas() {
        $data['current_page'] = 'pinjam-berkas';
        return view('pinjam_berkas', $data);
    }
}