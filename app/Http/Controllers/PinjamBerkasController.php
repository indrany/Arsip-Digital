<?php

namespace App\Http\Controllers;

use App\Models\PinjamBerkas;
use App\Models\Permohonan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PinjamBerkasController extends Controller
{
    
    public function index(Request $request)
{

    $query = PinjamBerkas::with('permohonan');
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

    // 1. TAMBAHKAN VALIDASI petugas_arsip
    $request->validate([
        'no_permohonan' => 'required|exists:permohonan,no_permohonan',
        'nama_personil' => 'required|string|max:255', 
        'petugas_arsip' => 'required|string|max:255', // TAMBAHKAN INI
        'keterangan'    => 'required|string|max:255',
    ], [
        'no_permohonan.exists' => 'Nomor permohonan tidak ditemukan.',
        'nama_personil.required' => 'Nama orang yang meminjam wajib diisi.',
        'petugas_arsip.required' => 'Nama petugas arsip wajib diisi.', // TAMBAHKAN INI
    ]);

    // 2. Tentukan Divisi Peminjam
    if (strtoupper($user->role) === 'ADMIN' || strtoupper($user->role) === 'KANIM') {
        $divisi = $request->nama_peminjam;
    } else {
        $divisi = $user->role;
    }

    if (!$divisi) {
        return back()->with('error', 'Gagal! Identitas divisi peminjam tidak ditemukan.');
    }

    $permohonan = \App\Models\Permohonan::where('no_permohonan', $request->no_permohonan)->first();
    $isBorrowed = \App\Models\PinjamBerkas::where('permohonan_id', $permohonan->id)
        ->whereIn('status', ['Pengajuan', 'Disetujui'])
        ->exists();

    if ($isBorrowed) {
        return back()->with('error_pinjam', 'Gagal! Berkas dengan nomor ' . $request->no_permohonan . ' saat ini masih dipinjam atau dalam proses pengajuan.');
    }

    $bulan = date('m');
    $tahun = date('Y');
    $urutan = \App\Models\PinjamBerkas::whereMonth('tgl_pinjam', $bulan)
                ->whereYear('tgl_pinjam', $tahun)
                ->count() + 1;
                
    $nomorUrutFormated = str_pad($urutan, 2, '0', STR_PAD_LEFT);
    $nomorPeminjaman = $nomorUrutFormated . '/' . $bulan . '/PNJM/' . $tahun;

    // 3. Simpan nama petugas_arsip ke database
    \App\Models\PinjamBerkas::create([
        'permohonan_id' => $permohonan->id,
        'no_peminjaman' => $nomorPeminjaman,
        'nama_peminjam' => $divisi, 
        'nama_personil' => $request->nama_personil,
        'petugas_arsip' => $request->petugas_arsip, // TAMBAHKAN INI
        'keterangan'    => $request->keterangan, 
        'tgl_pinjam'    => now(),
        'status'        => 'Pengajuan'
    ]);

    return redirect()->route('pinjam-berkas.index')->with('success', 'Peminjaman berhasil diajukan.');
}
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
        $pinjamAktif = PinjamBerkas::where('permohonan_id', $permohonan->id)
            ->whereIn('status', ['Pengajuan', 'Disetujui'])
            ->first();

        return response()->json([
            'success' => true,
            'data' => $permohonan,
            'is_borrowed' => $pinjamAktif ? true : false,
            'borrower_name' => $pinjamAktif ? $pinjamAktif->nama_peminjam : '',
            'personnel_name' => $pinjamAktif ? $pinjamAktif->nama_personil : '' // TAMBAHKAN INI
        ]);
    }
    return response()->json(['success' => false]);
}
    public function cetak($id)
    {
        $data = \App\Models\PinjamBerkas::with('permohonan')->findOrFail($id);
        return view('arsip.surat_tanda_terima', compact('data'));
    }
public function cetakKembali($id)
{
    $data = PinjamBerkas::with('permohonan')->findOrFail($id);
    
    // Logika nomor urut pengembalian: 01/02/KMBL/ARSIP/2026
    $bulan = date('m');
    $tahun = date('Y');
    $urutan = PinjamBerkas::where('status', 'Selesai')
                ->whereMonth('tgl_kembali', $bulan)
                ->whereYear('tgl_kembali', $tahun)
                ->count();
                
    $no_kembali = str_pad($urutan, 2, '0', STR_PAD_LEFT) . '/' . $bulan . '/KMBL/ARSIP/' . $tahun;

    return view('arsip.berita_acara_kembali', compact('data', 'no_kembali'));
}
}