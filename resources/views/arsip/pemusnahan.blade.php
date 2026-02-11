@extends('layouts.app')

@section('title', 'Pemusnahan Arsip')

{{-- ISI HEADER ATAS --}}
@section('page-title', 'Pemusnahan Arsip')
@section('page-subtitle', 'Manajemen penghapusan berkas fisik yang telah melewati masa retensi')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row g-4">
        {{-- BAGIAN INPUT: Hanya muncul untuk TIKIM --}}
        @if(strtoupper(Auth::user()->role) == 'TIKIM')
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white py-3">
                    {{-- Warna icon dan teks diganti ke primary (biru) --}}
                    <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-file-invoice me-2"></i>Input Berita Acara</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('pemusnahan.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nomor Berita Acara</label>
                            <input type="text" name="no_berita_acara" class="form-control" placeholder="Contoh: BA/2026/001" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Dari Tanggal</label>
                                <input type="date" name="filter_mulai" id="filter_mulai" class="form-control date-calc" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Sampai Tanggal</label>
                                <input type="date" name="filter_selesai" id="filter_selesai" class="form-control date-calc" required>
                            </div>
                        </div>
                        
                        <div class="p-3 bg-light rounded-3 mb-3 text-center">
                            <small class="text-muted d-block">Kalkulasi Jumlah Dokumen:</small>
                            <h3 class="fw-bold text-primary mb-0" id="label-jumlah">0</h3>
                            <small class="text-muted">Berkas Fisik</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Upload Scan Berita Acara (PDF)</label>
                            <input type="file" name="file_pdf" class="form-control" accept="application/pdf">
                            <small class="text-muted" style="font-size: 10px;">*Bisa dikosongkan dulu untuk cetak lampiran.</small>
                        </div>

                        {{-- Tombol diganti ke btn-primary (biru) --}}
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2" id="btn-submit" disabled>
                            <i class="fas fa-paper-plane me-2"></i> AJUKAN PEMUSNAHAN
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- BAGIAN TABEL: Lebar otomatis menyesuaikan Role --}}
        <div class="{{ strtoupper(Auth::user()->role) == 'TIKIM' ? 'col-lg-8' : 'col-lg-12' }}">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-history me-2"></i>Riwayat Pemusnahan</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">No. Berita Acara</th>
                                    <th>Periode Dokumen</th>
                                    <th class="text-center">Jumlah</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($riwayat as $row)
                                <tr>
                                    <td class="ps-3 fw-bold">{{ $row->no_berita_acara }}</td>
                                    <td class="small">{{ $row->filter_mulai }} s/d {{ $row->filter_selesai }}</td>
                                    <td class="text-center">{{ $row->jumlah_dokumen }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $row->status == 'Disetujui' ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ strtoupper($row->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            {{-- 1. Tombol Cetak --}}
                                            <a href="{{ route('pemusnahan.cetak', $row->id) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Cetak Lampiran">
                                                <i class="fas fa-print"></i>
                                            </a>

                                            {{-- 2. TIKIM: Tombol Upload Susulan --}}
                                            @if(strtoupper(Auth::user()->role) == 'TIKIM' && !$row->file_pdf)
                                            <button type="button" class="btn btn-sm btn-warning" onclick="document.getElementById('upload-{{$row->id}}').click()" title="Upload PDF">
                                                <i class="fas fa-upload"></i>
                                            </button>
                                            <form action="{{ route('pemusnahan.upload', $row->id) }}" method="POST" enctype="multipart/form-data" id="form-upload-{{$row->id}}" class="d-none">
                                                @csrf
                                                <input type="file" name="file_pdf" id="upload-{{$row->id}}" onchange="document.getElementById('form-upload-{{$row->id}}').submit()">
                                            </form>
                                            @endif

                                            {{-- 3. Lihat PDF --}}
                                            @if($row->file_pdf)
                                            <a href="{{ asset('uploads/pemusnahan/' . $row->file_pdf) }}" target="_blank" class="btn btn-sm btn-outline-info" title="Lihat PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            @endif
                                            
                                            {{-- 4. ADMIN: Tombol Setujui --}}
                                            @if(strtoupper(Auth::user()->role) == 'ADMIN' && $row->status == 'Diajukan')
                                            <form action="{{ route('pemusnahan.approve', $row->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="button" class="btn btn-sm btn-success fw-bold btn-konfirmasi-setuju" title="Setujui Pemusnahan">
                                                    <i class="fas fa-check"></i> SETUJUI
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // 1. AJAX Kalkulasi Jumlah Dokumen
    $('.date-calc').on('change', function() {
        let mulai = $('#filter_mulai').val();
        let selesai = $('#filter_selesai').val();

        if (mulai && selesai) {
            $('#label-jumlah').text('...'); 
            $.get("{{ route('pemusnahan.hitung') }}", { mulai: mulai, selesai: selesai }, function(res) {
                $('#label-jumlah').text(res.jumlah);
                if (res.jumlah > 0) {
                    $('#btn-submit').prop('disabled', false);
                } else {
                    $('#btn-submit').prop('disabled', true);
                }
            }).fail(function() {
                alert("Gagal mengambil data dari server.");
            });
        }
    });

    // 2. SweetAlert Konfirmasi Approval (Khusus Admin)
    $(document).on('click', '.btn-konfirmasi-setuju', function(e) {
        e.preventDefault();
        let form = $(this).closest('form');
        Swal.fire({
            title: 'Konfirmasi Pemusnahan',
            text: "Setelah disetujui, status berkas pada database akan berubah dan kapasitas RAK akan dikosongkan secara otomatis.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Setujui & Kosongkan Rak',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush