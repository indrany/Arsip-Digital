@extends('layouts.app') 

@section('title', 'Penerimaan Berkas') 
@section('page-title', 'Penerimaan Berkas')
@section('page-subtitle', 'Verifikasi dan penerimaan berkas permohonan satu per satu.')

@section('content')

{{-- Input Scan Barcode Permohonan (Tetap disembunyikan agar otomatis fokus) --}}
<div class="form-container mb-4" style="border: none; padding: 0;">
    <div class="row align-items-center">
        <div class="col-md-5"> 
            <div class="input-group search-box" style="position: absolute; left: -9999px;">
                <input type="text" id="input-barcode-permohonan" class="form-control" placeholder="Scan Barcode..." autofocus> 
            </div>
        </div>
        
        <div class="col-md-12 d-flex justify-content-end align-items-center">
             <div class="barcode-area text-end p-3 rounded bg-light border">
                 <h5 class="m-0 text-muted">Berkas Menunggu Konfirmasi</h5>
                 <span id="scan-count" class="h3 fw-bold text-success">0 Berkas</span>
                 <p class="mb-0 text-sm">di sesi ini (Termasuk Scan HP)</p>
             </div>
        </div>
    </div>
</div>

<div class="row penerimaan-berkas-container">
    {{-- KOLOM KIRI --}}
    <div class="col-lg-6 mb-4">
        <div class="form-container" style="min-height: 350px;">
            <div class="section-title">Data Berkas Dikirim (Menunggu Diterima)</div>
            <div class="table-container p-0 border-0 shadow-none">
                <table id="table-berkas-dikirim" class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 40%;">No. Permohonan</th>
                            <th style="width: 30%;">Tgl Permohonan</th>
                            <th style="width: 30%;">Nama</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-berkas-dikirim">
                        @if (isset($list_siap_diterima) && count($list_siap_diterima) > 0)
                            @foreach ($list_siap_diterima as $item)
                                <tr data-permohonan-id="{{ $item->no_permohonan }}" class="row-pending">
                                    <td>{{ $item->no_permohonan }}</td>
                                    <td>{{ $item->tanggal_permohonan }}</td>
                                    <td>{{ $item->nama }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3" class="text-center text-muted">Tidak ada berkas yang berstatus Siap Diterima.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- KOLOM KANAN --}}
    <div class="col-lg-6 mb-4">
        <div class="form-container" style="min-height: 350px;">
            <div class="section-title">Berkas Diterima (Data Scan Komputer & HP)</div>
            <div class="table-container p-0 border-0 shadow-none">
                <table id="table-berkas-diterima" class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 35%;">No. Permohonan</th>
                            <th style="width: 35%;">Tgl Permohonan</th>
                            <th style="width: 30%;">Nama</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-berkas-diterima">
                        <tr>
                            <td colspan="3" class="text-center text-muted">Gunakan scanner atau scan melalui HP.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="form-footer">
    <button id="btn-simpan-penerimaan" class="action-button primary-action" style="background-color: #10b981;" disabled>
        <i class="fas fa-check-circle me-2"></i> Simpan & Konfirmasi Penerimaan (<span id="count-simpan">0</span> Berkas)
    </button>
</div>
@endsection

@push('scripts')
<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
    
$(document).ready(function() {
    
    const btnSimpan = $('#btn-simpan-penerimaan');
    const inputBarcode = $('#input-barcode-permohonan');
    const tbodyDiterima = $('#tbody-berkas-diterima');
    const scanCountDisplay = $('#scan-count');
    const countSimpanDisplay = $('#count-simpan');
    const TARGET_BARCODE_LENGTH = 11; 
    
    let list_berkas_diterima = {}; 
    let scanTimeout = null;

    function renderTableDiterima() {
        tbodyDiterima.empty();
        const diterimaKeys = Object.keys(list_berkas_diterima);
        
        scanCountDisplay.text(diterimaKeys.length + ' Berkas');
        countSimpanDisplay.text(diterimaKeys.length);

        if (diterimaKeys.length === 0) {
            tbodyDiterima.html('<tr><td colspan="3" class="text-center text-muted">Scan Barcode untuk menambahkan ke daftar ini.</td></tr>');
            btnSimpan.prop('disabled', true);
            return;
        }

        let html = '';
        diterimaKeys.forEach(key => {
            const item = list_berkas_diterima[key];
            html += `
                <tr>
                    <td>${item.no_permohonan}</td>
                    <td>${item.tanggal_permohonan || '-'}</td>
                    <td>${item.nama}</td>
                </tr>
            `;
        });
        tbodyDiterima.html(html);
        btnSimpan.prop('disabled', false); 
    }
    
    // Fungsi untuk memproses data (baik dari scan lokal maupun polling HP)
    function processDataEntry(data) {
        if (!list_berkas_diterima[data.no_permohonan]) {
            list_berkas_diterima[data.no_permohonan] = data;
            $(`#tbody-berkas-dikirim tr[data-permohonan-id="${data.no_permohonan}"]`).remove();
            renderTableDiterima();
            return true;
        }
        return false;
    }

    function processScan(barcode) {
        if (list_berkas_diterima[barcode]) {
            alert('PERINGATAN: Berkas ' + barcode + ' sudah ada.');
            inputBarcode.val('').focus();
            return;
        }
        
        $.ajax({
            url: "{{ route('penerimaan-berkas.scan-permohonan') }}", 
            type: 'POST',
            data: { nomor_permohonan: barcode },
            success: function(response) { 
                processDataEntry(response.data);
                inputBarcode.val('').focus();
            }, 
            error: function(xhr) {
                alert('Error: Berkas tidak ditemukan.');
                inputBarcode.val('').focus();
            }
        });
    }

    // =========================================================================
    // PERBAIKAN: LOGIKA POLLING (UNTUK SCAN DARI HP)
    // =========================================================================
    setInterval(function() {
    $.ajax({
        url: "{{ route('penerimaan-berkas.check-new') }}", 
        method: "GET",
        dataType: "json",
        success: function(response) {
            if(response.hasNewData && response.data_list.length > 0) {
                let updated = false;
                response.data_list.forEach(item => {
                    // Jika data belum ada di daftar kanan, tambahkan
                    if (!list_berkas_diterima[item.no_permohonan]) {
                        processDataEntry(item);
                        updated = true;
                    }
                });
                if (updated) {
                    console.log("Data HP masuk!");
                    // Opsional: tambahkan suara alert atau notifikasi kecil di sini
                }
            }
        }
    });
}, 2000); // Percepat menjadi 2 detik agar lebih responsif

    // Logika input manual/scanner kabel
    inputBarcode.on('input', function() {
        clearTimeout(scanTimeout); 
        const barcode = $(this).val().trim();
        if (barcode.length === TARGET_BARCODE_LENGTH) {
            processScan(barcode);
        } else if (barcode.length > 0) {
            scanTimeout = setTimeout(() => {
                if (inputBarcode.val().trim()) processScan(inputBarcode.val().trim());
            }, 1000);
        }
    });

    // Tombol Simpan Masal
    btnSimpan.on('click', function() {
        const diterimaKeys = Object.keys(list_berkas_diterima);
        if (confirm(`Konfirmasi penerimaan ${diterimaKeys.length} berkas?`)) {
            $.ajax({
                url: "{{ route('penerimaan-berkas.konfirmasi-bulk') }}", 
                type: 'POST',
                data: { nomor_permohonan_list: diterimaKeys },
                success: function(response) {
                    alert(response.message);
                    location.reload(); 
                }
            });
        }
    });

    renderTableDiterima(); 
});
</script>
@endpush