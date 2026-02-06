@extends('layouts.app')

@section('title', 'Penerimaan Berkas')
@section('page-title', 'Penerimaan Berkas')
@section('page-subtitle', 'Verifikasi berkas fisik yang masuk dari unit kerja.')

@section('content')

{{-- CSS UNTUK MODAL DETAIL & TABEL PRESISI --}}
<style>
    #modalDetailBerkas .modal-dialog {
        max-width: 420px;
        margin: 1.75rem auto;
    }
    #modalDetailBerkas .modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    #form-detail-pop .row {
        margin-bottom: 7px !important;
    }
    #form-detail-pop label {
        font-size: 12.5px;
        font-weight: 600;
        color: #6c757d;
        display: flex;
        align-items: center;
    }
    #form-detail-pop .form-control-sm {
        font-size: 13px;
        height: 36px;
        border-radius: 8px;
        background-color: #fff !important;
        border: 1px solid #ced4da;
        color: #333;
    }
    #modalDetailBerkas .modal-footer {
        padding: 0.5rem 1.5rem 1.5rem 1.5rem;
        justify-content: flex-end;
        border: none;
    }
    #modalDetailBerkas .btn-danger {
        background-color: #ff7675;
        border: none;
        padding: 8px 25px;
        border-radius: 8px;
        font-weight: bold;
        font-size: 14px;
    }

    /* KUNCI LEBAR TABEL: Agar tidak berantakan saat scan */
    #tbody-perlu-scan, #tbody-hasil-scan {
        display: table;
        width: 100%;
        table-layout: fixed !important; 
    }
    #tbody-perlu-scan tr td, #tbody-hasil-scan tr td {
        vertical-align: top !important;
        padding-top: 12px !important;
        padding-bottom: 12px !important;
    }
</style>

{{-- Input Scan Barcode (Hidden) --}}
<div style="position: fixed; top: 0; left: 0; width: 1px; height: 1px; opacity: 0; overflow: hidden; z-index: -9999; pointer-events: none;">
    <input type="text" id="input-barcode-permohonan" autofocus autocomplete="off">
</div>

{{-- BAGIAN 1: TABEL ANTREAN UTAMA --}}
<div id="section-riwayat">
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <h6 class="m-0 fw-bold text-dark"><i class="fas fa-inbox me-2 text-primary"></i>Daftar Antrean</h6>
            <div class="d-flex gap-2 align-items-center">
                <input type="date" id="filter-tanggal-antrean" class="form-control form-control-sm bg-light border-0" style="width: 140px;">
                <select id="filter-status-antrean" class="form-select form-select-sm bg-light fw-bold border-0" style="width: 180px;">
                    <option value="">-- Status --</option>
                    <option value="DIAJUKAN">⚠️ DIAJUKAN</option>
                    <option value="DITERIMA OLEH ARSIP">✅ DITERIMA OLEH ARSIP</option>
                </select>
                <div class="input-group input-group-sm border rounded bg-light" style="width: 250px;">
                    <span class="input-group-text bg-transparent border-0 pe-1"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="search-antrean" class="form-control border-0 bg-transparent shadow-none" placeholder="Cari...">
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="table-antrean-utama" style="font-size: 13px;">
                    <thead class="bg-light text-center">
                        <tr>
                            <th class="ps-4 text-start">No. Pengirim</th>
                            <th>Jumlah Berkas</th>
                            <th>Asal Unit</th>
                            <th>Tanggal Kirim</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @forelse($antrean_batches->sortByDesc(fn($i) => strtoupper($i->status) === 'DIAJUKAN') as $row)
                        @php $statusText = strtoupper($row->status) == 'DIAJUKAN' ? 'DIAJUKAN' : 'DITERIMA OLEH ARSIP'; @endphp
                        <tr class="row-antrean" data-tanggal="{{ \Carbon\Carbon::parse($row->tgl_pengirim)->format('Y-m-d') }}" data-status="{{ $statusText }}">
                            <td class="ps-4 fw-bold text-primary text-start">{{ $row->no_pengirim }}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary px-3 py-2">{{ $row->jumlah_berkas }} Berkas</span></td>
                            <td><span class="badge bg-info-subtle text-info px-2 fw-bold">{{ $row->asal_unit ?? 'Kanim' }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($row->tgl_pengirim)->format('d-m-Y') }}</td>
                            <td><span class="badge rounded-pill {{ $statusText == 'DIAJUKAN' ? 'bg-warning text-dark' : 'bg-success text-white' }} px-3">{{ $statusText }}</span></td>
                            <td>
                                @if($statusText == 'DIAJUKAN')
                                    <button class="btn btn-primary btn-sm px-3 btn-proses-batch shadow-sm" data-id="{{ $row->no_pengirim }}" style="border-radius: 8px;">Mulai Terima</button>
                                @else
                                    <span class="text-success fw-bold small"><i class="fas fa-check-circle me-1"></i> Sudah di Scan</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">Tidak ada data antrean.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- BAGIAN 2: PROSES SCAN VERIFIKASI --}}
<div id="section-proses-scan" style="display: none;">
    <div class="mb-4 text-start">
        <button class="btn btn-sm btn-outline-secondary btn-back-riwayat shadow-sm px-3" style="border-radius: 20px;">
            <i class="fas fa-arrow-left me-2"></i> Kembali ke Antrean
        </button>
    </div>
    <div class="row g-4 text-start">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4" style="min-height: 500px; border-radius: 15px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold m-0 text-muted small uppercase">1. List Kiriman (Belum Verifikasi)</h6>
                    <span id="pending-count" class="badge bg-light text-dark border" style="font-size: 10px;">0 BERKAS BELUM SCAN</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <tbody id="tbody-perlu-scan" style="font-size: 13px;"></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4" style="min-height: 500px; border-radius: 15px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold m-0 text-muted small uppercase">2. Hasil Verifikasi (Selesai Scan)</h6>
                    <span id="scan-count-live" class="badge bg-success" style="font-size: 10px;">0 BERKAS TERVERIFIKASI</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <tbody id="tbody-hasil-scan" style="font-size: 13px;"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center mt-5 d-flex justify-content-center gap-3">
        <button id="btn-simpan-draft" class="btn btn-outline-primary px-5 fw-bold shadow-sm py-3" style="border-radius: 50px;">Simpan Draft</button>
        <button id="btn-simpan-batch" class="btn btn-secondary px-5 fw-bold shadow-sm py-3" style="border-radius: 50px;" disabled>Selesaikan & Terima Berkas</button>
    </div>
</div>

{{-- MODAL DETAIL PERMOHONAN --}}
<div class="modal fade" id="modalDetailBerkas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header border-0 d-flex justify-content-between align-items-center px-4 pt-4 pb-2">
                <h5 class="modal-title">Detail Permohonan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size: 12px;"></button>
            </div>
            <div class="modal-body px-4">
                <form id="form-detail-pop">
                    @php $fields = [
                        'nomor'=>'Nomor Permohonan', 'tgl-mohon'=>'Tgl Permohonan', 'tgl-terbit'=>'Tgl Terbit', 'nama'=>'Nama', 
                        'tempat-lahir'=>'Tempat Lahir', 'tgl-lahir'=>'Tgl Lahir', 'jk'=>'Jenis Kelamin', 'telp'=>'No Telpon', 
                        'jenis-mohon'=>'Jenis Permohonan', 'jenis-paspor'=>'Jenis Paspor', 'tujuan'=>'Tujuan Paspor', 
                        'no-paspor'=>'No Paspor', 'alur'=>'Alur Terakhir', 'lokasi'=>'Lokasi Arsip'
                    ]; @endphp
                    @foreach($fields as $id => $label)
                    <div class="row align-items-center">
                        <label class="col-5 fw-bold text-muted" style="font-size: 11.5px;">{{ $label }}</label>
                        <div class="col-7">
                            @if($id === 'nama')
                                <div id="det-nama" class="form-control form-control-sm bg-white border text-dark" 
                                     style="height: auto; min-height: 36px; word-break: break-word; white-space: normal; line-height: 1.4; display: block; padding: 6px 10px;">
                                     -
                                </div>
                            @else
                                <input type="text" id="det-{{ $id }}" readonly class="form-control form-control-sm bg-white border text-dark" style="border-radius: 8px; height: 38px; font-size: 13px;">
                            @endif
                        </div>
                    </div>
                    @endforeach
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-danger shadow-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
window.fetchAndShowDetail = function(nomor) {
    $.get(`/penerimaan-berkas/get-detail/${nomor}`, function(res) {
        if (res.success) {
            let item = res.data;
            $('#det-nomor').val(item.no_permohonan || '-');
            $('#det-tgl-mohon').val(item.tanggal_permohonan || '-');
            $('#det-tgl-terbit').val(item.tanggal_terbit || '-');
            $('#det-nama').text(item.nama || '-');
            $('#det-tempat-lahir').val(item.tempat_lahir || '-');
            $('#det-tgl-lahir').val(item.tanggal_lahir || '-');
            $('#det-jk').val(item.jenis_kelamin || '-');
            $('#det-telp').val(item.no_telp || '-');
            $('#det-jenis-mohon').val(item.jenis_permohonan || '-');
            $('#det-jenis-paspor').val(item.jenis_paspor || '-');
            $('#det-tujuan').val(item.tujuan_paspor || '-');
            $('#det-no-paspor').val(item.no_paspor || '-');
            $('#det-alur').val(item.status_berkas || '-');
            $('#det-lokasi').val(item.lokasi_arsip || '-');
            new bootstrap.Modal(document.getElementById('modalDetailBerkas')).show();
        }
    });
};

$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    const inputBarcode = $('#input-barcode-permohonan');
    let currentBatchID = '';

    $(document).on('click', function(e) {
        if (!$('.modal').is(':visible') && $('#section-proses-scan').is(':visible') && !$(e.target).closest('button, input').length) {
            inputBarcode.focus();
        }
    });

    function playAudio(type) {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.connect(gain); gain.connect(audioCtx.destination);
        if (type === 'success') { osc.frequency.setValueAtTime(880, audioCtx.currentTime); } 
        else { osc.frequency.setValueAtTime(220, audioCtx.currentTime); }
        gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
        osc.start(); osc.stop(audioCtx.currentTime + 0.1);
    }

    function updateCounters() {
        let total = $('#tbody-perlu-scan tr').length;
        let done = $('#tbody-perlu-scan tr.table-success').length;
        
        $('#pending-count').text((total - done) + ' BERKAS BELUM SCAN');
        $('#scan-count-live').text(done + ' BERKAS TERVERIFIKASI');

        if (total > 0 && total === done) {
            $('#btn-simpan-batch').prop('disabled', false).removeClass('btn-secondary').addClass('btn-success').css('cursor', 'pointer');
        } else {
            $('#btn-simpan-batch').prop('disabled', true).addClass('btn-secondary').removeClass('btn-success').css('cursor', 'not-allowed');
        }
    }

    inputBarcode.on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            const barcode = $(this).val().replace(/[^a-zA-Z0-9]/g, '').trim();
            $(this).val('');
            
            if (!barcode) return;

            // 1. Cari apakah baris nomor permohonan ada di tabel "Perlu Scan"
            const rowTarget = $(`#row-${barcode}`);

            // 2. Jika tidak ditemukan dalam daftar batch ini
            if (rowTarget.length === 0) {
                playAudio('error');
                Swal.fire({
                    icon: 'error',
                    title: 'Berkas Tidak Sesuai',
                    text: `Nomor permohonan ${barcode} tidak terdaftar dalam batch pengiriman ini!`,
                    timer: 3000,
                    showConfirmButton: false
                });
                return;
            }

            // 3. Jika sudah pernah di-scan sebelumnya
            if (rowTarget.hasClass('table-success')) { 
                playAudio('error'); 
                Swal.fire({
                    icon: 'info',
                    title: 'Sudah Diverifikasi',
                    text: 'Nomor ini sudah Anda scan sebelumnya.',
                    timer: 1500,
                    showConfirmButton: false
                });
                return; 
            }

            // 4. Jika sesuai, proses ke server
            $.post("{{ route('penerimaan-berkas.scan-permohonan') }}", { nomor_permohonan: barcode }, function(res) {
                if (res.success) {
                    playAudio('success');
                    rowTarget.addClass('table-success fw-bold');
                    
                    if (rowTarget.find('.fa-check-circle').length === 0) {
                        rowTarget.find('.icon-container').prepend('<i class="fas fa-check-circle text-success me-2"></i>');
                    }
                    
                    const rowClone = rowTarget.clone();
                    $('#tbody-hasil-scan').prepend(rowClone);
                    updateCounters();
                } else {
                    playAudio('error');
                    Swal.fire('Gagal', res.message, 'error');
                }
            });
        }
    });

    $('.btn-proses-batch').on('click', function() {
        currentBatchID = $(this).data('id');
        $('#tbody-hasil-scan, #tbody-perlu-scan').empty();
        $.get(`/arsip/list-berkas/${currentBatchID}`, function(res) {
            if(res.success) {
                res.data.forEach(item => {
                    let isDone = (item.status_berkas === 'DITERIMA' || item.status_berkas === 'DITERIMA OLEH ARSIP');
                    let checkIcon = isDone ? '<i class="fas fa-check-circle text-success me-2"></i>' : '';
                    let rowHtml = `
                    <tr id="row-${item.no_permohonan}" class="align-middle border-bottom ${isDone ? 'table-success fw-bold' : ''}">
                        <td style="width: 35%;" class="py-3 ps-2 text-start">
                            <div class="d-flex align-items-center icon-container">
                                ${checkIcon}
                                <span>${item.no_permohonan}</span>
                            </div>
                        </td>
                        <td style="width: 40%;" class="py-3 text-start">
                            <div style="word-break: break-word; line-height: 1.4;">${item.nama}</div>
                        </td>
                        <td style="width: 15%;" class="py-3 text-end pe-2">
                            <button type="button" class="btn btn-primary btn-sm py-0" 
                                    onclick="window.fetchAndShowDetail('${item.no_permohonan}')" 
                                    style="font-size: 10px; height: 22px; width: 50px;">
                                Detail
                            </button>
                        </td>
                    </tr>`;
                    $('#tbody-perlu-scan').append(rowHtml);
                    if(isDone) $('#tbody-hasil-scan').prepend(rowHtml);
                });
                updateCounters();
                $('#section-riwayat').hide(); $('#section-proses-scan').fadeIn();
                setTimeout(() => inputBarcode.focus(), 500);
            }
        });
    });

    $(document).on('click', '#btn-simpan-batch', function() {
        if ($(this).is(':disabled')) return;
        
        Swal.fire({
            title: 'Selesaikan Batch?',
            text: "Pastikan semua berkas fisik sudah sesuai untuk diverifikasi.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'Ya, Selesaikan!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("{{ route('penerimaan-berkas.konfirmasi-bulk') }}", { no_pengirim: currentBatchID }, function(res) {
                    if(res.success) {
                        Swal.fire('Berhasil!', 'Batch telah diterima.', 'success').then(() => window.location.reload());
                    }
                });
            }
        });
    });

    function applyFilters() {
        let s = $("#search-antrean").val().toLowerCase(), t = $("#filter-tanggal-antrean").val(), st = $("#filter-status-antrean").val();
        $("#table-antrean-utama tbody tr").each(function() {
            let rowText = $(this).text().toLowerCase(), rowDate = $(this).data('tanggal'), rowStatus = $(this).data('status');
            $(this).toggle(rowText.indexOf(s) > -1 && (t === "" || rowDate === t) && (st === "" || rowStatus === st));
        });
    }
    $("#search-antrean").on("keyup", applyFilters);
    $("#filter-tanggal-antrean, #filter-status-antrean").on("change", applyFilters);
    $('.btn-back-riwayat, #btn-simpan-draft').on('click', () => window.location.reload());
});
</script>
@endpush