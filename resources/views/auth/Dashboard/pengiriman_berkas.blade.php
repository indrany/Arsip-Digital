@extends('layouts.app')

@section('title', 'Tambah Pengiriman Berkas')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-primary">Tambah Pengiriman Berkas</h5>
            <div>
                <a href="{{ route('pengiriman-berkas.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                    <i class="fas fa-arrow-left"></i> Kembali ke Riwayat
                </a>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-cetak-semua" disabled>
                    <i class="fas fa-print"></i> Cetak Semua Barcode
                </button>
            </div>
        </div>
        <div class="card-body">
            {{-- Input Area --}}
            <div class="row g-2 mb-4">
                <div class="col-md-5">
                    <label class="small text-muted">Nomor Permohonan</label>
                    <input type="text" id="input_no_permohonan" class="form-control" placeholder="Contoh: 0234822811" autofocus>
                </div>
                <div class="col-md-5">
                    <label class="small text-muted">Nama Pemohon</label>
                    <input type="text" id="input_nama" class="form-control" placeholder="Nama Pemohon">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-primary w-100 fw-bold" id="btn-tambah-langsung">
                        <i class="fas fa-plus"></i> Tambah
                    </button>
                </div>
            </div>

            <hr>

            {{-- Tabel Input Berkas --}}
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="tabel-pengiriman">
                    <thead class="table-light">
                        <tr>
                            <th>No. Permohonan</th>
                            <th>Nama</th>
                            <th style="width: 250px;" class="text-center">Barcode</th>
                            <th class="text-center" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Data yang ditambah secara dinamis --}}
                    </tbody>
                </table>
            </div>

            <div class="text-end mt-4">
                <button type="button" class="btn btn-success px-5 fw-bold" id="btn-simpan-pengiriman">
                    <i class="fas fa-paper-plane me-2"></i> Simpan Pengiriman
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
$(document).ready(function() {
    let daftarBerkas = [];

    function prosesTambah() {
        let no = $('#input_no_permohonan').val().trim();
        let nama = $('#input_nama').val().trim();

        if (no === "" || nama === "") {
            alert("Harap isi Nomor dan Nama!");
            return;
        }

        if (daftarBerkas.includes(no)) {
            alert("Nomor Permohonan sudah ada di daftar!");
            return;
        }

        $.ajax({
            url: "{{ route('cari-permohonan') }}",
            method: "POST",
            data: {
                no_permohonan: no,
                nama: nama,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if(response.success) {
                    daftarBerkas.push(no);
                    $('#btn-cetak-semua').prop('disabled', false);

                    let safeId = no.replace(/[^a-z0-9]/gi, '-');
                    let html = `
                        <tr id="row-${safeId}">
                            <td class="fw-bold text-primary">${no}</td>
                            <td>${response.data.nama}</td>
                            <td class="text-center bg-light p-2">
                                <div id="print-area-${safeId}">
                                    <svg id="barcode-${safeId}"></svg>
                                    <div class="barcode-label-text" style="font-size: 11px; font-weight: bold; font-family: Arial;">${no}</div>
                                </div>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-info btn-sm text-white mb-1" onclick="printBarcode('${safeId}')">
                                    <i class="fas fa-print"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm mb-1" onclick="hapusBaris('${no}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>`;
                    
                    $('#tabel-pengiriman tbody').append(html);
                    
                    JsBarcode("#barcode-" + safeId, no, { 
                        format: "CODE128", 
                        width: 2, 
                        height: 45, 
                        displayValue: false 
                    });
                    
                    $('#input_no_permohonan').val('').focus();
                    $('#input_nama').val('');
                }
            }
        });
    }

    $('#btn-tambah-langsung').on('click', function(e) { e.preventDefault(); prosesTambah(); });

    $('#input_no_permohonan').on('keypress', function(e) { 
        if (e.which === 13) { e.preventDefault(); $('#input_nama').focus(); } 
    });

    $('#input_nama').on('keypress', function(e) { 
        if (e.which === 13) { e.preventDefault(); prosesTambah(); } 
    });

    window.printBarcode = function(safeId) {
        let content = document.getElementById('print-area-' + safeId).innerHTML;
        let printWindow = window.open('', '_blank', 'height=300,width=500');
        printWindow.document.write('<html><head><title>Print Label</title>');
        printWindow.document.write('<style>body{margin:0;display:flex;flex-direction:column;justify-content:center;align-items:center;height:90vh;font-family:Arial,sans-serif;}svg{width:220px;height:auto;}.barcode-label-text{font-size:16px!important;margin-top:4px;font-weight:bold;}</style>');
        printWindow.document.write('</head><body>' + content + '</body></html>');
        printWindow.document.close();
        setTimeout(() => { printWindow.print(); printWindow.close(); }, 500);
    };

    window.hapusBaris = function(no) {
        let safeId = no.replace(/[^a-z0-9]/gi, '-');
        $(`#row-${safeId}`).remove();
        daftarBerkas = daftarBerkas.filter(item => item !== no);
        if(daftarBerkas.length === 0) $('#btn-cetak-semua').prop('disabled', true);
    };

    $('#btn-simpan-pengiriman').on('click', function() {
        const dataList = [];
        $('#tabel-pengiriman tbody tr').each(function() {
            dataList.push({
                no_permohonan: $(this).find('td:eq(0)').text().trim(),
                nama: $(this).find('td:eq(1)').text().trim()
            });
        });

        if (dataList.length === 0) return alert("Tambahkan berkas dulu!");

        $.ajax({
            url: "{{ route('pengiriman-berkas.store') }}",
            method: "POST",
            data: { _token: "{{ csrf_token() }}", nomor_permohonan_list: dataList },
            success: function(res) {
                if(res.success) {
                    alert("Berhasil disimpan!");
                    window.location.href = "{{ route('pengiriman-berkas.index') }}"; // Kembali ke tabel riwayat
                }
            }
        });
    });

    $('#btn-cetak-semua').on('click', function() {
        let printWindow = window.open('', '_blank', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Cetak Semua</title><style>body{font-family:sans-serif;padding:10px;text-align:center;}.item{display:inline-block;width:220px;margin:10px;padding:10px;border:1px dashed #ccc;}svg{width:100%;height:auto;}</style></head><body>');
        daftarBerkas.forEach(no => {
            let safeId = no.replace(/[^a-z0-9]/gi, '-');
            let content = document.getElementById('print-area-' + safeId).innerHTML;
            printWindow.document.write('<div class="item">' + content + '</div>');
        });
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        setTimeout(() => { printWindow.print(); printWindow.close(); }, 500);
    });
});
</script>
@endpush