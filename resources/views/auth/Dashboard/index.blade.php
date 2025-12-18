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
<div class="v1_203 main-container">
    
    <div class="v1_204 sidebar">
        
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
                <li><a href="#">
                    <div class="menu-icon cari-icon"></div> Pencarian Berkas
                </a></li>
                
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
                <div class="card-icon"></div>
                <span class="card-value">70</span>
                <span class="card-label">Data Pemohon</span>
            </div>
            <div class="statistic-card dipinjam-card">
                <div class="card-icon"></div>
                <span class="card-value">12</span>
                <span class="card-label">Berkas Dipinjam</span>
            </div>
            <div class="statistic-card aktif-card">
                <div class="card-icon"></div>
                <span class="card-value">25</span>
                <span class="card-label">Data Aktif</span>
            </div>
        </div>

        <div class="v1_309 chart-container">
            <div class="chart-header">
                <span class="chart-title">Aktivitas Berkas Bulanan</span>
            </div>
            <div class="data-chart-area">
                <canvas id="monthlyActivityChart"></canvas>
            </div>
        </div>
    
    </div>

<script>
    // Kode Chart.js tetap sama
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('monthlyActivityChart');

        const data = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
            datasets: [{
                label: 'Berkas Masuk',
                data: [15, 20, 18, 25, 30, 22],
                backgroundColor: 'rgba(45, 156, 219, 0.7)', 
                borderColor: 'rgba(45, 156, 219, 1)',
                borderWidth: 2, 
                fill: false, 
                tension: 0.3 
            },
            {
                label: 'Berkas Keluar',
                data: [10, 15, 12, 18, 20, 15],
                backgroundColor: 'rgba(243, 105, 96, 0.7)', 
                borderColor: 'rgba(243, 105, 96, 1)',
                borderWidth: 2,
                fill: false,
                tension: 0.3
            }]
        };

        new Chart(ctx, {
            type: 'line', 
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false, 
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>

</body>
</html>