@extends('layouts.app')

@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')
@section('page-subtitle', 'Kelola hak akses dan status aktif pengguna sistem')

@section('content')
<div class="container-fluid">
    {{-- Pesan Sukses --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center bg-white py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Pengguna Sistem</h6>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
                <i class="fas fa-user-plus"></i> Tambah User Baru
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Role / Unit</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td class="fw-bold">{{ $user->name }}</td>
                            <td>{{ $user->nama_lengkap }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge bg-outline-primary text-primary border border-primary">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Tidak Aktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    {{-- Tombol Edit --}}
                                    <button class="btn btn-sm btn-outline-warning" title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    {{-- Tombol Ubah Status (Aktif/Matikan) --}}
                                    <form action="{{ route('users.update-status', $user->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        @if($user->is_active)
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Matikan akun ini?')">
                                                Matikan
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Aktifkan akun ini?')">
                                                Aktifkan
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Data user tidak ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH USER --}}
<div class="modal fade" id="modalTambahUser" tabindex="-1" aria-labelledby="modalTambahUserLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('users.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahUserLabel">Input Data User Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Contoh: admin_tikim" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama Lengkap Pegawai" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="email@kemenkumham.go.id" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Role (Unit Kerja)</label>
                    <select name="role" class="form-select" required>
                        <option value="" selected disabled>-- Pilih Unit --</option>
                        <option value="TIKIM">TIKIM</option>
                        <option value="LANTASKIM">LANTASKIM</option>
                        <option value="INTELDAKIM">INTELDAKIM</option>
                        <option value="INTELTUSKIM">INTELTUSKIM</option>
                        <option value="ADMIN">ADMIN</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Status Awal</label>
                    <select name="is_active" class="form-select" required>
                        <option value="1">Langsung Aktif</option>
                        <option value="0">Non-Aktif (Pending)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan User</button>
            </div>
        </form>
    </div>
</div>
@endsection