<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan;
use App\Models\PinjamBerkas;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ArsipController extends Controller
{
    // 1. DASHBOARD
    public function dashboard() {
        return view('auth.Dashboard.index', [ 
            'userAktif' => User::where('is_active', 1)->count(),
            'userNonAktif' => User::where('is_active', 0)->count(),
        ]);
    }

    // 2. HALAMAN TAMBAH PENGIRIMAN (LOKET)
    public function tambahPengiriman()
    {
        $user = Auth::user();
        $query = DB::table('pengiriman_batch');

        if (!in_array($user->role, ['admin', 'Arsip', 'ADMIN'])) {
            $query->where('petugas_kirim', $user->name);
        }

        $riwayat = $query->orderBy('created_at', 'desc')->get();

        return view('auth.Dashboard.pengiriman_berkas', [
            'current_page' => 'pengiriman-berkas',
            'riwayat' => $riwayat
        ]);
    }

    // 3. RIWAYAT PENGIRIMAN
    public function pengirimanBerkas()
    {
        return view('arsip.riwayat_pengiriman', [
            'current_page' => 'pengiriman-berkas',
            'riwayat' => DB::table('pengiriman_batch')->orderBy('created_at', 'desc')->get()
        ]);
    }

    // 4. PENERIMAAN BERKAS (ARSIP)
    public function penerimaanBerkas()
    {
        $user = Auth::user();

        // Cek akses role
        if (!$user || !in_array($user->role, ['admin', 'Arsip', 'ADMIN'])) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
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

    // 5. AJAX LIST BERKAS
    public function listBerkas($no_pengirim)
    {
        try {
            $batch = DB::table('pengiriman_batch')->where('no_pengirim', $no_pengirim)->first();
            $data = Permohonan::where('no_pengirim', $no_pengirim)->get();
            return response()->json(['success' => true, 'batch' => $batch, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 6. CETAK PENGANTAR
    public function cetakPengantar($no_pengirim)
    {
        $batch = DB::table('pengiriman_batch')->where('no_pengirim', $no_pengirim)->first();
        if (!$batch) abort(404);
        $items = Permohonan::where('no_pengirim', $no_pengirim)->get();
        return view('arsip.cetak_pengantar', compact('batch', 'items'));
    }

    // 7. SCAN PERMOHONAN
    public function scanPermohonan(Request $request)
    {
        $nomor = trim($request->nomor_permohonan);
        $permohonan = Permohonan::where('no_permohonan', $nomor)
                                ->where('status_berkas', 'SIAP_DITERIMA')
                                ->first();

        if (!$permohonan) {
            return response()->json(['success' => false, 'message' => 'Berkas tidak ditemukan'], 404);
        }

        $permohonan->update(['status_berkas' => 'DITERIMA', 'updated_at' => now()]);

        return response()->json(['success' => true, 'data' => ['no_permohonan' => $permohonan->no_permohonan, 'nama' => $permohonan->nama]]);
    }

    // 8. KONFIRMASI BULK
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

    // 9. PENCARIAN
    public function pencarianBerkas() {
        return view('auth.Dashboard.pencarian_berkas', ['current_page' => 'pencarian-berkas', 'results' => null]);
    }

    public function searchAction(Request $request) {
        $q = $request->nomor_permohonan;
        $results = Permohonan::where('no_permohonan', 'LIKE', "%$q%")->orWhere('nama', 'LIKE', "%$q%")->get();
        return view('auth.Dashboard.pencarian_berkas', ['current_page' => 'pencarian-berkas', 'results' => $results]);
    }

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
}