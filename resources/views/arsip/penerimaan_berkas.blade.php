@extends('layouts.app')

@section('title', 'Penerimaan Berkas')
@section('page-title', 'Penerimaan Berkas')
@section('page-subtitle', 'Verifikasi berkas fisik yang masuk dari unit kerja.')

@section('content')

{{-- Input Scan Barcode (Tersembunyi secara visual) --}}
<div style="position: fixed; top: 0; left: 0; width: 1px; height: 1px; opacity: 0; overflow: hidden; z-index: -1;">
    <input type="text" id="input-barcode-permohonan" autofocus autocomplete="off">
</div>

{{-- BAGIAN 1: TABEL RIWAYAT ANTREAN --}}
<div id="section-riwayat">
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; font-family: 'Inter', sans-serif;">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-dark">Daftar Kiriman Berkas Masuk (Antrean)</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size: 13px;">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">No. Pengirim</th>
                            <th>Asal Unit</th>
                            <th>Petugas Kirim</th>
                            <th>Tanggal Kirim</th>
                            <th>Jumlah Berkas</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayat_batches as $batch)
                        <tr>
                            <td class="ps-4 fw-bold text-primary">{{ $batch->no_pengirim }}</td>
                            <td><span class="badge bg-info text-dark px-2">{{ $batch->asal_unit ?? 'Kanim' }}</span></td>
                            <td>{{ $batch->petugas_kirim ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($batch->tgl_pengirim)->format('d-m-Y') }}</td>
                            <td>{{ $batch->jumlah_berkas }} Berkas</td>
                            <td>
                                <span class="badge rounded-pill {{ $batch->status == 'DITERIMA OLEH ARSIP' ? 'bg-success' : 'bg-warning text-dark' }} px-3">
                                    {{ $batch->status }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($batch->status != 'DITERIMA OLEH ARSIP')
                                    <button class="btn btn-primary btn-sm btn-proses-batch" data-id="{{ $batch->no_pengirim }}" style="border-radius: 8px;">
                                        <i class="fas fa-barcode me-1"></i> Mulai Terima Berkas
                                    </button>
                                @else
                                    <span class="text-muted small">Selesai Verifikasi</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada kiriman berkas masuk.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- BAGIAN 2: PROSES SCAN VERIFIKASI --}}
<div id="section-proses-scan" style="display: none;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <button class="btn btn-sm btn-outline-secondary btn-back-riwayat">
            <i class="fas fa-arrow-left"></i> Kembali ke Antrean
        </button>
        <h5 class="fw-bold m-0">Proses Verifikasi Batch: <span id="current-batch-id" class="text-primary">-</span></h5>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-3" style="min-height: 450px; border-radius: 12px;">
                <h6 class="fw-bold mb-3 text-muted">1. Berkas Harus Ada (List Kiriman)</h6>
                <div class="table-responsive">
                    <table class="table table-sm" id="table-verifikasi-kiri">
                        <thead>
                            <tr class="text-muted" style="font-size: 12px;">
                                <th style="width: 40%">No. Pengirim</th>
                                <th>Nama Pemohon</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-perlu-scan" style="font-size: 13px;"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-3" style="min-height: 450px; border-radius: 12px;">
                <h6 class="fw-bold mb-3 text-muted">2. Berkas Fisik Ditemukan (Hasil Scan)</h6>
                <div class="alert alert-success py-2 px-3 mb-3 d-flex justify-content-between align-items-center" style="border-radius: 8px; border: none;">
                    <span class="small fw-bold">Total Terverifikasi:</span>
                    <strong id="scan-count-live" class="h5 m-0">0 Berkas</strong>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr class="text-muted" style="font-size: 12px;">
                                <th style="width: 40%">No. Permohonan</th>
                                <th>Nama Pemohon</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-hasil-scan" style="font-size: 13px;"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <button id="btn-simpan-batch" class="btn btn-success px-5 fw-bold shadow-sm" style="border-radius: 50px; padding: 12px;">
            <i class="fas fa-check-double me-2"></i> Konfirmasi & Selesaikan Batch
        </button>
    </div>
</div>

{{-- MODAL DETAIL --}}
<div class="modal fade" id="modalDetailBerkas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; width: 420px; margin: auto;">
            <div class="modal-header border-0 pb-0 pt-3 px-4" style="display: flex; justify-content: space-between; align-items: center;">
                <h6 class="modal-title fw-bold text-secondary" style="font-size: 16px; margin: 0;">Detail</h6>
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
                <button type="button" class="btn btn-danger btn-sm shadow-sm text-white fw-bold" data-bs-dismiss="modal" style="background-color: #F97066; border: none; border-radius: 8px; font-size: 12px; padding: 7px 25px;">Tutup</button>
            </div>
        </div>
    </div>
</div>

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

    /**
     * Fungsi Beep Sintetik (Clean & Professional)
     * type: 'success' (beep tinggi singkat) atau 'error' (beep rendah dua kali)
     */
    function playBeep(type) {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        
        if (type === 'success') {
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, audioCtx.currentTime); // Nada tinggi (A5)
            gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.1);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.1);
        } else {
            // Beep rendah untuk error
            [0, 0.15].forEach(delay => {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.type = 'square';
                osc.frequency.setValueAtTime(220, audioCtx.currentTime + delay); // Nada rendah
                gain.gain.setValueAtTime(0.05, audioCtx.currentTime + delay);
                osc.start(audioCtx.currentTime + delay);
                osc.stop(audioCtx.currentTime + delay + 0.1);
            });
        }
    }

    // 1. LOAD DATA BATCH
    $('.btn-proses-batch').on('click', function() {
        currentBatch = $(this).data('id');
        $('#current-batch-id').text(currentBatch);
        $('#tbody-hasil-scan, #tbody-perlu-scan').empty();
        
        $.get(`/arsip/list-berkas/${currentBatch}`, function(res) {
            if(res.success) {
                let htmlKiri = '';
                res.data.forEach(item => {
                    let isDiterima = item.status_berkas === 'DITERIMA' || item.status_berkas === 'DITERIMA OLEH ARSIP';
                    let rowClass = isDiterima ? 'table-success fw-bold' : '';
                    let checkIcon = isDiterima ? ' <i class="fas fa-check-circle text-success ms-1"></i>' : '';
                    let actionDetail = `<div class="d-flex justify-content-between align-items-center"><span class="nama-text">${item.nama}</span><button type="button" class="btn btn-primary btn-sm px-2 py-1" onclick="fetchAndShowDetail('${item.no_permohonan}')" style="font-size: 10px; border-radius: 5px;">Detail</button></div>`;

                    htmlKiri += `<tr id="row-${item.no_permohonan}" class="align-middle border-bottom ${rowClass}">
                                <td class="py-3 ps-2 no-pengirim-cell">${currentBatch}${checkIcon}</td>
                                <td>${actionDetail}</td>
                            </tr>`;

                    if(isDiterima) {
                        $('#tbody-hasil-scan').append(`<tr id="hasil-${item.no_permohonan}" class="border-bottom align-middle">
                                <td class="fw-bold py-3 text-dark">${item.no_permohonan}</td>
                                <td>${actionDetail}</td>
                            </tr>`);
                    }
                });
                $('#tbody-perlu-scan').html(htmlKiri);
                $('#scan-count-live').text($('#tbody-hasil-scan tr').length + ' Berkas');
                $('#section-riwayat').hide(); $('#section-proses-scan').fadeIn();
                inputBarcode.val('').focus();
            }
        });
    });

    // 2. LOGIKA SCAN BARCODE (REALTIME)
    inputBarcode.on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            const barcode = $(this).val().trim();
            if (!barcode) return;

            const rowTarget = $(`#row-${barcode}`);
            
            // Validasi: Apakah ada di tabel kiri?
            if (rowTarget.length === 0) {
                playBeep('error');
                Toast.fire({ icon: 'error', title: 'Berkas tidak ada dalam batch ini!' });
                $(this).val(''); return;
            }

            // Validasi: Apakah sudah di-scan?
            if ($(`#hasil-${barcode}`).length > 0) {
                playBeep('error');
                Toast.fire({ icon: 'warning', title: 'Berkas sudah di-scan!' });
                $(this).val(''); return;
            }

            // Jika valid, kirim ke server
            $.post("{{ route('penerimaan-berkas.scan-permohonan') }}", { nomor_permohonan: barcode }, function(res) {
                if (res.success) {
                    playBeep('success');
                    
                    // 1. Update Tabel Kiri (Tetap ada, jadi hijau)
                    rowTarget.addClass('table-success fw-bold');
                    if (rowTarget.find('.fa-check-circle').length === 0) {
                        rowTarget.find('.no-pengirim-cell').append(' <i class="fas fa-check-circle text-success ms-1"></i>');
                    }

                    // 2. Tambah ke Tabel Kanan
                    const nama = rowTarget.find('.nama-text').text();
                    let actionDetail = `<div class="d-flex justify-content-between align-items-center"><span>${nama}</span><button type="button" class="btn btn-primary btn-sm px-2 py-1" onclick="fetchAndShowDetail('${barcode}')" style="font-size: 10px; border-radius: 5px;">Detail</button></div>`;
                    
                    $('#tbody-hasil-scan').prepend(`<tr id="hasil-${barcode}" class="border-bottom align-middle">
                            <td class="fw-bold py-3 text-dark">${barcode}</td>
                            <td>${actionDetail}</td>
                        </tr>`);

                    $('#scan-count-live').text($('#tbody-hasil-scan tr').length + ' Berkas');
                }
            }).fail(() => {
                playBeep('error');
                Toast.fire({ icon: 'error', title: 'Gagal verifikasi ke server!' });
            });

            $(this).val('').focus();
        }
    });

    // 4. SIMPAN BATCH
    $('#btn-simpan-batch').on('click', function() {
        if ($('#tbody-hasil-scan tr').length === 0) return alert("Belum ada scan!");
        Swal.fire({
            title: 'Selesaikan Batch?',
            text: "Konfirmasi penerimaan batch ini.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("{{ route('penerimaan-berkas.konfirmasi-bulk') }}", { no_pengirim: currentBatch }).done(() => { window.location.reload(); });
            }
        });
    });

    $('.btn-back-riwayat').on('click', () => { $('#section-proses-scan').hide(); $('#section-riwayat').fadeIn(); });
    $(document).on('click', () => { if (!$('.modal').is(':visible') && $('#section-proses-scan').is(':visible')) inputBarcode.focus(); });
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