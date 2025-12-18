<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengirimanBerkas; // Model untuk Header Transaksi (Riwayat)
use App\Models\Permohonan; // Model untuk Data Berkas/Permohonan Individu
use Illuminate\Support\Facades\DB; 

class PengirimanBerkasController extends Controller
{
    // 1. Tampilkan Halaman Riwayat (Index)
    public function index()
    {
        // Ambil data riwayat pengiriman untuk ditampilkan di tabel
        $riwayat_berkas = PengirimanBerkas::orderBy('created_at', 'desc')->get();
        
        // Pastikan Anda meneruskan data ke view yang benar
        return view('auth.Dashboard.pengiriman_berkas', compact('riwayat_berkas'));
    }

    // 2. Tampilkan Halaman Formulir (Create)
    public function create()
    {
        return view('auth.Dashboard.pengiriman_berkas_create');
    }

    // 3. FUNGSI AJAX: Mencari Detail Permohonan (Dipanggil Tombol 'Tambah')
    public function cariPermohonan(Request $request)
    {
        $request->validate(['nomor_permohonan' => 'required|string']);
        $nomor = $request->nomor_permohonan;
        
        // --- Ganti dengan LOGIKA PENCARIAN Anda yang sesungguhnya ---
        // Cari Permohonan/Berkas berdasarkan nomor uniknya
        $permohonan = Permohonan::where('no_permohonan', $nomor)->first();

        if ($permohonan) {
            // Asumsi model Permohonan memiliki kolom yang relevan
            return response()->json([
                'success' => true,
                'data' => [
                    'no_permohonan' => $permohonan->no_permohonan,
                    'tanggal_permohonan' => $permohonan->tanggal_permohonan->format('d-m-Y'), 
                    'nama' => $permohonan->nama_pemohon,
                    'tempat_lahir' => $permohonan->tempat_lahir,
                    'tanggal_lahir' => $permohonan->tanggal_lahir->format('d-m-Y') 
                ]
            ]);
        }
        
        // Jika data tidak ditemukan
        return response()->json([
            'success' => false,
            'message' => 'Nomor Permohonan ' . $nomor . ' tidak ditemukan.'
        ], 404);
    }

    // 4. FUNGSI UTAMA: Menyimpan Transaksi Pengiriman (Dipanggil Tombol 'Simpan')
    public function store(Request $request)
    {
        $request->validate([
            'berkas' => 'required|array|min:1', 
            'berkas.*.no_permohonan' => 'required|string', 
        ]);
        
        // Mulai transaksi database
        DB::beginTransaction();
        
        try {
            $berkas_list = $request->berkas;
            $jumlah_berkas = count($berkas_list);

            // A. Buat Header Transaksi Pengiriman Berkas
            $pengiriman = new PengirimanBerkas();
            $pengiriman->no_pengirim = 'PRM-' . time(); // Generate No. Pengirim unik
            $pengiriman->tanggal_pengirim = now();
            $pengiriman->jumlah_berkas = $jumlah_berkas; // Angka yang akan muncul di tabel Riwayat
            // $pengiriman->user_id = auth()->id(); // Jika perlu mencatat pengirim
            $pengiriman->save();

            // B. Simpan Detail Transaksi & Update Status Berkas Individu
            foreach ($berkas_list as $item) {
                // Asumsi ada tabel Pivot atau Detail Transaksi untuk mencatat permohonan mana saja yang dikirim
                // Contoh: $pengiriman->details()->create(['no_permohonan' => $item['no_permohonan']]); 
                
                // C. (PENTING) Update status permohonan agar tidak bisa dikirim dua kali
                Permohonan::where('no_permohonan', $item['no_permohonan'])
                           ->update(['status_kirim' => 'Diajukan', 'pengiriman_id' => $pengiriman->id]);
            }
            
            DB::commit();

            return response()->json([
                'success' => true,
                'redirect_url' => route('pengiriman-berkas.index'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error $e
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pengiriman. Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}