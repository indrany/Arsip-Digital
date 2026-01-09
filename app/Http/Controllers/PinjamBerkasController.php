<?php

namespace App\Http\Controllers;

use App\Models\PinjamBerkas;
use App\Models\Permohonan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PinjamBerkasController extends Controller
{
    /**
     * TAMPIL DATA PINJAM BERKAS*/
    public function index(Request $request)
{
    // Ambil SEMUA data tanpa filter role agar riwayat tetap sama untuk semua user
    $query = PinjamBerkas::with('permohonan');

    // Filter No Permohonan jika ada input cari
    if ($request->filled('no_permohonan')) {
        $query->whereHas('permohonan', function ($q) use ($request) {
            $q->where('no_permohonan', 'like', '%' . $request->no_permohonan . '%');
        });
    }

    // Mengurutkan dari yang terbaru diinput
    $dataPinjam = $query->orderBy('created_at', 'desc')->get();

    return view('scripts.pinjam_berkas', compact('dataPinjam'));
}

public function store(Request $request)
{
    $user = Auth::user();

    // 1. Validasi minimal: hanya cek nomor permohonan
    $request->validate([
        'no_permohonan' => 'required|exists:permohonan,no_permohonan',
    ], [
        'no_permohonan.exists' => 'Nomor permohonan tidak ditemukan.',
    ]);

    // 2. Tentukan Divisi Peminjam
    // Jika Admin/Kanim, ambil dari pilihan dropdown (nama_peminjam)
    // Jika Role lain, ambil langsung dari field role user tersebut
    if (strtoupper($user->role) === 'ADMIN' || strtoupper($user->role) === 'KANIM') {
        $divisi = $request->nama_peminjam;
    } else {
        $divisi = $user->role;
    }

    // Pastikan variabel divisi tidak kosong
    if (!$divisi) {
        return back()->with('error', 'Gagal! Identitas divisi peminjam tidak ditemukan.');
    }

    $permohonan = \App\Models\Permohonan::where('no_permohonan', $request->no_permohonan)->first();

    // Logika: Berkas tidak bisa dipinjam jika ada record yang statusnya 'Pengajuan' atau 'Disetujui'
    $isBorrowed = \App\Models\PinjamBerkas::where('permohonan_id', $permohonan->id)
        ->whereIn('status', ['Pengajuan', 'Disetujui'])
        ->exists();

    if ($isBorrowed) {
        // Mengirimkan pesan error ke halaman sebelumnya
        return back()->with('error_pinjam', 'Gagal! Berkas dengan nomor ' . $request->no_permohonan . ' saat ini masih dipinjam atau dalam proses pengajuan.');
    }

    // 4. SIMPAN DATA
    \App\Models\PinjamBerkas::create([
        'permohonan_id' => $permohonan->id,
        'nama_peminjam' => $divisi, 
        'tgl_pinjam'    => now(),
        'status'        => 'Pengajuan'
    ]);

    return redirect()->route('pinjam-berkas.index')->with('success', 'Peminjaman berhasil diajukan.');
}
    /**
     * APPROVE PEMINJAMAN
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
        // Cek apakah ada peminjaman yang statusnya masih 'Pengajuan' atau 'Disetujui'
        $pinjamAktif = PinjamBerkas::where('permohonan_id', $permohonan->id)
            ->whereIn('status', ['Pengajuan', 'Disetujui'])
            ->first();

        return response()->json([
            'success' => true,
            'data' => $permohonan,
            'is_borrowed' => $pinjamAktif ? true : false,
            'borrower_name' => $pinjamAktif ? $pinjamAktif->nama_peminjam : ''
        ]);
    }

    return response()->json(['success' => false]);
}
}