@extends('layouts.app')

@section('page-title', 'Riwayat Pengiriman Berkas')
@section('page-subtitle', 'Daftar batch pengiriman berkas yang telah diajukan ke bagian arsip.')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-body">
            {{-- HEADER: TOMBOL & FILTER GABUNGAN --}}
            <div class="row g-3 mb-4 align-items-center">
                <div class="col-md-3">
                    <a href="{{ route('pengiriman-berkas.create') }}" class="btn btn-primary fw-bold px-3 shadow-sm w-100" style="border-radius: 8px;">
                        <i class="fas fa-plus-circle me-1"></i> Tambah Pengiriman
                    </a>
                </div>
                <div class="col-md-2">
                    <input type="date" id="filterTanggal" class="form-control form-control-sm bg-light" title="Filter Tanggal Kirim">
                </div>

                <div class="col-md-3">
                    <select id="filterStatus" class="form-select form-select-sm bg-light fw-bold">
                        <option value="">-- Semua Status --</option>
                        <option value="DIAJUKAN">⚠️ DIAJUKAN</option>
                        <option value="DITERIMA OLEH ARSIP">✅ DITERIMA OLEH ARSIP</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="inputSearchRiwayat" class="form-control border-0 bg-light" placeholder="Cari No. Pengirim...">
                        <button class="btn btn-light border-0" type="button" id="btnResetFilter">
                            <i class="fas fa-sync-alt text-muted"></i>
                        </button>
                    </div>
                </div>
            </div>

            <h6 class="fw-bold mb-3 d-flex align-items-center">
                <i class="fas fa-history me-2 text-primary"></i>Tabel Riwayat Pengiriman Berkas
            </h6>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tableRiwayat" style="font-size: 13px;">
                    <thead class="bg-light text-muted uppercase">
                        <tr>
                            <th class="ps-4" style="width: 20%;">No. Pengirim</th>
                            <th style="width: 15%;">Tanggal Kirim</th>
                            <th class="text-center" style="width: 15%;">Jumlah Berkas</th>
                            <th style="width: 15%;">Asal Unit</th>
                            <th class="text-center" style="width: 15%;">Status</th>
                            <th class="text-center" style="width: 20%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayat as $row)
                        <tr class="data-row {{ $row->status === 'Diajukan' ? 'priority-row' : '' }}" 
                            data-tanggal="{{ \Carbon\Carbon::parse($row->tgl_pengirim)->format('Y-m-d') }}" 
                            data-status="{{ strtoupper($row->status) }}">
                            
                            <td class="ps-4 fw-bold text-primary">{{ $row->no_pengirim }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->tgl_pengirim)->format('d-m-Y') }}</td>
                            <td class="text-center">
                                <span class="badge bg-secondary-subtle text-secondary px-3 py-2" style="border-radius: 8px;">
                                    {{ $row->jumlah_berkas }} Berkas
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info px-3 py-2 text-uppercase">
                                    {{ $row->asal_unit }}
                                </span>
                            </td>
                            <td class="text-center">
                                @php
                                    $statusClass = [
                                        'Diajukan' => 'bg-warning text-dark border-warning',
                                        'DITERIMA OLEH ARSIP' => 'bg-success text-white',
                                        'SIAP_DITERIMA' => 'bg-info text-white'
                                    ][$row->status] ?? 'bg-secondary text-white';
                                @endphp
                                <span class="badge {{ $statusClass }} px-3 py-2" style="border-radius: 8px; font-size: 10px;">
                                    {{ strtoupper($row->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <button type="button" onclick="lihatDetailBatch('{{ $row->no_pengirim }}')" class="btn btn-outline-primary btn-sm px-3" style="border-radius: 6px;">
                                        <i class="fas fa-eye me-1"></i> Detail
                                    </button>
                                    <a href="{{ route('arsip.cetak-pengantar', $row->no_pengirim) }}" target="_blank" class="btn btn-success btn-sm px-3" style="border-radius: 6px;">
                                        <i class="fas fa-print me-1"></i> Cetak
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr class="no-data-row">
                            <td colspan="6" class="text-center py-5 text-muted">Belum ada riwayat pengiriman berkas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('components.pagination-footer', ['data' => $riwayat])

        </div>
    </div>
</div>

{{-- MODAL DETAIL BATCH --}}
{{-- MODAL DETAIL BATCH --}}
<div class="modal fade" id="modalDetailBatch" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content shadow border-0" style="border-radius: 12px;">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h6 class="modal-title fw-bold m-0"><i class="fas fa-file-alt me-2"></i>Detail Batch Pengiriman</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-4">
                {{-- 1. INFO HEADER BATCH --}}
                <div class="row bg-light p-3 rounded mb-4 g-3 border mx-0">
                    <div class="col-md-4">
                        <label class="d-block text-muted small fw-bold uppercase">ID Batch</label>
                        <span id="det_no_pengirim" class="fw-bold text-primary" style="font-size: 16px;">-</span>
                    </div>
                    <div class="col-md-4">
                        <label class="d-block text-muted small fw-bold uppercase">Tanggal Pengiriman</label>
                        <span id="det_tgl_pengirim" class="fw-bold" style="font-size: 16px;">-</span>
                    </div>
                    <div class="col-md-4">
                        <label class="d-block text-muted small fw-bold uppercase">Status Batch</label>
                        <div id="det_status_wrapper">
                             <span id="det_status" class="badge bg-warning text-dark">-</span>
                        </div>
                    </div>
                </div>

                {{-- 2. KONTROL: SHOW (KIRI) & SEARCH (KANAN) --}}
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
                        <div class="input-group input-group-sm shadow-sm" style="border-radius: 8px; overflow: hidden;">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="inputSearchDetail" class="form-control border-start-0 ps-0" 
                                   placeholder="Cari nama..." onkeyup="searchDetailBerkas()" style="font-size: 12px;">
                        </div>
                    </div>
                </div>

                {{-- 3. TABEL DATA --}}
                <div class="table-responsive border rounded" style="max-height: 400px;">
                    <table class="table table-sm table-hover align-middle mb-0" id="tableDetailBerkas">
                        <thead class="bg-dark text-white text-center" style="font-size: 12px; position: sticky; top: 0; z-index: 5;">
                            <tr>
                                <th class="py-2">No Permohonan</th>
                                <th>Nama Pemohon</th>
                                <th>Jenis Permohonan</th>
                                <th>Jenis Paspor</th>
                                <th>Tujuan</th>
                                <th>Status Berkas</th>
                            </tr>
                        </thead>
                        <tbody id="det_list_berkas" style="font-size: 12px;"></tbody>
                    </table>
                </div>

                {{-- 4. FOOTER: PAGINASI (ATAS) & INFO (BAWAH) - SEMUA DI TENGAH --}}
                <div class="mt-4 border-top pt-3">
                    <div class="d-flex flex-column align-items-center gap-2">
                        
                        {{-- Tempat Angka 1, 2, dst --}}
                        <div id="det_pagination_links"></div>
                
                        {{-- Tempat Tulisan SHOWING --}}
                        <div id="det_pagination_info" style="font-size: 10px; color: #adb5bd; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;"></div>
                
                    </div>
                </div>

            </div> {{-- INI PENUTUP MODAL-BODY --}}

            <div class="modal-footer border-0 p-3">
                <button type="button" class="btn btn-secondary px-4 fw-bold shadow-sm" data-bs-dismiss="modal" style="border-radius: 8px;">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // 1. DATA GLOBAL MODAL
    let currentDetailData = []; 
    let filteredDetailData = null; 
    let rowsPerPageDetail = 5;
    let currentPageDetail = 1;
    
    $(document).ready(function() {
        // FUNGSI FILTER TABEL UTAMA (Halaman Riwayat)
        function applyFilters() {
            var searchValue = $("#inputSearchRiwayat").val().toLowerCase();
            var filterDate = $("#filterTanggal").val();
            var filterStatus = $("#filterStatus").val();
            var rows = $("#tableRiwayat tbody tr").not(".no-data-row");
            
            rows.each(function() {
                var rowText = $(this).text().toLowerCase();
                var rowDate = $(this).data('tanggal');
                var rowStatus = $(this).data('status') ? $(this).data('status').toString().toUpperCase() : "";
    
                var matchSearch = (searchValue === "") || (rowText.indexOf(searchValue) > -1);
                var matchDate = (filterDate === "") || (rowDate === filterDate);
                var matchStatus = (filterStatus === "") || (rowStatus === filterStatus.toUpperCase());
    
                $(this).toggle(matchSearch && matchDate && matchStatus);
            });
        }
    
        $("#inputSearchRiwayat").on("keyup", applyFilters);
        $("#filterTanggal, #filterStatus").on("change", applyFilters);
        $("#btnResetFilter").on("click", function() {
            $("#inputSearchRiwayat, #filterTanggal, #filterStatus").val("");
            $("#tableRiwayat tbody tr").show();
        });
    });
    
    // 2. FUNGSI BUKA MODAL
    function lihatDetailBatch(noPengirim) {
        document.getElementById('det_list_berkas').innerHTML = '<tr><td colspan="6" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>';
        fetch(`/arsip/list-berkas/${noPengirim}`)
            .then(response => response.json())
            .then(res => {
                if(res.success) {
                    currentDetailData = res.data;
                    filteredDetailData = null; 
                    currentPageDetail = 1; 
                    document.getElementById('inputSearchDetail').value = ""; 
                    
                    document.getElementById('det_no_pengirim').innerText = res.batch.no_pengirim;
                    document.getElementById('det_tgl_pengirim').innerText = res.batch.tgl_pengirim;
                    
                    const elStatusBatch = document.getElementById('det_status');
                    const sText = res.batch.status ? res.batch.status.toUpperCase() : '';
                    elStatusBatch.innerText = sText;
                    elStatusBatch.className = sText.includes('DITERIMA') ? 'badge bg-success text-white' : 'badge bg-warning text-dark';
                    
                    renderTableDetail(); // Panggil fungsi render
                    new bootstrap.Modal(document.getElementById('modalDetailBatch')).show();
                }
            });
    }
    
    // 3. FUNGSI RENDER TABEL DETAIL (SOLUSI FINAL)
    function renderTableDetail() {
        let displayData = (filteredDetailData !== null) ? filteredDetailData : currentDetailData;
        
        const selectShow = document.getElementById('det_rows_per_page');
        if (selectShow) rowsPerPageDetail = parseInt(selectShow.value);

        let totalItems = displayData.length;
        let totalPages = Math.ceil(totalItems / rowsPerPageDetail);

        let start = (currentPageDetail - 1) * rowsPerPageDetail;
        let end = start + rowsPerPageDetail;
        let paginatedItems = displayData.slice(start, end);
        
        let html = '';
        paginatedItems.forEach(item => {
            let s = (item.status_berkas || '').toUpperCase();
            
            // --- LOGIKA WARNA DINAMIS ---
            let sClass = '';
            let sText = s;

            if (s.includes('DIMUSNAHKAN')) {
                // MERAH TEBAL UNTUK DIMUSNAHKAN
                sClass = 'text-danger fw-bold border border-danger px-2 rounded';
                sText = 'DIMUSNAHKAN';
            } else if (s.includes('DITERIMA')) {
                // HIJAU UNTUK SELESAI
                sClass = 'badge bg-success-subtle text-success border px-2';
                sText = 'SELESAI';
            } else {
                // KUNING UNTUK PROSES
                sClass = 'badge bg-warning-subtle text-warning border px-2';
                sText = s;
            }

            html += `<tr>
                <td class="text-primary fw-bold text-center py-2">${item.no_permohonan}</td>
                <td>${item.nama}</td>
                <td class="text-center">${item.jenis_permohonan || '-'}</td>
                <td class="text-center">${item.jenis_paspor || '-'}</td>
                <td class="text-center">${item.tujuan_paspor || '-'}</td>
                <td class="text-center"><span class="${sClass}" style="font-size:9px;">${sText}</span></td>
            </tr>`;
        });
        
        document.getElementById('det_list_berkas').innerHTML = html || '<tr><td colspan="6" class="text-center py-3">Data Tidak Ditemukan</td></tr>';
        
        let from = totalItems > 0 ? start + 1 : 0;
        let to = Math.min(end, totalItems);
        document.getElementById('det_pagination_info').innerHTML = `SHOWING <b>${from} - ${to}</b> OF <b>${totalItems}</b> ENTRIES`;
        
        renderJSNav('det_pagination_links', currentPageDetail, totalPages);
    }
    
    // 4. FUNGSI PENGGAMBAR TOMBOL NAVIGASI
    function renderJSNav(containerId, currentPage, totalPages) {
        const container = document.getElementById(containerId);
        if (!container) return;

        let html = '<ul class="app-pagination-list">';
        html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><span onclick="${currentPage > 1 ? `changeDetailPage(${currentPage - 1})` : ''}"><i class="fas fa-chevron-left"></i></span></li>`;
        
        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}"><span onclick="changeDetailPage(${i})">${i}</span></li>`;
        }

        html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}"><span onclick="${currentPage < totalPages ? `changeDetailPage(${currentPage + 1})` : ''}"><i class="fas fa-chevron-right"></i></span></li>`;
        html += '</ul>';
        container.innerHTML = html;
    }

    function changeDetailPage(page) {
        currentPageDetail = page;
        renderTableDetail();
    }
    
    function searchDetailBerkas() {
        let input = document.getElementById("inputSearchDetail").value.toLowerCase();
        if (input === "") {
            filteredDetailData = null;
        } else {
            filteredDetailData = currentDetailData.filter(item => {
                let noPermohonan = (item.no_permohonan || '').toString().toLowerCase();
                let namaPemohon = (item.nama || '').toString().toLowerCase();
                return noPermohonan.includes(input) || namaPemohon.includes(input);
            });
        }
        currentPageDetail = 1; 
        renderTableDetail(); 
    }
</script>

<style>
    .priority-row { background-color: #fffbe6 !important; }
    .uppercase { text-transform: uppercase; letter-spacing: 0.5px; }
    .bg-danger-subtle { background-color: #fee2e2 !important; color: #dc2626 !important; }
    .bg-success-subtle { background-color: #dcfce7 !important; color: #16a34a !important; }
    .bg-warning-subtle { background-color: #fef9c3 !important; color: #a16207 !important; }
    .bg-secondary-subtle { background-color: #f1f5f9 !important; }
    .bg-info-subtle { background-color: #e0f2fe !important; color: #0369a1 !important; }

    .app-pagination-list { 
    display: flex !important; 
    flex-direction: row !important; /* WAJIB: Biar angka 1, 2, 3 sejajar ke samping */
    justify-content: center !important;
    list-style: none !important; 
    padding: 0 !important; 
    margin: 0 !important; 
    gap: 6px !important; 
}
    .app-pagination-list .page-item span {
        display: flex; align-items: center; justify-content: center; 
        width: 34px; height: 34px; cursor: pointer; 
        background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px;
        color: #64748b; font-size: 13px; font-weight: 700; transition: all 0.2s;
    }
    .app-pagination-list .page-item.active span { 
        background-color: #3b82f6; color: #ffffff; border-color: #2563eb; 
    }
    .app-pagination-list .page-item.disabled span {
    background-color: #f8fafc !important;
    color: #cbd5e1 !important;
    cursor: not-allowed !important;
    border-color: #e2e8f0 !important;
}
</style>
@endsection