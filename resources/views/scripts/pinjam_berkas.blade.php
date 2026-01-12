@extends('layouts.app') 
@section('title', 'Pinjam Berkas') 
@section('page-title', 'Pinjam Berkas')
@section('page-subtitle', 'Data berkas yang dipinjam dan status peminjaman.')

@section('content')
<div class="container-fluid">
    <div class="card-custom">
        <div class="table-header-custom">
            {{-- JUDUL DIPERKECHIL MENGGUNAKAN H6 --}}
            <h6 class="table-title-custom fw-bold m-0">Data Berkas yang dipinjam</h6>

            {{-- AREA TOMBOL SEJAJAR --}}
            <div class="table-actions-custom" id="filterArea" style="display: flex; gap: 10px; align-items: center;">
                @php $roleUser = strtoupper(auth()->user()->role); @endphp
                
                {{-- Tombol Pinjam --}}
                <button type="button" class="btn-filter-custom" data-bs-toggle="modal" data-bs-target="#modalPinjam" style="white-space: nowrap;">
                    + Pinjam Berkas
                </button>

                {{-- Tombol Cari --}}
                <button type="button" class="btn-filter-custom" onclick="toggleFilter()" style="white-space: nowrap;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/>
                    </svg>
                    Cari
                </button>
            </div>
        </div>

        {{-- DROPDOWN FILTER --}}
        <div id="filterDropdown" class="filter-dropdown-custom shadow-lg">
            <form action="{{ route('pinjam-berkas.index') }}" method="GET">
                <div class="filter-group-custom">
                    <label>No Permohonan</label>
                    <input type="text" name="no_permohonan" value="{{ request('no_permohonan') }}" placeholder="Masukkan No Permohonan">
                </div>
                <button type="submit" class="btn-submit-filter">Cari</button>
                <a href="{{ route('pinjam-berkas.index') }}" class="btn-reset-filter">Reset</a>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th style="width: 15%;">No. Permohonan</th>
                        <th style="width: 15%;">Tanggal Permohonan</th>
                        <th style="width: 15%;">Nama Pemohon</th>
                        <th style="width: 12%;">Divisi Peminjam</th>
                        <th style="width: 12%;">Tanggal Pinjam</th>
                        <th style="width: 12%;">Tanggal Kembali</th>
                        <th style="width: 10%; text-align: center;">Aksi</th>
                        <th style="width: 9%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataPinjam as $item)
                    <tr>
                        <td class="fw-bold">{{ $item->permohonan->no_permohonan ?? '-' }}</td>
                        <td>{{ $item->permohonan->tanggal_permohonan ?? '-' }}</td>
                        <td>{{ $item->permohonan->nama ?? '-' }}</td>
                        <td><span class="badge-divisi">{{ $item->nama_peminjam }}</span></td>
                        <td>{{ $item->tgl_pinjam }}</td>
                        <td>{{ $item->tgl_kembali ?? '-' }}</td>
                        <td>
                            <div class="aksi-wrapper" style="display:flex; gap:5px; justify-content:center; align-items:center;">
                                <button type="button" onclick="showDetail({{ json_encode($item->permohonan) }})" class="btn-detail-blue">Detail</button>

                                @if(in_array(strtoupper(auth()->user()->role), ['ADMIN', 'TIKIM']))
                                    @if($item->status == 'Pengajuan')
                                        <form action="{{ route('pinjam-berkas.approve', $item->id) }}" method="POST" class="d-inline"> @csrf
                                            <button class="btn-check-custom" title="Setujui">✓</button>
                                        </form>
                                        <form action="{{ route('pinjam-berkas.reject', $item->id) }}" method="POST" class="d-inline"> @csrf
                                            <button class="btn-reject-custom" title="Tolak">✕</button>
                                        </form>
                                    @elseif($item->status == 'Disetujui')
                                        <form action="{{ route('pinjam-berkas.complete', $item->id) }}" method="POST" class="d-inline"> @csrf
                                            <button class="btn-selesai">Selesai</button>
                                        </form>
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
    </div>
</div>

{{-- MODAL PINJAM --}}
<div class="modal fade" id="modalPinjam" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;"> 
        <div class="modal-content p-4 shadow border-0" style="border-radius: 12px;">
            <h6 class="mb-3 fw-bold text-primary"><i class="fas fa-plus-circle me-2"></i>Pinjam Berkas Baru</h6>
            <form id="formPinjamBerkas" action="{{ route('pinjam-berkas.store') }}" method="POST">
                @csrf
                
                {{-- Input Utama --}}
                <div class="mb-4">
                    <label class="form-label fw-bold">Nomor Permohonan</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-primary"><i class="fas fa-barcode text-primary"></i></span>
                        <input type="text" id="input_no_permohonan" name="no_permohonan" class="form-control border-primary" placeholder="Ketik nomor permohonan..." required autocomplete="off">
                    </div>
                </div>

                {{-- AREA DETAIL OTOMATIS (Lengkap 14 Field) --}}
        <div id="areaDetailOtomatis" class="p-3 bg-light rounded-3 mb-4 border" style="display: none; max-width: 600px; margin: 0 auto;">
            <h6 class="small fw-bold text-muted mb-3 text-uppercase text-center">Informasi Lengkap Berkas</h6>
            
            <div class="row g-2 justify-content-center">
        {{-- Baris 1 --}}
        <div class="col-sm-5">
            <label class="small text-muted mb-1">Tanggal Permohonan</label>
            <input type="text" id="det_tgl_mohon" class="form-control form-control-sm bg-white" readonly>
        </div>
        <div class="col-sm-5">
            <label class="small text-muted mb-1">Tanggal Terbit</label>
            <input type="text" id="det_tgl_terbit" class="form-control form-control-sm bg-white" readonly>
        </div>
        
        {{-- Baris 2 --}}
        <div class="col-sm-10">
            <label class="small text-muted mb-1">Nama Lengkap</label>
            <input type="text" id="det_nama" class="form-control form-control-sm bg-white" readonly>
        </div>

        {{-- Baris 3 --}}
        <div class="col-sm-5">
            <label class="small text-muted mb-1">Tempat Lahir</label>
            <input type="text" id="det_tempat_lahir" class="form-control form-control-sm bg-white" readonly>
        </div>
        <div class="col-sm-5">
            <label class="small text-muted mb-1">Tanggal Lahir</label>
            <input type="text" id="det_tgl_lahir" class="form-control form-control-sm bg-white" readonly>
        </div>

        {{-- Baris 4 --}}
        <div class="col-sm-5">
            <label class="small text-muted mb-1">Jenis Kelamin</label>
            <input type="text" id="det_jk" class="form-control form-control-sm bg-white" readonly>
        </div>
        <div class="col-sm-5">
            <label class="small text-muted mb-1">No Telpon</label>
            <input type="text" id="det_telp" class="form-control form-control-sm bg-white" readonly>
        </div>

        {{-- Baris 5 --}}
        <div class="col-sm-5">
            <label class="small text-muted mb-1">Jenis Permohonan</label>
            <input type="text" id="det_jns_mohon" class="form-control form-control-sm bg-white" readonly>
        </div>
        <div class="col-sm-5">
            <label class="small text-muted mb-1">Jenis Paspor</label>
            <input type="text" id="det_jns_paspor" class="form-control form-control-sm bg-white" readonly>
        </div>

        {{-- Baris 6 --}}
        <div class="col-sm-10">
            <label class="small text-muted mb-1">Tujuan Paspor</label>
            <input type="text" id="det_tujuan" class="form-control form-control-sm bg-white" readonly>
        </div>

        {{-- Baris 7 --}}
        <div class="col-sm-5">
            <label class="small text-muted mb-1">No Paspor</label>
            <input type="text" id="det_no_paspor" class="form-control form-control-sm bg-white" readonly>
        </div>
        <div class="col-sm-5">
            <label class="small text-muted mb-1">Alur Terakhir</label>
            <input type="text" id="det_alur" class="form-control form-control-sm bg-white" readonly>
        </div>

        {{-- Baris 8 --}}
        <div class="col-sm-10">
            <label class="small text-muted mb-1">Lokasi Arsip</label>
            <input type="text" id="det_lokasi" class="form-control form-control-sm bg-white fw-bold text-success border-success" readonly>
        </div>
    </div>
</div>

                {{-- Input Peminjam --}}
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
/* STYLE UTAMA */
.card-custom { background: #ffffff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.table-header-custom { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.btn-filter-custom { display: flex; align-items: center; gap: 8px; background: #ffffff; border: 1px solid #D0D5DD; height: 38px; padding: 0 16px; border-radius: 8px; color: #5D6679; font-size: 14px; cursor: pointer; }
.btn-submit-filter { width: 100%; background: #1366D9; color: #ffffff; border: none; padding: 10px; border-radius: 8px; font-size: 14px; }
.table-custom { width: 100%; border-collapse: collapse; font-size: 13px; }
.table-custom thead th { text-align: left; padding: 12px; border-bottom: 2px solid #F0F1F3; font-weight: 600; color: #48505E; }
.table-custom tbody td { padding: 12px; border-bottom: 1px solid #F0F1F3; vertical-align: middle; }
.badge-divisi { background: #f8f9fa; border: 1px solid #e9ecef; padding: 4px 10px; border-radius: 15px; font-size: 11px; color: #48505E; }
.btn-detail-blue { background: #629FF4; color: white; border: none; padding: 5px 12px; border-radius: 6px; font-size: 11px; }
.btn-check-custom { background: #34C759; color: white; border: none; width: 28px; height: 28px; border-radius: 6px; }
.btn-reject-custom { background: #FF383C; color: white; border: none; width: 28px; height: 28px; border-radius: 6px; }
.btn-selesai { background: #0088FF; color: white; border: none; padding: 5px 12px; border-radius: 6px; font-size: 11px; }

.badge-custom { padding: 5px 10px; border-radius: 6px; font-size: 10px; font-weight: 600; color: white; text-transform: uppercase; }
.bg-pengajuan { background-color: #FFCC00; }
.bg-disetujui { background-color: #34C759; }
.bg-ditolak { background-color: #FF383C; }
.bg-selesai { background-color: #0088FF; }
.filter-dropdown-custom { display: none; position: absolute; background: white; padding: 20px; border-radius: 12px; width: 280px; z-index: 100; right: 20px; top: 120px; }
.filter-dropdown-custom.show { display: block; }

/* REVISI UKURAN JUDUL DI CSS */
.table-title-custom {
    font-size: 15px !important; 
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function toggleFilter(){ document.getElementById('filterDropdown').classList.toggle('show'); }

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

let debounceTimer;

document.getElementById('input_no_permohonan').addEventListener('input', function() {
    const no = this.value;
    const detailArea = document.getElementById('areaDetailOtomatis');
    
    clearTimeout(debounceTimer);
    if (!no || no.length < 5) {
        detailArea.style.display = 'none';
        return;
    }

    debounceTimer = setTimeout(() => {
        fetch(`/cari-permohonan/${no}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (data.is_borrowed) {
                        Swal.fire('Info', `Berkas sedang dipinjam oleh ${data.borrower_name}`, 'warning');
                        this.value = '';
                        detailArea.style.display = 'none';
                    } else {
                        // Isi 14 Field Lengkap
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
                        document.getElementById('det_alur').value = data.data.status_berkas || '-';
                        document.getElementById('det_lokasi').value = data.data.lokasi_arsip || '-';
                        
                        detailArea.style.display = 'block';
                    }
                } else {
                    detailArea.style.display = 'none';
                }
            });
    }, 500); 
});
function handleSimpanPinjaman() {
    const no = document.getElementById('input_no_permohonan').value;
    if(!no) { Swal.fire('Peringatan', 'Masukkan Nomor Permohonan', 'warning'); return; }

    fetch(`/cari-permohonan/${no}`).then(r => r.json()).then(data => {
        if(data.success && data.is_borrowed) {
            Swal.fire({
                title: 'Berkas Sedang Dipinjam!',
                html: `Berkas ini sedang dipinjam oleh divisi <b>${data.borrower_name}</b>.<br>Selesaikan pengembalian berkas terlebih dahulu.`,
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
@endsection