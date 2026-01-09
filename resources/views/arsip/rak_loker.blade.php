@extends('layouts.app')

@section('title', 'Master Rak Loker')
@section('page-title', 'Master Rak Loker')
@section('page-subtitle', 'Manajemen kapasitas penyimpanan fisik berkas arsip.')

@section('content')
<div class="row g-4">
    {{-- FORM INPUT RAK BARU --}}
    <div class="col-md-4">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-dark"><i class="fas fa-plus-circle me-2 text-primary"></i>Tambah Lemari Baru</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('rak-loker.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nomor Lemari</label>
                        <input type="text" name="no_lemari" class="form-control" placeholder="Contoh: 5" required>
                        <div class="form-text" style="font-size: 11px;">Hanya angka/huruf utama lemari.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Jumlah Rak di Lemari Ini</label>
                        <input type="number" name="jumlah_rak" class="form-control" placeholder="Contoh: 3" min="1" max="26" required>
                        <div class="form-text" style="font-size: 11px;">Otomatis akan dibuatkan rak a, b, c, dst.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Kapasitas per Rak</label>
                        <input type="number" name="kapasitas" class="form-control" placeholder="Contoh: 100" required>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm" style="border-radius: 8px;">
                        <i class="fas fa-save me-2"></i> Simpan Data Rak
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- TABEL DAFTAR RAK --}}
    <div class="col-md-8">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-dark"><i class="fas fa-th-list me-2 text-primary"></i>Monitoring Kapasitas Rak</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">No. Lemari</th>
                                <th>Lokasi Rak</th>
                                <th>Kapasitas</th>
                                <th>Terisi (Counter)</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rak as $row)
                            <tr>
                                <td class="ps-4 fw-bold">Lemari {{ $row->no_lemari }}</td>
                                <td class="text-primary fw-bold">{{ strtoupper($row->kode_rak) }}</td>
                                <td>{{ $row->kapasitas }} Berkas</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">{{ $row->terisi }}</span>
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            @php $persen = ($row->terisi / $row->kapasitas) * 100; @endphp
                                            <div class="progress-bar {{ $persen > 80 ? 'bg-danger' : 'bg-success' }}" role="progressbar" style="width: {{ $persen }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($row->status == 'Tersedia')
                                        <span class="badge bg-success-subtle text-success px-3">Tersedia</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-3">Penuh</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($row->terisi == 0)
                                    <form action="{{ route('rak-loker.destroy', $row->id) }}" method="POST" onsubmit="return confirm('Hapus rak ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                    @else
                                    <span class="text-muted small">Aktif</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">Belum ada data rak. Silakan tambah lemari di sebelah kiri.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<script>
    setTimeout(() => { alert("{{ session('success') }}"); }, 500);
</script>
@endif
@if(session('error'))
<script>
    setTimeout(() => { alert("{{ session('error') }}"); }, 500);
</script>
@endif

@endsection