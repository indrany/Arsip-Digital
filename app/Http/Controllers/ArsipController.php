<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan;
use App\Models\PinjamBerkas;
use App\Models\User;
use App\Models\RakLoker;
use App\Models\PemusnahanArsip;
use App\Models\Rak;             
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
public function tambahPengiriman(Request $request)
{
    $user = Auth::user();
    $query = DB::table('pengiriman_batch');
    if (!in_array(strtoupper($user->role), ['ADMIN', 'TIKIM'])) {
        $query->where('petugas_kirim', $user->name);
    }

    // Ambil limit, paksa jadi Integer (Angka)
    $perPage = (int) $request->input('per_page', 5);

    $riwayat = $query->orderBy('created_at', 'desc')
        ->paginate($perPage) 
        ->appends(['per_page' => $perPage]);

    return view('auth.Dashboard.pengiriman_berkas', [
        'current_page' => 'pengiriman-berkas',
        'riwayat' => $riwayat
    ]);
}

    // 3. RIWAYAT PENGIRIMAN
    public function pengirimanBerkas(Request $request) // <--- PASTIKAN ADA Request $request
{
    // 1. Tangkap angka dari dropdown. Kalau kosong, default kasih 5.
    $perPage = $request->get('per_page', 5); 

    $riwayat = DB::table('pengiriman_batch')
        ->orderByRaw("CASE WHEN status = 'Diajukan' THEN 0 ELSE 1 END")
        ->orderBy('created_at', 'desc')
        
        // 2. PAKAI variabel $perPage biar otomatis motong datanya
        ->paginate($perPage) 
        
        // 3. Tambahkan appends agar pilihan 10 ini tidak hilang pas pindah halaman
        ->appends(['per_page' => $perPage]);

    return view('arsip.riwayat_pengiriman', compact('riwayat'));
}

    // 4. PENERIMAAN BERKAS (ARSIP) - UPDATE LOGIKA RAK
    public function penerimaanBerkas(Request $request)
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
            ->paginate($request->get('per_page', 5))->appends(['per_page' => $request->get('per_page')]);

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

    public function listBerkas(Request $request, $no_pengirim)
{
    try {
        $batch = DB::table('pengiriman_batch')->where('no_pengirim', $no_pengirim)->first();
        
        // JANGAN PAKAI PAGINATE DISINI
        // Kita pakai ->get() supaya semua 11 data terkirim sekaligus
        $data = Permohonan::where('no_pengirim', $no_pengirim)->get();

        return response()->json([
            'success' => true, 
            'batch'   => $batch, 
            'data'    => $data, // Kirim array utuh
            'pagination' => [
                'total' => $data->count(), // Hitung total dari koleksi
                // Sisanya biarkan 0 atau hapus saja karena JS akan menghitung sendiri
            ]
        ]);
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
        
        // 1. Cari data permohonan berdasarkan nomor
        $permohonan = Permohonan::where('no_permohonan', $nomor)->first();

        // 2. Jika nomor tidak ditemukan di database
        if (!$permohonan) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal! Nomor permohonan ' . $nomor . ' tidak ditemukan dalam sistem.'
            ], 404);
        }

        // 3. Jika berkas statusnya sudah selesai (sudah masuk rak)
        if ($permohonan->status_berkas === 'DITERIMA OLEH ARSIP') {
             return response()->json([
                'success' => false, 
                'message' => 'Berkas ini sudah terverifikasi dan sudah masuk rak arsip.'
            ], 422);
        }

        // 4. Update status menjadi DITERIMA (Agar pindah ke tabel sebelah kanan di View)
        // Kita pakai status 'DITERIMA' sementara, nanti pas klik 'Selesaikan' baru jadi 'DITERIMA OLEH ARSIP'
        $permohonan->update([
            'status_berkas' => 'DITERIMA', 
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true, 
            'data' => [
                'no_permohonan' => $permohonan->no_permohonan, 
                'nama' => $permohonan->nama
            ]
        ]);
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

    public function pencarianBerkas(Request $request) {
        // 1. Ambil angka dropdown, paksa jadi Integer
        $perPage = (int) $request->input('per_page', 5);
    
        $results = DB::table('permohonan')
            ->leftJoin('datapaspor.datapaspor', function($join) {
                $join->on('permohonan.no_permohonan', '=', DB::raw('datapaspor.nopermohonan COLLATE utf8mb4_unicode_ci'));
            })
            ->select('permohonan.*', 'datapaspor.alurterakhir as alur_paspor_update')
            ->orderBy('permohonan.created_at', 'desc')
            
            // 2. Gunakan variabel dinamis
            ->paginate($perPage)
            ->appends(['per_page' => $perPage]);
    
        foreach ($results as $item) {
            if ($item->status_berkas === 'DIMUSNAHKAN') {
                $ba = PemusnahanArsip::where('daftar_id_permohonan', 'LIKE', '%"' . $item->id . '"%')
                        ->orWhere('daftar_id_permohonan', 'LIKE', '%' . $item->id . '%')
                        ->first();
                $item->nomor_ba_arsip = $ba ? $ba->no_berita_acara : '-';
            } else {
                $item->nomor_ba_arsip = '-';
            }
        }
        
        return view('auth.Dashboard.pencarian_berkas', [
            'current_page' => 'pencarian-berkas', 
            'results' => $results
        ]);
    }

    public function searchAction(Request $request) {
        $q = $request->nomor_permohonan;
        
        $results = DB::table('permohonan')
            ->leftJoin('datapaspor.datapaspor', function($join) {
                $join->on(
                    'permohonan.no_permohonan', 
                    '=', 
                    // Paksa datapaspor ikuti collation permohonan agar tidak error 1267
                    DB::raw('datapaspor.nopermohonan COLLATE utf8mb4_unicode_ci')
                );
            })
            ->where(function($query) use ($q) {
                // Gunakan nama tabel spesifik untuk kolom nama agar tidak ambiguous
                $query->where('permohonan.no_permohonan', 'LIKE', "%$q%")
                      ->orWhere('permohonan.nama', 'LIKE', "%$q%");
            })
            ->select(
                'permohonan.*', 
                'datapaspor.alurterakhir as alur_paspor_update'
            )
            ->paginate($request->get('per_page', 5))->appends(['per_page' => $request->get('per_page')]);

        // Biar keyword search tidak hilang pas pindah page
        $results->appends(['nomor_permohonan' => $q]);

        foreach ($results as $item) {
            $item->nomor_ba_arsip = '-'; 
            if ($item->status_berkas === 'DIMUSNAHKAN') {
                 $ba = PemusnahanArsip::where('daftar_id_permohonan', 'LIKE', '%"' . $item->id . '"%')->first();
                 $item->nomor_ba_arsip = $ba ? $ba->no_berita_acara : '-';
            }
        }

        return view('auth.Dashboard.pencarian_berkas', [
            'current_page' => 'pencarian-berkas',
            'results' => $results
        ]);
    }

    public function getPermohonanDetail($nomor)
{
    try {
        // 1. CEK DULU DI DATABASE LOKAL (Pintu Gerbang)
        $cekLokal = Permohonan::where('no_permohonan', $nomor)->first();

        if ($cekLokal) {
            // Jika statusnya sudah diajukan, diterima, atau sudah di arsip, TOLAK.
            $statusTerlarang = ['DIAJUKAN', 'DITERIMA', 'DITERIMA OLEH ARSIP', 'SIAP_DITERIMA'];
            
            if (in_array(strtoupper(trim($cekLokal->status_berkas)), $statusTerlarang)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Gagal! Nomor permohonan ini sudah pernah dikirim/terdaftar dengan status: ' . $cekLokal->status_berkas
                ]);
            }
        }

        // 2. JIKA TIDAK ADA DI LOKAL / STATUSNYA AMAN, BARU CARI KE DATABASE PASPOR
        $dataPaspor = DB::table('datapaspor.datapaspor')->where('nopermohonan', $nomor)->first();

        if ($dataPaspor) {
            $statusAlur = strtoupper(trim($dataPaspor->alurterakhir ?? ''));

            if ($statusAlur !== 'SELESAI') {
                return response()->json([
                    'success' => false, 
                    'message' => 'Berkas belum bisa diproses karena alur belum SELESAI (Status: ' . $statusAlur . ').'
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

        return response()->json(['success' => false, 'message' => 'Nomor permohonan tidak ditemukan.']);
    } catch (\Exception $e) { 
        return response()->json(['success' => false, 'message' => 'Kesalahan sistem: ' . $e->getMessage()], 500); 
    }
}

public function store(Request $request) {
    $list = $request->nomor_permohonan_list;
    if (!$list || count($list) == 0) {
        return response()->json(['success' => false, 'message' => 'Daftar permohonan kosong'], 400);
    }

    $user = Auth::user(); 
    $waktuSekarang = now();
    
    DB::beginTransaction();
    try {
        $noBatch = 'B-' . time();
        
        // LOGIKA PENENTUAN UNIT PENGIRIM (Anti-Null)
        // Cek input form dulu, kalau kosong cek role user, kalau masih kosong isi 'UMUM'
        $unitAsal = $request->asal_unit ?? ($user->role ?? 'UMUM');

        // 1. SIMPAN KE TABEL pengiriman_batch
        // PASTIKAN: Nama kolom di phpMyAdmin kamu adalah 'tgl_pengirim' (bukan tgl_pengiriman)
        DB::table('pengiriman_batch')->insert([
            'no_pengirim'   => $noBatch, 
            'tgl_pengirim'  => $waktuSekarang->format('Y-m-d'), // Simpan sebagai tanggal
            'jumlah_berkas' => count($list), 
            'status'        => 'Diajukan', 
            'asal_unit'     => strtoupper($unitAsal), 
            'petugas_kirim' => $user->nama_lengkap ?? ($user->name ?? 'Petugas'), 
            'created_at'    => $waktuSekarang, 
            'updated_at'    => $waktuSekarang
        ]);

        // 2. SIMPAN/UPDATE KE TABEL permohonan
        foreach ($list as $item) {
            // Deteksi apakah $item berupa array atau string (dari barcode scanner)
            $noPermohonan = is_array($item) ? ($item['no_permohonan'] ?? null) : $item;

            if (!$noPermohonan) continue;

            $asalData = DB::table('datapaspor.datapaspor')->where('nopermohonan', $noPermohonan)->first();
            
            if ($asalData) {
                // Handling format tanggal agar tidak 0000-00-00
                $tglMohon = ($asalData->tglpermohonan_datetime && $asalData->tglpermohonan_datetime != '0000-00-00 00:00:00') 
                            ? Carbon::parse($asalData->tglpermohonan_datetime)->format('Y-m-d') 
                            : $waktuSekarang->format('Y-m-d');

                Permohonan::updateOrCreate(
                    ['no_permohonan' => $noPermohonan], 
                    [
                        'no_pengirim'        => $noBatch, 
                        'tgl_pengirim'       => $waktuSekarang->format('Y-m-d'), 
                        'asal_unit'          => strtoupper($unitAsal),
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
                        'status_berkas'      => 'DIAJUKAN', 
                        'alur_terakhir'      => 'SELESAI (' . strtoupper($unitAsal) . ')',
                        'updated_at'         => $waktuSekarang
                    ]
                );
            }
        }
        
        DB::commit(); 
        return response()->json(['success' => true]);
    } catch (\Exception $e) { 
        DB::rollBack(); 
        return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500); 
    }
}

public function rakIndex(Request $request) {
    // Ambil angka dropdown
    $perPage = (int) $request->input('per_page', 5);

    $rak = RakLoker::orderBy('no_lemari', 'asc')
        ->orderBy('kode_rak', 'asc')
        ->paginate($perPage) 
        ->appends(['per_page' => $perPage]);
                   
    return view('arsip.rak_loker', [
        'current_page' => 'rak-loker', 
        'rak' => $rak
    ]);
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

    public function pemusnahanIndex(Request $request) // Tambahkan parameter Request $request
{
    $query = PemusnahanArsip::query();
    if ($request->filled('start_date') && $request->filled('end_date')) {
        $query->whereBetween('created_at', [
            $request->start_date . ' 00:00:00', 
            $request->end_date . ' 23:59:59'
        ]);
    }
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    $riwayat = $query->orderBy('created_at', 'desc')
    ->paginate($request->get('per_page', 5))->appends(['per_page' => $request->get('per_page')]); 
        
    return view('arsip.pemusnahan', compact('riwayat'));
}
    public function hitungDokumen(Request $request) {
        $jumlah = Permohonan::whereBetween('tanggal_permohonan', [$request->mulai, $request->selesai])->where('status_berkas', 'DITERIMA OLEH ARSIP')->count();
        return response()->json(['jumlah' => $jumlah]);
    }

    public function simpanPemusnahan(Request $request) {
        $request->validate([
            'no_berita_acara' => 'required|unique:pemusnahan_arsip',
        ]);
    
        // 1. Jalur Kalkulasi Otomatis (Filter Tanggal) - Tetap cari ID buat sistem hapus berkas
        $idsOtomatis = [];
        if ($request->filled('filter_mulai') && $request->filled('filter_selesai')) {
            $idsOtomatis = Permohonan::whereBetween('tanggal_permohonan', [$request->filter_mulai, $request->filter_selesai])
                ->where('status_berkas', 'DITERIMA OLEH ARSIP')
                ->pluck('id')->toArray();
        }
    
        // 2. Jalur Inputan Manual (CUMA AMBIL ANGKA BEBAS)
        // Kita ambil inputan user, kalau bukan angka kita jadikan 0
        $jumlahManual = $request->filled('nomor_manual_input') ? (int) $request->nomor_manual_input : 0;
    
        // Pastikan ada data (entah dari filter atau dari angka manual)
        if (count($idsOtomatis) <= 0 && $jumlahManual <= 0) {
            return back()->with('error', 'Gagal: Isi filter tanggal atau masukkan jumlah manual.');
        }
    
        // Proses upload PDF
        $fileName = null;
        if ($request->hasFile('file_pdf')) {
            $fileName = 'BA_' . time() . '.' . $request->file_pdf->extension();  
            $request->file_pdf->move(public_path('uploads/pemusnahan'), $fileName);
        }
    
        // 3. SIMPAN KE DATABASE
        PemusnahanArsip::create([
            'no_berita_acara'   => $request->no_berita_acara,
            'tgl_pemusnahan'    => now(),
            'filter_mulai'      => $request->filter_mulai,
            'filter_selesai'    => $request->filter_selesai,
            
            'jumlah_dokumen'    => count($idsOtomatis), // Masuk ke kolom Kalkulasi
            'jumlah_manual'     => $jumlahManual,       // Masuk ke kolom Jumlah (Angka bebas)
            
            'file_pdf'          => $fileName,
            'status'            => 'Diajukan',
            'daftar_id_permohonan' => $idsOtomatis // Cuma ID otomatis yang disimpan buat dihapus sistem
        ]);
    
        return back()->with('success', 'Berhasil disimpan! Jumlah manual tercatat: ' . $jumlahManual);
    }
    public function uploadPDF(Request $request, $id) {
        // Validasi: Kita izinkan sampai 20MB karena file dari pusat biasanya lengkap/besar
        $request->validate([
            'file_pdf' => 'required|mimes:pdf|max:20480' 
        ]);
    
        $ba = PemusnahanArsip::findOrFail($id);
    
        if ($request->hasFile('file_pdf')) {
            $file = $request->file('file_pdf');
            $path = public_path('uploads/pemusnahan');
    
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
    
            // Nama file dibuat lebih umum: DOC_[waktu]_[id].pdf
            $fileName = 'DOC_' . time() . '_' . $id . '.pdf'; 
    
            if ($file->move($path, $fileName)) {
                $ba->update(['file_pdf' => $fileName]);
                return back()->with('success', 'Dokumen berhasil diunggah dan dilampirkan.');
            }
        }
        
        return back()->with('error', 'Gagal mengunggah dokumen.');
    }
    public function setujuiPemusnahan($id)
{
    $ba = PemusnahanArsip::findOrFail($id);
    
    // Ambil ID permohonan yang sudah didaftarkan saat simpan tadi
    $ids = is_array($ba->daftar_id_permohonan) ? $ba->daftar_id_permohonan : json_decode($ba->daftar_id_permohonan, true);

    if (empty($ids)) {
        return redirect()->back()->with('error', 'Gagal: Data rincian berkas tidak ditemukan.');
    }

    DB::beginTransaction();
    try {
        // Ambil data berkas asli berdasarkan ID yang tersimpan
        $berkas = Permohonan::whereIn('id', $ids)->get();

        foreach ($berkas as $item) {
            // Update counter di Master Rak Loker jika ada
            if ($item->rak_id) {
                $rak = RakLoker::find($item->rak_id); 
                if ($rak) {
                    $rak->decrement('terisi'); 
                    if ($rak->fresh()->terisi < $rak->kapasitas) {
                        $rak->update(['status' => 'Tersedia']);
                    }
                }
            }
        
            // Update status berkas menjadi DIMUSNAHKAN
            $item->update([
                'status_berkas' => 'DIMUSNAHKAN',
                'rak_id'        => null,
                'no_urut_di_rak' => null,
                'lokasi_arsip'  => 'SUDAH DIMUSNAHKAN'
            ]);
        }

        // Update status Berita Acara
        $ba->update(['status' => 'Disetujui']);

        DB::commit();
        return redirect()->back()->with('success', 'Berhasil: ' . $berkas->count() . ' berkas telah dimusnahkan secara sistem.');

    } catch (\Exception $e) {
        DB::rollback();
        return redirect()->back()->with('error', 'Kesalahan: ' . $e->getMessage());
    }
}

    public function reject($id)
{
    try {
        // Gunakan Eloquent Model agar lebih aman
        $ba = \App\Models\PemusnahanArsip::findOrFail($id);
        $ba->status = 'Ditolak'; // Sesuai dengan ENUM di phpMyAdmin
        $ba->save();

        return redirect()->back()->with('success', 'Berhasil! Status di database sudah jadi Ditolak.');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
    }
}

    public function getDetailPemusnahan($id) {
        try {
            $ba = PemusnahanArsip::findOrFail($id);
            $statusAsli = $ba->status ?: 'Diajukan'; 
            $ba->status = strtoupper($statusAsli); 
            
            $ids = is_array($ba->daftar_id_permohonan) ? $ba->daftar_id_permohonan : json_decode($ba->daftar_id_permohonan, true);
            $data = Permohonan::whereIn('id', $ids)->get();
    
            return response()->json(['success' => true, 'ba' => $ba, 'data' => $data]);
        } catch (\Exception $e) { 
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500); 
        }
    }
    // Tambahkan di bagian bawah sebelum penutup class
    public function upload(Request $request, $id)
{
    $request->validate([
        'file_pdf' => 'required|mimes:pdf|max:2048',
    ]);

    if ($request->hasFile('file_pdf')) {
        $file = $request->file('file_pdf');
        $namaFile = 'BA_' . time() . '_' . $id . '.' . $file->getClientOriginalExtension();

        // Tentukan folder tujuan di public/uploads (Ganti agar tidak bentrok dengan symlink storage)
        $tujuan = public_path('uploads/pemusnahan');

        // Buat folder jika belum ada secara otomatis
        if (!file_exists($tujuan)) {
            mkdir($tujuan, 0755, true);
        }

        // Pindahkan file
        $file->move($tujuan, $namaFile);

        $pemusnahan = \App\Models\PemusnahanArsip::findOrFail($id);
        $pemusnahan->file_pdf = $namaFile;
        $pemusnahan->save();

        return response()->json(['success' => true]);
    }
}
    
}