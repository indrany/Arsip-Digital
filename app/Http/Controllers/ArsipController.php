<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan;
use App\Models\PinjamBerkas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // Penting untuk menghilangkan error P1013
use Carbon\Carbon;

class ArsipController extends Controller
{
    // 1. DASHBOARD
    public function dashboard()
    {
        return view('auth.dashboard.index', ['current_page' => 'dashboard']);
    }

    // 2. HALAMAN TAMBAH PENGIRIMAN (Sisi Loket/Unit)
    public function tambahPengiriman()
    {
        // Filter agar user UKK/ULP hanya melihat riwayat milik mereka sendiri
        $user = Auth::user();
        $query = DB::table('pengiriman_batch');

        if (!in_array($user->role, ['admin', 'Arsip'])) {
            $query->where('petugas_kirim', $user->name);
        }

        $riwayat = $query->orderBy('created_at', 'desc')->get();

        return view('auth.dashboard.pengiriman_berkas', [
            'current_page' => 'pengiriman-berkas',
            'riwayat' => $riwayat
        ]);
    }

    // 3. HALAMAN RIWAYAT PENGIRIMAN (Daftar Global untuk Admin)
    public function pengirimanBerkas()
    {
        return view('arsip.riwayat_pengiriman', [
            'current_page' => 'pengiriman-berkas',
            'riwayat' => DB::table('pengiriman_batch')->orderBy('created_at', 'desc')->get()
        ]);
    }

    // 4. AJAX: Load Data untuk Modal "Detail & Daftar Berkas"
    public function listBerkas($no_pengirim)
    {
        try {
            $batch = DB::table('pengiriman_batch')->where('no_pengirim', $no_pengirim)->first();
            $data = Permohonan::where('no_pengirim', $no_pengirim)->get();
            
            return response()->json([
                'success' => true,
                'batch'   => $batch,
                'data'    => $data
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 5. FUNGSI CETAK PENGANTAR
    public function cetakPengantar($no_pengirim)
    {
        $batch = DB::table('pengiriman_batch')->where('no_pengirim', $no_pengirim)->first();
        if (!$batch) abort(404, 'Data Batch tidak ditemukan.');
        
        $items = Permohonan::where('no_pengirim', $no_pengirim)->get();
        return view('arsip.cetak_pengantar', compact('batch', 'items'));
    }

    // 6. HALAMAN PENERIMAAN BERKAS (Tampilan Arsip)
    public function penerimaanBerkas()
    {
        $user = Auth::user();

        // Proteksi Role: Hanya admin, Arsip, atau kanim yang bisa masuk
        if (!$user || !in_array($user->role, ['admin', 'Arsip', 'kanim'])) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman Penerimaan Berkas.');
        }

        $riwayat_batches = DB::table('pengiriman_batch')->orderBy('created_at', 'desc')->get();
        $list_semua = Permohonan::where('status_berkas', 'SIAP_DITERIMA')->get();
        $list_sudah_scan = Permohonan::where('status_berkas', 'DITERIMA')->get();

        return view('arsip.penerimaan_berkas', [
            'current_page'    => 'penerimaan-berkas',
            'riwayat_batches' => $riwayat_batches,
            'list_semua'      => $list_semua,
            'list_sudah_scan' => $list_sudah_scan 
        ]);
    }

    // 7. AJAX: Ambil Item Batch untuk Verifikasi Scan
    public function getBatchItems($no_pengirim)
    {
        $items = Permohonan::where('no_pengirim', $no_pengirim)
            ->select('no_permohonan', 'nama', 'status_berkas')
            ->get();
        return response()->json(['success' => true, 'data' => $items]);
    }

    // 8. AJAX: Detail Berkas saat Scan Pop-up & Tombol Detail
    public function getDetail($nomor)
    {
        $data = Permohonan::where('no_permohonan', trim($nomor))->first();
        
        if ($data) {
            return response()->json(['success' => true, 'data' => $data]);
        }
        return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
    }

    // 9. AJAX: Scan Per Item (DIPERBAIKI UNTUK REALTIME)
    public function scanPermohonan(Request $request)
    {
        $nomor = trim($request->nomor_permohonan);
        
        $permohonan = Permohonan::where('no_permohonan', $nomor)
                                ->where('status_berkas', 'SIAP_DITERIMA')
                                ->first();

        if (!$permohonan) {
            return response()->json(['success' => false, 'message' => 'Berkas tidak ditemukan atau sudah di-scan'], 404);
        }

        $permohonan->update([
            'status_berkas' => 'DITERIMA', 
            'updated_at' => now()
        ]);

        // Mengembalikan data nama agar bisa langsung muncul di tabel kanan secara realtime
        return response()->json([
            'success' => true,
            'data' => [
                'no_permohonan' => $permohonan->no_permohonan,
                'nama' => $permohonan->nama
            ]
        ]);
    }

    // 10. AJAX: Konfirmasi Penerimaan Batch
    public function konfirmasiBulk(Request $request)
    {
        $no_pengirim = $request->no_pengirim;
        DB::beginTransaction();
        try {
            Permohonan::where('no_pengirim', $no_pengirim)
                ->where('status_berkas', 'DITERIMA')
                ->update(['status_berkas' => 'DITERIMA OLEH ARSIP', 'updated_at' => now()]);

            DB::table('pengiriman_batch')->where('no_pengirim', $no_pengirim)->update([
                'status' => 'DITERIMA OLEH ARSIP',
                'tgl_diterima' => now()->format('Y-m-d'),
                'updated_at' => now()
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 11. FUNGSI STORE: Otomatis deteksi User & Unit
    public function store(Request $request) {
        $list = $request->nomor_permohonan_list;
        if (!$list) return response()->json(['success' => false], 400);
    
        $user = Auth::user(); 
    
        DB::beginTransaction();
        try {
            $noBatch = 'B-' . time();
            
            DB::table('pengiriman_batch')->insert([
                'no_pengirim'   => $noBatch,
                'tgl_pengirim'  => now()->format('Y-m-d'),
                'jumlah_berkas' => count($list),
                'status'        => 'Diajukan',
                'asal_unit'     => $user->unit_kerja ?? $user->role ?? 'KANIM', 
                'petugas_kirim' => $user->name, 
                'created_at'    => now(),
                'updated_at'    => now()
            ]);
    
            foreach ($list as $item) {
                $asalData = DB::table('datapaspor.datapaspor')
                            ->where('nopermohonan', $item['no_permohonan'])->first();
    
                if ($asalData) {
                    $tglMohon = ($asalData->tglpermohonan_datetime && $asalData->tglpermohonan_datetime != '0000-00-00 00:00:00') 
                                ? Carbon::parse($asalData->tglpermohonan_datetime)->format('Y-m-d') : now()->format('Y-m-d');
    
                    Permohonan::updateOrCreate(
                        ['no_permohonan' => $item['no_permohonan']],
                        [
                            'no_pengirim'        => $noBatch,
                            'nama'               => $asalData->nama,
                            'tempat_lahir'       => $asalData->tempatlahir,  
                            'tanggal_lahir'      => $asalData->tanggallahir,
                            'jenis_kelamin'      => $asalData->jeniskelamin,
                            'no_telp'            => $asalData->notelepon,
                            'jenis_permohonan'   => $asalData->jenispermohonan,
                            'jenis_paspor'       => $asalData->jenispaspor, 
                            'tujuan_paspor'      => $asalData->tujuanpaspor,
                            'no_paspor'          => $asalData->nopaspor,
                            'tanggal_permohonan' => $tglMohon,
                            'status_berkas'      => 'SIAP_DITERIMA',
                            'alur_terakhir'      => 'Loket Pengiriman',
                            'updated_at'         => now(),
                        ]
                    );
                }
            }
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 12. PENCARIAN
    public function pencarianBerkas()
    {
        return view('auth.dashboard.pencarian_berkas', ['current_page' => 'pencarian-berkas', 'results' => null]);
    }

    public function searchAction(Request $request)
    {
        $q = $request->nomor_permohonan;
        $results = Permohonan::where('no_permohonan', 'LIKE', "%$q%")->orWhere('nama', 'LIKE', "%$q%")->get();
        return view('auth.dashboard.pencarian_berkas', ['current_page' => 'pencarian-berkas', 'results' => $results]);
    }
}