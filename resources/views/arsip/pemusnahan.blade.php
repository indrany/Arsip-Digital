@extends('layouts.app')

@section('title', 'Pemusnahan Arsip')
@section('page-title', 'Pemusnahan Arsip')
@section('page-subtitle', 'Manajemen penghapusan berkas fisik yang telah melewati masa retensi')

@section('content')

<style>
    /* Styling Tabel Utama */
    #tablePemusnahan th, #tablePemusnahan td {
        white-space: nowrap; 
        vertical-align: middle !important;
    }
    .col-ba { width: 25%; text-align: left; padding-left: 20px !important; }
    .col-periode { width: 20%; text-align: left; }
    .col-jumlah { width: 10%; text-align: center; }
    .col-status { width: 15%; text-align: center; }
    .col-aksi { width: 30%; text-align: center; }

    /* Layout Filter Tanggal Sejajar */
    .filter-date-box {
        max-width: 130px;
        font-size: 12px;
        cursor: pointer;
    }

    /* Badge & Button Styling (BALIK KE ASLI) */
    .badge { padding: 6px 12px; font-weight: 600; border-radius: 8px; }
    .btn-action { border-radius: 8px; width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s; }
    .btn-action:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }

    /* Warna Status Dinamis */
    .bg-danger-subtle { background-color: #fee2e2 !important; color: #dc2626 !important; border: 1px solid #fecaca !important; }
    .bg-success-subtle { background-color: #dcfce7 !important; color: #16a34a !important; border: 1px solid #bbf7d0 !important; }
    .bg-warning-subtle { background-color: #fef9c3 !important; color: #a16207 !important; border: 1px solid #fef08a !important; }

    /* Modal Detail Styling */
    #modalDetailPemusnahan .modal-content { border-radius: 15px; border: none; }
    .table-detail-head { background-color: #1e293b !important; color: white !important; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
</style>

<div class="container-fluid px-4 py-4">
    <div class="row g-4">
        {{-- BAGIAN INPUT: Hanya TIKIM --}}
        @if(strtoupper(Auth::user()->role) == 'TIKIM')
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-file-invoice me-2"></i>Input Berita Acara</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('pemusnahan.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nomor Berita Acara</label>
                            <input type="text" name="no_berita_acara" class="form-control shadow-none" placeholder="Contoh: BA/2026/001" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Dari Tanggal</label>
                                <input type="date" name="filter_mulai" id="filter_mulai" class="form-control date-calc shadow-none" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Sampai Tanggal</label>
                                <input type="date" name="filter_selesai" id="filter_selesai" class="form-control date-calc shadow-none" required>
                            </div>
                        </div>
                        
                        <div class="p-3 bg-light rounded-3 mb-3 text-center border">
                            <small class="text-muted d-block text-uppercase" style="font-size: 10px; letter-spacing: 1px;">Kalkulasi Dokumen:</small>
                            <h3 class="fw-bold text-primary mb-0" id="label-jumlah">0</h3>
                            <small class="text-muted small">Berkas Fisik Terdeteksi</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Upload Scan Berita Acara (PDF)</label>
                            <input type="file" name="file_pdf" class="form-control shadow-none" accept="application/pdf">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm" id="btn-submit" disabled>
                            <i class="fas fa-paper-plane me-2"></i> AJUKAN PEMUSNAHAN
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- BAGIAN TABEL RIWAYAT --}}
        <div class="{{ strtoupper(Auth::user()->role) == 'TIKIM' ? 'col-lg-8' : 'col-lg-12' }}">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-history me-2 text-primary"></i>Riwayat Pemusnahan</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablePemusnahan" style="font-size: 13px;">
                            <thead class="bg-light text-muted uppercase">
                                <tr>
                                    <th class="col-ba">No. Berita Acara</th>
                                    <th class="col-periode">Periode Berkas</th>
                                    <th class="col-jumlah">Jumlah</th>
                                    <th class="col-status">Status</th>
                                    <th class="col-aksi">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($riwayat as $row)
                                <tr>
                                    <td class="col-ba fw-bold text-primary">{{ $row->no_berita_acara }}</td>
                                    <td class="col-periode">
                                        {{-- PERIODE SEJAJAR --}}
                                        <div class="d-flex align-items-center gap-1 small fw-bold">
                                            <span class="text-dark">{{ \Carbon\Carbon::parse($row->filter_mulai)->format('d/m/Y') }}</span>
                                            <span class="text-muted fw-normal">s/d</span>
                                            <span class="text-dark">{{ \Carbon\Carbon::parse($row->filter_selesai)->format('d/m/Y') }}</span>
                                        </div>
                                    </td>
                                    <td class="col-jumlah">
                                        <span class="badge bg-secondary-subtle text-secondary px-3">{{ $row->jumlah_dokumen }} Berkas</span>
                                    </td>
                                    <td class="col-status">
                                        @if($row->status == 'Disetujui')
                                            <span class="badge bg-success text-white px-3">DISETUJUI</span>
                                        @else
                                            <span class="badge bg-warning text-dark px-3">DIAJUKAN</span>
                                        @endif
                                    </td>
                                    <td class="col-aksi">
                                        <div class="d-flex justify-content-center gap-2">
                                            {{-- BALIK KE ICON SAJA --}}
                                            <button type="button" class="btn btn-action btn-outline-primary shadow-sm" onclick="lihatDetailPemusnahan('{{ $row->id }}')" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="{{ route('pemusnahan.cetak', $row->id) }}" target="_blank" class="btn btn-action btn-success text-white shadow-sm" title="Cetak BA">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            @if(in_array(strtoupper(Auth::user()->role), ['TIKIM', 'ADMIN']))
                                            <button type="button" class="btn btn-action btn-info text-white shadow-sm" onclick="document.getElementById('upload-{{$row->id}}').click()" title="Upload Scan">
                                                <i class="fas fa-upload"></i>
                                            </button>
                                            <form action="{{ route('pemusnahan.upload', $row->id) }}" method="POST" enctype="multipart/form-data" id="form-upload-{{$row->id}}" class="d-none">
                                                @csrf
                                                <input type="file" name="file_pdf" id="upload-{{$row->id}}" onchange="document.getElementById('form-upload-{{$row->id}}').submit()">
                                            </form>
                                            @endif
                                            @if(strtoupper(Auth::user()->role) == 'ADMIN' && $row->status == 'Diajukan')
                                            <form action="{{ route('pemusnahan.approve', $row->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="button" class="btn btn-primary btn-sm fw-bold px-3 btn-konfirmasi-setuju shadow-sm" style="border-radius: 8px; height: 35px;">
                                                    SETUJUI
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

{{-- MODAL DETAIL --}}
<div class="modal fade" id="modalDetailPemusnahan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h6 class="modal-title fw-bold m-0"><i class="fas fa-file-alt me-2"></i>Detail Berkas Pemusnahan</h6>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row bg-light p-3 rounded mb-4 g-3 border mx-0">
                    <div class="col-md-6">
                        <label class="d-block text-muted small fw-bold uppercase">No. Berita Acara</label>
                        <span id="det_no_ba" class="fw-bold text-primary" style="font-size: 16px;">-</span>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <label class="d-block text-muted small fw-bold uppercase">Status Pengajuan</label>
                        <div id="det_status_wrapper">
                             <span id="det_status_ba" class="badge">-</span>
                        </div>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <div class="col-md-7"><h6 class="fw-bold m-0">Rincian Berkas</h6></div>
                    <div class="col-md-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="searchDetailPemusnahan" class="form-control border-start-0 shadow-none" placeholder="Cari...">
                        </div>
                    </div>
                </div>
                <div class="table-responsive" style="max-height: 400px;">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-detail-head text-center sticky-top">
                            <tr><th>No. Permohonan</th><th>Nama</th><th>Jenis</th><th>Status Berkas</th></tr>
                        </thead>
                        <tbody id="list-detail-pemusnahan" style="font-size: 12px;"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function lihatDetailPemusnahan(id) {
    $('#list-detail-pemusnahan').html('<tr><td colspan="4" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></td></tr>');
    $.get("/pemusnahan-arsip/detail/" + id, function(res) {
        if(res.success) {
            $('#det_no_ba').text(res.ba.no_berita_acara);
            let statusBA = res.ba.status.toUpperCase();
            $('#det_status_ba').attr('class', 'badge ' + (statusBA === 'DISETUJUI' ? 'bg-success text-white' : 'bg-warning text-dark')).text(statusBA);
            let html = '';
            res.data.forEach(item => {
                let badge = (statusBA === 'DISETUJUI') ? `<span class="badge bg-danger-subtle text-danger border">DIMUSNAHKAN</span>` : `<span class="badge bg-warning-subtle text-warning border">DIAJUKAN</span>`;
                html += `<tr><td class="text-center py-2">${item.no_permohonan}</td><td>${item.nama}</td><td class="text-center">${item.jenis_permohonan || '-'}</td><td class="text-center">${badge}</td></tr>`;
            });
            $('#list-detail-pemusnahan').html(html || '<tr><td colspan="4" class="text-center py-4">Data kosong</td></tr>');
            new bootstrap.Modal(document.getElementById('modalDetailPemusnahan')).show();
        }
    });
}

$(document).ready(function() {
    $("#searchDetailPemusnahan").on("keyup", function() {
        var v = $(this).val().toLowerCase();
        $("#list-detail-pemusnahan tr").filter(function() { $(this).toggle($(this).text().toLowerCase().indexOf(v) > -1) });
    });
    $('.date-calc').on('change', function() {
        let m = $('#filter_mulai').val(), s = $('#filter_selesai').val();
        if (m && s) {
            $.get("{{ route('pemusnahan.hitung') }}", { mulai: m, selesai: s }, function(res) {
                $('#label-jumlah').text(res.jumlah);
                $('#btn-submit').prop('disabled', res.jumlah <= 0);
            });
        }
    });
    $(document).on('click', '.btn-konfirmasi-setuju', function(e) {
        e.preventDefault();
        let f = $(this).closest('form');
        Swal.fire({ title: 'Setujui Pemusnahan?', text: "Berkas akan dihapus permanen.", icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Setujui' }).then((r) => { if (r.isConfirmed) f.submit(); });
    });
});
</script>
@endpush