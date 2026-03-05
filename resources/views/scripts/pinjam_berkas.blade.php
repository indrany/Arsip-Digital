@extends('layouts.app') 
@section('title', 'Pinjam Berkas') 
@section('page-title', 'Pinjam Berkas')
@section('page-subtitle', 'Data berkas yang dipinjam dan status peminjaman.')

@section('content')
<div class="container-fluid p-0" style="margin-left: -35px;"> 
    <div class="card-custom" style="width: 108%; margin-left: 0px;">
    <div class="table-header-custom">
    <h5 class="table-title-custom fw-bold m-0">Data Berkas yang dipinjam</h5>
    <div class="table-actions-custom" id="filterArea" style="position: relative; display: flex; gap: 10px; align-items: center;">
        @php $roleUser = strtoupper(auth()->user()->role); @endphp
 
        <button type="button" class="btn-filter-custom" data-bs-toggle="modal" data-bs-target="#modalPinjam" style="white-space: nowrap;">
            + Pinjam Berkas
        </button>

        {{-- TOMBOL FILTER BARU --}}
        <button type="button" class="btn-filter-custom" onclick="toggleFilter(event)" style="white-space: nowrap;">
    <i class="filter-icon-svg"></i> Filters
</button>

        {{-- DROPDOWN FILTER IDENTIK PENCARIAN --}}
        <div id="filterDropdown" class="filter-dropdown-custom shadow-lg">
            <form action="{{ route('pinjam-berkas.index') }}" method="GET">
                {{-- Tetap simpan search permohonan jika ada --}}
                <input type="hidden" name="no_permohonan" value="{{ request('no_permohonan') }}">

                <div class="filter-group-custom">
                    <label style="display: block; font-size: 11px; font-weight: 700; color: #667085; text-transform: uppercase; margin-bottom: 8px;">Range Tanggal</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="date" name="start_date" value="{{ request('start_date') }}" 
                            style="width: 100%; padding: 6px; border: 1px solid #D0D5DD; border-radius: 6px; font-size: 13px;">
                        <input type="date" name="end_date" value="{{ request('end_date') }}" 
                            style="width: 100%; padding: 6px; border: 1px solid #D0D5DD; border-radius: 6px; font-size: 13px;">
                    </div>
                </div>
                
                <div class="filter-group-custom" style="margin-top: 15px;">
                    <label style="display: block; font-size: 11px; font-weight: 700; color: #667085; text-transform: uppercase; margin-bottom: 8px;">Status</label>
                    <select name="status" style="width: 100%; padding: 8px; border: 1px solid #D0D5DD; border-radius: 6px; font-size: 13px; color: #344054;">
                        <option value="">Semua Status</option>
                        <option value="Pengajuan" {{ request('status') == 'Pengajuan' ? 'selected' : '' }}>Pengajuan</option>
                        <option value="Disetujui" {{ request('status') == 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 20px;">
                    <button type="submit" class="btn-apply" style="background: #1366D9; color: white; border: none; padding: 10px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">Apply</button>
                    <a href="{{ route('pinjam-berkas.index') }}" style="text-decoration: none; text-align: center; font-size: 13px; font-weight: 600; padding: 8px; background: #ffffff; color: #344054; border: 1px solid #D0D5DD; border-radius: 8px;">Reset</a>
                </div>
            </form>
        </div>
        </div> {{-- Penutup filterArea --}}

        @if(request('no_permohonan') || request('status') || request('start_date'))
            <div class="mb-3 d-flex align-items-center justify-content-between p-3 rounded" style="background-color: #F9FAFB; border: 1px solid #EAECF0;">
                <span style="font-size: 13px; color: #475467;">
                    Menampilkan hasil untuk: 
                    @if(request('no_permohonan')) <strong>"{{ request('no_permohonan') }}"</strong> @endif
                    @if(request('start_date')) <strong>Tanggal: {{ request('start_date') }} s/d {{ request('end_date') }}</strong> @endif
                    @if(request('status')) <strong>Status: {{ request('status') }}</strong> @endif
                </span>
                <a href="{{ route('pinjam-berkas.index') }}" class="badge rounded-pill text-decoration-none" style="background-color: #1366D9; padding: 6px 12px; font-size: 11px; color: white;">
                    {{ $dataPinjam->total() }} Berkas Ditemukan (Reset)
                </a>
            </div>
        @endif
        </div>

        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th style="width: 16%;">No. Permohonan</th> 
                        <th style="width: 12%;">Tanggal Permohonan</th>
                        <th style="width: 14%; text-align: left;">Nama Pemohon</th> 
                        <th style="width: 16%;">Peminjam</th>
                        <th style="width: 11%;">Tanggal Pinjam</th>
                        <th style="width: 11%;">Tanggal Kembali</th>
                        <th style="width: 15%; text-align: center;">Aksi</th>
                        <th style="width: 9%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataPinjam as $item)
                    <tr>
                        <td class="fw-bold">{{ $item->permohonan->no_permohonan ?? '-' }}</td>
                        <td>{{ $item->permohonan->tanggal_permohonan ?? '-' }}</td>
                        <td>{{ $item->permohonan->nama ?? '-' }}</td>        
                        <td style="padding: 10px; vertical-align: middle;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div>
                                    <div style="font-size: 12px; font-weight: 500; color: #1F2937; margin-bottom: 2px;">
                                        {{ $item->nama_peminjam }}
                                    </div>
                                    <div style="font-size: 11px; color: #6B7280; display: flex; align-items: center; gap: 4px;">
                                        <span style="width: 6px; height: 6px; background: #10B981; border-radius: 50%;"></span>
                                        {{ $item->divisi_peminjam }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $item->tgl_pinjam }}</td>
                        <td>{{ $item->tgl_kembali ?? '-' }}</td>
                        <td style="width: 15%; text-align: center;">
                            <div class="aksi-wrapper" style="display:flex; gap:5px; justify-content:center; align-items:center;">
                                <button type="button" 
                                    onclick="showDetail({{ json_encode($item) }})" 
                                    class="btn-detail-blue">
                                    Detail
                                </button> 

                                @if(in_array(strtoupper(auth()->user()->role), ['ADMIN', 'TIKIM']))
                                    @if($item->status == 'Pengajuan')
                                        <form action="{{ route('pinjam-berkas.approve', $item->id) }}" method="POST" class="d-inline"> @csrf
                                            <button class="btn-check-custom" title="Setujui">✓</button>
                                        </form>
                                        <form action="{{ route('pinjam-berkas.reject', $item->id) }}" method="POST" class="d-inline"> @csrf
                                            <button class="btn-reject-custom" title="Tolak">✕</button>
                                        </form>
                                    @elseif($item->status == 'Disetujui')
                                        <a href="{{ route('pinjam-berkas.cetak', $item->id) }}" 
                                        class="btn-cetak" title="Cetak Tanda Terima" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <form action="{{ route('pinjam-berkas.complete', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn-selesai" onclick="return confirm('Yakin berkas sudah kembali?')">
                                                Selesai
                                            </button>
                                        </form>
                                    @elseif($item->status == 'Selesai')
                                        <a href="{{ route('pinjam-berkas.cetak-kembali', $item->id) }}" 
                                        class="btn-cetak" style="background: #34C759;" 
                                        title="Cetak Berita Acara Kembali" target="_blank">
                                            <i class="fas fa-file-invoice"></i>
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td>
                            @php
                                $statusCheck = strtoupper($item->status);
                                $badgeColor = ['PENGAJUAN'=>'bg-pengajuan', 'DISETUJUI'=>'bg-disetujui', 'DITOLAK'=>'bg-ditolak', 'SELESAI'=>'bg-selesai'][$statusCheck] ?? 'bg-secondary';
                            @endphp
                            <span class="badge-custom {{ $badgeColor }}">{{ $item->status }}</span>
                        </td>
                    </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Data tidak ditemukan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION FOOTER - TARUH DISINI AGAR MASUK KE DALAM CARD --}}
        @if(isset($dataPinjam) && method_exists($dataPinjam, 'links'))
            <div class="mt-4 px-2">
                @include('components.pagination-footer', ['data' => $dataPinjam])
            </div>
        @endif
    </div>
</div>
{{-- MODAL PINJAM --}}
<div class="modal fade" id="modalPinjam" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;"> 
        <div class="modal-content p-4 shadow border-0" style="border-radius: 10px;">
            <h6 class="mb-3 fw-bold text-primary"><i class="fas fa-plus-circle me-2"></i>Pinjam Berkas Baru</h6>
            <form id="formPinjamBerkas" action="{{ route('pinjam-berkas.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="form-label fw-bold">Nomor Permohonan</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-primary"><i class="fas fa-barcode text-primary"></i></span>
                        <input type="text" id="input_no_permohonan" name="no_permohonan" class="form-control border-primary" placeholder="Ketik nomor permohonan..." required autocomplete="off">
                    </div>
                </div>
                <div id="areaDetailOtomatis" class="p-3 bg-light rounded-3 mb-4 border" style="display: none; max-width: 600px; margin: 0 auto;">
                    <h6 class="small fw-bold text-muted mb-3 text-uppercase text-center">Informasi Lengkap Berkas</h6>
                    <div class="row g-2 justify-content-center">
                        <div class="col-sm-5">
                            <label class="small text-muted mb-1">Tanggal Permohonan</label>
                            <input type="text" id="det_tgl_mohon" class="form-control form-control-sm bg-white" readonly>
                        </div>
                        <div class="col-sm-5">
                            <label class="small text-muted mb-1">Tanggal Terbit</label>
                            <input type="text" id="det_tgl_terbit" class="form-control form-control-sm bg-white" readonly>
                        </div>
                        <div class="col-sm-10">
                            <label class="small text-muted mb-1">Nama Lengkap</label>
                            <input type="text" id="det_nama" class="form-control form-control-sm bg-white" readonly>
                        </div>
                        <div class="col-sm-5">
                            <label class="small text-muted mb-1">Tempat Lahir</label>
                            <input type="text" id="det_tempat_lahir" class="form-control form-control-sm bg-white" readonly>
                        </div>
                        <div class="col-sm-5">
                            <label class="small text-muted mb-1">Tanggal Lahir</label>
                            <input type="text" id="det_tgl_lahir" class="form-control form-control-sm bg-white" readonly>
                        </div>
                        <div class="col-sm-5">
                            <label class="small text-muted mb-1">Jenis Kelamin</label>
                            <input type="text" id="det_jk" class="form-control form-control-sm bg-white" readonly>
                        </div>
                        <div class="col-sm-5">
                            <label class="small text-muted mb-1">No Telpon</label>
                            <input type="text" id="det_telp" class="form-control form-control-sm bg-white" readonly>
                        </div>
                        <div class="col-sm-5">
                            <label class="small text-muted mb-1">Jenis Permohonan</label>
                            <input type="text" id="det_jns_mohon" class="form-control form-control-sm bg-white" readonly>
                        </div>
                        <div class="col-sm-5">
                            <label class="small text-muted mb-1">Jenis Paspor</label>
                            <input type="text" id="det_jns_paspor" class="form-control form-control-sm bg-white" readonly>
                        </div>
                        <div class="col-sm-10">
                            <label class="small text-muted mb-1">Tujuan Paspor</label>
                            <input type="text" id="det_tujuan" class="form-control form-control-sm bg-white" readonly>
                        </div>
                        <div class="col-sm-5">
                            <label class="small text-muted mb-1">No Paspor</label>
                            <input type="text" id="det_no_paspor" class="form-control form-control-sm bg-white" readonly>
                        </div>
                        <div class="col-sm-5">
                            <label class="small text-muted mb-1">Alur Terakhir</label>
                            <input type="text" id="det_alur" class="form-control form-control-sm bg-white" readonly>
                        </div>
                        <div class="col-sm-10">
                            <label class="small text-muted mb-1">Lokasi Arsip</label>
                            <input type="text" id="det_lokasi" class="form-control form-control-sm bg-white fw-bold text-success border-success" readonly>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Divisi Peminjam</label>
                    @if(in_array($roleUser, ['ADMIN', 'KANIM']))
                        <select name="nama_peminjam" class="form-select" required>
                            <option value="" selected disabled>-- Pilih Divisi --</option>
                            <option value="LANTASKIM">LANTASKIM</option>
                            <option value="TIKIM">TIKIM</option>
                            <option value="INTELDAKIM">INTELDAKIM</option>
                            <option value="INTELTUSKIM">INTELTUSKIM</option>
                        </select>
                    @else
                        <input type="text" class="form-control bg-light" value="{{ auth()->user()->role }}" readonly>
                        <input type="hidden" name="nama_peminjam" value="{{ auth()->user()->role }}">
                    @endif
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Nama Peminjam (Personil)</label>
                    <input type="text" name="nama_personil" class="form-control" placeholder="Masukkan nama orang yang meminjam" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control" placeholder="Keterangan Meminjam" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Petugas Arsip (Yang Menyetujui)</label>
                    <input type="text" name="petugas_arsip" class="form-control" placeholder="Nama Petugas Arsip" required>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-light w-100 border" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary w-100" onclick="handleSimpanPinjaman()">Simpan Peminjaman</button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- MODAL DETAIL --}}
<div class="modal fade" id="modalDetailBerkas" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; width: 440px; margin: auto;">
            <div class="modal-header border-0 pb-0 pe-3 pt-3">
                <h5 class="modal-title fw-bold text-secondary" style="font-size: 18px;">Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size: 12px;"></button>
            </div>
            <div class="modal-body p-4 pt-2">
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
                <div class="detail-container">
                    @foreach($fields as $id => $label)
                    <div class="row align-items-center mb-2">
                        <div class="col-5">
                            <label class="text-secondary fw-medium m-0" style="font-size: 12px;">{{ $label }}</label>
                        </div>
                        <div class="col-7">
                            <input type="text" id="{{ $id }}" readonly class="form-control form-control-sm bg-white text-dark shadow-none" style="font-size: 12px; border: 1px solid #ced4da; border-radius: 6px;">
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="text-end mt-3 pt-2 border-top">
                    <button type="button" class="btn btn-danger btn-sm px-4 py-2 fw-bold" data-bs-dismiss="modal" style="background: #F97066; border: none; border-radius: 8px;">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* CSS Styling */
.card-custom { background: #ffffff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.table-header-custom { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.btn-filter-custom { display: flex; align-items: center; gap: 8px; background: #ffffff; border: 1px solid #D0D5DD; height: 38px; padding: 0 16px; border-radius: 8px; color: #5D6679; font-size: 14px; cursor: pointer; }
.btn-submit-filter { width: 100%; background: #1366D9; color: #ffffff; border: none; padding: 10px; border-radius: 8px; font-size: 14px; }
.table-custom { width: 100%; border-collapse: collapse; font-size: 13px; }
.table-custom thead th { text-align: left; padding: 12px; border-bottom: 2px solid #F0F1F3; font-weight: 600; color: #48505E; }
.table-custom tbody td { padding: 12px; border-bottom: 1px solid #F0F1F3; vertical-align: middle; }
.btn-detail-blue { background: #629FF4; color: white; border: none; padding: 5px 12px; border-radius: 6px; font-size: 11px; }
.btn-check-custom { background: #34C759; color: white; border: none; width: 28px; height: 28px; border-radius: 6px; }
.btn-reject-custom { background: #FF383C; color: white; border: none; width: 28px; height: 28px; border-radius: 6px; }
.btn-selesai { background: #0088FF; color: white; border: none; padding: 5px 12px; border-radius: 6px; font-size: 11px; }
.badge-custom { padding: 5px 10px; border-radius: 6px; font-size: 10px; font-weight: 600; color: white; text-transform: uppercase; }
.bg-pengajuan { background-color: #FFCC00; }
.bg-disetujui { background-color: #34C759; }
.bg-ditolak { background-color: #FF383C; }
.bg-selesai { background-color: #0088FF; }
.filter-dropdown-custom {
    display: none; /* Tetap sembunyi di awal */
    position: absolute;
    background: #ffffff;
    padding: 20px;
    border-radius: 12px;
    width: 450px; 
    z-index: 9999 !important; 
    right: 0;
    top: 48px; /* Jarak dari tombol */
    border: 1px solid #E4E7EC;
    box-shadow: 0px 12px 16px -4px rgba(16, 24, 40, 0.08);
    pointer-events: auto; 
}
.filter-dropdown-custom.show { display: block; }
.btn-cetak { background: #667085; color: white; border: none; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 12px; }
.filter-icon-svg {
    width: 16px;
    height: 16px;
    background-color: #667085;
    display: inline-block;
    vertical-align: middle;
    margin-right: 5px;
    -webkit-mask: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/></svg>') no-repeat center;
    mask: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/></svg>') no-repeat center;
}
.filter-dropdown-custom {
    display: none;
    position: absolute;
    background: #ffffff;
    padding: 16px;
    border-radius: 12px;
    width: 280px;
    z-index: 1050;
    right: 0;
    top: 45px;
    border: 1px solid #E4E7EC;
    box-shadow: 0px 12px 16px -4px rgba(16, 24, 40, 0.08);
}
.filter-dropdown-custom.show {
    display: block;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function toggleFilter(event) {
    if (event) event.stopPropagation(); 
    document.getElementById('filterDropdown').classList.toggle('show');
}
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('filterDropdown');
    const area = document.getElementById('filterArea');
    if (dropdown && dropdown.classList.contains('show') && area && !area.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});

function showDetail(item) {
    if(!item) return;
    const permohonan = item.permohonan;
    document.getElementById('m_no_permohonan').value = permohonan.no_permohonan || '-';
    document.getElementById('m_tgl_permohonan').value = permohonan.tanggal_permohonan || '-';
    document.getElementById('m_tgl_terbit').value = permohonan.tanggal_terbit || '-';
    document.getElementById('m_nama').value = (permohonan.nama || '-').toUpperCase();
    document.getElementById('m_tempat_lahir').value = permohonan.tempat_lahir || '-';
    document.getElementById('m_tgl_lahir').value = permohonan.tanggal_lahir || '-';
    document.getElementById('m_gender').value = permohonan.jenis_kelamin || '-';
    document.getElementById('m_telp').value = permohonan.no_telp || '-';
    document.getElementById('m_jns_permohonan').value = permohonan.jenis_permohonan || '-';
    document.getElementById('m_jns_paspor').value = permohonan.jenis_paspor || '-';
    document.getElementById('m_tujuan').value = permohonan.tujuan_paspor || '-';
    document.getElementById('m_no_paspor').value = permohonan.no_paspor || '-';
    document.getElementById('m_lokasi').value = permohonan.lokasi_arsip || '-';

    const alurInput = document.getElementById('m_alur');
    let alurTerupdate = item.alur_paspor_update ? item.alur_paspor_update.toUpperCase() : (permohonan.status_berkas || '-');
    alurInput.value = alurTerupdate;

    if (alurTerupdate === 'SELESAI') {
        alurInput.style.backgroundColor = '#d1e7dd'; 
        alurInput.style.color = '#0f5132';           
        alurInput.style.fontWeight = 'bold';
        alurInput.style.borderColor = '#badbcc';
    } else {
        alurInput.style.backgroundColor = '#ffffff';
        alurInput.style.color = '#212529';
        alurInput.style.fontWeight = 'normal';
        alurInput.style.borderColor = '#ced4da';
    }
    new bootstrap.Modal(document.getElementById('modalDetailBerkas')).show();
}

let debounceTimer;
const inputPermohonan = document.getElementById('input_no_permohonan');
if (inputPermohonan) {
    inputPermohonan.addEventListener('input', function() {
        const no = this.value;
        const detailArea = document.getElementById('areaDetailOtomatis');
        clearTimeout(debounceTimer);
        if (!no || no.length < 5) { detailArea.style.display = 'none'; return; }
        debounceTimer = setTimeout(() => {
            fetch(`/cari-permohonan/${no}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        if (data.is_borrowed) {
                            Swal.fire({
                                title: 'Info',
                                html: `Berkas sedang dipinjam oleh: <br><strong>${data.personnel_name}</strong> <br><small>(Divisi: ${data.borrower_name})</small>`,
                                icon: 'warning',
                                confirmButtonColor: '#5D5FEF'
                            });
                            this.value = '';
                            detailArea.style.display = 'none';
                        } else {
                            document.getElementById('det_tgl_mohon').value = data.data.tanggal_permohonan || '-';
                            document.getElementById('det_tgl_terbit').value = data.data.tanggal_terbit || '-';
                            document.getElementById('det_nama').value = data.data.nama || '-';
                            document.getElementById('det_tempat_lahir').value = data.data.tempat_lahir || '-';
                            document.getElementById('det_tgl_lahir').value = data.data.tanggal_lahir || '-';
                            document.getElementById('det_jk').value = data.data.jenis_kelamin || '-';
                            document.getElementById('det_telp').value = data.data.no_telp || '-';
                            document.getElementById('det_jns_mohon').value = data.data.jenis_permohonan || '-';
                            document.getElementById('det_jns_paspor').value = data.data.jenis_paspor || '-';
                            document.getElementById('det_tujuan').value = data.data.tujuan_paspor || '-';
                            document.getElementById('det_no_paspor').value = data.data.no_paspor || '-';
                            document.getElementById('det_alur').value = data.status_terupdate || '-';
                            document.getElementById('det_lokasi').value = data.data.lokasi_arsip || '-';
                            detailArea.style.display = 'block';
                        }
                    } else { detailArea.style.display = 'none'; }
                });
        }, 500); 
    }); 
}

function handleSimpanPinjaman() {
    const no = document.getElementById('input_no_permohonan').value;
    if(!no) { Swal.fire('Peringatan', 'Masukkan Nomor Permohonan', 'warning'); return; }
    fetch(`/cari-permohonan/${no}`)
        .then(r => r.json())
        .then(data => {
            if(data.success && data.is_borrowed) {
                Swal.fire({
                    title: 'Berkas Sedang Dipinjam!',
                    html: `Berkas ini sedang dipinjam oleh <b>${data.personnel_name}</b> dari divisi <b>${data.borrower_name}</b>.<br>Selesaikan pengembalian berkas terlebih dahulu.`,
                    icon: 'error',
                    confirmButtonColor: '#F97066'
                });
            } else if(data.success) {
                document.getElementById('formPinjamBerkas').submit();
            } else {
                Swal.fire('Gagal', 'Nomor Permohonan tidak valid.', 'error');
            }
        });
}
</script>

@if(session('success_kembali'))
<script>
    Swal.fire({
        title: "Berkas Kembali!",
        text: "Status telah menjadi Selesai. Silakan cetak Berita Acara Pengembalian.",
        icon: "success",
        showCancelButton: true,
        confirmButtonColor: "#34C759",
        confirmButtonText: "Cetak Sekarang",
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.open("{{ route('pinjam-berkas.cetak-kembali', session('success_kembali')) }}", "_blank");
        }
    });
</script>
@endif
@endsection