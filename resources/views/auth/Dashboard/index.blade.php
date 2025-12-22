<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Inter&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Poppins&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Barlow&display=swap" rel="stylesheet" />
    
    <link href="{{ asset('css/main.css') }}" rel="stylesheet" />
    <title>Sistem Arsip Imigrasi - Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
<button class="navbar-toggler" id="navbar-toggle-btn">
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
</button>

<!-- <nav class="sidebar-nav" id="main-sidebar">
    </nav> -->
<div class="v1_203 main-container">
    
    <div class="v1_204 sidebar" id="main-sidebar">
        
        <div class="v1_205 sidebar-header">
            <div class="v1_206">
                <div class="v1_207 sidebar-logo-area">
                    <div class="v1_208"></div>
                    <span class="sidebar-system-title">Sistem Arsip Digital</span>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <ul>
                {{-- Dashboard: Menggunakan logika dinamis untuk 'active' --}}
                <li class="{{ (isset($current_page) && $current_page == 'dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <div class="menu-icon dashboard-icon"></div> Dashboard
                    </a>
                </li>
                
                {{-- Pengiriman Berkas: Menggunakan logika dinamis untuk 'active' --}}
                <li class="{{ (isset($current_page) && $current_page == 'pengiriman-berkas') ? 'active' : '' }}">
                    <a href="{{ route('pengiriman-berkas.index') }}">
                        <div class="menu-icon kirim-icon"></div> Pengiriman Berkas
                    </a>
                </li>
                
                {{-- Penerimaan Berkas --}}
                <li><a href="#">
                    <div class="menu-icon terima-icon"></div> Penerimaan Berkas
                </a></li>
                
                {{-- Pencarian Berkas --}}
                <li class="{{ request()->is('pencarian-berkas*') ? 'active' : '' }}">
                    <a href="{{ route('pencarian-berkas.index') }}">
                        <div class="menu-icon cari-icon"></div> Pencarian Berkas
                    </a>
                </li>
                
                {{-- Pinjam Berkas --}}
                <li><a href="#">
                    <div class="menu-icon pinjam-icon"></div> Pinjam Berkas
                </a></li>
            </ul>
        </nav>
        <div class="v1_353 logout-bottom"> 
            <form method="POST" action="{{ route('logout') }}">
                @csrf 
                <button type="submit" class="logout-button" style="background: none; border: none; padding: 0; color: inherit; cursor: pointer; display: flex; align-items: center; width: 100%;">
                    <div class="menu-icon logout-icon"></div>
                    Log Out
                </button>
            </form>
        </div>
        
    </div> 
    
    <div class="content-wrapper">
        <div class="header-top">
            <div class="v1_221 header-background"></div>
            
            <div class="title-area">
                <span class="v1_230">Dashboard</span>
                <span class="v1_232">Hi, Admin. Selamat datang di Arsip Digital!</span>
            </div>

            <div class="header-right-tools">
                <div class="imigrasi-logo-container">
                    </div>
                <div class="admin-profile-container">
                    <span class="admin-role-text">Admin</span>
                </div>
            </div>
        </div>
    
        <div class="statistic-row">
    
            <div class="statistic-card pemohon-card">
                <div class="card-icon-container">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <line x1="10" y1="9" x2="8" y2="9"></line>
                    </svg>
                </div>
                <div class="card-content-area">
                    <span class="card-value">70</span>
                    <span class="card-label">Data Pemohon</span>
                </div>
            </div>
            
            <div class="statistic-card dipinjam-card">
                <div class="card-icon-container">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
            
            <div class="statistic-card aktif-card">
                <div class="card-icon-container">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                    <select id="tahun-filter" onchange="updateChartData(this.value)">
                        </select>
                </div>
            </div>
            
            <div class="data-chart-area">
                <canvas id="myLineChart"></canvas>
            </div>
        </div>     
    </div>

    <script>
    // Kode Chart.js tetap sama
    // Variabel global untuk menyimpan instance grafik
    let myChart; 

    // Data Dummy untuk setiap tahun
    const dummyData = {
        '2025': {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            pemohon: [15, 20, 18, 25, 30, 20, 25, 28, 50, 20, 15,40], // <-- Data Pemohon Akhir (Desember) dijadikan 70
            dipinjam: [10, 15, 12, 18, 20, 15, 18, 20, 17, 14, 11, 12] // <-- Berkas Dipinjam Akhir (Desember) dijadikan 12
        },
        '2026': {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            pemohon: [5, 10, 15, 12, 18, 25, 20, 35, 30, 25, 20, 85], // Contoh data tahun depan
            dipinjam: [3, 8, 10, 8, 10, 12, 10, 15, 14, 12, 10, 15] 
        }
        // Tambahkan tahun lain sesuai kebutuhan
    };


    // Fungsi untuk memperbarui semua card statistik
    function updateStatisticCards(dataPemohonDesember, dataDipinjamDesember) {
        // 1. Data Pemohon (mengambil nilai Desember untuk Pemohon)
        const totalPemohon = dataPemohonDesember;
        document.querySelector('.pemohon-card .card-value').textContent = totalPemohon;

        // 2. Berkas Dipinjam (mengambil nilai Desember untuk Dipinjam)
        const totalDipinjam = dataDipinjamDesember;
        document.querySelector('.dipinjam-card .card-value').textContent = totalDipinjam;

        // 3. Data Aktif (Logika: Data Pemohon - Berkas Dipinjam)
        const dataAktif = totalPemohon - totalDipinjam;
        document.querySelector('.aktif-card .card-value').textContent = dataAktif;
    }


    // Fungsi untuk inisialisasi atau memperbarui grafik
    function initializeChart(year) {
        // Hancurkan instance chart yang lama jika ada
        if (myChart) {
            myChart.destroy();
        }

        const dataTahunIni = dummyData[year] || dummyData['2025']; // Gunakan 2025 sebagai fallback

        const ctx = document.getElementById('myLineChart'); 
        
        const chartData = {
            labels: dataTahunIni.labels,
            datasets: [{
                label: 'Data Pemohon',
                data: dataTahunIni.pemohon, 
                backgroundColor: 'rgba(98, 159, 244, 0.7)', 
                borderColor: 'rgba(98, 159, 244, 1)',
                borderWidth: 2, 
                fill: false, 
                tension: 0.3,
                pointRadius: 5 // Tambahkan ini agar titiknya terlihat
            },
            {
                label: 'Berkas Dipinjam',
                data: dataTahunIni.dipinjam, 
                backgroundColor: 'rgba(243, 105, 96, 0.7)', 
                borderColor: 'rgba(243, 105, 96, 1)',
                borderWidth: 2,
                fill: false,
                tension: 0.3,
                pointRadius: 5 // Tambahkan ini agar titiknya terlihat
            }]
        };

        myChart = new Chart(ctx, { 
            type: 'line', 
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false, 
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                         mode: 'point',
                         intersect: true,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        // Pastikan skala Y cukup besar untuk menampung angka 70/85
                        suggestedMax: 100 
                    }
                }
            }
        });
        const dataPemohonDesember = dataTahunIni.pemohon[dataTahunIni.pemohon.length - 1]; // Index terakhir (Desember)
        const dataDipinjamDesember = dataTahunIni.dipinjam[dataTahunIni.dipinjam.length - 1]; // Index terakhir (Desember)
        
        updateStatisticCards(dataPemohonDesember, dataDipinjamDesember);
    }
    // Fungsi yang dipanggil saat dropdown "Pilih Tahun" diubah
    function updateChartData(year) {
        console.log('Memperbarui data untuk tahun:', year);
        
        // Panggil kembali fungsi inisialisasi dengan data tahun yang baru
        initializeChart(year);

    }
    // Fungsi untuk menginisialisasi grafik dan dropdown saat DOM sudah siap
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- 1. OTOMATISASI DROPDOWN TAHUN (START) ---
        const yearSelect = document.getElementById('tahun-filter');
        const currentYear = new Date().getFullYear(); 
        
        // Ambil semua tahun dari data dummy
        const availableYears = Object.keys(dummyData).map(Number).sort((a, b) => b - a);
        
        let defaultYear = currentYear;

        yearSelect.innerHTML = ''; 

        // Iterasi dan buat opsi
        availableYears.forEach(year => {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            
            // Tentukan tahun yang dipilih secara default
            if (year === currentYear) {
                option.selected = true;
                defaultYear = year; // Tetapkan tahun default
            }
            yearSelect.appendChild(option);
        });
        // --- OTOMATISASI DROPDOWN TAHUN (END) ---

        // --- 2. INISIALISASI GRAFIK AWAL ---
        // Inisialisasi grafik dengan tahun yang dipilih (default: currentYear atau 2025 jika 2026 belum ada)
        initializeChart(defaultYear);
    });

</script>
</body>
</html>