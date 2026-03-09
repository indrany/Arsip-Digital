<?php

namespace App\Http\Controllers;

use App\Models\PinjamBerkas;
use App\Models\Permohonan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PinjamBerkasController extends Controller
{
    public function index(Request $request)
{
    $query = PinjamBerkas::with('permohonan')
        ->join('permohonan', 'pinjam_berkas.permohonan_id', '=', 'permohonan.id')
        ->leftJoin('datapaspor.datapaspor', function($join) {
            $join->on('permohonan.no_permohonan', '=', DB::raw('datapaspor.nopermohonan COLLATE utf8mb4_unicode_ci'));
        });
    if ($request->filled('no_permohonan')) {
        $query->where('permohonan.no_permohonan', 'like', '%' . $request->no_permohonan . '%')
              ->orWhere('permohonan.nama', 'like', '%' . $request->no_permohonan . '%'); 
    }
    if ($request->filled('start_date') && $request->filled('end_date')) {
        $query->whereBetween('pinjam_berkas.tgl_pinjam', [$request->start_date, $request->end_date]);
    }
    if ($request->filled('status')) {
        $query->where('pinjam_berkas.status', $request->status);
    }
    
    $dataPinjam = $query->select(
            'pinjam_berkas.*',
            'permohonan.no_permohonan',
            'permohonan.nama',
            'permohonan.tanggal_permohonan',
            'pinjam_berkas.id as id',
            'pinjam_berkas.status as status',
            'datapaspor.alurterakhir as alur_paspor_update'
        )
        ->orderBy('pinjam_berkas.created_at', 'desc')
        ->paginate(10);
        $dataPinjam->appends($request->query());

    return view('scripts.pinjam_berkas', compact('dataPinjam'));
}

    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'no_permohonan' => 'required|exists:permohonan,no_permohonan',
            'nama_personil' => 'required|string|max:255', 
            'petugas_arsip' => 'required|string|max:255',
            'keterangan'    => 'required|string|max:255',
        ]);

        $divisi = $request->nama_peminjam ?? $user->role ?? 'UMUM';
        $permohonan = Permohonan::where('no_permohonan', $request->no_permohonan)->first();

        $bulan = date('m'); $tahun = date('Y');
        $urutan = PinjamBerkas::whereMonth('tgl_pinjam', $bulan)->whereYear('tgl_pinjam', $tahun)->count() + 1;
        $nomorPeminjaman = str_pad($urutan, 2, '0', STR_PAD_LEFT) . '/' . $bulan . '/PNJM/' . $tahun;

        PinjamBerkas::create([
            'permohonan_id'   => $permohonan->id,
            'no_peminjaman'   => $nomorPeminjaman,
            'nama_peminjam'   => $request->nama_personil, 
            'divisi_peminjam' => $divisi,                
            'petugas_arsip'   => $request->petugas_arsip,
            'keterangan'      => $request->keterangan, 
            'tgl_pinjam'      => now(),
            'status'          => 'Pengajuan'
        ]);

        return redirect()->route('pinjam-berkas.index')->with('success', 'Data berhasil disimpan!');
    }

    // --- PERBAIKAN DI SINI: FUNGSI CARI SEKARANG JOIN KE PUSAT ---
    public function cariPermohonan($no)
    {
        // Join ke datapaspor agar dapat status SELESAI dari pusat
        $permohonan = DB::table('permohonan')
            ->leftJoin('datapaspor.datapaspor', function($join) {
                $join->on('permohonan.no_permohonan', '=', DB::raw('datapaspor.nopermohonan COLLATE utf8mb4_unicode_ci'));
            })
            ->where('permohonan.no_permohonan', $no)
            ->select('permohonan.*', 'datapaspor.alurterakhir as alur_paspor_update')
            ->first();

        if ($permohonan) {
            $pinjamAktif = PinjamBerkas::where('permohonan_id', $permohonan->id)
                ->whereIn('status', ['Pengajuan', 'Disetujui'])
                ->first();

            return response()->json([
                'success' => true,
                'data' => $permohonan,
                // Kita kirim status_berkas dari pusat jika ada, kalau tidak ada pakai yang lokal
                'status_terupdate' => $permohonan->alur_paspor_update ?? $permohonan->status_berkas,
                'is_borrowed' => $pinjamAktif ? true : false,
                'borrower_name' => $pinjamAktif ? $pinjamAktif->nama_peminjam : '',
                'personnel_name' => $pinjamAktif ? $pinjamAktif->nama_personil : ''
            ]);
        }
        return response()->json(['success' => false]);
    }

    public function approve($id)
    {
        PinjamBerkas::findOrFail($id)->update(['status' => 'Disetujui', 'tgl_pinjam' => now()]);
        return back()->with('success', 'Peminjaman berkas telah disetujui.');
    }

    public function reject($id)
    {
        PinjamBerkas::findOrFail($id)->update(['status' => 'Ditolak']);
        return back()->with('success', 'Peminjaman berkas ditolak.');
    }

    public function complete($id)
    {
        PinjamBerkas::findOrFail($id)->update(['status' => 'Selesai', 'tgl_kembali' => now()]);
        return back()->with('success', 'Berkas telah dikembalikan.');
    }

    public function cetak($id)
    {
        $data = PinjamBerkas::with('permohonan')->findOrFail($id);
        return view('arsip.surat_tanda_terima', compact('data'));
    }

    public function cetakKembali($id)
    {
        $data = PinjamBerkas::with('permohonan')->findOrFail($id);
        $bulan = date('m'); $tahun = date('Y');
        $urutan = PinjamBerkas::where('status', 'Selesai')->whereMonth('tgl_kembali', $bulan)->whereYear('tgl_kembali', $tahun)->count();
        $no_kembali = str_pad($urutan, 2, '0', STR_PAD_LEFT) . '/' . $bulan . '/KMBL/ARSIP/' . $tahun;
        return view('arsip.berita_acara_kembali', compact('data', 'no_kembali'));
    }
}