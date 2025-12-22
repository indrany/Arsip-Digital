@extends('layouts.app') 

@section('title', 'Penerimaan Berkas') 
@section('page-title', 'Penerimaan Berkas')
@section('page-subtitle', 'Verifikasi dan penerimaan berkas permohonan satu per satu.')

@section('content')

{{-- Input Scan Barcode (Hidden secara visual tapi tetap aktif) --}}
<div style="position: fixed; top: 0; left: 0; width: 1px; height: 1px; opacity: 0; overflow: hidden; z-index: -1;">
    <input type="text" id="input-barcode-permohonan" autofocus autocomplete="off">
</div>

<div class="form-container mb-4" style="border: none; padding: 0;">
    <div class="row align-items-center">
        <div class="col-md-12 d-flex justify-content-end align-items-center">
             <div class="barcode-area text-end p-3 rounded bg-light border">
                 <h5 class="m-0 text-muted">Berkas Menunggu Konfirmasi</h5>
                 <span id="scan-count" class="h3 fw-bold text-success">{{ count($list_sudah_scan) }} Berkas</span>
             </div>
        </div>
    </div>
</div>

<div class="row penerimaan-berkas-container">
    {{-- KOLOM KIRI (Data Berkas Dikirim) --}}
    <div class="col-lg-6 mb-4">
        <div class="form-container" style="min-height: 450px;">
            <div class="section-title">Data Berkas Dikirim</div>
            <div class="table-container p-0 border-0 shadow-none">
                <table id="table-berkas-dikirim" class="custom-table">
                    <thead>
                        <tr>
                            <th>No. Permohonan</th>
                            <th>Nama</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-berkas-dikirim">
                        @forelse ($list_semua as $item)
                            <tr data-permohonan-id="{{ $item->no_permohonan }}" 
                                style="{{ $item->status_berkas == 'DITERIMA' ? 'background-color: #d1fae5;' : '' }}">
                                <td class="fw-bold {{ $item->status_berkas == 'DITERIMA' ? 'text-success' : '' }}">
                                    {{ $item->no_permohonan }}
                                    @if($item->status_berkas == 'DITERIMA')
                                        <i class="fas fa-check-circle ms-1"></i>
                                    @endif
                                </td>
                                <td>{{ $item->nama }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info text-white btn-detail-native" data-item="{{ json_encode($item) }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr class="empty-row">
                                <td colspan="3" class="text-center text-muted">Tidak ada berkas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- KOLOM KANAN (Berkas Diterima) --}}
    <div class="col-lg-6 mb-4">
        <div class="form-container" style="min-height: 450px;">
            <div class="section-title">Berkas Diterima</div>
            <div class="table-container p-0 border-0 shadow-none">
                <table id="table-berkas-diterima" class="custom-table">
                    <thead>
                        <tr>
                            <th>No. Permohonan</th>
                            <th>Nama</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-berkas-diterima">
                        @forelse ($list_sudah_scan as $item)
                            <tr class="animate__animated animate__fadeIn baris-diterima" data-id="{{ $item->no_permohonan }}">
                                <td class="fw-bold">{{ $item->no_permohonan }}</td>
                                <td>{{ $item->nama }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info text-white btn-detail-native" data-item="{{ json_encode($item) }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr class="empty-row-kanan">
                                <td colspan="3" class="text-center text-muted">Gunakan barcode scanner.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="form-footer">
    <button id="btn-simpan-penerimaan" class="action-button primary-action" style="background-color: #10b981;" {{ count($list_sudah_scan) > 0 ? '' : 'disabled' }}>
        <i class="fas fa-check-circle me-2"></i> Simpan & Konfirmasi Penerimaan (<span id="count-simpan">{{ count($list_sudah_scan) }}</span> Berkas)
    </button>
</div>

{{-- MODAL DETAIL (Langsung di sini agar tidak error) --}}
<div class="modal fade" id="modalDetailBerkas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
            <div class="modal-header bg-light py-2">
                <h6 class="modal-title fw-bold text-secondary">Detail</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="form-detail-pop">
                    @php
                        $fields = [
                            'nomor'        => 'Nomor Permohonan',
                            'tgl-mohon'    => 'Tanggal Permohonan',
                            'tgl-terbit'   => 'Tanggal Terbit',
                            'nama'         => 'Nama',
                            'tempat-lahir' => 'Tempat Lahir',
                            'tgl-lahir'    => 'Tanggal Lahir',
                            'jk'           => 'Jenis Kelamin',
                            'telp'         => 'No Telpon',
                            'jenis-mohon'  => 'Jenis Permohonan',
                            'jenis-paspor' => 'Jenis Paspor',
                            'tujuan'       => 'Tujuan Paspor',
                            'no-paspor'    => 'No Paspor',
                            'alur'         => 'Alur Terakhir',
                            'lokasi'       => 'Lokasi Arsip'
                        ];
                    @endphp

                    @foreach($fields as $id => $label)
                    <div class="row mb-2 align-items-center">
                        <label class="col-sm-5 col-form-label-sm text-muted">{{ $label }}</label>
                        <div class="col-sm-7">
                            <input type="text" id="det-{{ $id }}" class="form-control form-control-sm bg-white" readonly style="border: 1px solid #dee2e6; border-radius: 6px;">
                        </div>
                    </div>
                    @endforeach
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-danger btn-sm px-4" data-bs-dismiss="modal" style="background-color: #ff5a5a; border: none; border-radius: 6px;">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const inputBarcode = $('#input-barcode-permohonan');
    const beepSuccess = new Audio('https://assets.mixkit.co/active_storage/sfx/2568/2568-preview.mp3');
    const beepError = new Audio('https://assets.mixkit.co/active_storage/sfx/2573/2573-preview.mp3'); // Suara Buzzer Peringatan

    // 1. PENJAGA KURSOR (Agar kursor selalu standby menerima ketikan dari HP)
    function forceFocus() {
        if (!$('.modal').is(':visible')) {
            inputBarcode.focus();
        }
    }
    
    // Paksa kursor fokus setiap 1 detik agar scanner HP selalu masuk ke input rahasia
    setInterval(forceFocus, 1000); 
    $(document).on('click', forceFocus);
    $('#modalDetailBerkas').on('hidden.bs.modal', forceFocus);

    // 2. PROSES SCAN & VALIDASI
    inputBarcode.on('keypress', function(e) {
        if (e.which === 13) { 
            e.preventDefault();
            const barcode = $(this).val().trim();
            
            if (barcode) {
                console.log("Memproses scan: " + barcode);
                $.post("{{ route('penerimaan-berkas.scan-permohonan') }}", {
                    _token: "{{ csrf_token() }}",
                    nomor_permohonan: barcode
                })
                .done(res => {
                    if (res.success) {
                        beepSuccess.play(); 
                        location.reload(); // Reload agar angka 0 berubah jadi 1
                    }
                })
                .fail(err => {
                    // NOTIFIKASI JIKA BARCODE TIDAK PAS
                    beepError.play(); 
                    alert("⚠️ Barcode tidak sesuai, silahkan scan ulang!"); 
                    
                    inputBarcode.val(''); // Kosongkan input agar bisa scan ulang
                    forceFocus();         // Kembalikan kursor ke posisi standby
                })
                .always(() => inputBarcode.val(''));
            }
        }
    });

    // 3. POLLING OTOMATIS (Deteksi scan dari HP secara real-time)
    setInterval(function() {
        $.get("{{ route('penerimaan-berkas.check-new-scan') }}", function(res) {
            // Jika Controller mendeteksi ada data baru berstatus 'DITERIMA'
            if (res.has_new === true) { 
                console.log("Data baru terdeteksi dari HP, me-reload...");
                location.reload(); 
            }
        });
    }, 2000); // Cek ke database setiap 2 detik

    // 4. LOGIKA TOMBOL DETAIL (Melihat data detail permohonan)
    $(document).on('click', '.btn-detail-native', function() {
        const data = $(this).data('item');
        $('#det-nomor').val(data.no_permohonan || '-');
        $('#det-tgl-mohon').val(data.tgl_permohonan || '-');
        $('#det-tgl-terbit').val(data.tanggal_terbit || '-');
        $('#det-nama').val(data.nama || '-');
        $('#det-tempat-lahir').val(data.tempat_lahir || '-');
        $('#det-tgl-lahir').val(data.tanggal_lahir || '-');
        $('#det-jk').val(data.jenis_kelamin || '-');
        $('#det-telp').val(data.no_telp || '-');
        $('#det-jenis-mohon').val(data.jenis_permohonan || '-');
        $('#det-jenis-paspor').val(data.jenis_paspor || '-');
        $('#det-tujuan').val(data.tujuan_paspor || '-');
        $('#det-no-paspor').val(data.no_paspor || '-');
        $('#det-alur').val(data.status_berkas || '-');
        $('#det-lokasi').val(data.lokasi_arsip || '-');
        $('#modalDetailBerkas').modal('show');
    });

    // 5. AKSI TOMBOL SIMPAN & KONFIRMASI (Mereset angka ke 0)
    $(document).on('click', '#btn-simpan-penerimaan', function() {
        if(!confirm("Simpan sesi penerimaan ini dan mulai sesi baru?")) return;

        $.ajax({
            url: "{{ route('penerimaan-berkas.konfirmasi-bulk') }}",
            method: "POST",
            data: { _token: "{{ csrf_token() }}" },
            success: function(res) {
                if(res.success) {
                    alert("Status berhasil diperbarui menjadi DITERIMA OLEH ARSIP");
                    window.location.reload(); // Angka kembali jadi 0 Berkas
                }
            },
            error: function(xhr) {
                alert("Terjadi kesalahan: " + xhr.responseJSON.message);
            }
        });
    });
});
</script>
@endpush