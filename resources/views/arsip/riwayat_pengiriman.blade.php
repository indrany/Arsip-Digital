@extends('layouts.app')

@section('page-title', 'Pengiriman Berkas')
@section('page-subtitle', 'Silakan input data permohonan untuk pengiriman baru')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-body">

            {{-- HEADER TOMBOL: Cetak diletakkan di samping tombol tambah --}}
            <div class="d-flex justify-content-start align-items-center mb-4 gap-2">
                <a href="{{ route('pengiriman-berkas.create') }}" class="btn btn-primary fw-bold">
                    <i class="fas fa-plus-circle me-1"></i> Pengiriman Berkas
                </a>
                
                {{-- TOMBOL CETAK: Membuka tab cetak untuk batch terbaru --}}
                <button type="button" class="btn btn-success fw-bold" id="btnCetakGlobal">
                    <i class="fas fa-print me-1"></i> Cetak Pengantar
                </button>
            </div>

            <h6 class="fw-bold mb-3">Tabel Riwayat Pengiriman Berkas</h6>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No. Pengirim</th>
                            <th>Asal Unit</th> {{-- Tambahkan ini --}}
                            <th>Petugas Kirim</th> {{-- Tambahkan ini --}}
                            <th>Tanggal Pengirim</th>
                            <th>Jumlah Berkas</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayat as $row)
                        <tr>
                            <td class="fw-bold text-primary">{{ $row->no_pengirim }}</td>
                            <td><span class="badge bg-info text-dark">{{ $row->asal_unit ?? 'Kanim' }}</span></td>
                            <td>{{ $row->petugas_kirim ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->tgl_pengirim)->format('d-m-Y') }}</td>
                            <td>
                                {{ $row->tgl_diterima ? \Carbon\Carbon::parse($row->tgl_diterima)->format('d-m-Y') : '-' }}
                            </td>
                            <td>{{ $row->jumlah_berkas }} Berkas</td>
                            <td>
                                @if($row->status == 'Diajukan')
                                    <span class="badge bg-warning text-dark">Diajukan</span>
                                @else
                                    <span class="badge bg-success">DITERIMA</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button class="btn btn-primary btn-sm btn-detail-gabungan" data-item='@json($row)'>
                                    <i class="fas fa-eye me-1"></i> Detail & List Berkas
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada riwayat pengiriman.</td>
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
            <div class="modal-header bg-light border-0">
                <h5 class="modal-title fw-bold text-dark">Detail & Daftar Berkas Pengiriman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-4 p-3 bg-aliceblue rounded border mx-1">
                    <div class="col-md-3">
                        <label class="text-muted small d-block">No Pengirim</label>
                        <span id="v_no_pengirim" class="fw-bold text-primary h6">-</span>
                    </div>
                    <div class="col-md-3">
                        <label class="small d-block">Tanggal Pengirim</label>
                        <span id="v_tgl_pengirim" class="fw-bold h6">-</span>
                    </div>
                    <div class="col-md-3">
                        <label class="small d-block">Tanggal Diterima</label>
                        <span id="v_tgl_diterima" class="fw-bold h6">-</span>
                    </div>
                    <div class="col-md-3">
                        <label class="small d-block">Status</label>
                        <span id="v_status" class="fw-bold h6">-</span>
                    </div>
                </div>

                <hr class="my-4 opacity-25">

                <h6 class="fw-bold mb-3 ms-1"><i class="fas fa-list me-2 text-primary"></i>Daftar Berkas Terlampir</h6>
                <div class="table-responsive rounded border mx-1">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No Permohonan</th>
                                <th>Nama</th>
                                <th>Jenis Permohonan</th>
                                <th>Jenis Paspor</th>
                                <th>Tujuan</th> {{-- Kolom Tujuan --}}
                                <th>Status Berkas</th>
                            </tr>
                        </thead>
                        <tbody id="v_list_body">
                            <tr>
                                <td colspan="6" class="text-center text-muted">Memuat daftar berkas...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {

    /* ================= LOGIKA GABUNGAN DETAIL & LIST ================= */
    $(document).on('click', '.btn-detail-gabungan', function () {
        const data = $(this).data('item');

        $('#v_no_pengirim').text(data.no_pengirim);
        $('#v_tgl_pengirim').text(data.tgl_pengirim);
        $('#v_tgl_diterima').text(data.tgl_diterima || '-');
        $('#v_status').text(data.status);

        $('#v_list_body').html('<tr><td colspan="6" class="text-center text-muted">Memuat data...</td></tr>');
        $('#modalDetailGabungan').modal('show');

        $.get(`/arsip/list-berkas/${data.no_pengirim}`, function (res) {
            let html = '';
            if (!res.data || res.data.length === 0) {
                html = '<tr><td colspan="6" class="text-center text-muted">Tidak ada berkas ditemukan</td></tr>';
            } else {
                res.data.forEach(item => {
                    html += `<tr>
                        <td class="fw-bold text-primary">${item.no_permohonan}</td>
                        <td>${item.nama}</td>
                        <td>${item.jenis_permohonan}</td>
                        <td>${item.jenis_paspor}</td> 
                        <td class="fw-bold text-secondary">${item.tujuan_paspor || '-'}</td> {{-- Menampilkan UMROH/WISATA --}}
                        <td><span class="badge bg-info">${item.status_berkas}</span></td>
                    </tr>`;
                });
            }
            $('#v_list_body').html(html);
        });
    });

    /* ================= LOGIKA CETAK GLOBAL ================= */
    $('#btnCetakGlobal').on('click', function() {
        let noPengirim = "{{ $riwayat->first()->no_pengirim ?? '' }}";
        
        if (noPengirim !== "") {
            window.open("/arsip/cetak-pengantar/" + noPengirim, "_blank");
        } else {
            alert("Belum ada riwayat pengiriman yang dapat dicetak.");
        }
    });

});
</script>

<style>
    .bg-aliceblue { background-color: #f0f8ff; }
    .modal-header .btn-close { font-size: 0.8rem; }
    .table-responsive { border-radius: 8px; overflow: hidden; }
</style>
@endpush