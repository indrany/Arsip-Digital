@extends('layouts.app') 

@section('content')

{{-- Input Scan Barcode (Tersembunyi secara visual) --}}
<div style="position: fixed; top: 0; left: 0; width: 1px; height: 1px; opacity: 0; overflow: hidden; z-index: -1;">
    <input type="text" id="input-barcode-permohonan" autofocus autocomplete="off">
</div>

{{-- BAGIAN 1: TABEL RIWAYAT (Antrean Masuk dari Loket/Unit) --}}
<div id="section-riwayat">
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; font-family: Arial;">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-dark">Daftar Kiriman Berkas Masuk (Antrean)</h6>
        </div>
        <div class="card-body p-0">
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
                    <tr style="{{ $batch->status == 'DITERIMA OLEH ARSIP' ? 'background-color: #f8f9fa;' : '' }}">
                        <td class="ps-4 fw-bold text-primary">{{ $batch->no_pengirim }}</td>
                        {{-- Menampilkan Asal Unit (Kanim/UKK/ULP) --}}
                        <td><span class="badge bg-info text-dark px-2">{{ $batch->asal_unit ?? 'Kanim' }}</span></td>
                        {{-- Menampilkan Nama Lengkap Petugas --}}
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
                    <tr><td colspan="7" class="text-center py-4">Tidak ada kiriman berkas masuk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- BAGIAN 2: PROSES SCAN (Muncul setelah klik "Mulai Terima") --}}
<div id="section-proses-scan" style="display: none;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <button class="btn btn-sm btn-outline-secondary btn-back-riwayat">
            <i class="fas fa-arrow-left"></i> Kembali ke Antrean
        </button>
        <h5 class="fw-bold m-0" style="font-family: Arial;">Proses Verifikasi Batch: <span id="current-batch-id" class="text-primary">-</span></h5>
    </div>

    <div class="row">
        {{-- Tabel Kiri: Daftar yang Harus Diterima --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-3" style="min-height: 450px; border-radius: 12px;">
                <h6 class="fw-bold mb-3">1. Berkas Harus Ada (List Kiriman)</h6>
                <table class="table table-sm" id="table-verifikasi-kiri">
                    <thead>
                        <tr class="text-muted" style="font-size: 12px;">
                            <th>No. Pengirim</th>
                            <th>Nama</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-perlu-scan" style="font-size: 13px;">
                        {{-- Diisi via AJAX --}}
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tabel Kanan: Berkas Fisik Ditemukan (Hasil Scan) --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-3" style="min-height: 450px; border-radius: 12px;">
                <h6 class="fw-bold mb-3">2. Berkas Fisik Ditemukan (Hasil Scan)</h6>
                <div class="alert alert-success py-2 px-3 mb-3 d-flex justify-content-between align-items-center" style="border-radius: 8px; border: none;">
                    <span class="small fw-bold">Total Scan:</span>
                    <strong id="scan-count-live" class="h5 m-0">0 Berkas</strong>
                </div>
                <table class="table table-sm">
                    <thead>
                        <tr class="text-muted" style="font-size: 12px;">
                            <th>No. Permohonan</th>
                            <th>Nama</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-hasil-scan" style="font-size: 13px;">
                        {{-- Diisi saat scan berhasil --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Tombol Simpan Akhir --}}
    <div class="text-center mt-4">
        <button id="btn-simpan-batch" class="btn btn-success px-5 fw-bold shadow-sm" style="border-radius: 50px; padding: 12px;">
            <i class="fas fa-check-double me-2"></i> Simpan & Konfirmasi Seluruh Berkas
        </button>
    </div>
</div>

{{-- POP-UP KONFIRMASI (Setiap kali barcode di-scan) --}}
<div class="modal fade" id="modalConfirmScan" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-file-invoice text-success" style="font-size: 50px;"></i>
                </div>
                <h5 class="fw-bold mb-1" id="pop-nama-pemohon">-</h5>
                <p class="text-muted mb-4" id="pop-no-permohonan" style="font-size: 14px;">-</p>
                
                <div class="row g-2">
                    <div class="col-6">
                        <button type="button" class="btn btn-light w-100 fw-bold text-muted" data-bs-dismiss="modal" style="border-radius: 10px;">Batal</button>
                    </div>
                    <div class="col-6">
                        <button type="button" id="btn-fix-terima" class="btn btn-success w-100 fw-bold shadow-sm" style="border-radius: 10px;">TERIMA BERKAS</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    const inputBarcode = $('#input-barcode-permohonan');
    let currentBatch = '';

    // 1. PINDAH KE AREA SCAN & LOAD DATA BATCH LENGKAP
    $('.btn-proses-batch').on('click', function() {
        currentBatch = $(this).data('id');
        $('#current-batch-id').text(currentBatch);
        
        $('#tbody-hasil-scan').empty();
        $('#scan-count-live').text('0 Berkas');
        
        // Panggil fungsi listBerkas di controller yang sudah menyertakan data lengkap
        $.get(`/arsip/list-berkas/${currentBatch}`, function(res) {
            if(res.success) {
                let html = '';
                res.data.forEach(item => {
                    let rowClass = item.status_berkas === 'DITERIMA' || item.status_berkas === 'DITERIMA OLEH ARSIP' ? 'table-success' : '';
                    let checkIcon = rowClass ? ' <i class="fas fa-check-circle ms-2 text-success"></i>' : '';
                    
                    html += `
                        <tr id="row-${item.no_permohonan}" class="align-middle border-bottom ${rowClass}">
                            <td class="fw-bold no-pengirim-cell py-3 ps-2">${currentBatch}${checkIcon}</td>
                            <td>${item.nama}</td>
                        </tr>
                    `;

                    // Jika status sudah diterima, masukkan juga ke tabel kanan hasil scan
                    if(item.status_berkas === 'DITERIMA') {
                        $('#tbody-hasil-scan').append(`
                            <tr id="hasil-${item.no_permohonan}" class="border-bottom">
                                <td class="fw-bold py-3 text-dark">${item.no_permohonan}</td>
                                <td>${item.nama}</td>
                            </tr>
                        `);
                    }
                });
                
                $('#tbody-perlu-scan').html(html);
                $('#scan-count-live').text($('#tbody-hasil-scan tr').length + ' Berkas');
                
                $('#section-riwayat').hide();
                $('#section-proses-scan').fadeIn();
                inputBarcode.val('').focus();
            }
        });
    });

    // 2. LOGIKA SCAN BARCODE
    inputBarcode.on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            const barcode = $(this).val().trim();
            
            if (barcode) {
                $.get(`/penerimaan-berkas/get-detail/${barcode}`, function(res) {
                    if (res.success) {
                        // Pastikan berkas milik batch ini
                        if ($(`#row-${res.data.no_permohonan}`).length === 0) {
                            alert("Berkas ditemukan, tetapi bukan milik batch " + currentBatch);
                            return;
                        }
                        $('#pop-nama-pemohon').text(res.data.nama);
                        $('#pop-no-permohonan').text(res.data.no_permohonan);
                        $('#modalConfirmScan').modal('show');
                    }
                }).fail(() => alert("Barcode tidak terdaftar!"));
            }
            $(this).val('');
        }
    });

    // 3. KONFIRMASI TERIMA BERKAS
    $('#btn-fix-terima').off('click').on('click', function() {
        const noPermohonan = $('#pop-no-permohonan').text();
        const nama = $('#pop-nama-pemohon').text();

        $.post("{{ route('penerimaan-berkas.scan-permohonan') }}", {
            nomor_permohonan: noPermohonan
        }, function(res) {
            $('#modalConfirmScan').modal('hide');

            const barisKiri = $(`#row-${noPermohonan}`);
            barisKiri.addClass('table-success'); 
            
            if (barisKiri.find('.fa-check-circle').length === 0) {
                barisKiri.find('.no-pengirim-cell').append(' <i class="fas fa-check-circle ms-2 text-success"></i>');
            }

            if ($(`#hasil-${noPermohonan}`).length === 0) {
                $('#tbody-hasil-scan').prepend(`
                    <tr id="hasil-${noPermohonan}" class="border-bottom">
                        <td class="fw-bold py-3 text-dark">${noPermohonan}</td>
                        <td>${nama}</td>
                    </tr>
                `);
            }

            $('#scan-count-live').text($('#tbody-hasil-scan tr').length + ' Berkas');
            inputBarcode.val('').focus();
        });
    });

    // 4. SIMPAN SELURUH BATCH
    $('#btn-simpan-batch').on('click', function() {
        const totalScan = $('#tbody-hasil-scan tr').length;
        if (totalScan === 0) return alert("Belum ada berkas di-scan!");

        if(!confirm(`Konfirmasi penerimaan ${totalScan} berkas untuk batch ini?`)) return;
        
        $.post("{{ route('penerimaan-berkas.konfirmasi-bulk') }}", {
            no_pengirim: currentBatch
        }).done(function() {
            alert("Batch Berhasil Diterima.");
            window.location.reload(); 
        }).fail(() => alert("Gagal menyimpan konfirmasi batch."));
    });

    $('.btn-back-riwayat').on('click', () => {
        $('#section-proses-scan').hide();
        $('#section-riwayat').fadeIn();
    });

    $(document).on('click', () => {
        if (!$('.modal').is(':visible') && $('#section-proses-scan').is(':visible')) {
            inputBarcode.focus();
        }
    });
});
</script>
@endpush