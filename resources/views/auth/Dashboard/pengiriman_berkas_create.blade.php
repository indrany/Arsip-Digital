@extends('layouts.app') 

@section('title', 'Buat Pengiriman Berkas') 
@section('page-title', 'Pengiriman Berkas')
@section('page-subtitle', 'Formulir pengiriman berkas arsip baru.')

@section('content')

    {{-- Metadata untuk CSRF Token (PENTING untuk AJAX) --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Alert Status (Diposisikan di atas semua card) --}}
    <div id="status-alert" class="alert-placeholder mb-4">
        {{-- Alert/notifikasi akan muncul di sini (dengan JavaScript) --}}
    </div>

    {{-- START: CARD UTAMA (INPUT & BARCODE) --}}
    <div class="form-container mb-4"> 
        
        <h5 class="section-title mb-4">Formulir Pemohon & Berkas Pengiriman</h5>
        
        <div class="row">
            
            {{-- KOLOM 1: KIRI (SEKARANG BERISI INPUT DAN BARCODE) --}}
            <div class="col-md-6"> 
                
                {{-- BLOK 1A: INPUT NOMOR PERMOHONAN --}}
                <form id="form-permohonan" class="mb-4">
                    <label for="nomor_pemohon" class="form-label visually-hidden">Nomor Permohonan</label>
                    <div class="input-group search-box">
                        <input type="text" class="form-control" id="nomor_pemohon" placeholder="Masukkan Nomor Permohonan" required>
                        
                        <button type="button" class="action-button small-action" id="btn-tambah-permohonan">
                            <i class="fas fa-plus me-1"></i> Tambah
                        </button>
                    </div>
                </form>
                
                {{-- BLOK 1B: AREA BARCODE (Langsung di bawah Input) --}}
                <div id="barcode-area" class="barcode-area" style="display: none;">
                    
                    <h6 class="text-muted mt-0 mb-3">Barcode Berkas Pengiriman</h6> 
                    
                    <div id="print-area" class="p-2"> 
                        <canvas id="barcode-canvas" style="display: none;"></canvas> 
                        <span id="barcode-number" class="barcode-number-text d-block"></span>
                    </div>
                    
                    <button type="button" class="print-button mt-2" id="btn-print-barcode">
                        <i class="fas fa-print me-2"></i> Cetak Barcode
                    </button>
                </div>
            </div>
            
            {{-- MENGHAPUS KOLOM KEDUA (KANAN) KARENA TIDAK DIGUNAKAN --}}
            
        </div>
    {{-- END: CARD UTAMA --}}

    {{-- Bagian Bawah: Daftar Berkas (Tabel) --}}
    <div class="table-container mt-4"> 
        
        <div class="form-title-bar">
            <h5 class="v1_1303 m-0">Daftar Pemohon</h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover custom-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 15%;">No. Permohonan</th>
                        <th style="width: 15%;">Tanggal Permohonan</th>
                        <th style="width: 25%;">Nama</th>
                        <th style="width: 15%;">Tempat Lahir</th> 
                        <th style="width: 15%;">Tanggal Lahir</th> 
                        <th style="width: 15%;">Aksi</th> 
                    </tr>
                </thead>
                <tbody id="permohonan-list">
                    {{-- Konten akan diisi oleh JavaScript --}}
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada permohonan yang ditambahkan.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    {{-- FOOTER SIMPAN (Di luar table-container untuk diposisikan di kanan bawah) --}}
    <div class="form-footer">
        <button id="btn-simpan-pengiriman">
            <i class="fas fa-save me-1"></i> Simpan Pengiriman
        </button>
    </div>
    
    {{-- Memuat skrip AJAX dan Logic --}}
    @include('scripts.pengiriman_berkas_scripts')

@endsection

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
$(document).ready(function() {
    // Pengaturan CSRF Token untuk AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    const btnTambah = $('#btn-tambah-permohonan');
    const inputNomor = $('#nomor_pemohon');
    const permohonanList = $('#permohonan-list');
    const barcodeArea = $('#barcode-area');

    // 1. FUNGSI CARI & TAMBAH PERMOHONAN
    btnTambah.on('click', function() {
        let nomor = inputNomor.val().trim();

        if (nomor === "") {
            alert("Silakan masukkan nomor permohonan.");
            return;
        }

        // AJAX ke Controller untuk mencari data
        $.ajax({
            url: "{{ route('cari-permohonan') }}",
            type: "GET",
            data: { nomor_permohonan: nomor },
            beforeSend: function() {
                btnTambah.prop('disabled', true).text('Mencari...');
            },
            success: function(response) {
                btnTambah.prop('disabled', false).html('<i class="fas fa-plus me-1"></i> Tambah');
                
                if (response.data) {
                    const data = response.data;

                    // Tampilkan area barcode dan teks nomornya
                    barcodeArea.show();
                    $('#barcode-number').text(data.no_permohonan);

                    // GENERATE BARCODE (FORMAT CODE128)
                    // Ini kunci agar hasil scan 100% akurat sesuai database
                    JsBarcode("#barcode-canvas", data.no_permohonan, {
                        format: "CODE128", 
                        lineColor: "#000",
                        width: 2,
                        height: 50,
                        displayValue: false // Teks sudah kita tampilkan manual di #barcode-number
                    });
                    
                    $('#barcode-canvas').show();

                    // Tambahkan data ke tabel di bawah
                    renderTable(data);
                    
                    // Reset input
                    inputNomor.val('').focus();
                }
            },
            error: function(xhr) {
                btnTambah.prop('disabled', false).html('<i class="fas fa-plus me-1"></i> Tambah');
                alert("Data tidak ditemukan atau terjadi kesalahan server.");
            }
        });
    });

    // 2. FUNGSI RENDER TABEL
    function renderTable(data) {
        // Hapus pesan "Belum ada permohonan" jika ada
        if (permohonanList.find('td').length === 1) {
            permohonanList.empty();
        }

        // Cek agar tidak ada nomor ganda di tabel
        if ($(`tr[data-id="${data.no_permohonan}"]`).length > 0) {
            alert("Nomor permohonan ini sudah ada dalam daftar.");
            return;
        }

        const row = `
            <tr data-id="${data.no_permohonan}">
                <td>${data.no_permohonan}</td>
                <td>${data.tanggal_permohonan}</td>
                <td>${data.nama}</td>
                <td>${data.tempat_lahir || '-'}</td>
                <td>${data.tanggal_lahir || '-'}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm btn-hapus">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </td>
            </tr>`;

        permohonanList.append(row);
    }

    // 3. FUNGSI HAPUS BARIS TABEL
    $(document).on('click', '.btn-hapus', function() {
        $(this).closest('tr').remove();
        
        if (permohonanList.children().length === 0) {
            permohonanList.html('<tr><td colspan="6" class="text-center py-4 text-muted">Belum ada permohonan yang ditambahkan.</td></tr>');
            barcodeArea.hide();
        }
    });

    // 4. FUNGSI CETAK BARCODE
    $('#btn-print-barcode').on('click', function() {
        const printContents = document.getElementById('print-area').innerHTML;
        const originalContents = document.body.innerHTML;

        document.body.innerHTML = `
            <div style="text-align:center; padding-top: 50px;">
                ${printContents}
            </div>
        `;
        
        window.print();
        document.body.innerHTML = originalContents;
        location.reload(); // Reload untuk mengembalikan state JavaScript
    });

    // 5. FUNGSI SIMPAN PENGIRIMAN (CONTOH)
    $('#btn-simpan-pengiriman').on('click', function() {
        let items = [];
        $('#permohonan-list tr').each(function() {
            let id = $(this).data('id');
            if (id) items.push(id);
        });

        if (items.length === 0) {
            alert("Daftar pemohon masih kosong!");
            return;
        }

        // Kirim data list ke server untuk disimpan
        console.log("Menyimpan data:", items);
        alert("Simpan pengiriman berhasil (Logika simpan bisa disesuaikan di controller).");
    });
});
</script>