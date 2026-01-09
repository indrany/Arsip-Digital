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
        // Hitung Rak yang kapasitasnya sudah terisi > 80%
        $rakKritis = \App\Models\RakLoker::whereRaw('terisi / kapasitas >= 0.8')
            ->where('status', 'Tersedia')
            ->get();

        // Hitung Rak yang sudah Penuh 100%
        $rakPenuhCount = \App\Models\RakLoker::where('status', 'Penuh')->count();

        return view('auth.Dashboard.index', [ 
            'userAktif' => User::where('is_active', 1)->count(),
            'userNonAktif' => User::where('is_active', 0)->count(),
            'rakKritis' => $rakKritis, // Data rak yang hampir penuh
            'rakPenuhCount' => $rakPenuhCount, // Jumlah rak yang sudah penuh
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
    // Kita Join tabel pengiriman_batch dengan permohonan agar mendapatkan No. Permohonan di halaman depan
    $riwayat = DB::table('permohonan')
        ->join('pengiriman_batch', 'permohonan.no_pengirim', '=', 'pengiriman_batch.no_pengirim')
        ->select(
            'permohonan.no_permohonan', 
            'permohonan.nama', 
            'pengiriman_batch.asal_unit', 
            'pengiriman_batch.tgl_pengirim', 
            'pengiriman_batch.status',
            'pengiriman_batch.no_pengirim as batch_id'
        )
        ->orderBy('pengiriman_batch.created_at', 'desc')
        ->get();

    return view('arsip.riwayat_pengiriman', [
        'current_page' => 'pengiriman-berkas',
        'riwayat' => $riwayat
    ]);
}

    // 4. PENERIMAAN BERKAS (ARSIP)
    public function penerimaanBerkas()
{
    $user = Auth::user();

    // Izinkan ADMIN dan TIKIM (Arsip lama)
    if (!$user || !in_array(strtoupper($user->role), ['ADMIN', 'TIKIM'])) {
        return redirect()->route('dashboard')->with('error', 'Akses ditolak. Hanya Admin atau Tikim yang diizinkan.');
    }

    // QUERY INI YANG HARUS DIUBAH (Tambahkan Join ke tabel permohonan)
    $riwayat_batches = DB::table('permohonan')
        ->join('pengiriman_batch', 'permohonan.no_pengirim', '=', 'pengiriman_batch.no_pengirim')
        ->select(
            'permohonan.no_permohonan', 
            'permohonan.nama', 
            'pengiriman_batch.asal_unit', 
            'pengiriman_batch.tgl_pengirim', 
            'pengiriman_batch.status',
            'pengiriman_batch.no_pengirim as batch_id' // Simpan ID batch asli untuk tombol proses
        )
        ->orderBy('pengiriman_batch.created_at', 'desc')
        ->get();

    $list_semua = Permohonan::where('status_berkas', 'SIAP_DITERIMA')->get();
    $list_sudah_scan = Permohonan::where('status_berkas', 'DITERIMA')->get();

    return view('arsip.penerimaan_berkas', [
        'current_page'    => 'penerimaan-berkas',
        'riwayat_batches' => $riwayat_batches, // Sekarang sudah ada kolom no_permohonan
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
    
    // Cari permohonan. Kita izinkan scan jika statusnya SIAP_DITERIMA 
    // ATAU sudah DITERIMA (untuk kasus double scan/drafting)
    $permohonan = Permohonan::where('no_permohonan', $nomor)
                            ->whereIn('status_berkas', ['SIAP_DITERIMA', 'DITERIMA'])
                            ->first();

    if (!$permohonan) {
        return response()->json([
            'success' => false, 
            'message' => 'Berkas tidak ditemukan atau bukan bagian dari pengiriman ini.'
        ], 404);
    }

    // Update status berkas menjadi DITERIMA jika sebelumnya masih SIAP_DITERIMA
    if ($permohonan->status_berkas === 'SIAP_DITERIMA') {
        $permohonan->update([
            'status_berkas' => 'DITERIMA', 
            'updated_at' => now()
        ]);
    }

    return response()->json([
        'success' => true, 
        'data' => [
            'no_permohonan' => $permohonan->no_permohonan, 
            'nama' => $permohonan->nama
        ]
    ]);
}

    // 8. KONFIRMASI BULK
    public function konfirmasiBulk(Request $request)
{
    $no_pengirim = $request->no_pengirim;
    
    DB::beginTransaction();
    try {
        // Ambil semua berkas yang sudah di-scan (status DITERIMA)
        $berkasList = Permohonan::where('no_pengirim', $no_pengirim)
                                ->where('status_berkas', 'DITERIMA')
                                ->get();

        foreach ($berkasList as $berkas) {
            // CARI RAK YANG MASIH MUAT (Status Tersedia & Terisi < Kapasitas)
            $rak = \App\Models\RakLoker::where('status', 'Tersedia')
                    ->whereColumn('terisi', '<', 'kapasitas')
                    ->orderBy('id', 'asc')
                    ->first();

            if (!$rak) {
                return response()->json(['success' => false, 'message' => 'Semua Rak Penuh! Tambah rak di master data.'], 400);
            }

            // Hitung nomor urut baru di rak tersebut
            $nomorUrutBaru = $rak->terisi + 1;

            // Update data Permohonan dengan alamat loker
            $berkas->update([
                'status_berkas' => 'DITERIMA OLEH ARSIP',
                'rak_id' => $rak->id,
                'no_urut_di_rak' => $nomorUrutBaru,
                'lokasi_arsip' => "Lemari " . $rak->no_lemari . " / Rak " . $rak->kode_rak . " / No. " . $nomorUrutBaru,
                'updated_at' => now()
            ]);

            // Update Counter di tabel Rak
            $rak->terisi = $nomorUrutBaru;
            if ($rak->terisi >= $rak->kapasitas) {
                $rak->status = 'Penuh';
            }
            $rak->save();
        }

        // Update Status Batch Pengiriman
        DB::table('pengiriman_batch')->where('no_pengirim', $no_pengirim)->update([
            'status' => 'DITERIMA OLEH ARSIP',
            'tgl_diterima' => now(),
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

    public function getPermohonanDetail($nomor)
    {
        try {
            // Cari data ke database datapaspor (Sesuai koneksi di fungsi store kamu)
            $data = DB::table('datapaspor.datapaspor')
                        ->where('nopermohonan', $nomor)
                        ->first();

            if ($data) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'nama' => $data->nama,
                        'tempat_lahir' => $data->tempatlahir,
                        'tanggal_lahir' => $data->tanggallahir,
                        'jenis_paspor' => $data->jenispaspor,
                    ]
                ]);
            }

            return response()->json([
                'success' => false, 
                'message' => 'Nomor permohonan tidak ditemukan di database paspor.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request) {
        $list = $request->nomor_permohonan_list;
        if (!$list) return response()->json(['success' => false, 'message' => 'Daftar berkas kosong'], 400);
    
        $user = Auth::user(); 

        DB::beginTransaction();
        try {
            $noBatch = 'B-' . time();
            
            DB::table('pengiriman_batch')->insert([
                'no_pengirim'   => $noBatch,
                'tgl_pengirim'  => now()->format('Y-m-d'),
                'jumlah_berkas' => count($list),
                'status'        => 'Diajukan',
                'asal_unit'     => $user->role ?? 'KANIM', 
                'petugas_kirim' => $user->nama_lengkap ?? $user->name, // Ambil nama lengkap jika ada
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

    // Fungsi baru untuk mengambil detail data permohonan
    public function getDetail($nomor)
    {
        try {
            // Cari data di tabel permohonan berdasarkan no_permohonan
            $data = Permohonan::where('no_permohonan', $nomor)->first();

            if ($data) {
                return response()->json([
                    'success' => true,
                    'data'    => $data
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan di database aplikasi.'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // 10. Tampilan Daftar Rak
    public function rakIndex()
    {
        // Gunakan path lengkap \App\Models\RakLoker
        $rak = \App\Models\RakLoker::orderBy('no_lemari', 'asc')->orderBy('kode_rak', 'asc')->get();
        return view('arsip.rak_loker', [
            'current_page' => 'rak-loker',
            'rak' => $rak
        ]);
    }

    // 11. Simpan Data Rak Baru
    public function rakStore(Request $request)
    {
        $request->validate([
            'no_lemari' => 'required',
            'jumlah_rak' => 'required|numeric|min:1',
            'kapasitas' => 'required|numeric|min:1',
        ]);

        try {
            $huruf = range('a', 'z');

            for ($i = 0; $i < $request->jumlah_rak; $i++) {
                $kodeRak = $request->no_lemari . $huruf[$i];

                // Gunakan path lengkap \App\Models\RakLoker
                \App\Models\RakLoker::create([
                    'no_lemari' => $request->no_lemari,
                    'kode_rak'  => $kodeRak,
                    'kapasitas' => $request->kapasitas,
                    'terisi'    => 0,
                    'status'    => 'Tersedia'
                ]);
            }

            return redirect()->back()->with('success', 'Data Rak berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    // 12. Hapus Rak
    public function rakDestroy($id)
    {
        // Gunakan path lengkap \App\Models\RakLoker
        $rak = \App\Models\RakLoker::findOrFail($id);
        if ($rak->terisi > 0) {
            return redirect()->back()->with('error', 'Rak tidak bisa dihapus karena sudah berisi berkas!');
        }
        $rak->delete();
        return redirect()->back()->with('success', 'Rak berhasil dihapus.');
    }
}