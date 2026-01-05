@extends('layouts.app')

@section('title', 'Tambah Pengiriman Berkas')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="card shadow-sm border-0 rounded-3">
        {{-- Header Kartu --}}
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <div>
                <h5 class="mb-0 fw-bold text-primary">Tambah Pengiriman Berkas</h5>
                <small class="text-muted">Pengirim: <strong>{{ Auth::user()->name }}</strong> ({{ Auth::user()->unit_kerja ?? 'Kanim' }})</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('pengiriman-berkas.index') }}" class="btn btn-light btn-sm border">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Riwayat
                </a>
                <button type="button" class="btn btn-light btn-sm border" id="btn-cetak-semua" disabled>
                    <i class="fas fa-print me-1"></i> Cetak Semua Barcode
                </button>
            </div>
        </div>

        <div class="card-body p-4">
            {{-- Input Area --}}
            <div class="row g-3 mb-4">
                <div class="col-md-5">
                    <label class="small text-muted mb-1">Nomor Permohonan</label>
                    <input type="text" id="input_no_permohonan" class="form-control" placeholder="Scan atau Ketik Nomor Permohonan" autofocus>
                </div>
                <div class="col-md-5">
                    <label class="small text-muted mb-1">Nama Pemohon</label>
                    <input type="text" id="input_nama" class="form-control" placeholder="Nama Pemohon">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-primary w-100 fw-bold" id="btn-tambah-langsung" style="height: 38px;">
                        <i class="fas fa-plus me-1"></i> Tambah
                    </button>
                </div>
            </div>

            {{-- Tabel Pengiriman --}}
            <div class="table-responsive border rounded">
                <table class="table table-bordered align-middle mb-0" id="tabel-pengiriman">
                    <thead class="bg-light">
                        <tr class="text-dark">
                            <th class="fw-bold" style="width: 25%;">No. Permohonan</th>
                            <th class="fw-bold" style="width: 35%;">Nama</th>
                            <th class="fw-bold text-center" style="width: 25%;">Barcode</th>
                            <th class="fw-bold text-center" style="width: 15%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        {{-- Data muncul di sini via JavaScript --}}
                    </tbody>
                </table>
            </div>

            {{-- Tombol Simpan --}}
            <div class="text-end mt-4">
                <button type="button" class="btn btn-success px-5 py-2 fw-bold shadow-sm" id="btn-simpan-pengiriman" style="background-color: #10b981; border: none; border-radius: 8px;">
                    <i class="fas fa-paper-plane me-2"></i> Kirim ke Arsip
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .table thead th { border-bottom: 1px solid #dee2e6; font-size: 14px; padding: 12px; }
    .table td { font-size: 14px; padding: 12px !important; }
    .btn-action-icon { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px; border: none; color: white; cursor: pointer; }
    .btn-print-row { background-color: #3498db; }
    .btn-delete-row { background-color: #e74c3c; }
    .barcode-container { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 60px; }
    .barcode-container svg { width: 100%; height: 40px; }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
$(document).ready(function() {
    let daftarBerkas = [];

    function updateCetakButton() {
        $('#btn-cetak-semua').prop('disabled', daftarBerkas.length === 0);
    }

    function tambahKeTabel(no, nama) {
        if (daftarBerkas.includes(no)) {
            alert("Nomor sudah ada di daftar!");
            return;
        }

        daftarBerkas.push(no);
        let safeId = no.replace(/[^a-z0-9]/gi, '-');
        
        let html = `
            <tr id="row-${safeId}">
                <td class="fw-bold text-primary">${no}</td>
                <td class="text-dark">${nama}</td>
                <td class="text-center">
                    <div id="print-area-${safeId}" class="barcode-container">
                        <svg id="barcode-${safeId}"></svg>
                    </div>
                </td>
                <td class="text-center">
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn-action-icon btn-print-row" title="Cetak Barcode" onclick="printBarcode('${safeId}')">
                            <i class="fas fa-print"></i>
                        </button>
                        <button type="button" class="btn-action-icon btn-delete-row" title="Hapus" onclick="hapusBaris('${no}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        
        $('#tabel-pengiriman tbody').append(html);
        
        JsBarcode("#barcode-" + safeId, no, {
            format: "CODE128",
            width: 1.5,
            height: 35,
            displayValue: true,
            fontSize: 10,
            margin: 5
        });

        updateCetakButton();
        $('#input_no_permohonan').val('').focus();
        $('#input_nama').val('');
    }

    $('#btn-tambah-langsung').on('click', function() {
        let no = $('#input_no_permohonan').val().trim();
        let nama = $('#input_nama').val().trim();
        if (no && nama) tambahKeTabel(no, nama);
        else alert("Harap isi nomor dan nama!");
    });

    $('#input_no_permohonan').on('keypress', function(e) { if (e.which === 13) $('#input_nama').focus(); });
    $('#input_nama').on('keypress', function(e) { if (e.which === 13) $('#btn-tambah-langsung').click(); });

    window.hapusBaris = function(no) {
        let safeId = no.replace(/[^a-z0-9]/gi, '-');
        $(`#row-${safeId}`).remove();
        daftarBerkas = daftarBerkas.filter(item => item !== no);
        updateCetakButton();
    };

    window.printBarcode = function(safeId) {
        let content = document.getElementById('print-area-' + safeId).innerHTML;
        let printWindow = window.open('', '_blank', 'height=300,width=500');
        printWindow.document.write('<html><head><title>Print Label</title><style>body{margin:0;display:flex;justify-content:center;align-items:center;height:100vh;}svg{width:250px;}</style></head><body>' + content + '</body></html>');
        printWindow.document.close();
        setTimeout(() => { printWindow.print(); printWindow.close(); }, 500);
    };

    $('#btn-cetak-semua').on('click', function() {
        let printWindow = window.open('', '_blank', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Cetak Semua</title><style>body{font-family:sans-serif;display:grid;grid-template-columns:repeat(3,1fr);gap:15px;padding:20px;}.item{border:1px solid #ddd;padding:10px;text-align:center;}svg{width:150px;}</style></head><body>');
        daftarBerkas.forEach(no => {
            let safeId = no.replace(/[^a-z0-9]/gi, '-');
            let content = document.getElementById('print-area-' + safeId).innerHTML;
            printWindow.document.write('<div class="item">' + content + '</div>');
        });
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        setTimeout(() => { printWindow.print(); printWindow.close(); }, 500);
    });

    // PROSES SIMPAN: Mengirim data ke ArsipController@store
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

        $.ajax({
            url: "{{ route('pengiriman-berkas.store') }}",
            method: "POST",
            data: { 
                _token: "{{ csrf_token() }}", 
                nomor_permohonan_list: dataList 
            },
            success: function(res) { 
                if(res.success) {
                    alert("Berkas berhasil diajukan ke Arsip!");
                    window.location.href = "{{ route('pengiriman-berkas.index') }}"; 
                }
            },
            error: function(xhr) { 
                alert("Gagal menyimpan: " + (xhr.responseJSON ? xhr.responseJSON.message : "Terjadi kesalahan")); 
            }
        });
    });
});
</script>
@endpush