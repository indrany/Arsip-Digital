@extends('layouts.app')

@section('title', 'Penerimaan Berkas')
@section('page-title', 'Penerimaan Berkas')
@section('page-subtitle', 'Verifikasi berkas fisik yang masuk dari unit kerja.')

@section('content')

{{-- Input Scan Barcode (Tersembunyi secara visual) --}}
<div style="position: fixed; top: 0; left: 0; width: 1px; height: 1px; opacity: 0; overflow: hidden; z-index: -1;">
    <input type="text" id="input-barcode-permohonan" autofocus autocomplete="off">
</div>

{{-- BAGIAN 1: TABEL RIWAYAT ANTREAN (REVISI: Berbasis No. Pengirim) --}}
<div id="section-riwayat">
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; font-family: 'Inter', sans-serif;">
        <div class="card-header bg-white py-3 text-dark">
            <h6 class="m-0 fw-bold"><i class="fas fa-inbox me-2 text-primary"></i>Daftar Kiriman Berkas Masuk (Antrean)</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">No. Pengirim</th>
                            <th class="text-center">Jumlah Berkas</th>
                            <th>Asal Unit</th>
                            <th>Tanggal Kirim</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($antrean_batches as $row)
                        <tr>
                            <td class="ps-4 fw-bold text-primary">{{ $row->no_pengirim }}</td>
                            <td class="text-center">
                                <span class="badge bg-secondary-subtle text-secondary px-3 py-2" style="border-radius: 8px;">
                                    {{ $row->jumlah_berkas }} Berkas
                                </span>
                            </td>
                            <td><span class="badge bg-info text-dark px-2 text-uppercase">{{ $row->asal_unit ?? 'Kanim' }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($row->tgl_pengirim)->format('d-m-Y') }}</td>
                            <td class="text-center">
                                <span class="badge rounded-pill bg-warning text-dark px-3">
                                    {{ strtoupper($row->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-primary btn-sm btn-proses-batch" data-id="{{ $row->no_pengirim }}" style="border-radius: 8px;">
                                    <i class="fas fa-barcode me-1"></i> Mulai Terima Berkas
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Tidak ada antrean kiriman berkas baru.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- BAGIAN 2: PROSES SCAN VERIFIKASI (Tampil setelah klik aksi) --}}
<div id="section-proses-scan" style="display: none;">
    <div class="d-flex justify-content-start align-items-center mb-4">
        <button class="btn btn-sm btn-outline-secondary btn-back-riwayat shadow-sm px-3" style="border-radius: 20px;">
            <i class="fas fa-arrow-left me-2"></i> Kembali ke Antrean
        </button>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4" style="min-height: 500px; border-radius: 15px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold m-0 text-muted">1. List Kiriman (Belum Verifikasi)</h6>
                    <span id="pending-count" class="badge bg-light text-dark border">0 Berkas</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="table-verifikasi-kiri">
                        <thead class="bg-light">
                            <tr style="font-size: 12px;">
                                <th>No. Permohonan</th>
                                <th>Nama Pemohon</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-perlu-scan" style="font-size: 13px;"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4" style="min-height: 500px; border-radius: 15px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold m-0 text-muted">2. Hasil Verifikasi (Selesai Scan)</h6>
                    <span id="scan-count-live" class="badge bg-success">0 Berkas</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="bg-light">
                            <tr style="font-size: 12px;">
                                <th>No. Permohonan</th>
                                <th>Nama Pemohon</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-hasil-scan" style="font-size: 13px;"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-5 d-flex justify-content-center gap-3">
        <button id="btn-simpan-draft" class="btn btn-outline-primary px-5 fw-bold shadow-sm py-3" style="border-radius: 50px;">
            <i class="fas fa-save me-2"></i> Simpan Draft
        </button>
        <button id="btn-simpan-batch" class="btn btn-success px-5 fw-bold shadow-sm py-3" style="border-radius: 50px;" disabled>
            <i class="fas fa-check-double me-2"></i> Selesaikan & Terima Berkas
        </button>
    </div>
</div>

{{-- MODAL DETAIL --}}
<div class="modal fade" id="modalDetailBerkas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; width: 420px; margin: auto;">
            <div class="modal-header border-0 pb-0 pt-3 px-4" style="display: flex; justify-content: space-between; align-items: center;">
                <h6 class="modal-title fw-bold text-secondary" style="font-size: 16px; margin: 0;">Detail Permohonan</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 10px;"></button>
            </div>
            <div class="modal-body pt-3 pb-2 px-4">
                <form id="form-detail-pop">
                    @php
                        $fields = ['nomor'=>'Nomor Permohonan', 'tgl-mohon'=>'Tanggal Permohonan', 'tgl-terbit'=>'Tanggal Terbit', 'nama'=>'Nama', 'tempat-lahir'=>'Tempat Lahir', 'tgl-lahir'=>'Tanggal Lahir', 'jk'=>'Jenis Kelamin', 'telp'=>'No Telpon', 'jenis-mohon'=>'Jenis Permohonan', 'jenis-paspor'=>'Jenis Paspor', 'tujuan'=>'Tujuan Paspor', 'no-paspor'=>'No Paspor', 'alur'=>'Alur Terakhir', 'lokasi'=>'Lokasi Arsip'];
                    @endphp
                    @foreach($fields as $id => $label)
                    <div class="row mb-1 align-items-center">
                        <label style="flex: 0 0 42%; font-size: 11.5px; color: #667085; font-weight: 500;">{{ $label }}</label>
                        <div style="flex: 1;">
                            <input type="text" id="det-{{ $id }}" readonly class="form-control form-control-sm bg-white" style="border: 1px solid #D0D5DD; border-radius: 6px; font-size: 12px; color: #344054; padding: 4px 10px; height: 32px;">
                        </div>
                    </div>
                    @endforeach
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 pb-3 px-4 d-flex justify-content-end">
                <button type="button" class="btn btn-danger btn-sm px-4 fw-bold" data-bs-dismiss="modal" style="border-radius: 8px;">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
    .table-success { background-color: #d1e7dd !important; color: #0f5132 !important; }
    #section-proses-scan { animation: fadeIn 0.3s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .bg-secondary-subtle { background-color: #f1f5f9 !important; }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    const inputBarcode = $('#input-barcode-permohonan');
    let currentBatch = '';

    const Toast = Swal.mixin({
        toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, timerProgressBar: true
    });

    function updateButtonStatus() {
        let sudahScan = $('#tbody-perlu-scan tr.table-success').length;
        let totalBerkas = $('#tbody-perlu-scan tr').length;
        let sisa = totalBerkas - sudahScan;
        
        $('#pending-count').text(sisa + ' Berkas Belum Scan');
        $('#scan-count-live').text(sudahScan + ' Berkas Terverifikasi');

        if (sisa === 0 && totalBerkas > 0) {
            $('#btn-simpan-batch').prop('disabled', false).removeClass('btn-secondary').addClass('btn-success');
            $('#btn-simpan-draft').addClass('d-none');
        } else {
            $('#btn-simpan-batch').prop('disabled', true).addClass('btn-secondary');
            $('#btn-simpan-draft').removeClass('d-none');
        }
    }

    function playBeep(type) {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.connect(gain); gain.connect(audioCtx.destination);
        if (type === 'success') {
            osc.type = 'sine'; osc.frequency.setValueAtTime(880, audioCtx.currentTime);
        } else {
            osc.type = 'square'; osc.frequency.setValueAtTime(220, audioCtx.currentTime);
        }
        gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
        osc.start(); osc.stop(audioCtx.currentTime + 0.1);
    }

    // LOAD DATA BATCH SAAT KLIK 'MULAI TERIMA'
    $('.btn-proses-batch').on('click', function() {
        currentBatch = $(this).data('id');
        $('#tbody-hasil-scan, #tbody-perlu-scan').empty();
        
        $.get(`/arsip/list-berkas/${currentBatch}`, function(res) {
            if(res.success) {
                let htmlKiri = '';
                res.data.forEach(item => {
                    let isAlreadyScanned = (item.status_berkas === 'DITERIMA' || item.status_berkas === 'DITERIMA OLEH ARSIP');
                    let rowClass = isAlreadyScanned ? 'table-success fw-bold' : '';
                    let checkIcon = isAlreadyScanned ? '<i class="fas fa-check-circle text-success me-2"></i>' : '';
                    let actionDetail = `<div class="d-flex justify-content-between align-items-center"><span class="nama-text text-dark">${item.nama}</span><button type="button" class="btn btn-primary btn-sm px-2 py-1" onclick="fetchAndShowDetail('${item.no_permohonan}')" style="font-size: 10px; border-radius: 5px;">Detail</button></div>`;

                    htmlKiri += `<tr id="row-${item.no_permohonan}" class="align-middle border-bottom ${rowClass}">
                                    <td class="py-3 ps-2 no-permohonan-cell">${checkIcon}${item.no_permohonan}</td>
                                    <td>${actionDetail}</td>
                                </tr>`;

                    if(isAlreadyScanned) {
                        $('#tbody-hasil-scan').append(`<tr id="hasil-${item.no_permohonan}" class="border-bottom align-middle bg-light">
                                    <td class="fw-bold py-3 text-dark"><i class="fas fa-check-circle text-success me-2"></i>${item.no_permohonan}</td>
                                    <td>${actionDetail}</td>
                                </tr>`);
                    }
                });

                $('#tbody-perlu-scan').html(htmlKiri);
                updateButtonStatus();
                $('#section-riwayat').hide(); 
                $('#section-proses-scan').fadeIn();
                
                setTimeout(() => { inputBarcode.val('').focus(); }, 500);
            }
        });
    });

    inputBarcode.on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            const barcode = $(this).val().replace(/[^a-zA-Z0-9]/g, '').trim();
            if (!barcode) return;

            const rowTarget = $(`#row-${barcode}`);
            
            if (rowTarget.length === 0) {
                playBeep('error');
                Toast.fire({ icon: 'error', title: 'Nomor permohonan tidak ada dalam batch ini!' });
                $(this).val(''); return;
            }

            if (rowTarget.hasClass('table-success')) {
                playBeep('error');
                Toast.fire({ icon: 'warning', title: 'Berkas sudah ditandai!' });
                $(this).val(''); return;
            }

            $.post("{{ route('penerimaan-berkas.scan-permohonan') }}", { nomor_permohonan: barcode }, function(res) {
                if (res.success) {
                    playBeep('success');
                    rowTarget.addClass('table-success fw-bold');
                    const cellNo = rowTarget.find('.no-permohonan-cell');
                    if (cellNo.find('.fa-check-circle').length === 0) {
                        cellNo.prepend('<i class="fas fa-check-circle text-success me-2"></i>');
                    }

                    const nama = rowTarget.find('.nama-text').text();
                    let actionDetail = `<div class="d-flex justify-content-between align-items-center"><span>${nama}</span><button type="button" class="btn btn-primary btn-sm px-2 py-1" onclick="fetchAndShowDetail('${barcode}')" style="font-size: 10px; border-radius: 5px;">Detail</button></div>`;
                    
                    $('#tbody-hasil-scan').prepend(`
                        <tr id="hasil-${barcode}" class="border-bottom align-middle bg-light">
                            <td class="fw-bold py-3 text-dark"><i class="fas fa-check-circle text-success me-2"></i>${barcode}</td>
                            <td>${actionDetail}</td>
                        </tr>
                    `);

                    updateButtonStatus();
                    Toast.fire({ icon: 'success', title: 'Scan Berhasil: ' + barcode });
                }
            }).fail(() => {
                playBeep('error');
                Toast.fire({ icon: 'error', title: 'Gagal verifikasi ke server!' });
            });
            $(this).val('').focus();
        }
    });

    $('#btn-simpan-draft').on('click', function() { window.location.reload(); });

    $('#btn-simpan-batch').on('click', function() {
        Swal.fire({
            title: 'Selesaikan Batch?',
            text: "Konfirmasi penerimaan semua berkas dalam batch ini.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Selesaikan'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("{{ route('penerimaan-berkas.konfirmasi-bulk') }}", { no_pengirim: currentBatch }).done(() => { window.location.reload(); });
            }
        });
    });

    $('.btn-back-riwayat').on('click', () => { window.location.reload(); });
    
    $(document).on('click', function() {
        if (!$('.modal').is(':visible') && $('#section-proses-scan').is(':visible')) {
            inputBarcode.focus();
        }
    });
});

function fetchAndShowDetail(nomor) {
    $.get(`/penerimaan-berkas/get-detail/${nomor}`, function(res) {
        if (res.success) {
            let item = res.data;
            document.getElementById('det-nomor').value = item.no_permohonan || '-';
            document.getElementById('det-tgl-mohon').value = item.tanggal_permohonan || '-';
            document.getElementById('det-tgl-terbit').value = item.tanggal_terbit || '-';
            document.getElementById('det-nama').value = item.nama || '-';
            document.getElementById('det-tempat-lahir').value = item.tempat_lahir || '-';
            document.getElementById('det-tgl-lahir').value = item.tanggal_lahir || '-';
            document.getElementById('det-jk').value = item.jenis_kelamin || '-';
            document.getElementById('det-telp').value = item.no_telp || '-';
            document.getElementById('det-jenis-mohon').value = item.jenis_permohonan || '-';
            document.getElementById('det-jenis-paspor').value = item.jenis_paspor || '-';
            document.getElementById('det-tujuan').value = item.tujuan_paspor || '-';
            document.getElementById('det-no-paspor').value = item.no_paspor || '-';
            document.getElementById('det-alur').value = item.status_berkas || '-';
            document.getElementById('det-lokasi').value = item.lokasi_arsip || '-';
            new bootstrap.Modal(document.getElementById('modalDetailBerkas')).show();
        }
    });
}
</script>
@endpush