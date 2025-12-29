@extends('layouts.app')

@section('page-title', 'Pengiriman Berkas')
@section('page-subtitle', 'Silakan input data permohonan untuk pengiriman baru')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="mb-4">
                <a href="{{ route('pengiriman-berkas.create') }}" class="btn btn-primary fw-bold">
                    <i class="fas fa-plus-circle me-1"></i> Pengiriman Berkas
                </a>
            </div>

            <h6 class="fw-bold mb-3">Tabel Riwayat Pengiriman Berkas</h6>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No. Pengirim</th>
                            <th>Tanggal Pengirim</th>
                            <th>Tanggal Diterima</th>
                            <th>Jumlah Berkas</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayat as $row)
                        <tr>
                            <td class="fw-bold">{{ $row->no_pengirim }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->tgl_pengirim)->format('d-m-Y') }}</td>
                            <td>{{ $row->tgl_diterima ? \Carbon\Carbon::parse($row->tgl_diterima)->format('d-m-Y') : '-' }}</td>
                            <td>{{ $row->jumlah_berkas }}</td>
                            <td>
                                @if($row->status == 'Diajukan')
                                    <span class="badge bg-light text-dark border">Diajukan</span>
                                @else
                                    <span class="badge bg-success">Disetujui</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button class="btn btn-primary btn-sm px-3">Detail</button>
                                <button class="btn btn-outline-primary btn-sm">List Berkas yang dikirim</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada riwayat pengiriman.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection