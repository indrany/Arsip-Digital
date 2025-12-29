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

{{-- MODAL DETAIL (Disesuaikan dengan Desain Ramping & Padat) --}}
<div class="modal fade" id="modalDetailBerkas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; width: 410px; padding: 15px 25px;">
            <div class="modal-header border-0 p-0 mb-2">
                <h6 class="modal-title fw-bold text-secondary" style="font-size: 16px;">Detail</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 12px;"></button>
            </div>
            <div class="modal-body p-0">
                <form id="form-detail-pop">
                    @php
                        $fields = [
                            'm_no_permohonan'   => 'Nomor Permohonan',
                            'm_tgl_permohonan'  => 'Tanggal Permohonan',
                            'm_tgl_terbit'      => 'Tanggal Terbit',
                            'm_nama'            => 'Nama',
                            'm_tempat_lahir'    => 'Tempat Lahir',
                            'm_tgl_lahir'       => 'Tanggal Lahir',
                            'm_gender'          => 'Jenis Kelamin',
                            'm_telp'            => 'No Telpon',
                            'm_jns_permohonan'  => 'Jenis Permohonan',
                            'm_jns_paspor'      => 'Jenis Paspor',
                            'm_tujuan'          => 'Tujuan Paspor',
                            'm_no_paspor'       => 'No Paspor',
                            'm_alur'            => 'Alur Terakhir',
                            'm_lokasi'          => 'Lokasi Arsip'
                        ];
                    @endphp

                    @foreach($fields as $id => $label)
                    <div class="info-item-row d-flex align-items-center mb-1" style="margin-bottom: 6px !important;">
                        <label style="flex: 0 0 42%; font-size: 11px; color: #48505E; font-weight: 500;">{{ $label }}</label>
                        <div class="input-wrapper" style="flex: 0 0 58%;">
                            <input type="text" id="{{ $id }}" readonly 
                                   style="width: 100%; padding: 5px 10px; border: 1px solid #D0D5DD; border-radius: 6px; background: #FFFFFF; font-size: 11px; color: #344054; height: 28px;">
                        </div>
                    </div>
                    @endforeach
                </form>
            </div>
            <div class="modal-footer border-0 p-0 mt-3 d-flex justify-content-end">
                <button type="button" class="btn btn-danger btn-sm px-4 fw-medium" data-bs-dismiss="modal" 
                        style="background: #F97066; border: none; border-radius: 6px; font-size: 12px; padding: 7px 20px;">
                    Tutup
                </button>
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
    const beepError = new Audio('https://assets.mixkit.co/active_storage/sfx/2573/2573-preview.mp3');

    function forceFocus() {
        if (!$('.modal').is(':visible')) {
            inputBarcode.focus();
        }
    }
    
    setInterval(forceFocus, 1000); 
    $(document).on('click', forceFocus);
    $('#modalDetailBerkas').on('hidden.bs.modal', forceFocus);

    inputBarcode.on('keypress', function(e) {
        if (e.which === 13) { 
            e.preventDefault();
            const barcode = $(this).val().trim();
            if (barcode) {
                $.post("{{ route('penerimaan-berkas.scan-permohonan') }}", {
                    _token: "{{ csrf_token() }}",
                    nomor_permohonan: barcode
                })
                .done(res => {
                    if (res.success) {
                        beepSuccess.play(); 
                        location.reload(); 
                    }
                })
                .fail(err => {
                    beepError.play(); 
                    alert("⚠️ Barcode tidak sesuai, silahkan scan ulang!"); 
                    inputBarcode.val('');
                    forceFocus();
                })
                .always(() => inputBarcode.val(''));
            }
        }
    });

    setInterval(function() {
        $.get("{{ route('penerimaan-berkas.check-new-scan') }}", function(res) {
            if (res.has_new === true) { 
                location.reload(); 
            }
        });
    }, 2000);

    // LOGIKA TOMBOL DETAIL (Melihat data detail permohonan)
    $(document).on('click', '.btn-detail-native', function() {
        const data = $(this).data('item');
        
        // Mapping data (Sesuaikan dengan nama kolom database Anda)
        $('#m_no_permohonan').val(data.no_permohonan || '-');
        $('#m_tgl_permohonan').val(data.tanggal_permohonan || '-');
        $('#m_tgl_terbit').val(data.tanggal_terbit || '-');
        $('#m_nama').val(data.nama || '-');
        $('#m_tempat_lahir').val(data.tempat_lahir || '-');
        $('#m_tgl_lahir').val(data.tanggal_lahir || '-');
        $('#m_gender').val(data.jenis_kelamin || '-');
        $('#m_telp').val(data.no_telp || '-');
        $('#m_jns_permohonan').val(data.jenis_permohonan || '-');
        $('#m_jns_paspor').val(data.jenis_paspor || '-');
        $('#m_tujuan').val(data.tujuan_paspor || '-');
        $('#m_no_paspor').val(data.no_paspor || '-');
        $('#m_alur').val(data.status_berkas || '-');
        $('#m_lokasi').val(data.lokasi_arsip || '-');
        
        $('#modalDetailBerkas').modal('show');
    });

    $(document).on('click', '#btn-simpan-penerimaan', function() {
        if(!confirm("Simpan sesi penerimaan ini dan mulai sesi baru?")) return;

        $.ajax({
            url: "{{ route('penerimaan-berkas.konfirmasi-bulk') }}",
            method: "POST",
            data: { _token: "{{ csrf_token() }}" },
            success: function(res) {
                if(res.success) {
                    alert("Status berhasil diperbarui menjadi DITERIMA OLEH ARSIP");
                    window.location.reload();
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