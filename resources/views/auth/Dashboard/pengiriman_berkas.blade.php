@extends('layouts.app')

@section('title', 'Tambah Pengiriman Berkas')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row g-4">
        {{-- GRID KIRI: FORM INPUT (40%) --}}
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-edit me-2"></i>Input Data Berkas</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Nomor Permohonan</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-barcode"></i></span>
                            <input type="text" id="input_no_permohonan" class="form-control form-control-lg fw-bold" placeholder="Scan atau Ketik Nomor..." autofocus>
                        </div>
                        <div id="loading-spinner" class="mt-1 d-none">
                            <small class="text-primary"><i class="fas fa-spinner fa-spin me-1"></i> Mencari data...</small>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Nama Pemohon</label>
                            <input type="text" id="input_nama" class="form-control border-0 py-2" 
                                   style="background-color: #e9ecef; color: #6c757d; cursor: not-allowed;" readonly placeholder="Otomatis terisi...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Tempat Lahir</label>
                            <input type="text" id="input_tempat_lahir" class="form-control border-0 py-2" 
                                   style="background-color: #e9ecef; color: #6c757d; cursor: not-allowed;" readonly placeholder="-">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Tanggal Lahir</label>
                            <input type="text" id="input_tgl_lahir" class="form-control border-0 py-2" 
                                   style="background-color: #e9ecef; color: #6c757d; cursor: not-allowed;" readonly placeholder="-">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Jenis Paspor</label>
                            <input type="text" id="input_jenis_paspor" class="form-control border-0 py-2" 
                                   style="background-color: #e9ecef; color: #6c757d; cursor: not-allowed;" readonly placeholder="-">
                        </div>
                    </div>

                    {{-- Tombol Tambah yang Lebih Ramping --}}
                    <div class="text-end">
                        <button type="button" class="btn btn-primary fw-bold py-2 px-4 shadow-sm rounded-3" id="btn-tambah-langsung">
                            <i class="fas fa-plus me-2"></i> TAMBAH
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- GRID KANAN: TABEL DAFTAR (60%) --}}
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <div>
                        <h5 class="mb-0 fw-bold text-success"><i class="fas fa-list me-2"></i>Daftar Pengiriman</h5>
                        <small class="text-muted" id="count-info">0 berkas siap dikirim</small>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold" id="btn-cetak-semua" disabled>
                        <i class="fas fa-print me-1"></i> CETAK SEMUA BARCODE
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0" id="tabel-pengiriman">
                            <thead class="bg-light sticky-top">
                                <tr class="text-dark">
                                    <th class="px-4 fw-bold" style="width: 25%;">No. Permohonan</th>
                                    <th class="fw-bold" style="width: 35%;">Nama</th>
                                    <th class="fw-bold text-center" style="width: 20%;">Barcode</th>
                                    <th class="fw-bold text-center" style="width: 20%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                {{-- Data muncul di sini --}}
                            </tbody>
                        </table>
                    </div>

                    <div id="empty-state" class="text-center py-5">
                        <i class="fas fa-folder-open fa-3x text-light mb-3"></i>
                        <p class="text-muted">Belum ada berkas ditambahkan</p>
                    </div>
                </div>
                <div class="card-footer bg-white py-3 text-end border-top">
                    <button type="button" class="btn btn-success px-5 py-2 fw-bold shadow-sm rounded-3" id="btn-simpan-pengiriman" style="background-color: #10b981; border: none;">
                        <i class="fas fa-paper-plane me-2"></i> KIRIM KE ARSIP
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-control:focus { box-shadow: none; border-color: #3b82f6; }
    .table thead th { border-bottom: 0; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
    .table td { font-size: 14px; border-bottom: 1px solid #f3f4f6 !important; }
    .btn-action-icon { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; border: none; transition: 0.2s; }
    .btn-print-row { background-color: #e0f2fe; color: #0284c7; margin-right: 5px; }
    .btn-print-row:hover { background-color: #0284c7; color: white; }
    .btn-delete-row { background-color: #fee2e2; color: #ef4444; }
    .btn-delete-row:hover { background-color: #ef4444; color: white; }
    .barcode-container svg { max-width: 100%; height: 35px; }
    .sticky-top { z-index: 10; }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
$(document).ready(function() {
    let daftarBerkas = [];

    // FUNGSI 1: AUTO-SEARCH SAAT KETIK/SCAN
    $('#input_no_permohonan').on('input', function() {
        let no = $(this).val().trim();
        if (no.length >= 16) { 
            ambilDataOtomatis(no);
        }
    });

    function ambilDataOtomatis(no) {
        $('#loading-spinner').removeClass('d-none');
        $.ajax({
            url: "/get-permohonan-detail/" + no,
            method: "GET",
            success: function(res) {
                $('#loading-spinner').addClass('d-none');
                if (res.success) {
                    $('#input_nama').val(res.data.nama);
                    $('#input_tempat_lahir').val(res.data.tempat_lahir);
                    $('#input_tgl_lahir').val(res.data.tanggal_lahir);
                    $('#input_jenis_paspor').val(res.data.jenis_paspor);
                    $('#btn-tambah-langsung').focus();
                }
            },
            error: function() { $('#loading-spinner').addClass('d-none'); }
        });
    }

    function clearInput() {
        $('#input_no_permohonan').val('').focus();
        $('#input_nama, #input_tempat_lahir, #input_tgl_lahir, #input_jenis_paspor').val('');
    }

    function updateUI() {
        $('#count-info').text(daftarBerkas.length + " berkas siap dikirim");
        $('#btn-cetak-semua').prop('disabled', daftarBerkas.length === 0);
        if (daftarBerkas.length > 0) $('#empty-state').addClass('d-none');
        else $('#empty-state').removeClass('d-none');
    }

    // FUNGSI 2: TAMBAH KE TABEL
    $('#btn-tambah-langsung').on('click', function() {
        let no = $('#input_no_permohonan').val().trim();
        let nama = $('#input_nama').val().trim();

        if (!no || !nama) return alert("Data pemohon belum ditemukan!");
        if (daftarBerkas.includes(no)) { alert("Nomor sudah ada di daftar!"); clearInput(); return; }

        daftarBerkas.push(no);
        let safeId = no.replace(/[^a-z0-9]/gi, '-');
        
        let html = `
            <tr id="row-${safeId}">
                <td class="px-4 fw-bold text-primary">${no}</td>
                <td class="text-dark">${nama}</td>
                <td class="text-center">
                    <div id="print-area-${safeId}" class="barcode-container">
                        <svg id="barcode-${safeId}"></svg>
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn-action-icon btn-print-row" title="Cetak Barcode" onclick="printSingleBarcode('${safeId}')">
                        <i class="fas fa-print"></i>
                    </button>
                    <button type="button" class="btn-action-icon btn-delete-row" title="Hapus" onclick="hapusBaris('${no}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        
        $('#tabel-pengiriman tbody').prepend(html);
        JsBarcode("#barcode-" + safeId, no, { format: "CODE128", width: 1.2, height: 35, displayValue: true, fontSize: 10 });

        updateUI();
        clearInput();
    });

    // FUNGSI 3: CETAK SINGLE BARCODE
    window.printSingleBarcode = function(safeId) {
        let content = document.getElementById('print-area-' + safeId).innerHTML;
        let win = window.open('', '_blank', 'width=400,height=300');
        win.document.write('<html><head><style>body{display:flex;justify-content:center;align-items:center;height:90vh;margin:0;}</style></head><body>'+content+'</body></html>');
        win.document.close();
        setTimeout(() => { win.print(); win.close(); }, 500);
    };

    // FUNGSI 4: CETAK SEMUA BARCODE
    $('#btn-cetak-semua').on('click', function() {
        let win = window.open('', '_blank', 'width=800,height=600');
        win.document.write('<html><head><style>body{font-family:sans-serif;display:grid;grid-template-columns:repeat(3,1fr);gap:20px;padding:20px;}.item{border:1px solid #eee;padding:10px;text-align:center;}svg{width:100%;height:auto;}</style></head><body>');
        
        $('#tabel-pengiriman tbody tr').each(function() {
            let barcodeHtml = $(this).find('.barcode-container').html();
            win.document.write('<div class="item">' + barcodeHtml + '</div>');
        });
        
        win.document.write('</body></html>');
        win.document.close();
        setTimeout(() => { win.print(); win.close(); }, 500);
    });

    // FUNGSI 5: HAPUS BARIS
    window.hapusBaris = function(no) {
        let safeId = no.replace(/[^a-z0-9]/gi, '-');
        $(`#row-${safeId}`).remove();
        daftarBerkas = daftarBerkas.filter(item => item !== no);
        updateUI();
    };

    // FUNGSI 6: SIMPAN KE DATABASE
    $('#btn-simpan-pengiriman').on('click', function() {
        if (daftarBerkas.length === 0) return alert("Daftar masih kosong!");
        if (!confirm("Kirim " + daftarBerkas.length + " berkas ini ke Arsip?")) return;

        const dataList = [];
        $('#tabel-pengiriman tbody tr').each(function() {
            dataList.push({
                no_permohonan: $(this).find('td:eq(0)').text().trim(),
                nama: $(this).find('td:eq(1)').text().trim()
            });
        });

        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
        $.ajax({
            url: "{{ route('pengiriman-berkas.store') }}",
            method: "POST",
            data: { _token: "{{ csrf_token() }}", nomor_permohonan_list: dataList },
            success: function(res) { 
                if(res.success) { alert("Berkas berhasil diajukan!"); window.location.href = "{{ route('pengiriman-berkas.index') }}"; }
            },
            error: function(xhr) { 
                $('#btn-simpan-pengiriman').prop('disabled', false).text('Kirim ke Arsip');
                alert("Gagal menyimpan data."); 
            }
        });
    });

    // Enter di nomor permohonan
    $('#input_no_permohonan').on('keypress', function(e) { 
        if (e.which === 13) { let no = $(this).val().trim(); if (no) ambilDataOtomatis(no); }
    });
});
</script>
@endpush