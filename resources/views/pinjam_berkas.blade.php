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
                <button type="button" class="btn-filter-custom" onclick="toggleFilter()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>
                    Filters
                </button>

                <div id="filterDropdown" class="filter-dropdown-custom">
                <form onsubmit="applyFilter(); return false;">
                        <div class="filter-group-custom">
                            <label>Dari Tanggal</label>
                            <input type="date" name="from">
                        </div>
                        <div class="filter-group-custom">
                            <label>Sampai Tanggal</label>
                            <input type="date" name="to">
                        </div>
                        <button type="submit" class="btn-submit-filter">Terapkan</button>
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
                        <th>Tempat Lahir</th>
                        <th>Tanggal Lahir</th>
                        <th>Nama Peminjam</th>
                        <th>Aksi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
    <tr>
        <td>023445677744</td>
        <td>08-12-2025</td>
        <td>Ahmad Budianto</td>
        <td>Surabaya</td>
        <td>05-06-1985</td>
        <td>Nando</td>
        <td>
    <div class="aksi-wrapper" style="display:flex; gap:5px; align-items:center;">
    <button onclick="setujui(this)" class="btn-check-custom">
    ✓
    </button>
    <button onclick="tolak(this)"
    style="width:28px;height:28px;background:#FF383C;color:white;border:none;border-radius:4px;">
    ✕
    </button>

        <button 
            type="button"
            style="background:#629FF4;color:white;border:none;padding:0 10px;border-radius:4px;font-size:11px;height:28px;">
            Detail
        </button>
    </div>
</td>

<td>
    <span class="badge-custom bg-pengajuan">Pengajuan</span>
</td>
    </tr>

</tbody>
<style>
    /* Styling khusus agar tidak bentrok dengan layout utama */
    .card-custom {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        border: none;
        margin-top: 10px;
    }

    .table-header-custom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .table-title-custom {
        font-size: 18px;
        font-weight: 600;
        color: #383E49;
        margin: 0;
    }

    .table-actions-custom { position: relative; }
    
    .btn-filter-custom {
        display: flex;
        align-items: center;
        gap: 8px;
        background: white;
        border: 1px solid #D0D5DD;
        padding: 8px 16px;
        border-radius: 8px;
        color: #5D6679;
        font-size: 14px;
        cursor: pointer;
    }

    .filter-dropdown-custom {
        display: none;
        position: absolute;
        top: 45px;
        right: 0;
        background: white;
        border: 1px solid #D0D5DD;
        border-radius: 8px;
        padding: 16px;
        box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
        z-index: 100;
        width: 260px;
    }
    .filter-dropdown-custom.show { display: block; }

    .table-custom {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .table-custom th {
        text-align: left;
        padding: 12px;
        border-bottom: 2px solid #F0F1F3;
        color: #171C26;
        font-weight: 600;
    }

    .table-custom td {
        padding: 12px;
        border-bottom: 1px solid #F0F1F3;
        color: #48505E;
        vertical-align: middle;
    }

    .badge-custom {
        padding: 5px 12px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        color: white;
    }
    .bg-pengajuan { background-color: #FFCC00; color: #fff; }
    .bg-disetujui { background-color: #34C759; }
    .bg-ditolak { background-color: #FF383C; }
    .bg-selesai { background-color: #0088FF; }

    .action-group-custom { display: flex; gap: 6px; }
    
    .btn-icon-custom {
        width: 26px;
        height: 26px;
        border-radius: 4px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        cursor: pointer;
    }
    .btn-check { 
        background-color: #34C759; 
    }
    .btn-cross { 
        background-color: #FF383C; 
    }
    
    .btn-detail-small-custom {
        background-color: #629FF4;
        color: white;
        border: none;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 11px;
        cursor: pointer;
    }

    .filter-group-custom { margin-bottom: 12px; }
    .filter-group-custom label { display: block; font-size: 12px; margin-bottom: 5px; color: #344054; }
    .filter-group-custom input { width: 100%; padding: 8px; border: 1px solid #D0D5DD; border-radius: 6px; box-sizing: border-box; }
    .btn-submit-filter { width: 100%; background: #1366D9; color: white; border: none; padding: 10px; border-radius: 6px; cursor: pointer; }

    .btn-icon-custom.btn-check { 
        background-color: #34C759 !important; /* Hijau Centang */
        color: white !important;
    }

    .btn-icon-custom.btn-cross { 
        background-color: #FF383C !important; /* Merah Silang */
        color: white !important;
    }

    /* Tambahan agar saat di-hover warna tidak hilang */
    .btn-icon-custom:hover {
        opacity: 0.8;
    }
    .popup-progress {
    width: 100%;
    height: 4px;
    background: #eee;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 16px;
}

.popup-progress-bar {
    height: 100%;
    width: 100%;
    background: #34C759;
    animation: progressRun 2s linear forwards;
}

@keyframes progressRun {
    from { width: 100%; }
    to   { width: 0%; }
}
.btn-check-custom {
    width: 28px;
    height: 28px;
    background: #34C759;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
}
.btn-selesai {
    background-color: #0D6EFD; /* biru tua */
    color: white;
    border: none;
    padding: 0 12px;
    height: 28px;
    border-radius: 4px;
    font-size: 11px;
    cursor: pointer;
}

</style>
    
<script>
function showPopup(type, message) {
    const overlay = document.getElementById('popupOverlay');
    const icon = document.getElementById('popupIcon');
    const title = document.getElementById('popupTitle');
    const text = document.getElementById('popupText');
    const progress = document.querySelector('.popup-progress-bar');

    if (type === 'success') {
        icon.innerHTML = '✓';
        icon.style.background = '#34C759';
        title.innerText = 'SUCCESS';
        title.style.color = '#34C759';
        text.innerText = message;
        progress.style.background = '#34C759';
    } else {
        icon.innerHTML = '✕';
        icon.style.background = '#FF383C';
        title.innerText = 'DITOLAK';
        title.style.color = '#FF383C';
        text.innerText = message;
        progress.style.background = '#FF383C';
    }

    // reset animasi progress
    progress.style.animation = 'none';
    progress.offsetHeight;
    progress.style.animation = 'progressRun 2s linear forwards';

    overlay.style.display = 'flex';

    setTimeout(() => {
        overlay.style.display = 'none';
    }, 2000);
}

function setujui(btn) {
    const row = btn.closest('tr');
    const status = row.querySelector('.badge-custom');
    const aksiWrapper = row.querySelector('.aksi-wrapper');

    // Status jadi disetujui
    status.innerText = 'Disetujui';
    status.className = 'badge-custom bg-disetujui';

    // Aksi diganti tombol "Selesai"
    aksiWrapper.innerHTML = `
        <button onclick="selesai(this)" class="btn-selesai">
            Selesai
        </button>

        <button 
            type="button"
            style="background:#629FF4;color:white;border:none;padding:0 10px;border-radius:4px;font-size:11px;height:28px;">
            Detail
        </button>
    `;

    showPopup('success', 'Berkas berhasil dipinjam');
}

function tolak(btn) {
    const row = btn.closest('tr');
    const status = row.querySelector('.badge-custom');
    const aksiWrapper = row.querySelector('.aksi-wrapper');

    status.innerText = 'Ditolak';
    status.className = 'badge-custom bg-ditolak';

    aksiWrapper.innerHTML = `
        <button 
            type="button"
            style="background:#629FF4;color:white;border:none;padding:0 10px;border-radius:4px;font-size:11px;height:28px;">
            Detail
        </button>
    `;

    showPopup('error', 'Berkas ditolak dipinjam');
}

function selesai(btn) {
    const row = btn.closest('tr');
    const status = row.querySelector('.badge-custom');
    const aksiWrapper = row.querySelector('.aksi-wrapper');

    // Status jadi selesai
    status.innerText = 'Selesai';
    status.className = 'badge-custom bg-selesai';

    // Aksi tinggal detail
    aksiWrapper.innerHTML = `
        <button 
            type="button"
            style="background:#629FF4;color:white;border:none;padding:0 10px;border-radius:4px;font-size:11px;height:28px;">
            Detail
        </button>
    `;

    showPopup('success', 'Berkas telah dikembalikan');
}

function toggleFilter() {
    document.getElementById('filterDropdown').classList.toggle('show');
}

function applyFilter() {
    const from = document.querySelector('input[name="from"]').value;
    const to   = document.querySelector('input[name="to"]').value;

    const rows = document.querySelectorAll('.table-custom tbody tr');

    rows.forEach(row => {
        // Kolom tanggal permohonan = kolom ke-2
        const dateText = row.children[1].innerText.trim(); // 08-12-2025
        const rowDate = convertToDate(dateText);

        let show = true;

        if (from && rowDate < new Date(from)) show = false;
        if (to && rowDate > new Date(to)) show = false;

        row.style.display = show ? '' : 'none';
    });

    document.getElementById('filterDropdown').classList.remove('show');
}

function convertToDate(dateStr) {
    const [day, month, year] = dateStr.split('-');
    return new Date(year, month - 1, day);
}
</script>

<!-- POPUP MODAL -->
<div id="popupOverlay" style="position:fixed; inset:0; background:rgba(0,0,0,.45); display:none; align-items:center; justify-content:center; z-index:9999;">
    <div style="background:white; border-radius:12px; width:360px; padding:30px 20px; text-align:center;">
        
        <div id="popupIcon" style="
            width:72px;
            height:72px;
            margin:0 auto 16px;
            border-radius:50%;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:36px;
            color:white;
        ">✓</div>

        <h3 id="popupTitle" style="margin:0; font-size:20px; font-weight:700;">SUCCESS</h3>
        <p id="popupText" style="margin:10px 0 20px; font-size:14px; color:#555;"></p>

        <div class="popup-progress">
            <div class="popup-progress-bar"></div>
        </div>
    </div>
</div>
        </button>
    </div>
</div>

@endsection