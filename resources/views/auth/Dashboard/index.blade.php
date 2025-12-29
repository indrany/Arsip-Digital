<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Inter&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Poppins&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Barlow&display=swap" rel="stylesheet" />
    <link href="{{ asset('css/main.css') }}" rel="stylesheet" />
    <title>Sistem Arsip Digital - Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>

<div class="v1_203 main-container">
    {{-- SIDEBAR --}}
<div class="v1_204 sidebar" id="main-sidebar">
    <div class="v1_205 sidebar-header">
        <div class="v1_207 sidebar-logo-area">
            <div class="v1_208"></div> {{-- Pastikan class v1_208 ada di CSS untuk logo --}}
            <span class="sidebar-system-title">Sistem Arsip Digital</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul>
            {{-- Dashboard --}}
            <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}">
                    <div class="menu-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    </div> Dashboard
                </a>
            </li>

            {{-- Pengiriman Berkas --}}
            <li class="{{ request()->routeIs('pengiriman-berkas.*') ? 'active' : '' }}">
                <a href="{{ route('pengiriman-berkas.index') }}">
                    <div class="menu-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 4V20M12 4L18 10M12 4L6 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18 17H20C21.1046 17 22 17.8954 22 19V20C22 21.1046 21.1046 22 20 22H4C2.89543 22 2 21.1046 2 20V19C2 17.8954 2.89543 17 4 17H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div> Pengiriman Berkas
                </a>
            </li>

            {{-- Penerimaan Berkas --}}
            <li class="{{ request()->is('penerimaan-berkas*') ? 'active' : '' }}">
                <a href="/penerimaan-berkas">
                    <div class="menu-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 20V4M12 20L18 14M12 20L6 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18 17H20C21.1046 17 22 17.8954 22 19V20C22 21.1046 21.1046 22 20 22H4C2.89543 22 2 21.1046 2 20V19C2 17.8954 2.89543 17 4 17H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div> Penerimaan Berkas
                </a>
            </li>

            {{-- Pencarian Berkas --}}
            <li class="{{ request()->routeIs('pencarian-berkas.*') || request()->is('pencarian-berkas*') ? 'active' : '' }}">
                <a href="{{ route('pencarian-berkas.index') }}">
                    <div class="menu-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11 19C15.4183 19 19 15.4183 19 11C19 6.58172 15.4183 3 11 3C6.58172 3 3 6.58172 3 11C3 15.4183 6.58172 19 11 19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M20.9999 21L18.4999 18.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div> Pencarian Berkas
                </a>
            </li>

            {{-- Pinjam Berkas --}}
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
        <a href="{{ route('logout') }}" class="logout-button" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <div class="menu-icon">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 3H20C20.5523 3 21 3.44772 21 4V20C21 20.5523 20.5523 21 20 21H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 17L15 12L10 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 12H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div> Log Out
        </a>
    </div>
</div>

    {{-- MAIN CONTENT --}}
    <div class="content-wrapper">
        <div class="header-top">
            <div class="v1_221 header-background"></div>
            <div class="title-area">
                <span class="v1_230">Dashboard</span>
                <span class="v1_232">Hi, Admin. Selamat datang di Arsip Digital!</span>
            </div>
            <div class="header-right-tools">
                <div class="admin-profile-container">
                    <span class="admin-role-text">Admin</span>
                </div>
            </div>
        </div>
    
        <div class="statistic-row">
    <a href="{{ route('pengiriman-berkas.index') }}" class="card-link">
        <div class="statistic-card pemohon-card">
            <div class="card-icon-container">
                <svg class="card-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
            </div>
            <div class="card-content-area">
                <span class="card-value">70</span>
                <span class="card-label">Data Pemohon</span>
            </div>
        </div>
    </a>

    <a href="/pinjam-berkas" class="card-link">
        <div class="statistic-card dipinjam-card">
            <div class="card-icon-container">
                <svg class="card-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                    <line x1="10" y1="12" x2="14" y2="12"></line>
                    <line x1="12" y1="10" x2="12" y2="14"></line>
                </svg>
            </div>
            <div class="card-content-area">
                <span class="card-value">12</span>
                <span class="card-label">Berkas Dipinjam</span>
            </div>
        </div>
    </a>

    <div class="statistic-card aktif-card">
        <div class="card-icon-container">
            <svg class="card-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>
        <div class="card-content-area">
            <span class="card-value">25</span>
            <span class="card-label">Data Aktif</span>
        </div>
    </div>
</div>
        <div class="v1_309 chart-container">
            <div class="chart-header">
                <span class="chart-title">Aktivitas Berkas Bulanan</span>
                <div class="year-selector-container">
                    <label for="tahun-filter">Pilih Tahun:</label>
                    <select id="tahun-filter" onchange="updateChartData(this.value)"></select>
                </div>
            </div>
            <div class="data-chart-area">
                <canvas id="myLineChart"></canvas>
            </div>
        </div>     
    </div>
</div>

<script>
    // Logic Chart.js dan update statistic tetap di sini (sama seperti fungsi yang sudah Anda perbarui)
    let myChart; 
    const dummyData = {
        '2025': {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            pemohon: [15, 20, 18, 25, 30, 20, 25, 28, 50, 20, 15, 40],
            dipinjam: [10, 15, 12, 18, 20, 15, 18, 20, 17, 14, 11, 12]
        },
        '2026': {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            pemohon: [5, 10, 15, 12, 18, 25, 20, 35, 30, 25, 20, 85],
            dipinjam: [3, 8, 10, 8, 10, 12, 10, 15, 14, 12, 10, 15] 
        }
    };

    function updateStatisticCards(pemohon, dipinjam) {
        document.querySelector('.pemohon-card .card-value').textContent = pemohon;
        document.querySelector('.dipinjam-card .card-value').textContent = dipinjam;
        document.querySelector('.aktif-card .card-value').textContent = pemohon - dipinjam;
    }

    function initializeChart(year) {
    if (myChart) {
        myChart.destroy();
    }

    const dataTahunIni = dummyData[year] || dummyData['2025'];
    const ctx = document.getElementById('myLineChart'); 
    
    myChart = new Chart(ctx, { 
        type: 'line', 
        data: {
            labels: dataTahunIni.labels,
            datasets: [{
                label: 'Data Pemohon',
                data: dataTahunIni.pemohon, 
                backgroundColor: 'rgba(98, 159, 244, 0.3)', // Biru transparan untuk isi
                borderColor: 'rgba(98, 159, 244, 1)',
                borderWidth: 2, 
                fill: false, // DIUBAH JADI TRUE AGAR FULL WARNA
                tension: 0.3,
                pointRadius: 5
            },
            {
                label: 'Berkas Dipinjam',
                data: dataTahunIni.dipinjam, 
                backgroundColor: 'rgba(243, 105, 96, 0.3)', // Merah transparan untuk isi
                borderColor: 'rgba(243, 105, 96, 1)',
                borderWidth: 2,
                fill: false, // DIUBAH JADI TRUE AGAR FULL WARNA
                tension: 0.3,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, 
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                x: {
                    ticks: {
                        padding: 5 // TAMBAHKAN INI AGAR BULAN TIDAK MEPET BAWAH
                    }
                },
                y: {
                    beginAtZero: true,
                    suggestedMax: 100 
                }
            }
        }
    });
}
    function updateChartData(year) { initializeChart(year); }

    document.addEventListener('DOMContentLoaded', function() {
        const yearSelect = document.getElementById('tahun-filter');
        const availableYears = Object.keys(dummyData).sort((a, b) => b - a);
        availableYears.forEach(year => {
            const opt = document.createElement('option');
            opt.value = opt.textContent = year;
            yearSelect.appendChild(opt);
        });
        initializeChart(new Date().getFullYear());
    });
</script>
</body>
</html>