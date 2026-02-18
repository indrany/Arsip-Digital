@extends('layouts.app')

@section('title', 'Penerimaan Berkas')
@section('page-title', 'Penerimaan Berkas')
@section('page-subtitle', 'Verifikasi berkas fisik yang masuk dari unit kerja.')

@section('content')

<style>
    #table-antrean-utama th, #table-antrean-utama td { white-space: nowrap; padding: 12px 8px; }
    .col-no-pengirim { width: 20%; text-align: left; padding-left: 25px !important; }
    .col-jumlah { width: 15%; text-align: center; }
    .col-unit { width: 15%; text-align: center; }
    .col-tanggal { width: 15%; text-align: center; }
    .col-status { width: 15%; text-align: center; }
    .col-aksi { width: 20%; text-align: center; }
    .bg-danger-subtle { background-color: #fee2e2 !important; color: #dc2626 !important; border: 1px solid #fecaca !important; }
    .bg-success-subtle { background-color: #dcfce7 !important; color: #16a34a !important; border: 1px solid #bbf7d0 !important; }
    .bg-warning-subtle { background-color: #fef9c3 !important; color: #a16207 !important; border: 1px solid #fef08a !important; }
    #modalDetailBerkas .modal-dialog { max-width: 420px; margin: 1.75rem auto; }
    #modalDetailBerkas .modal-content { border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    #form-detail-pop .row { margin-bottom: 7px !important; }
    #form-detail-pop label { font-size: 12.5px; font-weight: 600; color: #6c757d; display: flex; align-items: center; }
    #form-detail-pop .form-control-sm { font-size: 13px; height: 36px; border-radius: 8px; background-color: #fff !important; border: 1px solid #ced4da; color: #333; }
    #modalDetailBerkas .modal-footer { padding: 0.5rem 1.5rem 1.5rem 1.5rem; justify-content: flex-end; border: none; }
    #modalDetailBerkas .btn-danger { background-color: #ff7675; border: none; padding: 8px 25px; border-radius: 8px; font-weight: bold; font-size: 14px; }
    #tbody-perlu-scan, #tbody-hasil-scan { display: table; width: 100%; table-layout: fixed !important; }
    #tbody-perlu-scan tr td, #tbody-hasil-scan tr td { vertical-align: top !important; padding-top: 12px !important; padding-bottom: 12px !important; }
    .bg-primary-subtle { background-color: #e0e7ff !important; }
    .text-primary { color: #3b82f6 !important; }
    .text-scanned { color: #198754; font-weight: 700; font-size: 11px; letter-spacing: 0.5px; }
</style>

{{-- INPUT SCAN BARCODE (AUTO FOCUS LOGIC) --}}
<div style="position: absolute; left: -9999px; top: 0;">
    <input type="text" id="input-barcode-permohonan" autofocus autocomplete="off">
</div>

<div id="section-riwayat">
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <h6 class="m-0 fw-bold text-dark"><i class="fas fa-inbox me-2 text-primary"></i>Daftar Antrean</h6>
            <div class="d-flex gap-2 align-items-center">
                <input type="date" id="filter-tanggal-antrean" class="form-control form-control-sm bg-light border-0" style="width: 140px;">
                <select id="filter-status-antrean" class="form-select form-select-sm bg-light fw-bold border-0" style="width: 220px;">
                    <option value="">-- Status --</option>
                    <option value="DIAJUKAN">⚠️ DIAJUKAN</option>
                    <option value="DITERIMA OLEH ARSIP">✅ DITERIMA OLEH ARSIP </option>
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
                    <thead class="bg-light text-center text-muted uppercase">
                        <tr>
                            <th class="col-no-pengirim">No. Pengirim</th>
                            <th class="col-jumlah">Jumlah</th>
                            <th class="col-unit">Asal Unit</th>
                            <th class="col-tanggal">Tanggal Kirim</th>
                            <th class="col-status">Status</th>
                            <th class="col-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @forelse($antrean_batches->sortByDesc(fn($i) => strtoupper($i->status) === 'DIAJUKAN') as $row)
                        @php $statusText = strtoupper($row->status) == 'DIAJUKAN' ? 'DIAJUKAN' : 'DITERIMA OLEH ARSIP'; @endphp
                        <tr class="row-antrean" data-tanggal="{{ \Carbon\Carbon::parse($row->tgl_pengirim)->format('Y-m-d') }}" data-status="{{ $statusText }}">
                            <td class="col-no-pengirim fw-bold text-primary">{{ $row->no_pengirim }}</td>
                            <td class="col-jumlah"><span class="badge bg-secondary-subtle text-secondary px-3 py-2" style="border-radius: 8px;">{{ $row->jumlah_berkas }} Berkas</span></td>
                            <td class="col-unit"><span class="badge bg-info-subtle text-info px-3 py-2 fw-bold" style="border-radius: 8px;">{{ $row->asal_unit ?? 'Kanim' }}</span></td>
                            <td class="col-tanggal">{{ \Carbon\Carbon::parse($row->tgl_pengirim)->format('d-m-Y') }}</td>
                            <td class="col-status"><span class="badge rounded-pill {{ $statusText == 'DIAJUKAN' ? 'bg-warning text-dark' : 'bg-success text-white' }} px-3">{{ $statusText }}</span></td>
                            <td class="col-aksi">
                                <div class="d-flex justify-content-center align-items-center gap-3">
                                    <button type="button" onclick="lihatDetailBatch('{{ $row->no_pengirim }}')" class="btn btn-outline-primary btn-sm px-3" style="border-radius: 8px;"><i class="fas fa-eye me-1"></i> Detail</button>
                                    @if($statusText == 'DIAJUKAN')
                                        <button class="btn btn-primary btn-sm px-3 btn-proses-batch shadow-sm" data-id="{{ $row->no_pengirim }}" style="border-radius: 8px;">Mulai Terima</button>
                                    @else
                                        <span class="text-scanned">SUDAH DI-SCAN</span>
                                    @endif
                                </div>
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

<div id="section-proses-scan" style="display: none;">
    <div class="mb-4 text-start">
        <button class="btn btn-sm btn-outline-secondary btn-back-riwayat shadow-sm px-3" style="border-radius: 20px;"><i class="fas fa-arrow-left me-2"></i> Kembali ke Antrean</button>
    </div>
    <div class="row g-4 text-start">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4" style="min-height: 500px; border-radius: 15px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold m-0 text-muted small uppercase">1. List Kiriman (Belum Verifikasi)</h6>
                    <span id="pending-count" class="badge bg-light text-dark border" style="font-size: 10px;">0 BERKAS BELUM SCAN</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle"><tbody id="tbody-perlu-scan" style="font-size: 13px;"></tbody></table>
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
                    <table class="table table-hover align-middle"><tbody id="tbody-hasil-scan" style="font-size: 13px;"></tbody></table>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center mt-5 d-flex justify-content-center gap-3">
        <button id="btn-simpan-draft" class="btn btn-outline-primary px-5 fw-bold shadow-sm py-3" style="border-radius: 50px;">Simpan Draft</button>
        <button id="btn-simpan-batch" class="btn btn-secondary px-5 fw-bold shadow-sm py-3" style="border-radius: 50px;" disabled>Selesaikan & Terima Berkas</button>
    </div>
</div>

{{-- MODAL DETAIL --}}
<div class="modal fade" id="modalDetailBatch" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content shadow border-0" style="border-radius: 12px;">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h6 class="modal-title fw-bold m-0"><i class="fas fa-file-alt me-2"></i>Detail Batch Penerimaan</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-start">
                <div class="row bg-light p-3 rounded mb-4 g-3 border mx-0">
                    <div class="col-md-4">
                        <label class="d-block text-muted small fw-bold">ID Batch (No Pengirim)</label>
                        <span id="det_no_pengirim" class="fw-bold text-primary" style="font-size: 16px;">-</span>
                    </div>
                    <div class="col-md-4">
                        <label class="d-block text-muted small fw-bold">Tanggal Kirim</label>
                        <span id="det_tgl_pengirim" class="fw-bold" style="font-size: 16px;">-</span>
                    </div>
                    <div class="col-md-4">
                        <label class="d-block text-muted small fw-bold">Status Batch</label>
                        <span id="det_status" class="badge">-</span>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <div class="col-md-7"><h6 class="fw-bold m-0 d-flex align-items-center"><i class="fas fa-list-ul me-2 text-primary"></i>Daftar Berkas Terlampir</h6></div>
                    <div class="col-md-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="searchDetailBerkas" class="form-control border-start-0 shadow-none" placeholder="Cari No. Permohonan atau Nama...">
                        </div>
                    </div>
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="bg-dark text-white text-center" style="font-size: 12px; position: sticky; top: 0; z-index: 5;">
                            <tr><th>No Permohonan</th><th>Nama Pemohon</th><th>Jenis Permohonan</th><th>Tujuan</th><th>Lokasi Rak</th><th>Status Berkas</th></tr>
                        </thead>
                        <tbody id="det_list_berkas_riwayat" style="font-size: 12px;"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 p-3"><button type="button" class="btn btn-secondary px-4 fw-bold shadow-sm" data-bs-dismiss="modal" style="border-radius: 8px;">Tutup</button></div>
        </div>
    </div>
</div>

{{-- MODAL DETAIL PERMOHONAN --}}
<div class="modal fade" id="modalDetailBerkas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header border-0 d-flex justify-content-between align-items-center px-4 pt-4 pb-2"><h5 class="modal-title">Detail Permohonan</h5><button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size: 12px;"></button></div>
            <div class="modal-body px-4">
                <form id="form-detail-pop">
                    @php $fields = ['nomor'=>'Nomor Permohonan', 'tgl-mohon'=>'Tgl Permohonan', 'tgl-terbit'=>'Tgl Terbit', 'nama'=>'Nama', 'tempat-lahir'=>'Tempat Lahir', 'tgl-lahir'=>'Tgl Lahir', 'jk'=>'Jenis Kelamin', 'telp'=>'No Telpon', 'jenis-mohon'=>'Jenis Permohonan', 'jenis-paspor'=>'Jenis Paspor', 'tujuan'=>'Tujuan Paspor', 'no-paspor'=>'No Paspor', 'alur'=>'Alur Terakhir', 'lokasi'=>'Lokasi Arsip']; @endphp
                    @foreach($fields as $id => $label)
                    <div class="row align-items-center"><label class="col-5 fw-bold text-muted" style="font-size: 11.5px;">{{ $label }}</label><div class="col-7">@if($id === 'nama')<div id="det-nama" class="form-control form-control-sm bg-white border text-dark" style="height: auto; min-height: 36px; word-break: break-word; white-space: normal; line-height: 1.4; display: block; padding: 6px 10px;">-</div>@else<input type="text" id="det-{{ $id }}" readonly class="form-control form-control-sm bg-white border text-dark" style="border-radius: 8px; height: 38px; font-size: 13px;">@endif</div></div>
                    @endforeach
                </form>
            </div>
            <div class="modal-footer border-0"><button type="button" class="btn btn-danger shadow-sm" data-bs-dismiss="modal">Tutup</button></div>
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

function lihatDetailBatch(noPengirim) {
    document.getElementById('det_list_berkas_riwayat').innerHTML = '<tr><td colspan="6" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Mengambil data...</td></tr>';
    fetch(`/arsip/list-berkas/${noPengirim}`).then(response => response.json()).then(res => {
        if(res.success) {
            document.getElementById('det_no_pengirim').innerText = res.batch.no_pengirim;
            document.getElementById('det_tgl_pengirim').innerText = res.batch.tgl_pengirim;
            const elStatusBatch = document.getElementById('det_status');
            const statusBatchText = res.batch.status.toUpperCase().trim();
            elStatusBatch.innerText = statusBatchText;
            elStatusBatch.className = statusBatchText.includes('DITERIMA') ? 'badge bg-success text-white' : 'badge bg-warning text-dark';
            let html = '';
            res.data.forEach(item => {
                let statusHtml = ''; let statusInput = item.status_berkas ? item.status_berkas.trim().toUpperCase() : '';
                if (statusInput === 'DIMUSNAHKAN') { statusHtml = `<span class="badge bg-danger-subtle text-danger px-2" style="font-size:9px;">DIMUSNAHKAN</span>`; } 
                else if (statusInput.includes('DITERIMA')) { statusHtml = `<span class="badge bg-success-subtle text-success px-2" style="font-size:9px;">DITERIMA OLEH ARSIP</span>`; } 
                else { statusHtml = `<span class="badge bg-warning-subtle text-warning px-2" style="font-size:9px;">${statusInput}</span>`; }
                html += `<tr><td class="text-primary fw-bold text-center py-2">${item.no_permohonan}</td><td class="text-start">${item.nama}</td><td class="text-center">${item.jenis_permohonan || '-'}</td><td class="text-center">${item.tujuan_paspor || '-'}</td><td class="text-center"><span class="badge bg-light text-dark border">${item.lokasi_arsip || '-'}</span></td><td class="text-center">${statusHtml}</td></tr>`;
            });
            document.getElementById('det_list_berkas_riwayat').innerHTML = html;
            new bootstrap.Modal(document.getElementById('modalDetailBatch')).show();
        }
    });
}

$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    const inputBarcode = $('#input-barcode-permohonan');
    let currentBatchID = '';

    // --- REVISI LOGIKA PENGUNCI FOKUS ---
    inputBarcode.focus();
    
    $(document).on('click', function(e) {
        // JANGAN curi fokus jika user sedang mengklik elemen input, select, atau dalam modal
        if ($(e.target).closest('#select-loker-bulk, .modal-content, input, select').length) {
            return; 
        }
        
        // Kembalikan fokus ke scanner jika modal tidak terbuka
        if (!$('.modal').is(':visible')) {
            inputBarcode.focus();
        }
    });

    // Kembalikan fokus setelah modal tutup (dengan delay agar stabil)
    $('.modal').on('hidden.bs.modal', function () {
        setTimeout(() => inputBarcode.focus(), 300);
    });

    // Tambahan khusus untuk Select Loker agar dropdown tidak hilang
    $('#select-loker-bulk').on('mousedown', function() {
        $(document).off('click.autoFocus'); // Matikan sementara
    }).on('change blur', function() {
        setTimeout(() => inputBarcode.focus(), 500); // Aktifkan lagi setelah milih
    });


    $("#searchDetailBerkas").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#det_list_berkas_riwayat tr").filter(function() { $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1) });
    });

    $('.btn-proses-batch').on('click', function() {
        currentBatchID = $(this).data('id');
        $('#tbody-hasil-scan, #tbody-perlu-scan').empty();
        $.get(`/arsip/list-berkas/${currentBatchID}`, function(res) {
            if(res.success) {
                res.data.forEach(item => {
                    let isDone = (item.status_berkas === 'DITERIMA' || item.status_berkas.includes('DITERIMA'));
                    let checkIcon = isDone ? '<i class="fas fa-check-circle text-success me-2"></i>' : '';
                    let rowHtml = `<tr id="row-${item.no_permohonan}" class="align-middle border-bottom ${isDone ? 'table-success fw-bold' : ''}"><td style="width: 35%;" class="py-3 ps-2 text-start"><div class="d-flex align-items-center icon-container">${checkIcon}<span>${item.no_permohonan}</span></div></td><td style="width: 45%;" class="py-3 text-start"><div style="word-break: break-word; line-height: 1.4;">${item.nama}</div></td><td style="width: 20%;" class="py-3 text-end pe-2"><button type="button" class="btn btn-primary btn-sm py-0" onclick="window.fetchAndShowDetail('${item.no_permohonan}')" style="font-size: 10px; height: 22px; width: 50px;">Detail</button></td></tr>`;
                    $('#tbody-perlu-scan').append(rowHtml); if(isDone) $('#tbody-hasil-scan').prepend(rowHtml);
                });
                updateCounters(); $('#section-riwayat').hide(); $('#section-proses-scan').fadeIn(); setTimeout(() => inputBarcode.focus(), 500);
            }
        });
    });

    function updateCounters() {
        let total = $('#tbody-perlu-scan tr').length;
        let done = $('#tbody-perlu-scan tr.table-success').length;
        $('#pending-count').text((total - done) + ' BERKAS BELUM SCAN');
        $('#scan-count-live').text(done + ' BERKAS TERVERIFIKASI');
        if (total > 0 && total === done) { $('#btn-simpan-batch').prop('disabled', false).removeClass('btn-secondary').addClass('btn-success'); } 
        else { $('#btn-simpan-batch').prop('disabled', true).addClass('btn-secondary').removeClass('btn-success'); }
    }

    inputBarcode.on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault(); const barcode = $(this).val().trim(); $(this).val('');
            if (!barcode) return;
            const rowTarget = $(`#row-${barcode}`);
            if (rowTarget.length === 0) { Swal.fire({ icon: 'error', title: 'Gagal', text: `Nomor ${barcode} tidak ada di batch ini!`, timer: 2000, showConfirmButton: false }); return; }
            if (rowTarget.hasClass('table-success')) return;
            $.post("{{ route('penerimaan-berkas.scan-permohonan') }}", { nomor_permohonan: barcode }, function(res) {
                if (res.success) { 
                    rowTarget.addClass('table-success fw-bold'); 
                    if (rowTarget.find('.fa-check-circle').length === 0) { rowTarget.find('.icon-container').prepend('<i class="fas fa-check-circle text-success me-2"></i>'); } 
                    const rowClone = rowTarget.clone(); 
                    $('#tbody-hasil-scan').prepend(rowClone); 
                    updateCounters(); 
                }
            }).fail(err => { Swal.fire({ icon: 'error', title: 'Gagal Scan', text: err.responseJSON.message }); });
        }
    });

    $(document).on('click', '#btn-simpan-batch', function() {
        const adaLoker = {{ $adaLoker ? 'true' : 'false' }};

        if (!adaLoker) {
            Swal.fire({
                icon: 'warning',
                title: 'Master Rak Belum Terisi',
                text: 'Data rak loker pada sistem arsip masih kosong. Mohon tambahkan data master rak loker terlebih dahulu.',
                confirmButtonText: 'Buka Master Rak',
                showCancelButton: true,
                cancelButtonText: 'Kembali'
            }).then((result) => {
                if (result.isConfirmed) { window.location.href = "{{ route('rak-loker.index') }}"; }
            });
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Simpan?',
            text: "Berkas akan didaftarkan ke rak loker secara otomatis oleh sistem.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Selesaikan',
            confirmButtonColor: '#198754'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                $.post("{{ route('penerimaan-berkas.konfirmasi-bulk') }}", { 
                    _token: "{{ csrf_token() }}",
                    no_pengirim: currentBatchID 
                }, function(res) {
                    if(res.success) {
                        Swal.fire('Berhasil!', 'Berkas telah diterima dan masuk rak.', 'success').then(() => window.location.reload());
                    }
                }).fail(err => Swal.fire('Error', err.responseJSON.message || 'Gagal memproses data', 'error'));
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