@extends('layouts.app') 

@section('title', 'Pinjam Berkas') 
@section('page-title', 'Pinjam Berkas')
@section('page-subtitle', 'Data berkas yang dipinjam dan status peminjaman.')

@section('content')
<div class="container-fluid">

    <div class="card-custom">
        <div class="table-header-custom">
            <h2 class="table-title-custom">Data Berkas yang dipinjam</h2>
            
            <div class="table-actions-custom" id="filterArea">
                {{-- TOMBOL FILTER --}}
                <button type="button" class="btn-filter-custom" onclick="toggleFilter()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/>
                    </svg>
                    Filters
                </button>

                {{-- DROPDOWN FILTER MELAYANG --}}
                <div id="filterDropdown" class="filter-dropdown-custom shadow-lg">
                    <form action="{{ route('pinjam-berkas.index') }}" method="GET">
                        <div class="filter-group-custom">
                            <label>Dari Tanggal</label>
                            <input type="date" name="from" value="{{ request('from') }}">
                        </div>
                        <div class="filter-group-custom">
                            <label>Sampai Tanggal</label>
                            <input type="date" name="to" value="{{ request('to') }}">
                        </div>
                        <button type="submit" class="btn-submit-filter">Terapkan Filter</button>
                        <a href="{{ route('pinjam-berkas.index') }}" class="btn-reset-filter">Reset</a>
                    </form>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>No. Permohonan</th>
                        <th>Tanggal Permohonan</th>
                        <th>Nama Pemohon</th>
                        <th style="width: 200px;">Divisi Peminjam</th>
                        <th>Tanggal Pinjam</th>
                        <th>Tanggal Kembali</th>
                        <th class="text-center">Aksi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataPinjam as $item)
                    <tr>
                        <td>{{ $item->permohonan->no_permohonan ?? '-' }}</td>
                        <td>{{ $item->permohonan->tanggal_permohonan ?? '-' }}</td>
                        <td>{{ $item->permohonan->nama ?? '-' }}</td>
                        <td>
                            {{-- SELECT DIVISI SPESIFIK PER BARIS --}}
                            <select class="form-select-divisi-table" onchange="updateDivisi({{ $item->id }}, this.value)">
                                <option value="Menunggu Input" {{ $item->nama_peminjam == 'Menunggu Input' ? 'selected' : '' }}>-- Pilih Divisi --</option>
                                <option value="UMUM" {{ $item->nama_peminjam == 'UMUM' ? 'selected' : '' }}>Bagian Umum</option>
                                <option value="WASDAK" {{ $item->nama_peminjam == 'WASDAK' ? 'selected' : '' }}>Wasdak</option>
                                <option value="LANTASKIM" {{ $item->nama_peminjam == 'LANTASKIM' ? 'selected' : '' }}>Lantaskim</option>
                                <option value="INTELDAKIM" {{ $item->nama_peminjam == 'INTELDAKIM' ? 'selected' : '' }}>Inteldakim</option>
                                <option value="TIKIM" {{ $item->nama_peminjam == 'TIKIM' ? 'selected' : '' }}>Tikim</option>
                            </select>
                        </td>
                        <td><span class="text-muted small">{{ $item->tgl_pinjam }}</span></td>
                        <td><span class="text-muted small">{{ $item->tgl_kembali ?? '-' }}</span></td>
                        <td>
                            <div class="aksi-wrapper" style="display:flex; gap:5px; align-items:center; justify-content:center;">
                                @if($item->status == 'Pengajuan')
                                    <form action="{{ route('pinjam-berkas.approve', $item->id) }}" method="POST" style="margin:0;">
                                        @csrf
                                        <button type="submit" class="btn-check-custom" title="Setujui">✓</button>
                                    </form>
                                    <form action="{{ route('pinjam-berkas.reject', $item->id) }}" method="POST" style="margin:0;">
                                        @csrf
                                        <button type="submit" class="btn-reject-custom" title="Tolak">✕</button>
                                    </form>
                                @elseif($item->status == 'Disetujui')
                                    <form action="{{ route('pinjam-berkas.complete', $item->id) }}" method="POST" style="margin:0;">
                                        @csrf
                                        <button type="submit" class="btn-selesai">Selesai</button>
                                    </form>
                                @endif
                                {{-- TOMBOL DETAIL MENGIRIM DATA LENGKAP KE MODAL --}}
                                <button type="button" onclick="showDetail({{ json_encode($item->permohonan) }})" class="btn-detail-blue">Detail</button>
                            </div>
                        </td>
                        <td>
                            @php
                                $statusClass = [
                                    'Pengajuan' => 'bg-pengajuan',
                                    'Disetujui' => 'bg-disetujui',
                                    'Ditolak'   => 'bg-ditolak',
                                    'Selesai'   => 'bg-selesai'
                                ][$item->status] ?? 'bg-secondary';
                            @endphp
                            <span class="badge-custom {{ $statusClass }}">{{ $item->status }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Data tidak ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL DETAIL (Disesuaikan dengan Desain Ramping & Padat) --}}
<div class="modal fade" id="modalDetailBerkas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; width: 410px; padding: 15px 25px; margin: auto;">
            <div class="modal-header border-0 p-0 mb-2">
                <h6 class="modal-title fw-bold text-secondary" style="font-size: 16px;">Detail</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 12px;"></button>
            </div>
            <div class="modal-body p-0">
                <form id="form-detail-pop">
                    @php
                        $fields = [
                            'm_no_permohonan'   => 'Nomor Permohonan',
                            'm_tgl_permohonan'  => 'Tanggal Permohonan',
                            'm_tgl_terbit'      => 'Tanggal Terbit',
                            'm_nama'            => 'Nama',
                            'm_tempat_lahir'    => 'Tempat Lahir',
                            'm_tgl_lahir'       => 'Tanggal Lahir',
                            'm_gender'          => 'Jenis Kelamin',
                            'm_telp'            => 'No Telpon',
                            'm_jns_permohonan'  => 'Jenis Permohonan',
                            'm_jns_paspor'      => 'Jenis Paspor',
                            'm_tujuan'          => 'Tujuan Paspor',
                            'm_no_paspor'       => 'No Paspor',
                            'm_alur'            => 'Alur Terakhir',
                            'm_lokasi'          => 'Lokasi Arsip'
                        ];
                    @endphp

                    @foreach($fields as $id => $label)
                    <div class="info-item-row d-flex align-items-center mb-1" style="margin-bottom: 6px !important;">
                        <label style="flex: 0 0 42%; font-size: 11px; color: #48505E; font-weight: 500;">{{ $label }}</label>
                        <div class="input-wrapper" style="flex: 0 0 58%;">
                            <input type="text" id="{{ $id }}" readonly 
                                   style="width: 100%; padding: 5px 10px; border: 1px solid #D0D5DD; border-radius: 6px; background: #FFFFFF; font-size: 11px; color: #344054; height: 28px;">
                        </div>
                    </div>
                    @endforeach
                </form>
            </div>
            <div class="modal-footer border-0 p-0 mt-3 d-flex justify-content-end">
                <button type="button" class="btn btn-danger btn-sm px-4 fw-medium" data-bs-dismiss="modal" 
                        style="background: #F97066; border: none; border-radius: 6px; font-size: 12px; padding: 7px 20px;">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .card-custom { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .table-header-custom { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; position: relative; }
    .table-title-custom { font-size: 18px; font-weight: 600; color: #383E49; margin: 0; }
    
    .table-actions-custom { display: flex; align-items: center; gap: 12px; position: relative !important; }

    /* STYLE SELECT DIVISI DI DALAM TABEL */
    .form-select-divisi-table {
        width: 100%; height: 32px; padding: 0 8px; border: 1px solid #D0D5DD; border-radius: 6px;
        background-color: #ffffff; font-family: 'Inter', sans-serif; font-size: 12px; color: #344054; cursor: pointer;
    }

    .btn-filter-custom { 
        display: flex; align-items: center; gap: 8px; background: white; border: 1px solid #D0D5DD; 
        height: 38px; padding: 0 16px; border-radius: 8px; color: #5D6679; font-size: 14px; cursor: pointer; 
    }

    .filter-dropdown-custom { 
        display: none; position: absolute; top: 45px; right: 0; background: white; 
        border: 1px solid #E4E7EC; border-radius: 12px; padding: 20px; width: 280px; 
        z-index: 1050; box-shadow: 0 10px 15px rgba(0,0,0,0.1);
    }
    .filter-dropdown-custom.show { display: block; }
    
    .btn-submit-filter { width: 100%; background: #1366D9; color: white; border: none; padding: 10px; border-radius: 8px; cursor: pointer; }

    .table-custom { width: 100%; border-collapse: collapse; font-size: 13px; }
    .table-custom th { text-align: left; padding: 12px; border-bottom: 2px solid #F0F1F3; font-weight: 600; }
    .table-custom td { padding: 12px; border-bottom: 1px solid #F0F1F3; vertical-align: middle; }
    .badge-custom { padding: 5px 12px; border-radius: 4px; font-size: 11px; font-weight: 600; color: white; }
    .bg-pengajuan { background-color: #FFCC00; }
    .bg-disetujui { background-color: #34C759; }
    .bg-ditolak { background-color: #FF383C; }
    .bg-selesai { background-color: #0088FF; }
    .btn-check-custom { width: 28px; height: 28px; background: #34C759; color: white; border: none; border-radius: 6px; cursor: pointer; }
    .btn-reject-custom { width: 28px; height: 28px; background: #FF383C; color: white; border: none; border-radius: 4px; cursor: pointer; }
    .btn-detail-blue { background:#629FF4; color:white; border:none; padding:0 10px; border-radius:4px; font-size:11px; height:28px; cursor:pointer; }
</style>

<script>
// AJAX UPDATE DIVISI
function updateDivisi(id, divisiBaru) {
    if (divisiBaru === "Menunggu Input") return;
    fetch(`/pinjam-berkas/update-divisi/${id}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ divisi: divisiBaru })
    })
    .then(response => response.json())
    .then(data => { if(data.success) console.log('Update Berhasil'); });
}

// MENGISI DATA KE MODAL DETAIL RAMPING
function showDetail(item) {
    if(!item) return;
    document.getElementById('m_no_permohonan').value = item.no_permohonan || '-';
    document.getElementById('m_tgl_permohonan').value = item.tanggal_permohonan || '-';
    document.getElementById('m_tgl_terbit').value = item.tanggal_terbit || '-';
    document.getElementById('m_nama').value = item.nama || '-';
    document.getElementById('m_tempat_lahir').value = item.tempat_lahir || '-';
    document.getElementById('m_tgl_lahir').value = item.tanggal_lahir || '-';
    document.getElementById('m_gender').value = item.jenis_kelamin || '-';
    document.getElementById('m_telp').value = item.no_telp || '-';
    document.getElementById('m_jns_permohonan').value = item.jenis_permohonan || '-';
    document.getElementById('m_jns_paspor').value = item.jenis_paspor || '-';
    document.getElementById('m_tujuan').value = item.tujuan_paspor || '-';
    document.getElementById('m_no_paspor').value = item.no_paspor || '-';
    document.getElementById('m_alur').value = item.status_berkas || '-';
    document.getElementById('m_lokasi').value = item.lokasi_arsip || '-';
    new bootstrap.Modal(document.getElementById('modalDetailBerkas')).show();
}

function toggleFilter() { document.getElementById('filterDropdown').classList.toggle('show'); }
</script>
@endsection