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

    /* Badge & Button Styling */
    .badge { padding: 6px 12px; font-weight: 600; border-radius: 8px; }
    .btn-action { 
        width: 32px !important; 
        height: 32px !important; 
        display: inline-flex !important; 
        align-items: center; 
        justify-content: center; 
        border-radius: 8px; 
    }

    .btn-action:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }

    /* Warna Status Dinamis */
    .bg-danger-subtle { background-color: #fee2e2 !important; color: #dc2626 !important; border: 1px solid #fecaca !important; }
    .bg-success-subtle { background-color: #dcfce7 !important; color: #16a34a !important; border: 1px solid #bbf7d0 !important; }
    .bg-warning-subtle { background-color: #fef9c3 !important; color: #a16207 !important; border: 1px solid #fef08a !important; }

    /* Modal Detail Styling */
    #modalDetailPemusnahan .modal-content { border-radius: 15px; border: none; }
    .table-detail-head { background-color: #1e293b !important; color: white !important; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }

    .app-pagination-list { 
        display: flex !important; 
        flex-direction: row !important; 
        justify-content: center !important; 
        list-style: none !important; 
        padding: 0 !important; 
        margin: 0 !important; 
        gap: 6px !important; 
    }
    .app-pagination-list .page-item span {
        display: flex; align-items: center; justify-content: center; 
        width: 32px; height: 32px; cursor: pointer; 
        background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px;
        color: #64748b; font-size: 13px; font-weight: 700; transition: all 0.2s;
    }
    .app-pagination-list .page-item.active span { 
        background-color: #3b82f6; color: #ffffff; border-color: #2563eb; 
    }
    .app-pagination-list .page-item.disabled span { 
        background-color: #f8fafc; color: #cbd5e1; cursor: not-allowed !important; 
    }
</style>

<div class="container-fluid px-4 py-4">
    <div class="row g-4">
        {{-- 1. BAGIAN INPUT: Hanya TIKIM --}}
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
                            <label class="form-label small fw-bold">1. Nomor Berita Acara</label>
                            <input type="text" name="no_berita_acara" class="form-control shadow-none border-primary" placeholder="Contoh: BA/2026/001" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">2. Dari Tanggal</label>
                                <input type="date" name="filter_mulai" id="filter_mulai" class="form-control date-calc shadow-none" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Sampai Tanggal</label>
                                <input type="date" name="filter_selesai" id="filter_selesai" class="form-control date-calc shadow-none" required>
                            </div>
                        </div>
                        
                        <div class="p-3 bg-light rounded-3 mb-3 text-center border">
                            <small class="text-muted d-block text-uppercase" style="font-size: 10px; letter-spacing: 1px;">3. Kalkulasi Dokumen:</small>
                            <h3 class="fw-bold text-primary mb-0" id="label-jumlah">0</h3>
                            <small class="text-muted small">Berkas Fisik Terdeteksi</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">4. Upload Scan BA (PDF)</label>
                            <input type="file" name="file_pdf" id="fileBeritaAcaraUtama" class="form-control shadow-none" accept="application/pdf">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm" id="btn-submit" disabled>
                            <i class="fas fa-paper-plane me-2"></i> AJUKAN PEMUSNAHAN
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- 2. BAGIAN TABEL RIWAYAT --}}
        <div class="{{ strtoupper(Auth::user()->role) == 'TIKIM' ? 'col-lg-8' : 'col-lg-12' }}">
            <div class="card shadow-sm border-0 rounded-3">
                {{-- HEADER RIWAYAT: JUDUL (KIRI) & FILTER + SHOW (KANAN) --}}
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            {{-- Sisi Kiri: Judul --}}
            <h6 class="mb-0 fw-bold text-dark">
                <i class="fas fa-history me-2 text-primary"></i>Riwayat Pemusnahan
            </h6>
            
            {{-- Sisi Kanan: Wadah Filter & SHOW Berdampingan --}}
            <div class="d-flex align-items-center gap-2" id="areaShowPemusnahan">
                {{-- Dropdown Filters --}}
                <div class="dropdown">
                    <button class="btn btn-light btn-sm border d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" style="border-radius:6px;">
                        <i class="fas fa-filter text-primary"></i> Filters
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-3 shadow-lg border-0" style="width:320px; border-radius:10px;">
                        <form action="{{ route('pemusnahan.index') }}" method="GET">
                            <label class="form-label small fw-bold text-muted mb-2">RANGE TANGGAL PENGAJUAN</label>
                            <div style="display: flex; gap: 8px; margin-bottom: 15px;">
                                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                            </div>
                            <label class="form-label small fw-bold text-muted mb-2">STATUS</label>
                            <select name="status" class="form-select form-select-sm mb-3">
                                <option value="">Semua Status</option>
                                <option value="Diajukan" {{ request('status') == 'Diajukan' ? 'selected' : '' }}>Diajukan</option>
                                <option value="Disetujui" {{ request('status') == 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                                <option value="Ditolak" {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                            <div class="d-grid gap-2 mt-2">
                                <button type="submit" class="btn btn-primary btn-sm fw-bold">Apply</button>
                                <a href="{{ route('pemusnahan.index') }}" class="btn btn-light btn-sm border fw-bold">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Dropdown SHOW akan mendarat di SINI secara otomatis karena ID areaShowPemusnahan --}}
            </div>
        </div>
        {{-- BADGE INFO FILTER AKTIF (Identik Badge Pinjam Berkas) --}}
        @if(request('start_date') || request('status'))
        <div class="px-4 py-2 bg-light border-bottom d-flex align-items-center justify-content-between">
            <span class="small text-muted">
                Menampilkan hasil: 
                @if(request('start_date')) <strong>{{ request('start_date') }} s/d {{ request('end_date') }}</strong> @endif
                @if(request('status')) <span class="badge bg-info text-dark ms-1">{{ strtoupper(request('status')) }}</span> @endif
            </span>
            <a href="{{ route('pemusnahan.index') }}" class="badge rounded-pill bg-primary text-decoration-none" style="font-size:11px; color: white;">
                {{ $riwayat->count() }} Data (Reset)
            </a>
        </div>
        @endif

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
                        @forelse($riwayat as $row)
                        <tr>
                            <td class="col-ba fw-bold text-primary">{{ $row->no_berita_acara }}</td>
                            <td class="col-periode">
                                <div class="d-flex align-items-center gap-1 small fw-bold">
                                    <span class="text-dark">{{ \Carbon\Carbon::parse($row->filter_mulai)->format('d/m/Y') }}</span>
                                    <span class="text-muted fw-normal">s/d</span>
                                    <span class="text-dark">{{ \Carbon\Carbon::parse($row->filter_selesai)->format('d/m/Y') }}</span>
                                </div>
                            </td>
                            <td class="col-jumlah">
                                <span class="badge bg-secondary-subtle text-secondary px-3">{{ $row->jumlah_dokumen }} Berkas</span>
                            </td>
                            <td class="col-status text-center">
                                @php 
                                    $currentStatus =   strtoupper($row->status ?? 'DIAJUKAN'); 
                                @endphp

                                @if($currentStatus == 'DISETUJUI')
                                    <span class="badge bg-success text-white px-3">DISETUJUI</span>
                                @elseif($currentStatus == 'DITOLAK')
                                    <span class="badge bg-danger text-white px-3">DITOLAK</span>
                                @else
                                    <span class="badge bg-warning text-dark px-3">DIAJUKAN</span>
                                @endif
                            </td>
                            <td class="col-aksi">
                                <div class="d-flex justify-content-center gap-2">
                                    {{-- 1. Tombol Detail Mata --}}
                                    <button type="button" class="btn btn-action btn-outline-primary shadow-sm" 
                                            onclick="lihatDetailPemusnahan('{{ $row->id }}')" 
                                            title="Lihat Detail Berkas">
                                        <i class="fas fa-eye"></i>
                                    </button>
                            
                                    {{-- 2. Logika Upload / Lihat PDF --}}
                                    @if($row->file_pdf)
                                        <a href="{{ asset('uploads/pemusnahan/' . $row->file_pdf) }}" target="_blank" 
                                           class="btn btn-action btn-info text-white shadow-sm" 
                                           title="Surat Pemusnahan">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    @else
                                        {{-- TOOLTIP TAMBAHAN DI SINI --}}
                                        <button type="button" class="btn btn-action btn-info text-white shadow-sm btn-upload-langsung" 
                                                data-id="{{ $row->id }}" 
                                                title="Upload Pemusnahan">
                                            <i class="fas fa-file-upload"></i>
                                        </button>
                                    @endif
                            
                                    {{-- 3. Tombol Cetak/Download BA --}}
                                    <a href="{{ route('pemusnahan.cetak', $row->id) }}" target="_blank" 
                                       class="btn btn-action btn-success text-white shadow-sm" 
                                       title="Cetak BA Pemusnahan">
                                        <i class="fas fa-download"></i>
                                    </a>
                            
                                    {{-- 4. Tombol Persetujuan Admin --}}
                                    @if(strtoupper(Auth::user()->role) == 'ADMIN' && strtoupper($row->status ?? 'DIAJUKAN') == 'DIAJUKAN')
                                        <form action="{{ route('pemusnahan.approve', $row->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="button" class="btn btn-success btn-sm btn-konfirmasi-setuju shadow-sm btn-action" 
                                                    title="Setujui Pemusnahan">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                            
                                        <form action="{{ route('pemusnahan.reject', $row->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="button" class="btn btn-danger btn-sm btn-konfirmasi-tolak shadow-sm btn-action" 
                                                    title="Tolak Pengajuan">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">Data riwayat tidak ditemukan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- PAGINATION FOOTER - Sudah diperbaiki variabelnya jadi $riwayat --}}
            @if(isset($riwayat) && method_exists($riwayat, 'links'))
            <div class="mt-3 px-3">
                @include('components.pagination-footer', ['data' => $riwayat, 'targetId' => 'areaShowPemusnahan'])
            </div>
            @endif
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
                {{-- BARIS ATAS: INFO BA (KIRI) & DROPDOWN SHOW (KANAN) --}}
                <div class="row align-items-center mb-4">
                    <div class="col-md-6">
                        <label class="d-block text-muted small fw-bold uppercase mb-1">No. Berita Acara</label>
                        <span id="det_no_ba" class="fw-bold text-primary" style="font-size: 16px;">-</span>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <label class="small fw-bold text-muted mb-0">SHOW</label>
                            <select id="det_rows_per_page" class="form-select form-select-sm" 
                            style="font-size: 11px; font-weight: 800; border-radius: 6px; width: 75px; color: #3b82f6; background: #f8f9fa; border: 1px solid #e2e8f0;" 
                            onchange="renderTableDetail()">
                        <option value="5" selected>5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                        </div>
                    </div>
                </div>
            
                {{-- BARIS KEDUA: JUDUL & SEARCH --}}
                <div class="row mb-3 align-items-center">
                    <div class="col-md-7">
                        <h6 class="fw-bold m-0"><i class="fas fa-list me-2"></i>Rincian Berkas</h6>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group input-group-sm shadow-sm" style="border-radius: 8px; overflow: hidden;">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="searchDetailPemusnahan" class="form-control border-start-0 ps-0" placeholder="Cari nomor atau nama...">
                        </div>
                    </div>
                </div>
            
                {{-- TABEL DATA --}}
                <div class="table-responsive border rounded mb-4" style="max-height: 400px;">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-detail-head text-center sticky-top">
                            <tr><th>No. Permohonan</th><th>Nama Pemohon</th><th>Jenis</th><th>Status Berkas</th></tr>
                        </thead>
                        <tbody id="list-detail-pemusnahan" style="font-size: 12px;"></tbody>
                    </table>
                </div>
            
                {{-- FOOTER: PAGINASI & INFO DI TENGAH --}}
                <div class="border-top pt-3 text-center">
                    <div class="d-flex flex-column align-items-center gap-2">
                        <div id="det_pagination_links"></div>
                        <div id="det_pagination_info" style="font-size: 10px; color: #adb5bd; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// 1. VARIABEL GLOBAL
let allDetailData = []; 
let currentPageDetail = 1;
let statusBatchUtama = ''; 

// 2. FUNGSI LIHAT DETAIL (AJAX)
function lihatDetailPemusnahan(id) {
    $('#list-detail-pemusnahan').html('<tr><td colspan="4" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></td></tr>');
    
    $.get("/pemusnahan-arsip/detail/" + id, function(res) {
        if(res.success) {
            // Normalisasi status dari database: hapus spasi dan jadikan uppercase
            let rawStatus = (res.ba.status || 'DIAJUKAN').toUpperCase().replace(/\s+/g, '');
            
            // Paksa pakai format "DISETUJUI" jika mengandung kata SETUJU
            statusBatchUtama = rawStatus.includes('SETUJU') ? 'DISETUJUI' : rawStatus;
            
            allDetailData = res.data;
            currentPageDetail = 1;
            
            // Set Header Modal
            let badgeClass = (statusBatchUtama === 'DISETUJUI') ? 'badge bg-success text-white' : (statusBatchUtama.includes('TOLAK') ? 'badge bg-danger text-white' : 'badge bg-warning text-dark');
            
            $('#det_no_ba').text(res.ba.no_berita_acara);
            // Tampilan di header tetap cantik (pakai spasi jika mau, tapi logika tetap DISETUJUI)
            $('#det_status_ba').attr('class', badgeClass).text(statusBatchUtama === 'DISETUJUI' ? 'DI SETUJUI' : statusBatchUtama);
            
            renderDetailPagination();
            new bootstrap.Modal(document.getElementById('modalDetailPemusnahan')).show();
        }
    });
}

// 3. FUNGSI RENDER TABEL & PAGINASI
function renderDetailPagination() {
    let searchValue = $('#searchDetailPemusnahan').val().toLowerCase();
    
    // 1. Filter data berdasarkan pencarian
    let filteredData = allDetailData.filter(item => 
        (item.no_permohonan && item.no_permohonan.toLowerCase().includes(searchValue)) || 
        (item.nama && item.nama.toLowerCase().includes(searchValue))
    );

    // 2. Ambil limit dari dropdown (Opsi: 5, 10, 25, 50, 100)
    let limit = parseInt($('#det_rows_per_page').val()) || 5;
    let total = filteredData.length;
    let totalPages = Math.ceil(total / limit);

    // 3. Reset ke halaman 1 jika halaman saat ini melampaui total halaman baru
    if (currentPageDetail > totalPages && totalPages > 0) {
        currentPageDetail = 1;
    }

    // 4. Potong data sesuai limit
    let startIdx = (currentPageDetail - 1) * limit;
    let paginatedData = filteredData.slice(startIdx, startIdx + limit);

    let html = '';
    paginatedData.forEach(item => {
        let badgeBerkas = '';
        
        // Logika Status Berdasarkan Variabel Global
        if (statusBatchUtama === 'DISETUJUI') {
            badgeBerkas = '<span class="text-danger fw-bold border border-danger px-2 rounded" style="font-size:10px; background: #fff5f5;">DIMUSNAHKAN</span>';
        } else if (statusBatchUtama.includes('TOLAK')) {
            badgeBerkas = '<span class="badge bg-danger-subtle text-danger border">DITOLAK</span>';
        } else {
            badgeBerkas = '<span class="badge bg-warning-subtle text-warning border">DIAJUKAN</span>';
        }
        
        html += `<tr>
            <td class="text-center py-2 fw-bold text-primary">${item.no_permohonan}</td>
            <td class="ps-3">${item.nama}</td>
            <td class="text-center">${item.jenis_permohonan || '-'}</td>
            <td class="text-center">${badgeBerkas}</td>
        </tr>`;
    });

    // 5. Tampilkan ke Tabel
    $('#list-detail-pemusnahan').html(html || '<tr><td colspan="4" class="text-center py-4">Data tidak ditemukan</td></tr>');
    
    // 6. Update Info Entries
    let from = total > 0 ? startIdx + 1 : 0;
    let to = Math.min(startIdx + limit, total);
    $('#det_pagination_info').html(`SHOWING <b>${from} - ${to}</b> OF <b>${total}</b> ENTRIES`);

    // 7. Gambar Navigasi
    renderJSNav('det_pagination_links', currentPageDetail, totalPages);
}

// 4. MESIN NAVIGASI ANGKA
function renderJSNav(containerId, currentPage, totalPages) {
    const container = document.getElementById(containerId);
    if (!container) return;
    let displayPages = totalPages < 1 ? 1 : totalPages;
    let html = '<ul class="app-pagination-list">';
    html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><span onclick="${currentPage > 1 ? `changeDetailPage(${currentPage - 1})` : ''}"><i class="fas fa-chevron-left"></i></span></li>`;
    for (let i = 1; i <= displayPages; i++) {
        html += `<li class="page-item ${i === currentPage ? 'active' : ''}"><span onclick="changeDetailPage(${i})">${i}</span></li>`;
    }
    html += `<li class="page-item ${currentPage === displayPages || displayPages === 1 ? 'disabled' : ''}"><span onclick="${currentPage < displayPages ? `changeDetailPage(${currentPage + 1})` : ''}"><i class="fas fa-chevron-right"></i></span></li>`;
    html += '</ul>';
    container.innerHTML = html;
}

function changeDetailPage(p) { currentPageDetail = p; renderDetailPagination(); }
function renderTableDetail() { currentPageDetail = 1; renderDetailPagination(); }

// 5. DOCUMENT READY
$(document).ready(function() {
    $("#searchDetailPemusnahan").on("keyup", function() {
        currentPageDetail = 1;
        renderDetailPagination();
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
        let form = $(this).closest('form');
        Swal.fire({ title: 'Setujui Pemusnahan?', text: "Berkas akan dihapus secara sistem.", icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Setujui' }).then((r) => { if (r.isConfirmed) form.submit(); });
    });

    $(document).on('click', '.btn-konfirmasi-tolak', function(e) {
        e.preventDefault();
        let form = $(this).closest('form');
        Swal.fire({ title: 'Tolak Pengajuan?', text: "Data berkas akan tetap aman.", icon: 'error', showCancelButton: true, confirmButtonText: 'Ya, Tolak', confirmButtonColor: '#dc3545' }).then((r) => { if (r.isConfirmed) form.submit(); });
    });

    $(document).on('click', '.btn-upload-langsung', function(e) {
        e.preventDefault();
        let idBA = $(this).data('id'); 
        let inputKiri = document.getElementById('fileBeritaAcaraUtama');
        if (!inputKiri || inputKiri.files.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Pilih File Dulu' });
            return;
        }
        let formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('file_pdf', inputKiri.files[0]);
        Swal.fire({ title: 'Sedang Mengunggah...', didOpen: () => { Swal.showLoading(); } });
        $.ajax({
            url: "/pemusnahan-arsip/upload/" + idBA,
            type: "POST", data: formData, processData: false, contentType: false,
            success: function() { Swal.fire('Berhasil!', 'Berkas disimpan.', 'success').then(() => { location.reload(); }); }
        });
    });
});

</script>
@endpush