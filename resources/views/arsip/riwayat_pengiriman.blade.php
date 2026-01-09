@extends('layouts.app')

@section('page-title', 'Riwayat Pengiriman Berkas')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            {{-- Tombol Navigasi & Cetak Global --}}
            <div class="d-flex justify-content-start align-items-center mb-4 gap-2">
                <a href="{{ route('pengiriman-berkas.create') }}" class="btn btn-primary fw-bold">
                    <i class="fas fa-plus-circle me-1"></i> Tambah Pengiriman
                </a>
                <button type="button" class="btn btn-success fw-bold" id="btnCetakGlobal">
                    <i class="fas fa-print me-1"></i> Cetak Surat Pengantar
                </button>
            </div>

            <h6 class="fw-bold mb-3"><i class="fas fa-history me-2"></i>Tabel Riwayat Pengiriman Berkas</h6>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No. Permohonan</th>
                            <th>Nama Pemohon</th>
                            <th>Asal Unit</th>
                            <th>Tanggal Kirim</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayat as $row)
                        <tr>
                            <td class="fw-bold text-primary">{{ $row->no_permohonan }}</td>
                            <td class="text-dark">{{ $row->nama }}</td>
                            <td><span class="badge bg-info text-dark">{{ $row->asal_unit ?? 'Kanim' }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($row->tgl_pengirim)->format('d-m-Y') }}</td>
                            <td>
                                @if($row->status == 'Diajukan')
                                    <span class="badge bg-warning text-dark">Diajukan</span>
                                @else
                                    <span class="badge bg-success">DITERIMA</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{-- Tombol untuk memicu modal detail --}}
                                <button class="btn btn-outline-primary btn-sm btn-detail-gabungan" data-batch="{{ $row->batch_id }}">
                                    <i class="fas fa-eye me-1"></i> Detail & List Berkas
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">Belum ada riwayat pengiriman ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL GABUNGAN (DETAIL + LIST TABEL) --}}
<div class="modal fade" id="modalDetailGabungan" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-alt me-2"></i>Detail Batch Pengiriman</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Box Info Header Modal --}}
                <div class="row g-3 mb-4 p-3 bg-light rounded border mx-0">
                    <div class="col-md-3">
                        <label class="text-muted small d-block">ID Batch (No Pengirim)</label>
                        <span id="v_no_pengirim" class="fw-bold text-primary h6">-</span>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small d-block">Tanggal Pengiriman</label>
                        <span id="v_tgl_pengirim_modal" class="fw-bold h6">-</span>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small d-block">Status Batch</label>
                        <div id="v_status_badge"></div>
                    </div>
                    <div class="col-md-3 text-md-end d-flex align-items-center justify-content-md-end">
                    </div>
                </div>

                <h6 class="fw-bold mb-3 ms-1 text-dark"><i class="fas fa-list me-2 text-primary"></i>Daftar Berkas Terlampir</h6>
                
                {{-- Tabel Daftar Berkas di dalam Modal --}}
                <div class="table-responsive rounded border">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3">No Permohonan</th>
                                <th>Nama Pemohon</th>
                                <th>Jenis Permohonan</th>
                                <th>Jenis Paspor</th>
                                <th>Tujuan</th>
                                <th class="text-center">Status Berkas</th>
                            </tr>
                        </thead>
                        <tbody id="v_list_body">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat daftar berkas...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let activeBatchId = null;

    /* 1. LOGIKA MENAMPILKAN DETAIL BATCH PADA MODAL */
    $(document).on('click', '.btn-detail-gabungan', function () {
        const batchId = $(this).data('batch');
        activeBatchId = batchId;

        $('#v_no_pengirim').text(batchId);
        $('#v_list_body').html('<tr><td colspan="6" class="text-center text-muted py-5"><i class="fas fa-spinner fa-spin me-2"></i>Mengambil data berkas...</td></tr>');
        $('#modalDetailGabungan').modal('show');

        // Request AJAX ke controller listBerkas
        $.get(`/arsip/list-berkas/${batchId}`, function (res) {
            let html = '';
            
            if (res.success && res.data.length > 0) {
                // Update Info di Header Modal
                $('#v_tgl_pengirim_modal').text(res.batch ? res.batch.tgl_pengirim : '-');
                
                let status = res.batch ? res.batch.status : 'Diajukan';
                let badgeClass = (status === 'Diajukan') ? 'bg-warning text-dark' : 'bg-success';
                $('#v_status_badge').html(`<span class="badge ${badgeClass}">${status}</span>`);

                // Generate baris tabel dari data yang diterima
                res.data.forEach(item => {
                    html += `<tr>
                        <td class="fw-bold text-primary ps-3">${item.no_permohonan}</td>
                        <td class="text-dark">${item.nama}</td>
                        <td class="small">${item.jenis_permohonan || '-'}</td>
                        <td class="small">${item.jenis_paspor || '-'}</td> 
                        <td class="fw-bold text-secondary">${item.tujuan_paspor || '-'}</td>
                        <td class="text-center"><span class="badge bg-info text-dark" style="font-size: 0.7rem;">${item.status_berkas}</span></td>
                    </tr>`;
                });
            } else {
                html = '<tr><td colspan="6" class="text-center text-muted py-4">Data berkas tidak ditemukan.</td></tr>';
            }
            $('#v_list_body').html(html);
        }).fail(function() {
            $('#v_list_body').html('<tr><td colspan="6" class="text-center text-danger py-4">Gagal terhubung ke server.</td></tr>');
        });
    });

    /* 2. LOGIKA CETAK GLOBAL (Batch Terbaru) */
    $('#btnCetakGlobal').on('click', function() {
        let lastBatchId = "{{ $riwayat->first()->batch_id ?? '' }}";
        if (lastBatchId !== "") {
            window.open("/arsip/cetak-pengantar/" + lastBatchId, "_blank");
        } else {
            alert("Tidak ada riwayat untuk dicetak.");
        }
    });

    /* 3. LOGIKA CETAK DARI DALAM MODAL */
    $('#btnCetakDariModal').on('click', function() {
        if (activeBatchId) {
            window.open("/arsip/cetak-pengantar/" + activeBatchId, "_blank");
        }
    });
});
</script>

<style>
    /* Styling agar modal terlihat lebih modern dan tabel bisa discroll */
    .modal-content { border-radius: 12px; overflow: hidden; }
    .table-responsive { max-height: 450px; overflow-y: auto; }
    .table thead th { position: sticky; top: 0; z-index: 10; border-top: 0; }
    .bg-light { background-color: #f8f9fa !important; }
    .badge { padding: 0.5em 1em; font-weight: 600; }
</style>
@endpush