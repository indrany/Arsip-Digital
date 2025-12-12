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
                <li class="active"><a href="#">
                    <div class="menu-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 13C4 12.4477 4.44772 12 5 12H11C11.5523 12 12 12.4477 12 13V19C12 19.5523 11.5523 20 11 20H5C4.44772 20 4 19.5523 4 19V13Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 4C14 3.44772 14.4477 3 15 3H19C19.5523 3 20 3.44772 20 4V8C20 8.55228 19.5523 9 19 9H15C14.4477 9 14 8.55228 14 8V4Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 15C14 14.4477 14.4477 14 15 14H19C19.5523 14 20 14.4477 20 15V20C20 20.5523 19.5523 21 19 21H15C14.4477 21 14 20.5523 14 20V15Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 4C4 3.44772 4.44772 3 5 3H9C9.55228 3 10 3.44772 10 4V10C10 10.5523 9.55228 11 9 11H5C4.44772 11 4 10.5523 4 10V4Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div> Dashboard
                </a></li>
                <li><a href="#">
                    <div class="menu-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 4V20M12 4L18 10M12 4L6 10" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18 17H20C21.1046 17 22 17.8954 22 19V20C22 21.1046 21.1046 22 20 22H4C2.89543 22 2 21.1046 2 20V19C2 17.8954 2.89543 17 4 17H6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div> Pengiriman Berkas
                </a></li>
                <li><a href="#">
                    <div class="menu-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 20V4M12 20L18 14M12 20L6 14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18 17H20C21.1046 17 22 17.8954 22 19V20C22 21.1046 21.1046 22 20 22H4C2.89543 22 2 21.1046 2 20V19C2 17.8954 2.89543 17 4 17H6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div> Penerimaan Berkas
                </a></li>
                <li><a href="#">
                    <div class="menu-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11 19C15.4183 19 19 15.4183 19 11C19 6.58172 15.4183 3 11 3C6.58172 3 3 6.58172 3 11C3 15.4183 6.58172 19 11 19Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M20.9999 21L18.4999 18.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div> Pencarian Berkas
                </a></li>
                <li><a href="#">
                    <div class="menu-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6C5.44772 2 5 2.44772 5 3V21C5 21.5523 5.44772 22 6 22H18C18.5523 22 19 21.5523 19 21V8L14 2Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 2V8H19" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div> Pinjam Berkas
                </a></li>
            </ul>
        </nav>

        <div class="v1_353 logout-bottom"> 
            <form method="POST" action="{{ route('logout') }}">
                @csrf 
                <button type="submit" class="logout-button" style="background: none; border: none; padding: 0; color: inherit; cursor: pointer; display: flex; align-items: center; width: 100%;">
                    <div class="menu-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 3H20C20.5523 3 21 3.44772 21 4V20C21 20.5523 20.5523 21 20 21H15" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 17L15 12L10 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 12H3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
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
                    <div class="imigrasi-logo-placeholder"></div>
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