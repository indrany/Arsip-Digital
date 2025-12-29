<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan;
use App\Models\PinjamBerkas;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArsipController extends Controller
{
    // =========================
    // DASHBOARD
    // =========================
    public function dashboard()
    {
        return view('auth.dashboard.index', [
            'current_page' => 'dashboard'
        ]);
    }

    // =========================
    // PENGIRIMAN BERKAS
    // =========================
    public function tambahPengiriman()
    {
        return view('auth.dashboard.pengiriman_berkas', [
            'current_page' => 'pengiriman-berkas'
        ]);
    }

    public function cariPermohonan(Request $request)
    {
        $permohonan = Permohonan::where('no_permohonan', $request->no_permohonan)->first();

        if (!$permohonan) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $permohonan
        ]);
    }

    public function pengirimanBerkas()
    {
        return view('arsip.riwayat_pengiriman', [
            'current_page' => 'pengiriman-berkas',
            'riwayat' => DB::table('pengiriman_batch')
                ->orderBy('created_at', 'desc')
                ->get()
        ]);
    }

    public function store(Request $request)
    {
        $list = $request->nomor_permohonan_list;
        if (!$list) {
            return response()->json(['success' => false], 400);
        }

        DB::beginTransaction();
        try {
            $noBatch = 'B-' . time();

            DB::table('pengiriman_batch')->insert([
                'no_pengirim'   => $noBatch,
                'tgl_pengirim'  => now()->format('Y-m-d'),
                'jumlah_berkas' => count($list),
                'status'        => 'Diajukan',
                'created_at'    => now(),
                'updated_at'    => now()
            ]);

            foreach ($list as $item) {
                Permohonan::updateOrCreate(
                    ['no_permohonan' => $item['no_permohonan']],
                    [
                        'nama'             => $item['nama'],
                        'no_pengirim'      => $noBatch,
                        'status_berkas'    => 'SIAP_DITERIMA',
                        'jenis_permohonan' => 'Baru',
                        'jenis_paspor'     => '48H Biometrik',
                        'tanggal_permohonan' => now()->format('Y-m-d'),
                        'alur_terakhir'    => 'Loket Pengiriman',
                        'updated_at'       => now(),
                    ]
                );
            }

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================
    // LIST BERKAS PER BATCH
    // =========================
    public function listBerkas($no_pengirim)
    {
        $data = Permohonan::where('no_pengirim', $no_pengirim)->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    // =========================
    // PENERIMAAN BERKAS
    // =========================
    public function penerimaanBerkas()
    {
        return view('arsip.penerimaan_berkas', [
            'current_page' => 'penerimaan-berkas',
            'list_semua' => Permohonan::where('status_berkas', 'SIAP_DITERIMA')->get(),
            'list_sudah_scan' => Permohonan::where('status_berkas', 'DITERIMA')
                ->whereDate('updated_at', today())
                ->get()
        ]);
    }

    public function scanPermohonan(Request $request)
    {
        $permohonan = Permohonan::where('no_permohonan', $request->nomor_permohonan)->first();

        if (!$permohonan) {
            return response()->json(['success' => false], 404);
        }

        $permohonan->update([
            'status_berkas' => 'DITERIMA',
            'updated_at' => now()
        ]);

        // 🔥 MASUK KE PINJAM BERKAS
        PinjamBerkas::create([
            'permohonan_id' => $permohonan->id,
            'nama_peminjam' => 'Menunggu Input',
            'tgl_pinjam'    => now()->format('Y-m-d'), // KUNCI
            'status'        => 'Pengajuan'
        ]);

        return response()->json(['success' => true]);
    }

    public function konfirmasiBulk()
    {
        DB::beginTransaction();
        try {
            Permohonan::where('status_berkas', 'DITERIMA')
                ->whereDate('updated_at', today())
                ->update([
                    'status_berkas' => 'DITERIMA OLEH ARSIP',
                    'updated_at' => now()
                ]);

            DB::table('pengiriman_batch')
                ->where('status', 'Diajukan')
                ->update([
                    'status' => 'DITERIMA OLEH ARSIP',
                    'tgl_diterima' => now()->format('Y-m-d'),
                    'updated_at' => now()
                ]);

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false], 500);
        }
    }

    // =========================
    // PENCARIAN BERKAS
    // =========================
    public function pencarianBerkas()
    {
        return view('auth.dashboard.pencarian_berkas', [
            'current_page' => 'pencarian-berkas',
            'results' => null
        ]);
    }

    public function searchAction(Request $request)
    {
        $q = $request->nomor_permohonan;

        $results = Permohonan::where('no_permohonan', 'LIKE', "%$q%")
            ->orWhere('nama', 'LIKE', "%$q%")
            ->get();

        return view('auth.dashboard.pencarian_berkas', [
            'current_page' => 'pencarian-berkas',
            'results' => $results
        ]);
    }
}
