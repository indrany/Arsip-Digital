<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan;
use App\Models\PinjamBerkas;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\PemusnahanArsip;    
use App\Models\Rak;           
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
            // Optimasi: Ambil semua data tahun tersebut dalam satu kali query (Eager Loading)
            $dataPemohon = \App\Models\Permohonan::whereYear('tanggal_permohonan', $year)
                ->selectRaw('MONTH(tanggal_permohonan) as month, COUNT(*) as count')
                ->groupBy('month')
                ->pluck('count', 'month')->toArray();
            $dataDipinjam = \App\Models\PinjamBerkas::whereYear('tgl_pinjam', $year)
                ->where('status', '!=', 'Ditolak') // Tambahkan filter ini
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
    
        $totalPemohon = \App\Models\Permohonan::count();
        $totalDipinjam = \App\Models\PinjamBerkas::where('status', '!=', 'Selesai')->count();
        $rakKritis = \App\Models\RakLoker::whereRaw('terisi / kapasitas >= 0.8')->get();
        $rakPenuhCount = \App\Models\RakLoker::where('status', 'Penuh')->count();
    
        return view('auth.Dashboard.index', compact('chartData', 'totalPemohon', 'totalDipinjam', 'rakKritis', 'rakPenuhCount'));
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

    // 4. PENERIMAAN BERKAS (ARSIP)
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

        return view('arsip.penerimaan_berkas', [
            'current_page'    => 'penerimaan-berkas',
            'antrean_batches' => $antrean_batches
        ]);
    }

    // 5. AJAX LIST BERKAS
    public function listBerkas($no_pengirim)
    {
        try {
            $batch = DB::table('pengiriman_batch')->where('no_pengirim', $no_pengirim)->first();
            $data = Permohonan::where('no_pengirim', $no_pengirim)->get();
            
            return response()->json([
                'success' => true, 
                'batch' => $batch, 
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 6. CETAK PENGANTAR
    public function cetakPengantar($no_pengirim)
    {
        $batch = DB::table('pengiriman_batch')->where('no_pengirim', $no_pengirim)->first();
        if (!$batch) abort(404);

        $items = \App\Models\Permohonan::where('no_pengirim', $no_pengirim)->get();

        return view('arsip.cetak_pengantar', compact('batch', 'items'));
    }

    // 7. SCAN PERMOHONAN
    public function scanPermohonan(Request $request)
    {
        $nomor = trim($request->nomor_permohonan);
        
        $permohonan = Permohonan::where('no_permohonan', $nomor)
                                ->whereIn('status_berkas', ['SIAP_DITERIMA', 'DITERIMA'])
                                ->first();

        if (!$permohonan) {
            return response()->json([
                'success' => false, 
                'message' => 'Berkas tidak ditemukan atau alur belum diselesaikan oleh unit pengirim.'
            ], 404);
        }

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
        $berkasList = Permohonan::where('no_pengirim', $no_pengirim)
                                ->where('status_berkas', 'DITERIMA')
                                ->get();

        foreach ($berkasList as $berkas) {
            // Logika pencarian rak kamu tetap sama
            $rak = \App\Models\RakLoker::where('status', 'Tersedia')
                    ->whereColumn('terisi', '<', 'kapasitas')
                    ->orderBy('id', 'asc')
                    ->first();

            if (!$rak) {
                return response()->json(['success' => false, 'message' => 'Semua Rak Penuh! Tambah rak di master data.'], 400);
            }

            $nomorUrutBaru = $rak->terisi + 1;

            // UPDATE BERKAS: Kita tambahkan 'rak_id' agar sinkron dengan pemusnahan
            $berkas->update([
                'status_berkas' => 'DITERIMA OLEH ARSIP',
                'rak_id' => $rak->id, // <--- TAMBAHKAN INI (Sangat Penting)
                'no_urut_di_rak' => $nomorUrutBaru,
                'lokasi_arsip' => "Lemari " . $rak->no_lemari . " / Rak " . $rak->kode_rak . " / No. " . $nomorUrutBaru,
                'updated_at' => now()
            ]);

            // Update counter di rak (Logika kamu tetap sama)
            $rak->terisi = $nomorUrutBaru;
            if ($rak->terisi >= $rak->kapasitas) {
                $rak->status = 'Penuh';
            }
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

    // 9. PENCARIAN & DETAIL
    public function pencarianBerkas() {
        $results = Permohonan::orderBy('created_at', 'desc')->get();

        // Tempelkan nomor BA secara manual karena daftar_id_permohonan bertipe array/JSON
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
        $start = $request->start_date;
        $end = $request->end_date;

        $query = Permohonan::query();

        if ($q) {
            $query->where(function($sub) use ($q) {
                $sub->where('no_permohonan', 'LIKE', "%$q%")
                    ->orWhere('nama', 'LIKE', "%$q%");
            });
        }

        if ($start && $end) {
            $query->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
        }

        $results = $query->orderBy('created_at', 'desc')->get();

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
    // FIX: getPermohonanDetail
    public function getPermohonanDetail($nomor)
{
    try {
        // 1. Cek di tabel lokal (Mencegah input nomor yang sudah dikirim ke arsip)
        $cekLokal = \App\Models\Permohonan::where('no_permohonan', $nomor)->first();
        $statusDilarang = ['SIAP_DITERIMA', 'DITERIMA', 'DITERIMA OLEH ARSIP'];

        if ($cekLokal && in_array(strtoupper(trim($cekLokal->status_berkas)), $statusDilarang)) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor permohonan ini sudah dalam proses pengiriman atau sudah berada di Arsip.'
            ]);
        }

        // 2. Ambil data dari database paspor eksternal
        $dataPaspor = DB::table('datapaspor.datapaspor')->where('nopermohonan', $nomor)->first();

        if ($dataPaspor) {
            // FIX: Menggunakan nama kolom yang benar sesuai di phpMyAdmin kamu yaitu 'alurterakhir'
            $rawStatus = $dataPaspor->alurterakhir ?? null;
            
            $currentAlur = $rawStatus ? strtoupper(trim($rawStatus)) : 'TIDAK DIKETAHUI'; 

            // LOGIKA: Bandingkan. Jika isinya 'WAWANCARA' atau selain 'SELESAI', maka TOLAK.
            if ($currentAlur !== 'SELESAI') {
                return response()->json([
                    'success' => false,
                    'message' => 'Berkas ditolak. Status database: "' . $currentAlur . '". (Hanya status SELESAI yang diperbolehkan).'
                ]);
            }

            // Jika statusnya SELESAI, lanjut isi form otomatis
            return response()->json([
                'success' => true,
                'data' => [
                    'nama' => $dataPaspor->nama,
                    'tempat_lahir' => $dataPaspor->tempatlahir,
                    'tanggal_lahir' => $dataPaspor->tanggallahir,
                    'jenis_paspor' => $dataPaspor->jenispaspor,
                ]
            ]);
        }

        return response()->json([
            'success' => false, 
            'message' => 'Nomor permohonan tidak ditemukan di database paspor.'
        ]);

    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Sistem Error: ' . $e->getMessage()], 500);
    }
}

    // 10. STORE DATA
    public function store(Request $request) {
        $list = $request->nomor_permohonan_list;
        if (!$list) return response()->json(['success' => false, 'message' => 'Daftar berkas kosong'], 400);
    
        $nomorList = collect($list)->pluck('no_permohonan')->toArray();
        $cekDuplikat = Permohonan::whereIn('no_permohonan', $nomorList)
                        ->where('status_berkas', '!=', 'DIAMBIL')
                        ->exists();
        
        if ($cekDuplikat) {
            return response()->json(['success' => false, 'message' => 'Salah satu nomor permohonan sudah pernah diinputkan sebelumnya.'], 422);
        }
    
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
                'petugas_kirim' => $user->nama_lengkap ?? $user->name,
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

    // 11. MODUL MASTER RAK LOKER
    public function rakIndex()
    {
        $rak = \App\Models\RakLoker::orderBy('no_lemari', 'asc')->orderBy('kode_rak', 'asc')->get();
        return view('arsip.rak_loker', [
            'current_page' => 'rak-loker',
            'rak' => $rak
        ]);
    }

    public function rakStore(Request $request)
    {
        $request->validate([
            'no_lemari'   => 'required',
            'jumlah_rak'  => 'required|integer|min:1|max:20',
            'kapasitas'   => 'required|integer|min:1'
        ]);

        try {
            $jumlah = $request->jumlah_rak;
            for ($i = 0; $i < $jumlah; $i++) {
                $huruf = chr(97 + $i);
                \App\Models\RakLoker::create([
                    'no_lemari' => $request->no_lemari,
                    'kode_rak'  => strtoupper($request->no_lemari . $huruf),
                    'kapasitas' => $request->kapasitas,
                    'terisi'    => 0,
                    'status'    => 'Tersedia'
                ]);
            }
            return back()->with('success', 'Data Lemari berhasil dibuat.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function rakDestroy($id)
    {
        $rak = \App\Models\RakLoker::findOrFail($id);
        if ($rak->terisi > 0) {
            return redirect()->back()->with('error', 'Rak tidak bisa dihapus karena sudah berisi berkas!');
        }
        $rak->delete();
        return redirect()->back()->with('success', 'Rak berhasil dihapus.');
    }

    // Fungsi Detail Permohonan Aplikasi Lokal
    public function getDetail($nomor)
    {
        try {
            $data = Permohonan::where('no_permohonan', $nomor)->first();
            if ($data) {
                return response()->json(['success' => true, 'data' => $data]);
            }
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    // Halaman Utama Pemusnahan
    public function pemusnahanIndex() {
        $riwayat = \App\Models\PemusnahanArsip::orderBy('created_at', 'desc')->get();
        return view('arsip.pemusnahan', compact('riwayat'));
    }

    // Ajax untuk menghitung jumlah dokumen sebelum di-submit
    public function hitungDokumen(Request $request) {
        $jumlah = \App\Models\Permohonan::whereBetween('tanggal_permohonan', [$request->mulai, $request->selesai])
                    ->where('status_berkas', 'DITERIMA OLEH ARSIP')
                    ->count();
        return response()->json(['jumlah' => $jumlah]);
    }

    // Simpan Pengajuan (Role: TIKIM)
    public function simpanPemusnahan(Request $request) {
        $request->validate([
            'no_berita_acara' => 'required|unique:pemusnahan_arsip',
            'file_pdf' => 'nullable|mimes:pdf|max:5000', // Diubah jadi nullable
            'filter_mulai' => 'required|date',
            'filter_selesai' => 'required|date',
        ]);

        // Ambil semua permohonan sesuai filter tanggal
        $permohonans = \App\Models\Permohonan::whereBetween('tanggal_permohonan', [$request->filter_mulai, $request->filter_selesai])
                        ->where('status_berkas', 'DITERIMA OLEH ARSIP')
                        ->get();

        if($permohonans->count() == 0) {
            return back()->with('error', 'Tidak ada dokumen ditemukan pada periode tersebut.');
        }

        // Upload PDF hanya jika ada file yang dipilih
        $fileName = null;
        if ($request->hasFile('file_pdf')) {
            $fileName = 'BA_' . time() . '.' . $request->file_pdf->extension();  
            $request->file_pdf->move(public_path('uploads/pemusnahan'), $fileName);
        }

        \App\Models\PemusnahanArsip::create([
            'no_berita_acara' => $request->no_berita_acara,
            'tgl_pemusnahan' => now(),
            'filter_mulai' => $request->filter_mulai,
            'filter_selesai' => $request->filter_selesai,
            'jumlah_dokumen' => $permohonans->count(),
            'file_pdf' => $fileName,
            'status' => 'Diajukan',
            'daftar_id_permohonan' => $permohonans->pluck('id')->toArray()
        ]);

        return back()->with('success', 'Data Berita Acara berhasil disimpan! Silakan cetak lampiran dan upload PDF jika surat sudah ditandatangani.');
    }

    // FUNGSI BARU: Untuk upload PDF susulan
    public function uploadPDF(Request $request, $id) {
        $request->validate([
            'file_pdf' => 'required|mimes:pdf|max:5000',
        ]);

        $ba = \App\Models\PemusnahanArsip::findOrFail($id);

        if ($request->hasFile('file_pdf')) {
            // Hapus file lama jika ada (opsional)
            if ($ba->file_pdf && file_exists(public_path('uploads/pemusnahan/' . $ba->file_pdf))) {
                unlink(public_path('uploads/pemusnahan/' . $ba->file_pdf));
            }

            $fileName = 'BA_' . time() . '.' . $request->file_pdf->extension();  
            $request->file_pdf->move(public_path('uploads/pemusnahan'), $fileName);
            
            $ba->update(['file_pdf' => $fileName]);
            return back()->with('success', 'File PDF Berita Acara berhasil diunggah!');
        }

        return back()->with('error', 'Gagal mengunggah file.');
    }
    public function setujuiPemusnahan($id)
{
    // 1. Ambil data Berita Acara
    $ba = \App\Models\PemusnahanArsip::findOrFail($id);
    
    // 2. Gunakan Transaction agar data konsisten (tidak ada update setengah-setengah)
    DB::beginTransaction();

    try {
        // Gabungkan query pencarian menggunakan whereIn untuk status berkas
        $berkas = \App\Models\Permohonan::whereBetween('tanggal_permohonan', [
            \Carbon\Carbon::parse($ba->filter_mulai)->startOfDay(), 
            \Carbon\Carbon::parse($ba->filter_selesai)->endOfDay()
        ])
        ->whereIn('status_berkas', ['DITERIMA OLEH ARSIP', 'DITERIMA']) 
        ->get();

        if ($berkas->isEmpty()) {
            return redirect()->back()->with('error', 'Gagal: Tidak ada berkas yang ditemukan untuk periode ini.');
        }

        foreach ($berkas as $item) {
            // 3. Update counter di Master Rak Loker
            if ($item->rak_id) {
                $rak = \App\Models\RakLoker::find($item->rak_id); 
                
                if ($rak) {
                    $rak->decrement('terisi'); 
                    
                    // Gunakan data fresh untuk validasi status rak
                    $rakBaru = $rak->fresh(); 
                    if ($rakBaru->terisi < $rakBaru->kapasitas) {
                        $rakBaru->update(['status' => 'Tersedia']);
                    }
                }
            }
        
            // 4. Update status berkas menjadi DIMUSNAHKAN
            // Data lokasi_arsip, rak_id, dan no_urut akan menjadi NULL
            $item->update([
                'status_berkas' => 'DIMUSNAHKAN',
                'rak_id'        => null, // Menghilangkan relasi ke rak
                'no_urut_di_rak' => null, // Mengosongkan nomor urut
                'lokasi_arsip'  => 'SUDAH DIMUSNAHKAN'
            ]);
        }

        // 5. Update status Berita Acara menjadi disetujui
        $ba->update(['status' => 'Disetujui']);

        DB::commit();
        return redirect()->back()->with('success', 'Berhasil: ' . $berkas->count() . ' berkas telah dimusnahkan dan rak telah dikosongkan.');

    } catch (\Exception $e) {
        DB::rollback();
        return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}
    public function cetakPemusnahan($id) {
        $ba = \App\Models\PemusnahanArsip::findOrFail($id);
        $ids = is_array($ba->daftar_id_permohonan) ? $ba->daftar_id_permohonan : json_decode($ba->daftar_id_permohonan, true);
        $permohonan = \App\Models\Permohonan::whereIn('id', $ids)->get();
    
        return view('arsip.cetak_ba', compact('ba', 'permohonan'));
    }
}