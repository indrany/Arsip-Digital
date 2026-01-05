<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Inter&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Poppins&display=swap" rel="stylesheet" />
    <link href="{{ asset('css/main.css') }}" rel="stylesheet" />
    <title>Pencarian Berkas - Sistem Arsip Digital</title>
    <style>
        .search-box-container { 
            margin: 20px 40px; 
            display: flex; 
            gap: 10px; }
        .search-box-container input { 
            padding: 10px; 
            border: 1px solid #D0D5DD; 
            border-radius: 4px; width: 
            300px; }
        .search-box-container button { padding: 10px 20px; background: #448DF2; color: white; border: none; border-radius: 4px; cursor: pointer; }
        
        .result-card { background: #FFFFFF; margin: 0 40px; border-radius: 8px; padding: 24px; box-shadow: 0px 1px 2px rgba(16, 24, 40, 0.05); }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { text-align: left; padding: 12px 16px; color: #667085; border-bottom: 1px solid #EAECF0; }
        .data-table td { padding: 16px; color: #48505E; border-bottom: 1px solid #EAECF0; }
        
        .btn-detail { background: #448DF2; color: white; padding: 6px 20px; border-radius: 4px; border: none; cursor: pointer; }

    /* Overlay Utama */
    .modal-overlay { 
        display: none; 
        position: fixed; 
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 100%; 
        background: rgba(0, 0, 0, 0.4); 
        z-index: 9999; 
        justify-content: center; 
        align-items: center; 
    }

    /* Kotak Modal - Dibuat lebih ramping dan padat */
    .modal-content { 
        background: #fff; 
        width: 410px; /* Lebar dikecilkan agar tidak terlalu besar */
        max-height: 90vh; 
        overflow-y: auto; 
        padding: 15px 25px; /* Padding dikurangi */
        border-radius: 12px; 
        box-shadow: 0px 10px 25px rgba(0, 0, 0, 0.1);
        position: relative;
    }

    /* Header Modal */
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px; /* Jarak header dikurangi */
    }

    .modal-header h2 {
        font-size: 16px; /* Font judul dikecilkan */
        font-weight: 600;
        color: #667085;
        margin: 0;
    }

    /* Baris Informasi - Jarak antar baris dipersempit */
    .info-item-row {
        display: flex;
        align-items: center;
        margin-bottom: 6px; /* Jarak antar list dikecilkan */
    }

    .info-item-row label {
        flex: 0 0 42%; 
        font-size: 11px; /* Font label diperkecil */
        color: #48505E;
        font-weight: 500;
        margin-bottom: 0;
    }

    .info-item-row .input-wrapper {
        flex: 0 0 58%; 
    }

    .info-item-row input {
        width: 100%;
        padding: 5px 10px; /* Padding input diperkecil agar lebih ramping */
        border: 1px solid #D0D5DD;
        border-radius: 6px;
        background: #FFFFFF !important;
        font-size: 11px; /* Font isi diperkecil */
        color: #344054;
        box-sizing: border-box;
        height: 28px;
    }

    .btn-tutup-merah {
        background: #F97066; 
        color: white; 
        border: none; 
        padding: 7px 20px; 
        border-radius: 6px; 
        cursor: pointer; 
        font-weight: 500;
        font-size: 12px;
        margin-top: 10px;
    }

    /* Scrollbar Tipis */
    .modal-content::-webkit-scrollbar { width: 6px; }
    .modal-content::-webkit-scrollbar-thumb { background: #E4E7EC; border-radius: 10px; }
    /* Container yang membungkus judul dan filter */
    .table-header {
        display: flex;
        justify-content: space-between; /* Ini kunci agar kiri-kanan lurus */
        align-items: center;
        margin-bottom: 20px;
        padding: 0 5px;
    }

    .table-title {
        font-size: 18px;
        font-weight: 600;
        color: #344054;
        margin: 0;
    }

    /* Tombol Filter di Pojok Kanan */
    .btn-filter {
        display: flex;
        align-items: center;
        gap: 8px;
        background: white;
        border: 1px solid #D0D5DD;
        padding: 8px 16px;
        border-radius: 8px;
        color: #344054;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-filter:hover {
        background-color: #f9fafb;
        border-color: #b3b9c4;
    }
    /* Ikon Filter */
    .filter-icon-svg {
        width: 16px;
        height: 16px;
        background-color: #667085;
        display: inline-block;
        -webkit-mask: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/></svg>') no-repeat center;
        mask: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/></svg>') no-repeat center;
    }
    .table-actions {
        position: relative; /* Penting agar dropdown muncul di posisi yang benar */
    }

    .filter-dropdown {
        display: none; /* Sembunyi secara default */
        position: absolute;
        top: 45px;
        right: 0;
        background: white;
        border: 1px solid #D0D5DD;
        border-radius: 8px;
        padding: 16px;
        box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
        z-index: 100;
        width: 280px;
    }

    .filter-dropdown.show {
        display: block; /* Muncul saat tombol diklik */
    }

    .filter-group {
        margin-bottom: 12px;
    }

    .filter-group label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #344054;
        margin-bottom: 5px;
    }

    .filter-group input {
        width: 100%;
        padding: 8px;
        border: 1px solid #D0D5DD;
        border-radius: 6px;
        font-size: 14px;
        font-family: inherit;
    }

    .filter-footer {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 15px;
    }

    .btn-apply {
        background: #448DF2;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 13px;
    }
    
.info-item-row {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.info-item-row label {
    flex: 0 0 40%; /* Lebar label 40% */
    font-size: 13px;
    color: #667085;
    font-weight: 500;
    margin-bottom: 0; /* Menghapus margin bawah bawaan */
}

.info-item-row .input-wrapper {
    flex: 0 0 60%; /* Lebar input 60% */
}

.info-item-row input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #D0D5DD;
    border-radius: 8px;
    background: #FFFFFF;
    font-size: 13px;
    color: #344054;
    box-sizing: border-box;
}

/* Mempercantik Header Modal */
.modal-header h2 {
    font-size: 18px;
    font-weight: 600;
    color: #344054;
}

.btn-tutup-merah {
    background: #F04438;
    color: white;
    border: none;
    padding: 8px 24px; 
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: 0.2s;
}

.btn-tutup-merah:hover {
    background: #D92D20;
}
/* Judul Utama (Sama seperti Dashboard/Pinjam Berkas) */
.v1_230 { 
    color: #383E49; /* Warna abu-abu gelap */
    font-family: 'Inter', sans-serif;
    font-weight: 500; /* Medium */
    font-size: 15px;
    display: block;
    margin-bottom: 4px; /* Jarak antara judul dan subtitle */
}

/* Subtitle (Sama seperti Dashboard/Pinjam Berkas) */
.v1_232 { 
    color: #A2A2A2; /* Warna abu-abu muda */
    font-family: 'Barlow', sans-serif;
    font-weight: 500;
    font-size: 14px;
    display: block;
}

/* Tambahkan ini agar konten tidak menempel ke atas layar */
.content-wrapper {
    padding-top: 25px; /* Jarak dari atas layar */
    padding-left: 40px; /* Jarak dari sidebar */
}
    </style>
    <link href="{{ asset('css/main.css') }}" rel="stylesheet" />
</head>
<body>

<div class="v1_203 main-container">
    
    {{-- SIDEBAR --}}
    <div class="v1_204 sidebar" id="main-sidebar">
            <div class="v1_205 sidebar-header">
                <div class="v1_207 sidebar-logo-area">
                    <div class="v1_208"></div> {{-- Pastikan CSS v1_208 untuk logo sudah ada --}}
                    <span class="sidebar-system-title">Sistem Arsip Digital</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <ul>
                    <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <a href="{{ route('dashboard') }}">
                            <div class="menu-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                            </div> Dashboard
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('pengiriman-berkas.*') ? 'active' : '' }}">
                        <a href="{{ route('pengiriman-berkas.index') }}">
                            <div class="menu-icon">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 4V20M12 4L18 10M12 4L6 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18 17H20C21.1046 17 22 17.8954 22 19V20C22 21.1046 21.1046 22 20 22H4C2.89543 22 2 21.1046 2 20V19C2 17.8954 2.89543 17 4 17H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div> Pengiriman Berkas
                        </a>
                    </li>
                    <li class="{{ request()->is('penerimaan-berkas*') ? 'active' : '' }}">
                        <a href="/penerimaan-berkas">
                            <div class="menu-icon">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 20V4M12 20L18 14M12 20L6 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18 17H20C21.1046 17 22 17.8954 22 19V20C22 21.1046 21.1046 22 20 22H4C2.89543 22 2 21.1046 2 20V19C2 17.8954 2.89543 17 4 17H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div> Penerimaan Berkas
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('pencarian-berkas.*') || request()->is('pencarian-berkas*') ? 'active' : '' }}">
                        <a href="{{ route('pencarian-berkas.index') }}">
                            <div class="menu-icon">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11 19C15.4183 19 19 15.4183 19 11C19 6.58172 15.4183 3 11 3C6.58172 3 3 6.58172 3 11C3 15.4183 6.58172 19 11 19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M20.9999 21L18.4999 18.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div> Pencarian Berkas
                        </a>
                    </li>
                    <li class="{{ request()->is('pinjam-berkas*') ? 'active' : '' }}">
                        <a href="/pinjam-berkas">
                            <div class="menu-icon">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6C5.44772 2 5 2.44772 5 3V21C5 21.5523 5.44772 22 6 22H18C18.5523 22 19 21.5523 19 21V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 2V8H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div> Pinjam Berkas
                        </a>
                    </li>
                </ul>
            </nav>

            {{-- Logout Button --}}
            <div class="v1_353 logout-bottom"> 
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf 
                </form>
                <button type="submit" form="logout-form" class="logout-button">
                    <div class="menu-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 3H20C20.5523 3 21 3.44772 21 4V20C21 20.5523 20.5523 21 20 21H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 17L15 12L10 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 12H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div> Log Out
                </button>
            </div>
        </div>

        <div class="content-wrapper">
        <div class="header-top" style="margin-bottom: 24px;">
        <div class="title-area">
            <h1 class="v1_230">Pencarian Berkas</h1>
            <p class="v1_232">Silakan masukkan nomor permohonan untuk mencari berkas.</p>
        </div>
        </div>

        <div class="search-box-container">
            <form action="{{ route('pencarian-berkas.search') }}" method="GET" style="display:flex; gap:10px;">
                <input type="text" name="nomor_permohonan" placeholder="Masukkan nomor permohonan..." value="{{ request('nomor_permohonan') }}">
                <button type="submit">Cari</button>
            </form>
        </div>

        <div class="result-card">
        <div class="table-header">
    <h2 class="table-title">Hasil Pencarian Berkas</h2>
    
    <div class="table-actions" style="position: relative;">
        <button type="button" class="btn-filter" onclick="toggleFilter()">
            <i class="filter-icon-svg"></i> Filters
        </button>

        <div id="filterDropdown" class="filter-dropdown">
            <form action="{{ route('pencarian-berkas.search') }}" method="GET">
                <input type="hidden" name="nomor_permohonan" value="{{ request('nomor_permohonan') }}">
                
                <div class="filter-group">
                    <label>Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}">
                </div>
                
                <div class="filter-group">
                    <label>Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}">
                </div>

                <div class="filter-footer">
                    <button type="submit" class="btn-apply">Terapkan Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Nomor Permohonan</th>
            <th>Nama Pemohon</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($results) && $results->count() > 0)
            @foreach($results as $item)
            <tr>
                <td>{{ $item->no_permohonan }}</td>
                <td>{{ $item->nama_pemohon ?? $item->nama ?? '-' }}</td> 
                <td>
                    <button type="button" 
                            class="btn-detail" 
                            onclick="showDetail(JSON.parse(atob('{{ base64_encode(json_encode($item)) }}')))">
                        Detail
                    </button>
                </td>
            </tr>
            @endforeach
        @else
            <tr>
                <td colspan="3" style="text-align:center; padding: 40px; color: #667085;">Data tidak ditemukan</td>
            </tr>
        @endif
    </tbody>
    </table>
    </div>
</div>
<div id="detailModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Detail</h2>
            <span style="cursor:pointer; font-size:18px; color: #98A2B3;" onclick="closeModal()">&times;</span>
        </div>
        
        <div class="modal-body">
            <div class="info-list">
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
                <div class="info-item-row">
                    <label>{{ $label }}</label>
                    <div class="input-wrapper">
                        <input type="text" id="{{ $id }}" readonly>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div style="display: flex; justify-content: flex-end;">
                <button class="btn-tutup-merah" onclick="closeModal()">Tutup</button>
            </div>
        </div>
    </div>
</div>
<script>
function showDetail(item) {
    console.log("Data Item:", item); // Cek ini di F12 Console

    // Isi data ke modal (Gunakan || '-' agar jika null tidak kosong melompong)
    document.getElementById('m_no_permohonan').value = item.no_permohonan || '-';
    document.getElementById('m_tgl_permohonan').value = item.tanggal_permohonan || '-';
    document.getElementById('m_tgl_terbit').value = item.tanggal_terbit || '-';
    document.getElementById('m_nama').value = item.nama_pemohon || item.nama || '-';
    document.getElementById('m_tempat_lahir').value = item.tempat_lahir || '-';
    document.getElementById('m_tgl_lahir').value = item.tanggal_lahir || '-';
    document.getElementById('m_gender').value = item.jenis_kelamin || '-';
    document.getElementById('m_telp').value = item.no_telp || '-';
    document.getElementById('m_jns_permohonan').value = item.jenis_permohonan || '-';
    document.getElementById('m_jns_paspor').value = item.jenis_paspor || '-';
    document.getElementById('m_tujuan').value = item.tujuan_paspor || '-';
    document.getElementById('m_no_paspor').value = item.no_paspor || '-';
    document.getElementById('m_alur').value = item.alur_terakhir || '-';
    document.getElementById('m_lokasi').value = item.lokasi_arsip || '-';

    document.getElementById('detailModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('detailModal').style.display = 'none';
}

    function toggleFilter() {
        const dropdown = document.getElementById('filterDropdown');
        dropdown.classList.toggle('show');
    }

    // Klik di luar dropdown untuk menutup
    window.onclick = function(event) {
        if (!event.target.closest('.table-actions')) {
            const dropdown = document.getElementById('filterDropdown');
            if (dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            }
        }
    }

    // Fungsi showDetail kamu yang sudah ada tetap di sini...

</script>
</body>
</html>