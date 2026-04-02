@extends('layouts.app')

@section('title', 'Tambah Pengiriman Berkas')
@section('page-title', 'Tambah Pengiriman Berkas')
@section('page-subtitle', 'Proses verifikasi dan input batch pengiriman berkas fisik ke bagian arsip.')

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
                            <input type="text" id="input_no_permohonan" class="form-control form-control-lg fw-bold" placeholder="Scan atau Ketik Nomor..." autofocus autocomplete="off">
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
                            <tbody class="bg-white"></tbody>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        let daftarBerkas = [];
        const inputBarcode = $('#input_no_permohonan');

        inputBarcode.focus();

        // --- 1. LOGIKA AUTO-FILL ---
        inputBarcode.on('input', function() {
            let no = $(this).val().replace(/[^a-zA-Z0-9]/g, '').trim();
            if (no.length === 16) { cariDataPemohon(no); }
        });

        function cariDataPemohon(no) {
            if (daftarBerkas.find(b => b.no === no)) {
                Swal.fire({ icon: 'warning', title: 'Sudah Ada!', text: 'Nomor ini sudah masuk dalam daftar.', timer: 2000, showConfirmButton: false });
                resetInputHanyaNomor();
                return;
            }
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
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                        resetInputHanyaNomor();
                    }
                }
            });
        }

        // --- 2. LOGIKA TAMBAH KE TABEL ---
        $('#btn-tambah-langsung').on('click', function() {
            let no = inputBarcode.val().trim();
            let nama = $('#input_nama').val().trim();
            if (!no || !nama) return Swal.fire('Data Kosong', 'Cari nomor valid dulu.', 'warning');

            daftarBerkas.push({ no: no, nama: nama });
            let safeId = no.replace(/[^a-z0-9]/gi, '-');
            let html = `
                <tr id="row-${safeId}">
                    <td class="px-4 fw-bold text-primary">${no}</td>
                    <td class="text-dark">${nama}</td>
                    <td class="text-center"><svg id="barcode-${safeId}"></svg></td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" class="btn-action-icon btn-print-row" onclick="printSingleBarcode('${no}', '${nama}')" title="Cetak">
                                <i class="fas fa-print"></i>
                            </button>
                            <button type="button" class="btn-action-icon btn-delete-row" onclick="hapusBaris('${no}')" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
            $('#tabel-pengiriman tbody').prepend(html);
            JsBarcode("#barcode-" + safeId, no, { format: "CODE128", width: 1.2, height: 30, displayValue: false });
            updateUI();
            resetSemuaInput();
        });

        // --- 3. CSS RESET & KONFIGURASI CETAK (MODEL FONT SERAGAM) ---
        const cetakStyles = `
<style>
    @media print {
        @page { 
            size: 70mm 50mm; 
            margin: 0; 
        }
    }

    * { box-sizing: border-box; -webkit-print-color-adjust: exact; }
    
    body { 
        margin: 0; padding: 0; 
        width: 70mm; height: 50mm; 
        overflow: hidden;
        background-color: white;
        font-family: Arial, Helvetica, sans-serif;
    }
    
    .wrapper {
        width: 70mm; 
        height: 50mm; 
        padding: 2mm; 
        display: flex; 
        flex-direction: column; 
        /* UBAH INI: Biar elemennya gak dipaksa mencar ke ujung-ujung */
        justify-content: flex-start; 
        align-items: center;
        page-break-after: always;
    }

    .header { 
        width: 100%; display: flex; align-items: center; 
        justify-content: center; gap: 4px; height: 7mm;
        margin-bottom: 2mm;
    }

    .logo { 
        width: 22px; height: 22px; 
        background: url("/images/v1_208.png") no-repeat center; 
        background-size: contain; 
    }
    
    .title { font-size: 7pt; font-weight: bold; text-transform: uppercase; white-space: nowrap; }

    .main {
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        /* Kasih jarak sedikit bawah barcode & nama */
        margin-bottom: 3mm; 
    }

    .nama { 
        font-size: 9pt; 
        font-weight: bold; 
        text-transform: uppercase; 
        text-align: center;
        width: 100%;
        margin-top: 2mm;
        line-height: 1.1;
        font-family: Arial, sans-serif;
    }

    .footer { 
        width: 100%; 
        text-align: center; 
        font-size: 6.5pt; 
        font-weight: bold; 
        text-transform: uppercase;
        
        /* SEKARANG INI PASTI NGEFEK */
        margin-top: 2mm; /* Atur jaraknya manual dari sini */
        padding-bottom: 0;
    }

    svg { 
        width: 46mm !important; 
        height: auto !important; 
        /* NAIKKAN INI */
        max-height: 25mm; 
        display: block;
        margin: 0 auto;
    }
</style>
`;
        // FUNGSI CETAK SATUAN
        window.printSingleBarcode = function(no, nama) {
            let printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html><head>${cetakStyles}</head>
                <body>
                    <div class="wrapper">
                        <div class="header">
                            <div class="logo"></div>
                            <div class="title">Sistem Arsip Digital</div>
                        </div>
                        <div class="main">
                            <svg id="b-print"></svg>
                            <div class="nama">${nama}</div>
                        </div>
                        <div class="footer">Kantor Imigrasi Kelas I TPI Tanjung Perak</div>
                    </div>
                    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
                    <script>
                        JsBarcode("#b-print", "${no}", { 
                        format: "CODE128", 
                        width: 1.1,      /* Rampingkan dikit lagi biar aman */
                        height: 45,      /* Jangan terlalu tinggi (45 cukup) */
                        displayValue: true, 
                        fontSize: 13,    /* KUNCI: Kecilkan sedikit biar gak kepotong bawahnya */
                        fontOptions: "bold", 
                        font: "Arial",
                        margin: 0,
                        textMargin: 3    /* Jarak batang ke angka jangan terlalu jauh */
                    });
                        window.onload = () => { setTimeout(() => { window.print(); window.close(); }, 500); };
                    <\/script>
                </body></html>
            `);
            printWindow.document.close();
        };

        // FUNGSI CETAK SEMUA (BULK)
        // FUNGSI CETAK SEMUA (BULK) - SUDAH FIX POTONG
        $('#btn-cetak-semua').on('click', function() {
            if (daftarBerkas.length === 0) return;
            let printWindow = window.open('', '_blank');
            let contentHtml = '';
            
            daftarBerkas.forEach((item, index) => {
                contentHtml += `
                    <div class="wrapper">
                        <div class="header">
                            <div class="logo"></div>
                            <div class="title">Sistem Arsip Digital</div>
                        </div>
                        <div class="main">
                            <svg id="bulk-${index}"></svg>
                            <div class="nama">${item.nama}</div>
                        </div>
                        <div class="footer">Kantor Imigrasi Kelas I TPI Tanjung Perak</div>
                    </div>`;
            });

            printWindow.document.write(`
                <html><head>${cetakStyles}</head>
                <body>
                    ${contentHtml}
                    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
                    <script>
                        const data = ${JSON.stringify(daftarBerkas)};
                        data.forEach((item, i) => {
                            JsBarcode("#bulk-" + i, item.no, { 
                                format: "CODE128", 
                                width: 1.1,      /* SAMAKAN: Biar gak tumpah samping */
                                height: 45,      /* SAMAKAN: Biar gak tumpah bawah */
                                displayValue: true, 
                                fontSize: 14,    /* SAMAKAN: Ukuran angka aman */
                                fontOptions: "bold", 
                                font: "Arial",
                                margin: 0,
                                textMargin: 2    /* SAMAKAN: Jarak angka ke batang */
                            });
                        });
                        window.onload = () => { setTimeout(() => { window.print(); window.close(); }, 500); };
                    <\/script>
                </body>
                </html>
            `);
            printWindow.document.close();
        });

        // --- FUNGSI PENDUKUNG ---
        function resetSemuaInput() { inputBarcode.val('').focus(); $('#input_nama, #input_tempat_lahir, #input_tgl_lahir, #input_jenis_paspor').val(''); }
        function resetInputHanyaNomor() { inputBarcode.val('').focus(); }
        window.hapusBaris = function(no) {
            daftarBerkas = daftarBerkas.filter(item => item.no !== no);
            $(`#row-${no.replace(/[^a-z0-9]/gi, '-')}`).remove();
            updateUI();
        };
        function updateUI() {
            $('#count-info').text(daftarBerkas.length + " berkas siap dikirim");
            $('#btn-cetak-semua').prop('disabled', daftarBerkas.length === 0);
            $('#empty-state').toggleClass('d-none', daftarBerkas.length > 0);
        }

        // LOGIKA SIMPAN DATABASE
        $('#btn-simpan-pengiriman').on('click', function() {
            if (daftarBerkas.length === 0) return;
            Swal.fire({ title: 'Kirim ke Arsip?', text: "Ajukan " + daftarBerkas.length + " berkas ini?", icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, Kirim!' }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('pengiriman-berkas.store') }}",
                        method: "POST",
                        data: { _token: "{{ csrf_token() }}", nomor_permohonan_list: daftarBerkas.map(b => ({ no_permohonan: b.no, nama: b.nama })) },
                        success: function(res) { if(res.success) { Swal.fire('Berhasil!', 'Data dikirim.', 'success').then(() => window.location.href = "{{ route('pengiriman-berkas.index') }}"); } }
                    });
                }
            });
        });
    });
</script>
@endpush