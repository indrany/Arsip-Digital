<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan; 
use Illuminate\Support\Facades\DB; 
use Carbon\Carbon; 

class ArsipController extends Controller
{
    // --- DASHBOARD & PENGIRIMAN ---

    public function dashboard() {
        $data['current_page'] = 'dashboard'; 
        return view('auth.dashboard.index', $data); 
    }

    // Menampilkan Riwayat Pengiriman
public function pengirimanBerkas() {
    $data['current_page'] = 'pengiriman-berkas';
    
    // Mengambil data riwayat pengiriman dari database
    // Sesuaikan nama tabel 'pengiriman_batch' dengan struktur DB Anda
    $data['riwayat'] = DB::table('pengiriman_batch')
                        ->orderBy('created_at', 'desc')
                        ->get();

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
    return view('arsip.riwayat_pengiriman', $data);
}

// Fungsi untuk menampilkan form tambah pengiriman berkas
public function tambahPengiriman()
{
    $data['current_page'] = 'pengiriman-berkas';
    // Pastikan path view ini sesuai dengan lokasi file form tambah Anda
    return view('auth.dashboard.pengiriman_berkas', $data);
}

    /**
     * PERBAIKAN: Fungsi untuk mencari data permohonan saat diinput di halaman pengiriman
     * Digunakan agar tombol + Tambah dan Enter bisa memproses data
     */
    public function cariPermohonan(Request $request) {
        $nomor = $request->no_permohonan;
        
        // Cari data berdasarkan nomor permohonan
        $permohonan = Permohonan::where('no_permohonan', $nomor)->first();
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

        // Jika data belum ada di DB (data baru), kirim balik data minimal agar bisa masuk tabel
        return response()->json([
            'success' => true,
            'data' => [
                'no_permohonan' => $nomor,
                'nama' => $request->nama ?? 'Data Baru'
            ]
        ]);
    }
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

    public function store(Request $request) {
        $dataList = $request->input('nomor_permohonan_list');
        return response()->json(['hasNewData' => false]);
    }

    public function konfirmasiBulk(Request $request)
    {
        $nomorPermohonanList = $request->input('nomor_permohonan_list');
        
        if (empty($dataList)) {
            return response()->json(['success' => false, 'message' => 'Daftar kosong.'], 400);
        }
    
        try {
            DB::beginTransaction();
    
            // 1. Buat Header Riwayat (Batch)
            $noPengirim = rand(1000, 9999); // Generate nomor pengirim acak
            DB::table('pengiriman_batch')->insert([
                'no_pengirim' => $noPengirim,
                'tgl_pengirim' => now(),
                'jumlah_berkas' => count($dataList),
                'status' => 'Diajukan',
                'created_at' => now(),
                'updated_at' => now()
            ]);
    
            // 2. Update status masing-masing berkas
            foreach ($dataList as $item) {
                \App\Models\Permohonan::updateOrCreate(
                    ['no_permohonan' => $item['no_permohonan']], 
                    [
                        'nama' => $item['nama'],
                        'status_berkas' => 'SIAP_DITERIMA',
                        'tanggal_permohonan' => now(),
                        'updated_at' => now()
                    ]
                );
            }
    

            Permohonan::whereIn('no_permohonan', $nomorPermohonanList)
                      ->update([
                          'status_berkas' => 'DITERIMA',
                          'tanggal_diterima' => Carbon::now()
                      ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // --- PENERIMAAN BERKAS ---

    public function penerimaanBerkas() {
        // Ambil SEMUA yang statusnya SIAP_DITERIMA dan DITERIMA agar tetap muncul di tabel kiri
        $data['list_semua'] = Permohonan::whereIn('status_berkas', ['SIAP_DITERIMA', 'DITERIMA'])->get();
        
        // Tabel kanan tetap hanya yang baru saja di-scan (DITERIMA)
        $data['list_sudah_scan'] = Permohonan::where('status_berkas', 'DITERIMA')
                                             ->whereDate('updated_at', Carbon::today())
                                             ->get();
    
        return view('arsip.penerimaan_berkas', $data);
    }

    public function scanPermohonan(Request $request) {
        $nomor = $request->nomor_permohonan;
        $permohonan = Permohonan::where('no_permohonan', $nomor)->first();
    
        if ($permohonan) {
            // Jika sudah pernah dikonfirmasi sebelumnya
            if ($permohonan->status_berkas == 'DITERIMA OLEH ARSIP') {
                return response()->json([
                    'success' => false, 
                    'message' => 'Berkas ini sudah pernah dikonfirmasi dan masuk arsip.'
                ], 400);
            }
    
    // --- MODUL PENCARIAN BERKAS (PERBAIKAN LOKASI VIEW) ---
    
            // Update status jadi DITERIMA agar muncul di tabel kanan
            $permohonan->update([
                'status_berkas' => 'DITERIMA',
                'updated_at' => now()
            ]);
            
            return response()->json(['success' => true, 'data' => $permohonan]);
        }
        
        return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
    }

    public function konfirmasiBulk(Request $request) {
        try {
            // Update semua berkas yang baru saja di-scan (DITERIMA) hari ini
            // Status diubah menjadi 'DITERIMA OLEH ARSIP' sesuai permintaan Anda
            \App\Models\Permohonan::where('status_berkas', 'DITERIMA')
                ->whereDate('updated_at', \Carbon\Carbon::today())
                ->update([
                    'status_berkas' => 'DITERIMA OLEH ARSIP', // Perubahan status disini
                    'updated_at' => now()
                ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Sesi berhasil disimpan dengan status DITERIMA OLEH ARSIP.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal konfirmasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PERBAIKAN: Polling untuk cek scan baru
     * Durasi diperpanjang ke 10 detik agar sinkronisasi lebih stabil
     */
    public function checkNewScan() {
        $hasNew = Permohonan::where('status_berkas', 'DITERIMA')
                            ->where('updated_at', '>=', now()->subSeconds(10))
                            ->exists();
        return response()->json(['has_new' => $hasNew]);
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