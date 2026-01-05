<?php

namespace App\Http\Controllers;

use App\Models\PinjamBerkas;
use App\Models\Permohonan;
use Illuminate\Http\Request;

class PinjamBerkasController extends Controller
{
    /**
     * TAMPIL DATA PINJAM BERKAS
     * Mengambil data HANYA dari tabel pinjam_berkas.
     */
    public function index(Request $request)
    {
        $query = PinjamBerkas::with('permohonan');

        // Filter No Permohonan jika ada input cari
        if ($request->filled('no_permohonan')) {
            $query->whereHas('permohonan', function ($q) use ($request) {
                $q->where('no_permohonan', 'like', '%' . $request->no_permohonan . '%');
            });
        }

        $dataPinjam = $query->orderBy('created_at', 'desc')->get();

        return view('scripts.pinjam_berkas', compact('dataPinjam'));
    }

    /**
     * SIMPAN PEMINJAMAN BARU (MANUAL)
     * Fungsi ini digunakan untuk menginputkan permohonan ke daftar pinjam.
     */
    public function store(Request $request)
{
    // 1. Validasi Input
    $request->validate([
        'no_permohonan' => 'required|exists:permohonan,no_permohonan', 
        'nama_peminjam' => 'required'
    ], [
        'no_permohonan.exists' => 'Nomor permohonan tidak ditemukan.',
        'nama_peminjam.required' => 'Divisi peminjam harus dipilih.'
    ]);

    // 2. Cari Data Permohonan
    $permohonan = Permohonan::where('no_permohonan', $request->no_permohonan)->first();

    // 3. Validasi Duplikasi (Cek apakah sudah dipinjam)
    $isBorrowed = PinjamBerkas::where('permohonan_id', $permohonan->id)
        ->whereIn('status', ['Pengajuan', 'Disetujui'])
        ->exists();

    if ($isBorrowed) {
        return back()->with('error', 'Gagal! Berkas ini masih dalam status dipinjam.');
    }

    // 4. SIMPAN DATA (Perbaikan Error tgl_pinjam)
    PinjamBerkas::create([
        'permohonan_id' => $permohonan->id,
        'nama_peminjam' => $request->nama_peminjam,
        'tgl_pinjam'    => now(), // SEGERA ISI TANGGAL (Agar tidak error NOT NULL)
        'status'        => 'Pengajuan'
    ]);

    return redirect()->route('pinjam-berkas.index')
        ->with('success', 'Peminjaman berhasil didaftarkan secara manual.');
}

    /**
     * APPROVE PEMINJAMAN
     * Mencatat tanggal pinjam secara otomatis saat disetujui.
     */
    public function approve($id)
    {
        $pinjam = PinjamBerkas::findOrFail($id);
        $pinjam->update([
            'status'     => 'Disetujui',
            'tgl_pinjam' => now() 
        ]);

        return back()->with('success', 'Peminjaman berkas telah disetujui.');
    }

    public function reject($id)
    {
        PinjamBerkas::findOrFail($id)->update(['status' => 'Ditolak']);
        return back()->with('success', 'Peminjaman berkas ditolak.');
    }

    public function updateDivisi(Request $request, $id)
    {
        PinjamBerkas::findOrFail($id)->update(['nama_peminjam' => $request->divisi]);
        return response()->json(['success' => true]);
    }

    public function complete($id)
    {
        PinjamBerkas::findOrFail($id)->update([
            'status'      => 'Selesai',
            'tgl_kembali' => now()
        ]);
        return back()->with('success', 'Berkas telah dikembalikan.');
    }
    public function cariPermohonan($no)
    {
        $permohonan = Permohonan::where('no_permohonan', $no)->first();

        if ($permohonan) {
            return response()->json([
                'success' => true,
                'data' => $permohonan
            ]);
        }

        return response()->json(['success' => false]);
    }
}
