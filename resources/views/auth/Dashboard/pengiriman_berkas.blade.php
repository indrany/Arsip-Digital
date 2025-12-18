@extends('layouts.app') 

@section('title', 'Pengiriman Berkas') 
@section('page-title', 'Pengiriman Berkas')
@section('page-subtitle', 'Kelola dan kirim berkas arsip.')

@section('content')
    
    {{-- Container Aksi: HANYA berisi tombol + Pengiriman Berkas (Sudah FIX Editor Error) --}}
    <div class="action-bar-container">
        {{-- Tombol Pengiriman Berkas --}}
        <a href="{{ route('pengiriman-berkas.create') }}" 
        class="action-button primary-action" 
        style="cursor: pointer; border: none; min-width: 200px; text-decoration: none; display: flex; align-items: center; justify-content: center;"> 
            
            <div class="action-icon-wrapper">
                <div class="plus-icon">+</div> 
            </div>
            
            <span class="action-text ml-2">Pengiriman Berkas</span> 
        </a>
        {{-- JUDUL TABEL DIHAPUS, hanya menyisakan tombol aksi --}}
    </div>

    {{-- Tabel Riwayat Pengiriman Berkas --}}
    <div class="table-container shadow-sm mt-4"> 
        <div class="table-responsive">
            <table class="table table-hover table-striped custom-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">No. Pengirim</th>
                        <th style="width: 20%;">Tanggal Pengirim</th>
                        <th style="width: 20%;">Tanggal Diterima</th>
                        <th style="width: 15%;">Jumlah Berkas</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 15%;">Aksi</th> {{-- Kolom Aksi Tambahan --}}
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayat_berkas as $berkas)
                        @php
                            $tanggal_diterima = $berkas->tanggal_diterima ?? null;
                            $status = empty($tanggal_diterima) ? 'Diajukan' : 'Disetujui';
                            $status_class = empty($tanggal_diterima) ? 'badge badge-warning' : 'badge badge-success';
                        @endphp

                        <tr>
                            <td>{{ $berkas->no_pengirim }}</td>
                            <td>{{ $berkas->tanggal_pengirim }}</td>
                            <td>{{ $tanggal_diterima ? $tanggal_diterima : '-' }}</td>
                            <td>{{ $berkas->jumlah_berkas }}</td>
                            <td><span class="{{ $status_class }}">{{ $status }}</span></td>
                            <td>
                                {{-- Aksi View Detail (Contoh) --}}
                                <a href="#" class="text-info mr-2" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(empty($tanggal_diterima))
                                {{-- Aksi Edit/Batalkan hanya jika status masih Diajukan --}}
                                <a href="#" class="text-danger" title="Batalkan Pengajuan">
                                    <i class="fas fa-times-circle"></i>
                                </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                Tidak ada riwayat pengiriman berkas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Contoh Pagination (MODIFIKASI: Ditengahkan agar Profesional) --}}
        <div class="d-flex justify-content-center align-items-center table-footer"> 
            
            {{-- Wrapper untuk Informasi dan Kontrol --}}
            <div class="pagination-center-content">
                <small class="text-muted pagination-info">Menampilkan 1-10 dari 87 data</small>
                <div class="pagination-controls">
                    <button class="btn btn-sm btn-outline-secondary" disabled>Sebelumnya</button>
                    <button class="btn btn-sm btn-outline-secondary">Berikutnya</button>
                </div>
            </div>
            
        </div>
        
    </div>

@endsection