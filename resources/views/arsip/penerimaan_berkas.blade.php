@extends('layouts.app')

@section('title', 'Penerimaan Berkas')
@section('page-title', 'Penerimaan Berkas')
@section('page-subtitle', 'Verifikasi berkas fisik yang masuk dari unit kerja.')

@section('content')

<style>
    /* 1. Pengaturan Tabel */
    #table-antrean-utama th, #table-antrean-utama td { white-space: nowrap; padding: 12px 8px; }
    .col-no-pengirim { width: 20%; text-align: left; padding-left: 25px !important; }
    .col-jumlah, .col-unit, .col-tanggal, .col-status, .col-aksi { width: 15%; text-align: center; }

    /* 2. Warna Badge */
    .bg-danger-subtle { background-color: #fee2e2 !important; color: #dc2626 !important; border: 1px solid #fecaca !important; }
    .bg-success-subtle { background-color: #dcfce7 !important; color: #16a34a !important; border: 1px solid #bbf7d0 !important; }
    .bg-warning-subtle { background-color: #fef9c3 !important; color: #a16207 !important; border: 1px solid #fef08a !important; }
    .text-scanned { color: #198754; font-weight: 700; font-size: 11px; letter-spacing: 0.5px; }

    /* 3. Gaya Paginasi Modal */
    .app-pagination-list { 
        display: flex !important; flex-direction: row !important; 
        justify-content: center !important; list-style: none !important; 
        padding: 0 !important; margin: 0 !important; gap: 6px !important; 
    }
    .app-pagination-list .page-item span {
        display: flex; align-items: center; justify-content: center; 
        width: 34px; height: 34px; cursor: pointer !important; 
        background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px;
        color: #64748b; font-size: 13px; font-weight: 700; transition: all 0.2s;
    }
    .app-pagination-list .page-item.active span { background-color: #3b82f6; color: #ffffff; border-color: #2563eb; }
    .app-pagination-list .page-item.disabled span { background-color: #f8fafc; color: #cbd5e1; cursor: not-allowed !important; }
    
    /* 4. Modal Styles */
    #modalDetailBerkas .modal-dialog { max-width: 420px; }
    #modalDetailBerkas .modal-content { border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }

    #areaShowPenerimaan .d-flex { 
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end;
}
</style>

{{-- INPUT SCAN BARCODE --}}
<div style="position: absolute; left: -9999px; top: 0;">
    <input type="text" id="input-barcode-permohonan" autofocus autocomplete="off">
</div>

{{-- 1. SECTION DAFTAR ANTREAN --}}
<div id="section-riwayat">
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        {{-- HEADER: JUDUL & FILTER (SUPER RAPAT) --}}
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                {{-- Sisi Kiri: Judul --}}
                <div class="me-auto">
                    <h6 class="m-0 fw-bold text-dark"><i class="fas fa-inbox me-2 text-primary"></i>Daftar Antrean</h6>
                </div>

                {{-- Sisi Kanan: Grup Filter Rapat --}}
                <div class="d-flex align-items-center gap-2">
                    {{-- Filter Tanggal --}}
                    <div style="width: 140px;">
                        <input type="date" id="filter-tanggal-antrean" class="form-control form-control-sm bg-light border-0">
                    </div>

                    {{-- Filter Status --}}
                    <div style="width: 180px;">
                        <select id="filter-status-antrean" class="form-select form-select-sm bg-light fw-bold border-0">
                            <option value="">-- Status --</option>
                            <option value="DIAJUKAN">⚠️ DIAJUKAN</option>
                            <option value="DITERIMA OLEH ARSIP">✅ DITERIMA OLEH ARSIP</option>
                        </select>
                    </div>

                    {{-- Search Bar --}}
                    <div style="width: 200px;">
                        <div class="input-group input-group-sm border rounded bg-light">
                            <span class="input-group-text bg-transparent border-0 pe-1"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="search-antrean" class="form-control border-0 bg-transparent shadow-none" placeholder="Cari...">
                        </div>
                    </div>

                    {{-- AREA SHOW: Pojok Kanan --}}
                    <div id="areaShowPenerimaan">
                        {{-- Dropdown SHOW nempel di sini otomatis --}}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="table-antrean-utama" style="font-size: 13px;">
                    <thead class="bg-light text-center text-muted uppercase">
                        <tr>
                            <th class="col-no-pengirim">No. Pengirim</th>
                            <th class="col-jumlah">Jumlah</th>
                            <th class="col-unit">Asal Unit</th>
                            <th class="col-tanggal">Tanggal Kirim</th>
                            <th class="col-status">Status</th>
                            <th class="col-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @forelse($antrean_batches->sortByDesc(fn($i) => strtoupper($i->status) === 'DIAJUKAN') as $row)
                        @php $statusText = strtoupper($row->status) == 'DIAJUKAN' ? 'DIAJUKAN' : 'DITERIMA OLEH ARSIP'; @endphp
                        <tr class="row-antrean" 
                            data-tanggal="{{ \Carbon\Carbon::parse($row->tgl_pengirim)->format('Y-m-d') }}" 
                            data-status="{{ strtoupper($row->status) == 'DIAJUKAN' ? 'DIAJUKAN' : 'DITERIMA OLEH ARSIP' }}">
                            <td class="col-no-pengirim fw-bold text-primary">{{ $row->no_pengirim }}</td>
                            <td class="col-jumlah"><span class="badge bg-secondary-subtle text-secondary px-3 py-2" style="border-radius: 8px;">{{ $row->jumlah_berkas }} Berkas</span></td>
                            <td class="col-unit"><span class="badge bg-info-subtle text-info px-3 py-2 fw-bold" style="border-radius: 8px;">{{ $row->asal_unit ?? 'Kanim' }}</span></td>
                            <td class="col-tanggal">{{ \Carbon\Carbon::parse($row->tgl_pengirim)->format('d-m-Y') }}</td>
                            <td class="col-status"><span class="badge rounded-pill {{ $statusText == 'DIAJUKAN' ? 'bg-warning text-dark' : 'bg-success text-white' }} px-3">{{ $statusText }}</span></td>
                            <td class="col-aksi">
                                <div class="d-flex justify-content-center align-items-center gap-3">
                                    <button type="button" onclick="lihatDetailBatch('{{ $row->no_pengirim }}')" class="btn btn-outline-primary btn-sm px-3" style="border-radius: 8px;"><i class="fas fa-eye me-1"></i> Detail</button>
                                    @if($statusText == 'DIAJUKAN')
                                        <button class="btn btn-primary btn-sm px-3 btn-proses-batch shadow-sm" data-id="{{ $row->no_pengirim }}" style="border-radius: 8px;">Mulai Terima</button>
                                    @else
                                        <span class="text-scanned">SUDAH DI-SCAN</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">Tidak ada data antrean.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('components.pagination-footer', ['data' => $antrean_batches, 'targetId' => 'areaShowPenerimaan'])
        </div>
    </div>
</div>

{{-- 2. SECTION PROSES SCAN (HIDDEN BY DEFAULT) --}}
<div id="section-proses-scan" style="display: none;">
    <div class="mb-4 text-start">
        <button class="btn btn-sm btn-outline-secondary btn-back-riwayat shadow-sm px-3" style="border-radius: 20px;"><i class="fas fa-arrow-left me-2"></i> Kembali ke Antrean</button>
    </div>
    <div class="row g-4 text-start">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4" style="min-height: 500px; border-radius: 15px;">
                <h6 class="fw-bold mb-3 text-muted small uppercase">1. List Kiriman (Belum Verifikasi)</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle"><tbody id="tbody-perlu-scan" style="font-size: 13px;"></tbody></table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4" style="min-height: 500px; border-radius: 15px;">
                <h6 class="fw-bold mb-3 text-muted small uppercase">2. Hasil Verifikasi (Selesai Scan)</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle"><tbody id="tbody-hasil-scan" style="font-size: 13px;"></tbody></table>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center mt-5">
        <button id="btn-simpan-batch" class="btn btn-success px-5 fw-bold shadow-sm py-3" style="border-radius: 50px;">Selesaikan & Terima Berkas</button>
    </div>
</div>

{{-- 3. MODAL DETAIL BATCH --}}
<div class="modal fade" id="modalDetailBatch" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content shadow border-0" style="border-radius: 12px;">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h6 class="modal-title fw-bold m-0"><i class="fas fa-file-alt me-2"></i>Detail Batch Penerimaan</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-start">
                <div class="row bg-light p-3 rounded mb-4 g-3 border mx-0">
                    <div class="col-md-4">
                        <label class="d-block text-muted small fw-bold">ID Batch (No Pengirim)</label>
                        <span id="det_no_pengirim" class="fw-bold text-primary">-</span>
                    </div>
                    <div class="col-md-4">
                        <label class="d-block text-muted small fw-bold">Tanggal Kirim</label>
                        <span id="det_tgl_pengirim" class="fw-bold">-</span>
                    </div>
                    <div class="col-md-4">
                        <label class="d-block text-muted small fw-bold">Status Batch</label>
                        <div id="det_status_wrapper">
                             <span id="det_status" class="badge bg-warning text-dark">-</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <label class="small fw-bold text-muted mb-0">SHOW</label>
                        <select id="det_rows_per_page" class="form-select form-select-sm" 
                                style="font-size: 12px; font-weight: 800; border-radius: 6px; width: 75px; color: #3b82f6; background: #f8f9fa; border: 1px solid #e2e8f0;" 
                                onchange="currentPageDetail = 1; renderTableDetail();">
                            <option value="5" selected>5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div style="width: 250px;">
                        <input type="text" id="searchDetailBerkas" class="form-control form-control-sm" placeholder="Cari nama..." onkeyup="searchDetailBerkasFungsi()">
                    </div>
                </div>

                <div class="table-responsive border rounded">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="bg-dark text-white text-center" style="font-size: 12px;">
                            <tr><th>No Permohonan</th><th>Nama Pemohon</th><th>Jenis Permohonan</th><th>Tujuan</th><th>Lokasi Rak</th><th>Status Berkas</th></tr>
                        </thead>
                        <tbody id="det_list_berkas_riwayat" style="font-size: 12px;"></tbody>
                    </table>
                </div>

                {{-- FOOTER: SEMUA DI TENGAH (ANGKA ATAS, INFO BAWAH) --}}
                <div class="mt-4 border-top pt-3">
                    <div class="d-flex flex-column align-items-center gap-2">
                        
                        {{-- Tempat Tombol Angka (Halaman 1, 2, dst) --}}
                        <div id="det_pagination_links"></div>

                        {{-- Tempat Tulisan SHOWING 1 - 5 OF 6 ENTRIES --}}
                        <div id="det_pagination_info" style="font-size: 10px; color: #adb5bd; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;"></div>

                    </div>
                </div>
                </div>
            <div class="modal-footer border-0 p-3">
                <button type="button" class="btn btn-secondary px-4 fw-bold shadow-sm" data-bs-dismiss="modal" style="border-radius: 8px;">Tutup</button>
            </div>
        </div> 
    </div> 
</div> 

{{-- 4. MODAL DETAIL PERMOHONAN --}}
<div class="modal fade" id="modalDetailBerkas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header border-0 px-4 pt-4 pb-2">
                <h5 class="modal-title">Detail Permohonan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <form id="form-detail-pop">
                    @php $fields = ['nomor'=>'Nomor Permohonan', 'tgl-mohon'=>'Tgl Permohonan', 'tgl-terbit'=>'Tgl Terbit', 'nama'=>'Nama', 'tempat-lahir'=>'Tempat Lahir', 'tgl-lahir'=>'Tgl Lahir', 'jk'=>'Jenis Kelamin', 'telp'=>'No Telpon', 'jenis-mohon'=>'Jenis Permohonan', 'jenis-paspor'=>'Jenis Paspor', 'tujuan'=>'Tujuan Paspor', 'no-paspor'=>'No Paspor', 'alur'=>'Alur Terakhir', 'lokasi'=>'Lokasi Arsip']; @endphp
                    @foreach($fields as $id => $label)
                    <div class="row align-items-center">
                        <label class="col-5 fw-bold text-muted" style="font-size: 11.5px;">{{ $label }}</label>
                        <div class="col-7">
                            @if($id === 'nama')
                                <div id="det-nama" class="form-control form-control-sm bg-white border text-dark" style="height: auto; min-height: 36px; padding: 6px 10px;">-</div>
                            @else
                                <input type="text" id="det-{{ $id }}" readonly class="form-control form-control-sm bg-white border text-dark" style="border-radius: 8px; height: 38px;">
                            @endif
                        </div>
                    </div>
                    @endforeach
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-danger shadow-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// 1. VARIABEL GLOBAL
let allDetailData = []; 
let currentPageDetail = 1;
const rowsPerPageDetail = 5; 
let currentBatchID = '';

// 2. FUNGSI DETAIL INDIVIDU (POP-UP BIODATA)
window.fetchAndShowDetail = function(nomor) {
    $.get(`/penerimaan-berkas/get-detail/${nomor}`, function(res) {
        if (res.success) {
            let item = res.data;
            $('#det-nomor').val(item.no_permohonan || '-'); 
            $('#det-tgl-mohon').val(item.tanggal_permohonan || '-'); 
            $('#det-tgl-terbit').val(item.tanggal_terbit || '-'); 
            $('#det-nama').text(item.nama || '-'); 
            $('#det-tempat-lahir').val(item.tempat_lahir || '-'); 
            $('#det-tgl-lahir').val(item.tanggal_lahir || '-'); 
            $('#det-jk').val(item.jenis_kelamin || '-'); 
            $('#det-telp').val(item.no_telp || '-'); 
            $('#det-jenis-mohon').val(item.jenis_permohonan || '-'); 
            $('#det-jenis-paspor').val(item.jenis_paspor || '-'); 
            $('#det-tujuan').val(item.tujuan_paspor || '-'); 
            $('#det-no-paspor').val(item.no_paspor || '-'); 
            $('#det-alur').val(item.status_berkas.includes('DITERIMA') ? 'SELESAI' : (item.status_berkas || '-'));; 
            $('#det-lokasi').val(item.lokasi_arsip || '-');
            new bootstrap.Modal(document.getElementById('modalDetailBerkas')).show();
        }
    });
};
// 3. FUNGSI RENDER TABEL & PAGINASI (MODAL RIWAYAT BATCH)
function renderDetailPagination() {
    let searchValue = $('#searchDetailBerkas').val().toLowerCase();
    let filteredData = allDetailData.filter(item => 
        (item.no_permohonan && item.no_permohonan.toLowerCase().includes(searchValue)) || 
        (item.nama && item.nama.toLowerCase().includes(searchValue))
    );

    let limit = parseInt($('#det_rows_per_page').val()) || 5;
    let total = filteredData.length;
    let totalPages = Math.ceil(total / limit);
    
    let startIdx = (currentPageDetail - 1) * limit;
    let paginatedData = filteredData.slice(startIdx, startIdx + limit);

    let html = '';
    paginatedData.forEach(item => {
        let s = (item.status_berkas || '').toUpperCase();
        
        // --- LOGIKA WARNA DINAMIS DI SINI ---
        let sClass = '';
        let sText = s;

        if (s.includes('DIMUSNAHKAN')) {
            // MERAH TEBAL UNTUK DIMUSNAHKAN
            sClass = 'text-danger fw-bold border border-danger px-2 rounded';
            sText = 'DIMUSNAHKAN';
        } else if (s.includes('DITERIMA')) {
            // HIJAU UNTUK SELESAI/DITERIMA
            sClass = 'badge bg-success-subtle text-success border px-2';
            sText = 'SELESAI';
        } else {
            // KUNING UNTUK PROSES/DIAJUKAN
            sClass = 'badge bg-warning-subtle text-warning border px-2';
        }

        html += `<tr>
            <td class="text-primary fw-bold text-center py-2">${item.no_permohonan}</td>
            <td class="text-start">${item.nama}</td>
            <td class="text-center">${item.jenis_permohonan || '-'}</td>
            <td class="text-center">${item.tujuan_paspor || '-'}</td>
            <td class="text-center"><span class="badge bg-light text-dark border">${item.lokasi_arsip || '-'}</span></td>
            <td class="text-center"><span class="${sClass}" style="font-size:9px;">${sText}</span></td>
        </tr>`;
    });

    $('#det_list_berkas_riwayat').html(html || '<tr><td colspan="6" class="text-center py-4">Data tidak ditemukan</td></tr>');
    
    let from = total > 0 ? startIdx + 1 : 0;
    let to = Math.min(startIdx + limit, total);
    $('#det_pagination_info').html(`SHOWING <b>${from} - ${to}</b> OF <b>${total}</b> ENTRIES`);

    renderJSNav('det_pagination_links', currentPageDetail, totalPages);
}

function renderJSNav(containerId, currentPage, totalPages) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    let html = '<ul class="app-pagination-list">';
    html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><span onclick="${currentPage > 1 ? `changeDetailPage(${currentPage - 1})` : ''}"><i class="fas fa-chevron-left"></i></span></li>`;
    
    for (let i = 1; i <= totalPages; i++) {
        html += `<li class="page-item ${i === currentPage ? 'active' : ''}"><span onclick="changeDetailPage(${i})">${i}</span></li>`;
    }

    html += `<li class="page-item ${currentPage === totalPages || totalPages === 0 ? 'disabled' : ''}"><span onclick="${currentPage < totalPages ? `changeDetailPage(${currentPage + 1})` : ''}"><i class="fas fa-chevron-right"></i></span></li>`;
    html += '</ul>';
    container.innerHTML = html;
}

// Fungsi pembantu klik angka
function changeDetailPage(page) {
    currentPageDetail = page;
    renderDetailPagination();
}

// Fungsi pembantu ganti limit SHOW
function renderTableDetail() {
    currentPageDetail = 1;
    renderDetailPagination();
}

// Fungsi pembantu search
function searchDetailBerkasFungsi() {
    currentPageDetail = 1;
    renderDetailPagination();
}

// 4. FUNGSI DETAIL BATCH (DARI TABEL UTAMA)
function lihatDetailBatch(noPengirim) {
    $('#det_list_berkas_riwayat').html('<tr><td colspan="6" class="text-center py-4">Loading...</td></tr>');
    $.get(`/arsip/list-berkas/${noPengirim}`, function(res) {
        if(res.success) {
            allDetailData = res.data; currentPageDetail = 1;
            $('#det_no_pengirim').text(res.batch.no_pengirim);
            $('#det_tgl_pengirim').text(res.batch.tgl_pengirim);
            const txt = res.batch.status.toUpperCase();
            $('#det_status').text(txt).attr('class', `badge ${txt.includes('DITERIMA') ? 'bg-success' : 'bg-warning text-dark'}`);
            renderDetailPagination();
            $('#modalDetailBatch').modal('show');
        }
    });
}

// 5. LOGIKA SCAN & SIMPAN BATCH
$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    const inputBarcode = $('#input-barcode-permohonan');
    inputBarcode.focus();

    // --- FUNGSI FILTER TABEL UTAMA ---
    function filterAntreanUtama() {
        let statusVal = $('#filter-status-antrean').val().toUpperCase();
        let searchVal = $('#search-antrean').val().toLowerCase();
        let tanggalVal = $('#filter-tanggal-antrean').val();

        $('.row-antrean').each(function() {
            let rowStatus = $(this).data('status').toUpperCase();
            let rowTanggal = $(this).data('tanggal');
            let rowText = $(this).text().toLowerCase();

            // Cek kecocokan
            let matchStatus = (statusVal === "") || (rowStatus === statusVal);
            let matchTanggal = (tanggalVal === "") || (rowTanggal === tanggalVal);
            let matchSearch = (searchVal === "") || (rowText.indexOf(searchVal) > -1);

            // Tampilkan jika semua kriteria cocok, sembunyikan jika tidak
            if (matchStatus && matchTanggal && matchSearch) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    // Jalankan filter saat dropdown status atau tanggal diganti
    $('#filter-status-antrean, #filter-tanggal-antrean').on('change', function() {
        filterAntreanUtama();
    });

    // Jalankan filter saat mengetik di kolom pencarian
    $('#search-antrean').on('keyup', function() {
        filterAntreanUtama();
    });

    // UPDATE COUNTER & BUKA BUTTON SIMPAN
    function updateCounters() {
        let total = $('#tbody-perlu-scan tr').length;
        let done = $('#tbody-perlu-scan tr.table-success').length;
        $('#pending-count').text((total - done) + ' BERKAS BELUM SCAN');
        $('#scan-count-live').text(done + ' BERKAS TERVERIFIKASI');
        
        // JIKA SEMUA SUDAH DI-SCAN, BUTTON AKTIF
        if (total > 0 && total === done) {
            $('#btn-simpan-batch').prop('disabled', false).removeClass('btn-secondary').addClass('btn-success');
        } else {
            $('#btn-simpan-batch').prop('disabled', true).addClass('btn-secondary').removeClass('btn-success');
        }
    }

    // MULAI TERIMA (TOMBOL BIRU DI TABEL UTAMA)
    $(document).on('click', '.btn-proses-batch', function() {
        currentBatchID = $(this).data('id');
        $('#tbody-hasil-scan, #tbody-perlu-scan').empty();
        $.get(`/arsip/list-berkas/${currentBatchID}`, function(res) {
            if(res.success) {
                res.data.forEach(item => {
                    let isDone = (item.status_berkas || '').toUpperCase().includes('DITERIMA');
                    let checkIcon = isDone ? '<i class="fas fa-check-circle text-success me-2"></i>' : '';
                    let rowHtml = `<tr id="row-${item.no_permohonan}" class="align-middle border-bottom ${isDone ? 'table-success fw-bold' : ''}">
                        <td style="width: 35%;" class="py-3 ps-2 text-start"><div class="d-flex align-items-center icon-container">${checkIcon}<span>${item.no_permohonan}</span></div></td>
                        <td style="width: 45%;" class="text-start"><div>${item.nama}</div></td>
                        <td style="width: 20%;" class="text-end pe-2"><button type="button" class="btn btn-primary btn-sm py-0" onclick="window.fetchAndShowDetail('${item.no_permohonan}')" style="font-size: 10px;">Detail</button></td>
                    </tr>`;
                    $('#tbody-perlu-scan').append(rowHtml); 
                    if(isDone) $('#tbody-hasil-scan').prepend(rowHtml);
                });
                updateCounters();
                $('#section-riwayat').hide(); $('#section-proses-scan').fadeIn();
                setTimeout(() => inputBarcode.focus(), 500);
            }
        });
    });

    // LOGIKA SCAN (ENTER)
    inputBarcode.on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            let barcode = $(this).val().trim(); $(this).val('');
            let rowTarget = $(`#row-${barcode}`);
            
            if (rowTarget.length === 0) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: 'Nomor tidak ada di batch ini!', timer: 1500, showConfirmButton: false });
                return;
            }
            if (rowTarget.hasClass('table-success')) return;

            $.post("{{ route('penerimaan-berkas.scan-permohonan') }}", { nomor_permohonan: barcode }, function(res) {
                if (res.success) { 
                    rowTarget.addClass('table-success fw-bold'); 
                    if (rowTarget.find('.fa-check-circle').length === 0) rowTarget.find('.icon-container').prepend('<i class="fas fa-check-circle text-success me-2"></i>');
                    $('#tbody-hasil-scan').prepend(rowTarget.clone()); 
                    updateCounters();
                }
            });
        }
    });

    // TOMBOL SELESAIKAN & TERIMA (VALIDASI LOKER)
    $('#btn-simpan-batch').on('click', function() {
        const adaLoker = "{{ $adaLoker ? 'true' : 'false' }}";
        if (!adaLoker) {
            Swal.fire({
                icon: 'warning',
                title: 'Master Rak Belum Terisi / Penuh',
                text: 'Silahkan tambah atau bersihkan kapasitas rak arsip terlebih dahulu.',
                confirmButtonText: 'Buka Master Rak',
                showCancelButton: true
            }).then((r) => { if (r.isConfirmed) window.location.href = "{{ route('rak-loker.index') }}"; });
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Simpan?',
            text: "Berkas akan otomatis didaftarkan ke rak loker arsip.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Selesaikan',
            confirmButtonColor: '#198754'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                $.post("{{ route('penerimaan-berkas.konfirmasi-bulk') }}", { no_pengirim: currentBatchID }, function(res) {
                    if(res.success) Swal.fire('Berhasil!', 'Berkas masuk ke arsip.', 'success').then(() => window.location.reload());
                });
            }
        });
    });

    $('.btn-back-riwayat, #btn-simpan-draft').on('click', () => window.location.reload());
});
</script>
@endpush