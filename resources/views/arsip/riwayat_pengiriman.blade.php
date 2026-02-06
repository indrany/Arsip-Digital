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

                {{-- FILTER TANGGAL --}}
                <div class="col-md-2">
                    <input type="date" id="filterTanggal" class="form-control form-control-sm bg-light" title="Filter Tanggal Kirim">
                </div>

                {{-- FILTER STATUS --}}
                <div class="col-md-3">
                    <select id="filterStatus" class="form-select form-select-sm bg-light fw-bold">
                        <option value="">-- Semua Status --</option>
                        <option value="DIAJUKAN">⚠️ BELUM DITINDAKLANJUTI</option>
                        <option value="DITERIMA OLEH ARSIP">✅ SUDAH DITERIMA</option>
                    </select>
                </div>

                {{-- SEARCH BAR --}}
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
                        {{-- URUTKAN: 'Diajukan' paling atas, lalu 'created_at' terbaru --}}
                        @php
                            $sortedRiwayat = $riwayat->sortByDesc(function($item) {
                                return $item->status === 'Diajukan' ? 1 : 0;
                            });
                        @endphp

                        @forelse($sortedRiwayat as $row)
                        <tr class="data-row {{ $row->status === 'Diajukan' ? 'priority-row' : '' }}" 
                            data-tanggal="{{ \Carbon\Carbon::parse($row->tgl_pengirim)->format('Y-m-d') }}" 
                            data-status="{{ strtoupper($row->status) }}">
                            
                            <td class="ps-4 fw-bold text-primary">{{ $row->no_pengirim }}</td>
                            <td>
                                <a href="#" class="text-decoration-none fw-bold text-dark" title="Klik untuk filter tanggal ini">
                                    {{ \Carbon\Carbon::parse($row->tgl_pengirim)->format('d-m-Y') }}
                                </a>
                            </td>
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
        </div>
    </div>
</div>

{{-- MODAL DETAIL BATCH (PASTE KODE MODAL ASLI ANDA DI SINI) --}}
<div class="modal fade" id="modalDetailBatch" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content shadow border-0" style="border-radius: 12px;">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h6 class="modal-title fw-bold m-0"><i class="fas fa-file-alt me-2"></i>Detail Batch Pengiriman</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row bg-light p-3 rounded mb-4 g-3 border">
                    <div class="col-md-4">
                        <label class="d-block text-muted small fw-bold">ID Batch (No Pengirim)</label>
                        <span id="det_no_pengirim" class="fw-bold text-primary" style="font-size: 16px;">-</span>
                    </div>
                    <div class="col-md-4">
                        <label class="d-block text-muted small fw-bold">Tanggal Pengiriman</label>
                        <span id="det_tgl_pengirim" class="fw-bold" style="font-size: 16px;">-</span>
                    </div>
                    <div class="col-md-4">
                        <label class="d-block text-muted small fw-bold">Status Batch</label>
                        <div id="det_status_wrapper">
                             <span id="det_status" class="badge bg-warning text-dark">-</span>
                        </div>
                    </div>
                </div>
                <h6 class="fw-bold mb-3 d-flex align-items-center"><i class="fas fa-list-ul me-2 text-primary"></i>Daftar Berkas Terlampir</h6>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="bg-dark text-white text-center" style="font-size: 12px;">
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
            </div>
            <div class="modal-footer border-0 p-3">
                <button type="button" class="btn btn-secondary px-4 fw-bold shadow-sm" data-bs-dismiss="modal" style="border-radius: 8px;">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // FUNGSI KOMBINASI FILTER (SEARCH + TANGGAL + STATUS)
    function applyFilters() {
        var searchValue = $("#inputSearchRiwayat").val().toLowerCase();
        var filterDate = $("#filterTanggal").val();
        var filterStatus = $("#filterStatus").val();

        $("#tableRiwayat tbody tr:not(.no-data-row)").each(function() {
            var rowText = $(this).text().toLowerCase();
            var rowDate = $(this).data('tanggal');
            var rowStatus = $(this).data('status');

            var matchSearch = rowText.indexOf(searchValue) > -1;
            var matchDate = (filterDate === "") || (rowDate === filterDate);
            var matchStatus = (filterStatus === "") || (rowStatus === filterStatus);

            if (matchSearch && matchDate && matchStatus) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    $("#inputSearchRiwayat").on("keyup", applyFilters);
    $("#filterTanggal, #filterStatus").on("change", applyFilters);
    
    // Reset Button
    $("#btnResetFilter").on("click", function() {
        $("#inputSearchRiwayat, #filterTanggal, #filterStatus").val("");
        $("#tableRiwayat tbody tr").show();
    });
});

// FUNGSI DETAIL BATCH
function lihatDetailBatch(noPengirim) {
    Swal.fire({ title: 'Mengambil data...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
    fetch(`/arsip/list-berkas/${noPengirim}`)
        .then(response => response.json())
        .then(res => {
            Swal.close();
            if(res.success) {
                document.getElementById('det_no_pengirim').innerText = res.batch.no_pengirim;
                document.getElementById('det_tgl_pengirim').innerText = res.batch.tgl_pengirim;
                document.getElementById('det_status').innerText = res.batch.status;
                let html = '';
                res.data.forEach(item => {
                    html += `<tr><td class="text-primary fw-bold text-center py-2">${item.no_permohonan}</td><td>${item.nama}</td><td class="text-center">${item.jenis_permohonan}</td><td class="text-center">${item.jenis_paspor}</td><td class="text-center">${item.tujuan_paspor || '-'}</td><td class="text-center"><span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2">${item.status_berkas}</span></td></tr>`;
                });
                document.getElementById('det_list_berkas').innerHTML = html;
                var myModal = new bootstrap.Modal(document.getElementById('modalDetailBatch'));
                myModal.show();
            }
        });
}
</script>

<style>
    /* 1. MENGHILANGKAN GARIS HITAM & OUTLINE PADA SEARCH BAR */
    .input-group, .form-control, .form-select, .btn {
        outline: none !important;
        box-shadow: none !important;
        border-color: #f1f5f9; /* Warna border yang sangat halus */
    }

    .input-group-text {
        border: none;
    }

    /* 2. BARIS PRIORITAS (BELUM TINDAK LANJUT) */
    .priority-row { 
        background-color: #fffbe6 !important; /* Kuning sangat muda agar tidak sakit mata */
        transition: background-color 0.3s ease;
    }
    
    .priority-row:hover {
        background-color: #fff3bf !important; /* Sedikit lebih gelap saat hover */
    }

    /* 3. STYLING TABEL & BADGE */
    .bg-info-subtle { background-color: #e0f2fe !important; }
    .text-info { color: #0369a1 !important; }
    .bg-secondary-subtle { background-color: #f1f5f9 !important; }
    .bg-primary-subtle { background-color: #e0e7ff !important; }
    .uppercase { text-transform: uppercase; letter-spacing: 0.5px; }

    .table thead th { 
        position: sticky; 
        top: 0; 
        background: #f8f9fa; 
        z-index: 10; 
        border-bottom: 2px solid #dee2e6; 
    }

    .modal-content { border-radius: 15px; }
    .badge { padding: 0.5em 1em; }

    /* 4. MERAPIKAN INPUT DATE & SELECT */
    input[type="date"], select {
        cursor: pointer;
    }

    /* Menghilangkan border hitam bawaan browser saat focus */
    .form-control:focus, .form-select:focus {
        background-color: #fff !important;
        border-color: #3b82f6 !important;
    }
</style>
@endsection