@extends('layouts.app') {{-- WAJIB: Agar sidebar konsisten di semua halaman --}}

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Hi, ' . Auth::user()->name . '. Selamat datang di Sistem Arsip Digital!')

@section('content')

@php
    $roleUser = strtoupper(Auth::user()->role);
@endphp

{{-- 1. ALERT KAPASITAS RAK (HANYA UNTUK ADMIN & TIKIM) --}}
@if(in_array($roleUser, ['ADMIN', 'TIKIM']))
    @if(isset($rakKritis) && ($rakKritis->count() > 0 || $rakPenuhCount > 0))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; background-color: #fff5f5; border-left: 5px solid #f97066 !important;">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-danger fa-2x me-3"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold text-danger mb-1">Peringatan Kapasitas Penyimpanan!</h6>
                        <p class="mb-0 text-muted" style="font-size: 13px;">
                            Terdapat <strong>{{ $rakKritis->count() }} rak</strong> hampir penuh (>80%) 
                            dan <strong>{{ $rakPenuhCount }} rak</strong> sudah penuh. 
                            Segera siapkan lemari/rak baru agar proses penerimaan berkas tidak terhambat.
                        </p>
                    </div>
                    <div class="ms-3">
                        <a href="{{ route('rak-loker.index') }}" class="btn btn-danger btn-sm fw-bold px-3 shadow-sm" style="border-radius: 8px;">
                            Cek Master Rak
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@endif

<div class="statistic-row">
    {{-- Card Data Pemohon --}}
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
            <span class="card-value">{{ $totalPemohon ?? 0 }}</span> 
            <span class="card-label">Data Pemohon</span>
            </div>
        </div>
    </a>

    {{-- Card Berkas Dipinjam --}}
    <a href="{{ route('pinjam-berkas.index') }}" class="card-link">
        <div class="statistic-card dipinjam-card">
            <div class="card-icon-container">
                <svg class="card-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                    <line x1="10" y1="12" x2="14" y2="12"></line>
                    <line x1="12" y1="10" x2="12" y2="14"></line>
                </svg>
            </div>
            <div class="card-content-area">
            <span class="card-value">{{ $totalDipinjam ?? 0 }}</span>
            <span class="card-label">Berkas Dipinjam</span>
            </div>
        </div>
    </a>

    {{-- 2. Card Monitoring Rak (HANYA UNTUK ADMIN & TIKIM) --}}
    @if(in_array($roleUser, ['ADMIN', 'TIKIM']))
    <a href="{{ route('rak-loker.index') }}" class="card-link">
        <div class="statistic-card aktif-card" style= border: 1px solid #b9e6fe;">
            <div class="card-icon-container" style="background-color: #F0FFDF; color: #75B06F;">
                <i class="fas fa-boxes-stacked" style="font-size: 24px;"></i>
            </div>
            <div class="card-content-area">
                <span class="card-value">{{ $rakPenuhCount ?? 0 }}</span>
                <span class="card-label">Rak Sudah Penuh</span>
            </div>
        </div>
    </a>
    @else
    {{-- JIKA BUKAN ADMIN/TIKIM, Tampilkan Card Default "Data Aktif" agar baris tetap terisi 3 card --}}
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
    @endif
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
@endsection

@push('scripts')
<script>
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

    function initializeChart(year) {
        if (myChart) { myChart.destroy(); }
        const dataTahunIni = dummyData[year] || dummyData['2025'];
        const ctx = document.getElementById('myLineChart'); 
        
        myChart = new Chart(ctx, { 
            type: 'line', 
            data: {
                labels: dataTahunIni.labels,
                datasets: [{
                    label: 'Data Pemohon',
                    data: dataTahunIni.pemohon, 
                    backgroundColor: 'rgba(98, 159, 244, 0.3)',
                    borderColor: 'rgba(98, 159, 244, 1)',
                    borderWidth: 2, 
                    fill: false,
                    tension: 0.3,
                    pointRadius: 5
                },
                {
                    label: 'Berkas Dipinjam',
                    data: dataTahunIni.dipinjam, 
                    backgroundColor: 'rgba(243, 105, 96, 0.3)',
                    borderColor: 'rgba(243, 105, 96, 1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.3,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, 
                plugins: { legend: { position: 'top' } },
                scales: {
                    x: { ticks: { padding: 5 } },
                    y: { beginAtZero: true, suggestedMax: 100 }
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
@endpush