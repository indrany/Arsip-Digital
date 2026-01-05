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
               {{-- Hanya tampilkan tombol jika role adalah admin --}}
                @if(auth()->user()->role == 'admin')
                    <button type="button" class="btn-filter-custom" data-bs-toggle="modal" data-bs-target="#modalPinjam">
                        + Pinjam Berkas
                    </button>
                @endif

                {{-- TOMBOL FILTER (Tetap muncul untuk semua role agar bisa mencari data) --}}
                <button type="button" class="btn-filter-custom" onclick="toggleFilter()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/>
                    </svg>
                    Cari
                </button>

                {{-- DROPDOWN FILTER --}}
                <div id="filterDropdown" class="filter-dropdown-custom shadow-lg">
                    <form action="{{ route('pinjam-berkas.index') }}" method="GET">
                        <div class="filter-group-custom">
                            <label>No Permohonan</label>
                            <input type="text" name="no_permohonan" 
                                value="{{ request('no_permohonan') }}" 
                                placeholder="Masukkan No Permohonan">
                        </div>
                        <button type="submit" class="btn-submit-filter">Cari</button>
                        <a href="{{ route('pinjam-berkas.index') }}" class="btn-reset-filter">Reset</a>
                    </form>
                </div>

            </div>
        </div>

        <div class="table-responsive">
            <table class="table-custom">
            <thead>
            <tr>
                <th style="width: 15%;">No. Permohonan</th>
                <th style="width: 15%;">Tanggal Permohonan</th>
                <th style="width: 15%;">Nama Pemohon</th>
                <th style="width: 12%;">Divisi Peminjam</th> {{-- Lebar dikurangi agar lebih rapat --}}
                <th style="width: 12%;">Tanggal Pinjam</th>
                <th style="width: 12%;">Tanggal Kembali</th>
                <th style="width: 10%; text-align: center;">Aksi</th>
                <th style="width: 9%;">Status</th>
            </tr>
            </thead>
                <tbody>

                {{-- KONDISI AWAL --}}
                @if($dataPinjam->isEmpty() && !request('no_permohonan'))
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            Silakan masukkan <b>No Permohonan</b> untuk menampilkan data
                        </td>
                    </tr>
                @endif

                @forelse($dataPinjam as $item)
                    <tr>
                        <td>{{ $item->permohonan->no_permohonan ?? '-' }}</td>
                        <td>{{ $item->permohonan->tanggal_permohonan ?? '-' }}</td>
                        <td>{{ $item->permohonan->nama ?? '-' }}</td>
                        
                        {{-- BAGIAN YANG DIPERBAIKI: Menampilkan Nama Divisi sebagai teks --}}
                        <td>
                            <span class="fw-medium text-dark" style="font-size: 13px; margin-left: 5px;">
                                @if($item->nama_peminjam == 'UMUM')
                                    Bagian Umum
                                @else
                                    {{ ucfirst(strtolower($item->nama_peminjam)) }}
                                @endif
                            </span>
                        </td>
                        <td>{{ $item->tgl_pinjam }}</td>
                        <td>{{ $item->tgl_kembali ?? '-' }}</td>
                        <td>
                            <div class="aksi-wrapper" style="display:flex; gap:5px; justify-content:center;">
                                @if($item->status == 'Pengajuan')
                                    <form action="{{ route('pinjam-berkas.approve', $item->id) }}" method="POST">
                                        @csrf
                                        <button class="btn-check-custom">✓</button>
                                    </form>
                                    <form action="{{ route('pinjam-berkas.reject', $item->id) }}" method="POST">
                                        @csrf
                                        <button class="btn-reject-custom">✕</button>
                                    </form>
                                @elseif($item->status == 'Disetujui')
                                    <form action="{{ route('pinjam-berkas.complete', $item->id) }}" method="POST">
                                        @csrf
                                        <button class="btn-selesai">Selesai</button>
                                    </form>
                                @endif
                                <button 
                                    type="button" 
                                    onclick="showDetail({{ json_encode($item->permohonan) }})" 
                                    class="btn-detail-blue">
                                    Detail
                                </button>

                            </div>
                        </td>
                        <td>
                            @php
                                $statusClass = [
                                    'Pengajuan'=>'bg-pengajuan',
                                    'Disetujui'=>'bg-disetujui',
                                    'Ditolak'=>'bg-ditolak',
                                    'Selesai'=>'bg-selesai'
                                ][$item->status] ?? '';
                            @endphp
                            <span class="badge-custom {{ $statusClass }}">{{ $item->status }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">Data tidak ditemukan</td>
                    </tr>
                @endforelse

                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL PINJAM --}}
<div class="modal fade" id="modalPinjam" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('pinjam-berkas.store') }}" method="POST" class="modal-content p-3">
            @csrf
            <h6 class="mb-3 fw-bold">Pinjam Berkas Baru</h6>
            
            <div class="mb-2">
                <label class="small">No Permohonan</label>
                <div class="input-group"> {{-- Menggunakan input group agar tombol ada di samping --}}
                    <input type="text" id="input_no_permohonan" name="no_permohonan" class="form-control" placeholder="Contoh: 02348..." required>
                    <button type="button" class="btn btn-primary" onclick="cekDetailSebelumPinjam()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        Cek
                    </button>
                </div>
            </div>

            <div class="mb-3">
                <label class="small">Divisi Tujuan</label>
                <select name="nama_peminjam" class="form-control" required>
                    <option value="">-- Pilih Divisi --</option>
                    <option value="UMUM">Bagian Umum</option>
                    <option value="WASDAK">Wasdak</option>
                    <option value="LANTASKIM">Lantaskim</option>
                    <option value="INTELDAKIM">Inteldakim</option>
                    <option value="TIKIM">Tikim</option>
                </select>
            </div>
            
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn-submit-filter w-100">Simpan Peminjaman</button>
            </div>
        </form>
    </div>
</div>
            {{-- MODAL DETAIL --}}
<div class="modal fade" id="modalDetailBerkas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; width: 410px; padding: 15px 25px; margin: auto;">
            <div class="modal-header border-0 p-0 mb-2">
                <h6 class="modal-title fw-bold text-secondary" style="font-size: 16px;">Detail Informasi</h6>
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
                            'm_alur'            => 'Status Berkas',
                            'm_lokasi'          => 'Lokasi Arsip'
                        ];
                    @endphp

                    @foreach($fields as $id => $label)
                    <div class="info-item-row d-flex align-items-center mb-1" style="margin-bottom: 6px !important;">
                        <label style="flex: 0 0 42%; font-size: 11px; color: #48505E; font-weight: 500;">{{ $label }}</label>
                        <div class="input-wrapper" style="flex: 0 0 58%;">
                            <input type="text" id="{{ $id }}" readonly 
                                   style="width: 100%; padding: 5px 10px; border: 1px solid #D0D5DD; border-radius: 6px; background: #f9fafb; font-size: 11px; color: #344054; height: 28px;">
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
/* =========================
   CARD & HEADER
========================= */
.card-custom {
    background: #ffffff;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.table-header-custom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    position: relative;
}

.table-title-custom {
    font-size: 18px;
    font-weight: 600;
    color: #383E49;
    margin: 0;
}

.table-actions-custom {
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
}

/* =========================
   BUTTON
========================= */
.btn-filter-custom {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #ffffff;
    border: 1px solid #D0D5DD;
    height: 38px;
    padding: 0 16px;
    border-radius: 8px;
    color: #5D6679;
    font-size: 14px;
    cursor: pointer;
}

.btn-filter-custom:hover {
    background: #F9FAFB;
}

.btn-submit-filter {
    width: 100%;
    background: #1366D9;
    color: #ffffff;
    border: none;
    padding: 10px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
}

.btn-reset-filter {
    display: block;
    text-align: center;
    margin-top: 8px;
    font-size: 13px;
    color: #667085;
    text-decoration: none;
}

/* =========================
   FILTER DROPDOWN
========================= */
.filter-dropdown-custom {
    display: none;
    position: absolute;
    top: 45px;
    right: 0;
    background: #ffffff;
    border: 1px solid #E4E7EC;
    border-radius: 12px;
    padding: 20px;
    width: 280px;
    z-index: 1050;
    box-shadow: 0 10px 15px rgba(0,0,0,0.1);
}

.filter-dropdown-custom.show {
    display: block;
}

.filter-group-custom {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 12px;
}

.filter-group-custom label {
    font-size: 12px;
    color: #48505E;
}

.filter-group-custom input {
    height: 34px;
    border-radius: 6px;
    border: 1px solid #D0D5DD;
    padding: 0 10px;
    font-size: 13px;
}

/* =========================
   TABLE
========================= */
.table-custom {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.table-custom thead th {
    text-align: left;
    padding: 12px;
    border-bottom: 2px solid #F0F1F3;
    font-weight: 600;
    color: #48505E;
}

.table-custom tbody td {
    padding: 12px;
    border-bottom: 1px solid #F0F1F3;
    vertical-align: middle;
    color: #344054;
}

/* =========================
   SELECT DIVISI
========================= */
.form-select-divisi-table {
    width: 100%;
    height: 32px;
    padding: 0 8px;
    border: 1px solid #D0D5DD;
    border-radius: 6px;
    background-color: #ffffff;
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    color: #344054;
    cursor: pointer;
}

/* =========================
   AKSI BUTTON
========================= */
.btn-check-custom {
    width: 28px;
    height: 28px;
    background: #34C759;
    color: #ffffff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
}

.btn-reject-custom {
    width: 28px;
    height: 28px;
    background: #FF383C;
    color: #ffffff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
}

.btn-selesai {
    height: 28px;
    background: #0088FF;
    color: #ffffff;
    border: none;
    border-radius: 6px;
    padding: 0 12px;
    font-size: 12px;
    cursor: pointer;
}

.btn-detail-blue {
    height: 28px;
    background: #629FF4;
    color: #ffffff;
    border: none;
    border-radius: 6px;
    padding: 0 10px;
    font-size: 11px;
    cursor: pointer;
}

/* =========================
   BADGE STATUS
========================= */
.badge-custom {
    padding: 5px 12px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    color: #ffffff;
}

.bg-pengajuan { background-color: #FFCC00; }
.bg-disetujui { background-color: #34C759; }
.bg-ditolak   { background-color: #FF383C; }
.bg-selesai   { background-color: #0088FF; }

/* =========================
   RESPONSIVE
========================= */
@media (max-width: 768px) {
    .table-header-custom {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .table-actions-custom {
        width: 100%;
        justify-content: flex-start;
    }
}
</style>

<script>
function toggleFilter(){document.getElementById('filterDropdown').classList.toggle('show');}
function updateDivisi(id,val){
    if(val==='Menunggu Input')return;
    fetch(`/pinjam-berkas/update-divisi/${id}`,{
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body:JSON.stringify({divisi:val})
    });
}

function showDetail(item) {
    if(!item) {
        alert("Data permohonan tidak ditemukan");
        return;
    }
    
    // Kembali menggunakan format no_permohonan, tempat_lahir, dll.
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

    var modalInstance = new bootstrap.Modal(document.getElementById('modalDetailBerkas'));
    modalInstance.show();
}
function cekDetailSebelumPinjam() {
    const noPermohonan = document.getElementById('input_no_permohonan').value;

    if (!noPermohonan) {
        alert("Silakan masukkan Nomor Permohonan terlebih dahulu.");
        return;
    }

    // Melakukan fetch ke route pencarian (kita buat route ini di langkah 3)
    fetch(`/cari-permohonan/${noPermohonan}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Jika ketemu, panggil fungsi showDetail yang sudah kamu punya sebelumnya
                showDetail(data.data);
            } else {
                alert("Nomor Permohonan tidak ditemukan!");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Terjadi kesalahan saat mencari data.");
        });
}
</script>
@endsection
