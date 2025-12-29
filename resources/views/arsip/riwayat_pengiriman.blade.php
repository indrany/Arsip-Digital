@extends('layouts.app')

@section('page-title', 'Pengiriman Berkas')
@section('page-subtitle', 'Silakan input data permohonan untuk pengiriman baru')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-body">

            {{-- Tombol Tambah --}}
            <div class="mb-4">
                <a href="{{ route('pengiriman-berkas.create') }}" class="btn btn-primary fw-bold">
                    <i class="fas fa-plus-circle me-1"></i> Pengiriman Berkas
                </a>
            </div>

            <h6 class="fw-bold mb-3">Tabel Riwayat Pengiriman Berkas</h6>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No. Pengirim</th>
                            <th>Tanggal Pengirim</th>
                            <th>Tanggal Diterima</th>
                            <th>Jumlah Berkas</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayat as $row)
                        <tr>
                            <td class="fw-bold">{{ $row->no_pengirim }}</td>

                            <td>{{ \Carbon\Carbon::parse($row->tgl_pengirim)->format('d-m-Y') }}</td>

                            <td>
                                {{ $row->tgl_diterima
                                    ? \Carbon\Carbon::parse($row->tgl_diterima)->format('d-m-Y')
                                    : '-' }}
                            </td>

                            <td>{{ $row->jumlah_berkas }}</td>

                            <td>
                                @if($row->status == 'Diajukan')
                                    <span class="badge bg-warning text-dark">Diajukan</span>
                                @else
                                    <span class="badge bg-success">DITERIMA</span>
                                @endif
                            </td>

                            <td class="text-center">
                                {{-- DETAIL --}}
                                <button
                                    class="btn btn-primary btn-sm btn-detail-native"
                                    data-item='@json($row)'
                                >
                                    Detail
                                </button>

                                {{-- LIST BERKAS --}}
                                <button
                                    class="btn btn-outline-primary btn-sm btn-list-berkas"
                                    data-no_pengirim="{{ $row->no_pengirim }}"
                                >
                                    List Berkas
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Belum ada riwayat pengiriman.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

{{-- ================================================= --}}
{{-- MODAL DETAIL PENGIRIMAN --}}
{{-- ================================================= --}}
<div class="modal fade" id="modalDetailBerkas" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Detail Pengiriman Berkas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label>No Pengirim</label>
                        <input type="text" id="m_no_permohonan" class="form-control" readonly>
                    </div>

                    <div class="col-md-6">
                        <label>Tanggal Pengirim</label>
                        <input type="text" id="m_tgl_permohonan" class="form-control" readonly>
                    </div>

                    <div class="col-md-6">
                        <label>Tanggal Diterima</label>
                        <input type="text" id="m_tgl_terbit" class="form-control" readonly>
                    </div>

                    <div class="col-md-6">
                        <label>Jumlah Berkas</label>
                        <input type="text" id="m_jumlah" class="form-control" readonly>
                    </div>

                    <div class="col-md-12">
                        <label>Status</label>
                        <input type="text" id="m_alur" class="form-control" readonly>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>

        </div>
    </div>
</div>

{{-- ================================================= --}}
{{-- MODAL LIST BERKAS --}}
{{-- ================================================= --}}
<div class="modal fade" id="modalListBerkas" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    List Berkas Pengiriman
                    <span id="lblNoPengirim" class="text-primary"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>No Permohonan</th>
                            <th>Nama</th>
                            <th>Jenis Permohonan</th>
                            <th>Jenis Paspor</th>
                            <th>Tujuan</th>
                            <th>Status Berkas</th>
                        </tr>
                    </thead>
                    <tbody id="listBerkasBody">
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                Memuat data...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
/* ================= DETAIL ================= */
$(document).on('click', '.btn-detail-native', function () {
    const data = $(this).data('item');

    $('#m_no_permohonan').val(data.no_pengirim || '-');
    $('#m_tgl_permohonan').val(data.tgl_pengirim || '-');
    $('#m_tgl_terbit').val(data.tgl_diterima || '-');
    $('#m_jumlah').val(data.jumlah_berkas || '-');
    $('#m_alur').val(data.status || '-');

    $('#modalDetailBerkas').modal('show');
});

/* ================= LIST BERKAS ================= */
$(document).on('click', '.btn-list-berkas', function () {
    const noPengirim = $(this).data('no_pengirim');

    $('#lblNoPengirim').text(noPengirim);
    $('#listBerkasBody').html(`
        <tr>
            <td colspan="6" class="text-center text-muted">
                Memuat data...
            </td>
        </tr>
    `);

    $('#modalListBerkas').modal('show');

    $.get(`/arsip/list-berkas/${noPengirim}`, function (res) {
        let html = '';

        if (res.data.length === 0) {
            html = `
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        Tidak ada berkas
                    </td>
                </tr>
            `;
        } else {
            res.data.forEach(item => {
                html += `
                    <tr>
                        <td>${item.no_permohonan}</td>
                        <td>${item.nama}</td>
                        <td>${item.jenis_permohonan}</td>
                        <td>${item.jenis_paspor}</td>
                        <td>${item.tujuan_paspor ?? '-'}</td>
                        <td>
                            <span class="badge bg-info">
                                ${item.status_berkas}
                            </span>
                        </td>
                    </tr>
                `;
            });
        }

        $('#listBerkasBody').html(html);
    });
});
</script>
@endpush
