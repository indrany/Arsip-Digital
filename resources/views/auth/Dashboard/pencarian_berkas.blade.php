@extends('layouts.app')

@section('page-title', 'Pencarian Berkas')
@section('page-subtitle', 'Cari rincian berkas arsip secara instan.')

@section('content')
<div class="container-fluid px-4">

{{-- SEARCH BAR SEJAJAR HASIL --}}
<div class="mb-3">
    <form action="{{ route('pencarian-berkas.search') }}" method="GET">
        <div class="d-flex align-items-center gap-2"
             style="max-width: 760px;">
            
            <div class="input-group shadow-sm"
                 style="border-radius:8px; overflow:hidden; border:1px solid #dee2e6; width:100%;">
                <span class="input-group-text bg-white border-0 text-muted">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text"
                       name="nomor_permohonan"
                       class="form-control border-0"
                       placeholder="Nomor permohonan atau nama pemohon"
                       value="{{ request('nomor_permohonan') }}"
                       style="font-size:13.5px; box-shadow:none;">
            </div>

            <button class="btn btn-primary fw-semibold"
                    style="height:38px; padding:0 24px; font-size:13px;">
                Cari
            </button>
        </div>
    </form>
</div>

    {{-- HASIL PENCARIAN --}}
    <div class="card shadow-sm border-0 mt-2" style="border-radius:12px;">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center border-bottom">
            <h6 class="mb-0 fw-bold text-dark" style="font-size:14px;">
                <i class="fas fa-list-ul me-2 text-primary"></i>Hasil Pencarian
            </h6>

            <div class="dropdown">
                <button class="btn btn-light btn-sm border d-flex align-items-center gap-2"
                        type="button"
                        data-bs-toggle="dropdown"
                        style="border-radius:6px; font-size:12px;">
                    <i class="fas fa-filter text-primary"></i> Filters
                </button>
                <div class="dropdown-menu dropdown-menu-end p-3 shadow-lg border-0"
                     style="width:280px; border-radius:10px;">
                    <form action="{{ route('pencarian-berkas.search') }}" method="GET">
                        <input type="hidden" name="nomor_permohonan" value="{{ request('nomor_permohonan') }}">
                        <label class="form-label small fw-bold text-muted mb-2">RANGE TANGGAL</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="date" name="start_date" class="form-control form-control-sm"
                                       value="{{ request('start_date') }}">
                            </div>
                            <div class="col-6">
                                <input type="date" name="end_date" class="form-control form-control-sm"
                                       value="{{ request('end_date') }}">
                            </div>
                        </div>
                        <div class="d-grid gap-2 mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                            <a href="{{ route('pencarian-berkas.index') }}"
                               class="btn btn-light btn-sm border">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:13px;">
                    <thead class="bg-light">
                        <tr class="text-muted text-uppercase">
                            <th class="ps-4 py-2 border-0">Nomor Permohonan</th>
                            <th class="py-2 border-0">Nama Pemohon</th>
                            <th class="text-center py-2 border-0" style="width:120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(collect($results ?? []) as $item)
                        <tr>
                            <td class="ps-4 py-2 fw-bold text-primary">
                                {{ $item->no_permohonan }}
                            </td>
                            <td class="text-dark">
                                {{ strtoupper($item->nama ?? '-') }}
                            </td>
                            <td class="text-center py-2">
                                <button type="button"
                                        class="btn btn-primary btn-sm px-3"
                                        onclick="showDetail(JSON.parse(atob('{{ base64_encode(json_encode($item)) }}')))"
                                        style="font-size:11px; border-radius:6px;">
                                    Detail
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png"
                                     width="60"
                                     class="opacity-25 mb-2"
                                     style="filter:grayscale(1);">
                                <p class="text-muted small mb-0">
                                    Data tidak ditemukan atau silakan masukkan nomor permohonan atau nama pemohon.
                                </p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DETAIL (Didesain Ramping & Profesional) --}}
<div class="modal fade" id="modalDetailBerkas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; width: 420px; margin: auto;">
            <div class="modal-header border-0 pb-0 pt-3 px-4" style="display: flex; justify-content: space-between; align-items: center;">
                <h6 class="modal-title fw-bold text-secondary" style="font-size: 16px; margin: 0;">Detail Permohonan</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 10px;"></button>
            </div>
            <div class="modal-body pt-3 pb-2 px-4">
                <form id="form-detail-pop">
                    @php
                        $fields = [
                            'nomor'        => 'Nomor Permohonan',
                            'tgl-mohon'    => 'Tanggal Permohonan',
                            'tgl-terbit'   => 'Tanggal Terbit',
                            'nama'         => 'Nama',
                            'tempat-lahir' => 'Tempat Lahir',
                            'tgl-lahir'    => 'Tanggal Lahir',
                            'jk'           => 'Jenis Kelamin',
                            'telp'         => 'No Telpon',
                            'jenis-mohon'  => 'Jenis Permohonan',
                            'jenis-paspor' => 'Jenis Paspor',
                            'tujuan'       => 'Tujuan Paspor',
                            'no-paspor'    => 'No Paspor',
                            'alur'         => 'Alur Terakhir',
                            'lokasi'       => 'Lokasi Arsip'
                        ];
                    @endphp
                    @foreach($fields as $id => $label)
                    <div class="row mb-1 align-items-center">
                        <label style="flex: 0 0 42%; font-size: 11.5px; color: #667085; font-weight: 500;">{{ $label }}</label>
                        <div style="flex: 1;">
                            <input type="text" id="det-{{ $id }}" readonly class="form-control form-control-sm bg-white"
                                   style="border: 1px solid #D0D5DD; border-radius: 6px; font-size: 12px; color: #344054; padding: 4px 10px; height: 32px;">
                        </div>
                    </div>
                    @endforeach
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 pb-3 px-4 d-flex justify-content-end">
                <button type="button" class="btn btn-danger btn-sm shadow-sm text-white fw-bold" data-bs-dismiss="modal" 
                        style="background-color: #F97066; border: none; border-radius: 8px; font-size: 12px; padding: 7px 25px;">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showDetail(item) {
    document.getElementById('det-nomor').value = item.no_permohonan || '-';
    document.getElementById('det-tgl-mohon').value = item.tanggal_permohonan || '-';
    document.getElementById('det-tgl-terbit').value = item.tanggal_terbit || '-';
    document.getElementById('det-nama').value = item.nama || '-';
    document.getElementById('det-tempat-lahir').value = item.tempat_lahir || '-';
    document.getElementById('det-tgl-lahir').value = item.tanggal_lahir || '-';
    document.getElementById('det-jk').value = item.jenis_kelamin || '-';
    document.getElementById('det-telp').value = item.no_telp || '-';
    document.getElementById('det-jenis-mohon').value = item.jenis_permohonan || '-';
    document.getElementById('det-jenis-paspor').value = item.jenis_paspor || '-';
    document.getElementById('det-tujuan').value = item.tujuan_paspor || '-';
    document.getElementById('det-no-paspor').value = item.no_paspor || '-';
    document.getElementById('det-alur').value = item.alur_terakhir || '-';
    document.getElementById('det-lokasi').value = item.lokasi_arsip || '-';

    var modal = new bootstrap.Modal(document.getElementById('modalDetailBerkas'));
    modal.show();
}
</script>
@endpush