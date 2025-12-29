@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header Section --}}
    <div class="mb-4">
        <h4 class="fw-bold text-dark">Pencarian Berkas</h4>
        <p class="text-muted small">Cari rincian berkas arsip berdasarkan nomor permohonan atau nama pemohon secara instan.</p>
        
        <form action="{{ route('pencarian-berkas.search') }}" method="GET" class="mt-3">
            <div class="input-group shadow-sm" style="max-width: 550px; border-radius: 12px; overflow: hidden; border: 1px solid #e0e0e0;">
                <span class="input-group-text bg-white border-0 text-muted ps-3"><i class="fas fa-search"></i></span>
                <input type="text" name="nomor_permohonan" class="form-control border-0 ps-2 py-2" 
                       placeholder="Masukkan Nomor Permohonan atau Nama..." value="{{ request('nomor_permohonan') }}" style="font-size: 14px; outline: none;">
                <button class="btn btn-primary px-4 fw-bold" type="submit" style="background-color: #2D9CDB; border: none;">Cari</button>
            </div>
        </form>
    </div>

    {{-- Hasil Pencarian Section --}}
    <div class="card shadow-sm border-0 mt-4" style="border-radius: 16px; overflow: hidden;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <h6 class="mb-0 fw-bold text-dark">Hasil Pencarian Berkas</h6>
            
            <div class="dropdown">
                <button class="btn btn-light btn-sm border d-flex align-items-center gap-2 fw-semibold" type="button" data-bs-toggle="dropdown" style="border-radius: 8px; color: #5D6679;">
                    <i class="fas fa-filter small"></i> Filters
                </button>
                <div class="dropdown-menu dropdown-menu-end p-3 shadow-lg border-0" style="width: 300px; border-radius: 12px;">
                    <form action="{{ route('pencarian-berkas.search') }}" method="GET">
                        <input type="hidden" name="nomor_permohonan" value="{{ request('nomor_permohonan') }}">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase" style="font-size: 10px;">Range Tanggal</label>
                            <div class="d-flex gap-2">
                                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold" style="background-color: #2D9CDB; border: none;">Apply</button>
                            <a href="{{ route('pencarian-berkas.index') }}" class="btn btn-light btn-sm border w-100 fw-bold">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-muted small text-uppercase">
                            <th class="ps-4 py-3 border-0" style="letter-spacing: 0.5px;">Nomor Permohonan</th>
                            <th class="py-3 border-0" style="letter-spacing: 0.5px;">Nama Pemohon</th>
                            <th class="text-center py-3 border-0" style="letter-spacing: 0.5px; width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(collect($results ?? []) as $item)
                        <tr class="border-bottom">
                            <td class="ps-4 py-3 fw-bold text-primary" style="font-size: 14px;">{{ $item->no_permohonan }}</td>
                            <td class="text-dark fw-medium" style="font-size: 14px;">{{ $item->nama ?? '-' }}</td>
                            <td class="text-center py-3">
                                <button type="button" class="btn btn-sm px-4 fw-bold shadow-sm text-white" 
                                        onclick="showDetail(JSON.parse(atob('{{ base64_encode(json_encode($item)) }}')))"
                                        style="background-color: #629FF4; border: none; border-radius: 6px; font-size: 11px; height: 28px;">
                                    Detail
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-2x mb-3 opacity-25 d-block"></i>
                                <span class="small">Data tidak ditemukan atau silakan masukkan keyword.</span>
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
                <h6 class="modal-title fw-bold text-secondary" style="font-size: 16px; margin: 0;">Detail</h6>
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