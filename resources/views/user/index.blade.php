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
                <button class="btn btn-sm btn-outline-warning" 
                        data-bs-toggle="modal" 
                        data-bs-target="#modalEditUser{{ $user->id }}" 
                        title="Edit User">
                    <i class="fas fa-edit"></i> Edit
                </button>

                {{-- Tombol Ubah Status --}}
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

            {{-- MODAL EDIT USER (Harus di dalam loop agar ID-nya sesuai) --}}
            <div class="modal fade" id="modalEditUser{{ $user->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="{{ route('users.update', $user->id) }}" method="POST" class="modal-content text-start">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit User: {{ $user->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                        <div class="mb-3">
                                <label class="form-label fw-bold">Username</label>
                                <input type="text" name="username" class="form-control" value="{{ $user->name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control" value="{{ $user->nama_lengkap }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Role (Unit Kerja)</label>
                                <select name="role" class="form-select" required>
                                    <option value="TIKIM" {{ $user->role == 'TIKIM' ? 'selected' : '' }}>TIKIM</option>
                                    <option value="LANTASKIM" {{ $user->role == 'LANTASKIM' ? 'selected' : '' }}>LANTASKIM</option>
                                    <option value="INTELDAKIM" {{ $user->role == 'INTELDAKIM' ? 'selected' : '' }}>INTELDAKIM</option>
                                    <option value="INTELTUSKIM" {{ $user->role == 'INTELTUSKIM' ? 'selected' : '' }}>INTELTUSKIM</option>
                                    <option value="ADMIN" {{ $user->role == 'ADMIN' ? 'selected' : '' }}>ADMIN</option>
                                </select>
                            </div>
                            <div class="mb-3">
                            <label class="form-label fw-bold">Password Baru (Kosongkan jika tidak ganti)</label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control pass-input" placeholder="Masukkan password baru">
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select name="is_active" class="form-select" required>
                                    <option value="1" {{ $user->is_active ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ !$user->is_active ? 'selected' : '' }}>Tidak Aktif</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Update Data</button>
                        </div>
                    </form>
                </div>
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
{{-- MODAL TAMBAH USER --}}
<div class="modal fade" id="modalTambahUser" tabindex="-1" autocomplete="off">
    <div class="modal-dialog">
        <form action="{{ route('users.store') }}" method="POST" class="modal-content">
            @csrf
            {{-- Trik matiin autofill --}}
            <input type="text" style="display:none">
            <input type="password" style="display:none">

            <div class="modal-header">
                <h5 class="modal-title">Input Data User Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Contoh: admin_tikim" required autocomplete="off">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama Lengkap Pegawai" required>
                </div>
                {{-- EMAIL DIHAPUS DARI SINI --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" class="form-control pass-input" placeholder="Minimal 6 karakter" required autocomplete="new-password">
                        <button class="btn btn-outline-secondary toggle-password" type="button"><i class="fas fa-eye"></i></button>
                    </div>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ambil semua tombol dengan class toggle-password
        const toggleButtons = document.querySelectorAll('.toggle-password');

        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Cari input password di dalam grup yang sama dengan tombol ini
                const input = this.closest('.input-group').querySelector('.pass-input');
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    // Ubah jadi teks biasa agar terlihat
                    input.type = 'text';
                    // Ganti ikon jadi mata tertutup
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    // Ubah balik jadi password (bintang-bintang)
                    input.type = 'password';
                    // Ganti ikon jadi mata terbuka
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    });
</script>