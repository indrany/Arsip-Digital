<?php

namespace App\Http\Controllers;

use App\Models\PinjamBerkas;
use App\Models\Permohonan;
use Illuminate\Http\Request;

class PinjamBerkasController extends Controller
{
    public function index(Request $request)
    {
        $query = PinjamBerkas::with('permohonan');

        // Filter Tanggal
        if ($request->filled('from')) {
            $query->whereDate('tgl_pinjam', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('tgl_pinjam', '<=', $request->to);
        }

        $dataPinjam = $query->orderBy('created_at', 'desc')->get();
        return view('pinjam_berkas', compact('dataPinjam'));
    }

    public function approve($id) {
        PinjamBerkas::findOrFail($id)->update(['status' => 'Disetujui']);
        return back()->with('success', 'Status diperbarui!');
    }

    public function reject($id) {
        PinjamBerkas::findOrFail($id)->update(['status' => 'Ditolak']);
        return back()->with('success', 'Peminjaman ditolak');
    }
    
    public function updateDivisi(Request $request, $id)
{
    // Cari data berdasarkan ID
    $item = \App\Models\PinjamBerkas::findOrFail($id);
    
    // Update data divisi sesuai pilihan di dropdown
    $item->update([
        'nama_peminjam' => $request->divisi
    ]);

    return response()->json([
        'success' => true, 
        'message' => 'Divisi berhasil diperbarui secara permanen!'
    ]);
}
    public function complete($id) {
        PinjamBerkas::findOrFail($id)->update([
            'status' => 'Selesai',
            'tgl_kembali' => now()
        ]);
        return back()->with('success', 'Berkas dikembalikan');
    }
}