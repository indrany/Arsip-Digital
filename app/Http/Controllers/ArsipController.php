<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan;
use App\Models\PinjamBerkas;
use App\Models\User;
use App\Models\RakLoker;
use App\Models\PemusnahanArsip;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ArsipController extends Controller
{
    // 1. DASHBOARD
    public function dashboard() {
        set_time_limit(120);
        $tahunMulai = 2026;
        $tahunSekarang = (int)date('Y');
        $availableYears = range($tahunSekarang, $tahunMulai);
        
        $chartData = [];
        foreach ($availableYears as $year) {  
            $dataPemohon = Permohonan::whereYear('tanggal_permohonan', $year)
                ->selectRaw('MONTH(tanggal_permohonan) as month, COUNT(*) as count')
                ->groupBy('month')
                ->pluck('count', 'month')->toArray();

            $dataDipinjam = PinjamBerkas::whereYear('tgl_pinjam', $year)
                ->where('status', '!=', 'Ditolak')
                ->selectRaw('MONTH(tgl_pinjam) as month, COUNT(*) as count')
                ->groupBy('month')
                ->pluck('count', 'month')->toArray();

            $pemohon = [];
            $dipinjam = [];
    
            for ($m = 1; $m <= 12; $m++) {
                $pemohon[] = $dataPemohon[$m] ?? 0;
                $dipinjam[] = $dataDipinjam[$m] ?? 0;
            }
    
            $chartData[$year] = [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                'pemohon' => $pemohon,
                'dipinjam' => $dipinjam
            ];
        }
    
        $totalPemohon = Permohonan::count();
        $totalDipinjam = PinjamBerkas::where('status', '!=', 'Selesai')->count();
        $rakKritis = RakLoker::whereRaw('terisi / kapasitas >= 0.8')->get();
        $rakPenuhCount = RakLoker::where('status', 'Penuh')->count();
    
        return view('auth.Dashboard.index', compact('chartData', 'totalPemohon', 'totalDipinjam', 'rakKritis', 'rakPenuhCount'));
    }

    // 2. HALAMAN TAMBAH PENGIRIMAN (LOKET)
    public function tambahPengiriman()
    {
        $user = Auth::user();
        $query = DB::table('pengiriman_batch');
        if (!in_array(strtoupper($user->role), ['ADMIN', 'TIKIM'])) {
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
        $riwayat = DB::table('pengiriman_batch')
            ->select('no_pengirim', 'tgl_pengirim', 'jumlah_berkas', 'asal_unit', 'status')
            ->orderByRaw("CASE WHEN status = 'Diajukan' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->get();
        return view('arsip.riwayat_pengiriman', [
            'current_page' => 'pengiriman-berkas',
            'riwayat' => $riwayat
        ]);
    }

    // 4. PENERIMAAN BERKAS (ARSIP) - UPDATE LOGIKA RAK
    public function penerimaanBerkas()
    {
        $user = Auth::user();
        if (!$user || !in_array(strtoupper($user->role), ['ADMIN', 'TIKIM'])) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        $antrean_batches = DB::table('pengiriman_batch')
            ->select('no_pengirim', 'asal_unit', 'tgl_pengirim', 'status', 'jumlah_berkas')
            ->whereIn('status', ['Diajukan', 'DITERIMA OLEH ARSIP']) 
            ->orderByRaw("FIELD(status, 'Diajukan') DESC")
            ->orderBy('created_at', 'desc')
            ->get();

        // Cek ketersediaan rak untuk validasi tombol simpan
        $jumlahLoker = RakLoker::count();
        $lokers = RakLoker::where('status', 'Tersedia')->get();

        return view('arsip.penerimaan_berkas', [
            'current_page'    => 'penerimaan-berkas',
            'antrean_batches' => $antrean_batches,
            'lokers'          => $lokers,
            'adaLoker'        => $jumlahLoker > 0
        ]);
    }

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

    public function cetakPengantar($no_pengirim)
    {
        $batch = DB::table('pengiriman_batch')->where('no_pengirim', $no_pengirim)->first();
        if (!$batch) abort(404);
        $items = Permohonan::where('no_pengirim', $no_pengirim)->get();
        return view('arsip.cetak_pengantar', compact('batch', 'items'));
    }

    public function scanPermohonan(Request $request)
    {
        $nomor = trim($request->nomor_permohonan);
        $permohonan = Permohonan::where('no_permohonan', $nomor)->first();
        if (!$permohonan) {
            return response()->json(['success' => false, 'message' => 'Barcode ' . $nomor . ' tidak terdaftar!'], 404);
        }
        if ($permohonan->status_berkas === 'DITERIMA OLEH ARSIP') {
             return response()->json(['success' => false, 'message' => 'Berkas sudah masuk rak arsip.'], 422);
        }
        $permohonan->update(['status_berkas' => 'DITERIMA', 'updated_at' => now()]);
        return response()->json(['success' => true, 'data' => ['no_permohonan' => $permohonan->no_permohonan, 'nama' => $permohonan->nama]]);
    }

    // 8. KONFIRMASI BULK (OTOMATIS CARI RAK)
    public function konfirmasiBulk(Request $request)
    {
        $no_pengirim = $request->no_pengirim;
        DB::beginTransaction();
        try {
            $berkasList = Permohonan::where('no_pengirim', $no_pengirim)
                                    ->where('status_berkas', 'DITERIMA')
                                    ->get();

            if ($berkasList->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Tidak ada berkas yang di-scan.'], 400);
            }

            foreach ($berkasList as $berkas) {
                // Cari rak yang masih tersedia secara otomatis
                $rak = RakLoker::where('status', 'Tersedia')
                                ->whereColumn('terisi', '<', 'kapasitas')
                                ->orderBy('id', 'asc')
                                ->first();

                if (!$rak) {
                    throw new \Exception('Semua Rak Penuh! Silakan tambah master rak loker.');
                }

                $nomorUrutBaru = $rak->terisi + 1;
                $berkas->update([
                    'status_berkas'  => 'DITERIMA OLEH ARSIP',
                    'rak_id'         => $rak->id,
                    'no_urut_di_rak' => $nomorUrutBaru,
                    'lokasi_arsip'   => "Lemari " . $rak->no_lemari . " / Rak " . $rak->kode_rak . " / No. " . $nomorUrutBaru,
                    'updated_at'     => now()
                ]);

                // Update counter rak
                $rak->terisi = $nomorUrutBaru;
                if ($rak->terisi >= $rak->kapasitas) { $rak->status = 'Penuh'; }
                $rak->save();
            }

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
        $cekLokal = Permohonan::where('no_permohonan', $nomor)->first();
        if ($cekLokal && in_array(strtoupper(trim($cekLokal->status_berkas)), ['SIAP_DITERIMA', 'DITERIMA', 'DITERIMA OLEH ARSIP'])) {
            return response()->json(['success' => false, 'message' => 'Nomor permohonan ini sudah dalam proses pengiriman atau sudah berada di Arsip.']);
        }

        $dataPaspor = DB::table('datapaspor.datapaspor')->where('nopermohonan', $nomor)->first();

        if ($dataPaspor) {
            // Ambil status alur terakhir
            $statusAlur = strtoupper(trim($dataPaspor->alurterakhir ?? 'BELUM DIKETAHUI'));

            // Validasi: Harus SELESAI
            if ($statusAlur !== 'SELESAI') {
                return response()->json([
                    'success' => false, 
                    // Pesan diubah agar dinamis menyebutkan status alur terakhirnya
                    'message' => 'Berkas belum bisa diproses karena alur belum SELESAI (Status saat ini: ' . $statusAlur . ').'
                ]);
            }

            return response()->json([
                'success' => true, 
                'data' => [
                    'nama' => $dataPaspor->nama, 
                    'tempat_lahir' => $dataPaspor->tempatlahir, 
                    'tanggal_lahir' => $dataPaspor->tanggallahir, 
                    'jenis_paspor' => $dataPaspor->jenispaspor
                ]
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Nomor permohonan tidak ditemukan pada Database Paspor.']);
    } catch (\Exception $e) { 
        return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500); 
    }
}

    public function store(Request $request) {
        $list = $request->nomor_permohonan_list;
        if (!$list) return response()->json(['success' => false, 'message' => 'Data kosong'], 400);
        $user = Auth::user(); 
        DB::beginTransaction();
        try {
            $noBatch = 'B-' . time();
            DB::table('pengiriman_batch')->insert(['no_pengirim' => $noBatch, 'tgl_pengirim' => now()->format('Y-m-d'), 'jumlah_berkas' => count($list), 'status' => 'Diajukan', 'asal_unit' => $user->role ?? 'KANIM', 'petugas_kirim' => $user->nama_lengkap ?? $user->name, 'created_at' => now(), 'updated_at' => now()]);
            foreach ($list as $item) {
                $asalData = DB::table('datapaspor.datapaspor')->where('nopermohonan', $item['no_permohonan'])->first();
                if ($asalData) {
                    $tglMohon = ($asalData->tglpermohonan_datetime && $asalData->tglpermohonan_datetime != '0000-00-00 00:00:00') ? Carbon::parse($asalData->tglpermohonan_datetime)->format('Y-m-d') : now()->format('Y-m-d');
                    Permohonan::updateOrCreate(['no_permohonan' => $item['no_permohonan']], ['no_pengirim' => $noBatch, 'nama' => $asalData->nama, 'tempat_lahir' => $asalData->tempatlahir, 'tanggal_lahir' => $asalData->tanggallahir, 'jenis_kelamin' => $asalData->jeniskelamin, 'no_telp' => $asalData->notelepon, 'jenis_permohonan' => $asalData->jenispermohonan, 'jenis_paspor' => $asalData->jenispaspor, 'tujuan_paspor' => $asalData->tujuanpaspor, 'no_paspor' => $asalData->nopaspor, 'tanggal_permohonan' => $tglMohon, 'status_berkas' => 'DIAJUKAN', 'alur_terakhir' => 'Loket Pengiriman', 'updated_at' => now()]);
                }
            }
            DB::commit(); return response()->json(['success' => true]);
        } catch (\Exception $e) { DB::rollBack(); return response()->json(['success' => false, 'message' => $e->getMessage()], 500); }
    }

    public function rakIndex() {
        $rak = RakLoker::orderBy('no_lemari', 'asc')->orderBy('kode_rak', 'asc')->get();
        return view('arsip.rak_loker', ['current_page' => 'rak-loker', 'rak' => $rak]);
    }

    public function rakStore(Request $request) {
        $request->validate(['no_lemari' => 'required', 'jumlah_rak' => 'required|integer|min:1', 'kapasitas' => 'required|integer']);
        try {
            for ($i = 0; $i < $request->jumlah_rak; $i++) {
                RakLoker::create(['no_lemari' => $request->no_lemari, 'kode_rak' => strtoupper($request->no_lemari . chr(97 + $i)), 'kapasitas' => $request->kapasitas, 'terisi' => 0, 'status' => 'Tersedia']);
            }
            return back()->with('success', 'Data Lemari berhasil dibuat.');
        } catch (\Exception $e) { return back()->with('error', 'Gagal: ' . $e->getMessage()); }
    }

    public function rakDestroy($id) {
        $rak = RakLoker::findOrFail($id);
        if ($rak->terisi > 0) return redirect()->back()->with('error', 'Rak sudah berisi berkas!');
        $rak->delete(); return redirect()->back()->with('success', 'Rak berhasil dihapus.');
    }

    public function getDetail($nomor) {
        $data = Permohonan::where('no_permohonan', $nomor)->first();
        return $data ? response()->json(['success' => true, 'data' => $data]) : response()->json(['success' => false], 404);
    }

    public function pemusnahanIndex() {
        $riwayat = PemusnahanArsip::orderBy('created_at', 'desc')->get();
        return view('arsip.pemusnahan', compact('riwayat'));
    }

    public function hitungDokumen(Request $request) {
        $jumlah = Permohonan::whereBetween('tanggal_permohonan', [$request->mulai, $request->selesai])->where('status_berkas', 'DITERIMA OLEH ARSIP')->count();
        return response()->json(['jumlah' => $jumlah]);
    }

    public function simpanPemusnahan(Request $request) {
        $request->validate(['no_berita_acara' => 'required|unique:pemusnahan_arsip', 'filter_mulai' => 'required', 'filter_selesai' => 'required']);
        $permohonans = Permohonan::whereBetween('tanggal_permohonan', [$request->filter_mulai, $request->filter_selesai])->where('status_berkas', 'DITERIMA OLEH ARSIP')->get();
        if($permohonans->isEmpty()) return back()->with('error', 'Data kosong.');
        $fileName = null;
        if ($request->hasFile('file_pdf')) {
            $fileName = 'BA_' . time() . '.' . $request->file_pdf->extension();  
            $request->file_pdf->move(public_path('uploads/pemusnahan'), $fileName);
        }
        PemusnahanArsip::create(['no_berita_acara' => $request->no_berita_acara, 'tgl_pemusnahan' => now(), 'filter_mulai' => $request->filter_mulai, 'filter_selesai' => $request->filter_selesai, 'jumlah_dokumen' => $permohonans->count(), 'file_pdf' => $fileName, 'status' => 'Diajukan', 'daftar_id_permohonan' => $permohonans->pluck('id')->toArray()]);
        return back()->with('success', 'Pengajuan berhasil disimpan.');
    }

    public function uploadPDF(Request $request, $id) {
        $request->validate(['file_pdf' => 'required|mimes:pdf|max:5000']);
        $ba = PemusnahanArsip::findOrFail($id);
        if ($request->hasFile('file_pdf')) {
            $fileName = 'BA_' . time() . '.' . $request->file_pdf->extension();  
            $request->file_pdf->move(public_path('uploads/pemusnahan'), $fileName);
            $ba->update(['file_pdf' => $fileName]);
            return back()->with('success', 'Berhasil upload PDF.');
        }
        return back()->with('error', 'Gagal.');
    }

    public function setujuiPemusnahan($id) {
        $ba = PemusnahanArsip::findOrFail($id);
        $ids = is_array($ba->daftar_id_permohonan) ? $ba->daftar_id_permohonan : json_decode($ba->daftar_id_permohonan, true);
        DB::beginTransaction();
        try {
            foreach ($ids as $permohonanId) {
                $p = Permohonan::find($permohonanId);
                if ($p) {
                    if ($p->rak_id) {
                        $rak = RakLoker::find($p->rak_id);
                        if ($rak && $rak->terisi > 0) {
                            $rak->decrement('terisi');
                            if($rak->terisi == 0) $rak->update(['status' => 'Tersedia']);
                        }
                    }
                    $p->update(['status_berkas' => 'DIMUSNAHKAN', 'rak_id' => null, 'no_urut_di_rak' => null, 'lokasi_arsip' => 'SUDAH DIMUSNAHKAN']);
                }
            }
            $ba->update(['status' => 'Disetujui']);
            DB::commit(); return back()->with('success', 'Pemusnahan Berhasil.');
        } catch (\Exception $e) { DB::rollBack(); return back()->with('error', $e->getMessage()); }
    }

    public function cetakPemusnahan($id) {
        $ba = PemusnahanArsip::findOrFail($id);
        $ids = is_array($ba->daftar_id_permohonan) ? $ba->daftar_id_permohonan : json_decode($ba->daftar_id_permohonan, true);
        $permohonan = Permohonan::whereIn('id', $ids)->get();
        return view('arsip.cetak_ba', compact('ba', 'permohonan'));
    }

    public function getDetailPemusnahan($id) {
        try {
            $ba = PemusnahanArsip::findOrFail($id);
            $ids = is_array($ba->daftar_id_permohonan) ? $ba->daftar_id_permohonan : json_decode($ba->daftar_id_permohonan, true);
            if (!$ids) return response()->json(['success' => true, 'ba' => $ba, 'data' => []]);
            $data = Permohonan::whereIn('id', $ids)->select('no_permohonan', 'nama', 'jenis_permohonan', 'tanggal_permohonan')->get();
            return response()->json(['success' => true, 'ba' => $ba, 'data' => $data]);
        } catch (\Exception $e) { return response()->json(['success' => false, 'message' => $e->getMessage()], 500); }
    }
}