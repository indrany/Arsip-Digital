<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Pengantar - {{ $batch->no_pengirim }}</title>
    <style>
        /* Base Styling Formal */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: Arial, Helvetica, sans-serif; 
            padding: 20px; 
            background: #e0e0e0; 
            color: black;
            line-height: 1.5; 
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Lembar Kertas Formal */
        .paper-container {
            background: white;
            width: 210mm; 
            min-height: 297mm;
            padding: 15mm 20mm;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            margin-bottom: 80px;
            font-size: 12pt; 
        }

        .kop-header {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
        }

        .kop-logo {
            flex: 0 0 120px;
            text-align: left;
        }

        .kop-logo img {
            width: 110px;
            height: auto;
        }

        .kop-instansi {
            flex: 1;
            text-align: center;
            padding-right: 120px; 
        }

        .instansi-title {
            font-size: 10pt;
            text-transform: uppercase;
            line-height: 1.2;
            margin: 0;
            white-space: nowrap;
        }

        .instansi-sub {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 1px 0;
            white-space: nowrap;
        }

        .instansi-alamat {
            font-size: 10pt;
            line-height: 1.3;
        }

        .doc-title-area {
            text-align: center;
            margin: 30px 0;
        }

        .doc-title {
            text-decoration: underline;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Styling Row Horizontal ala Detail */
        .batch-detail-row {
            display: flex;
            justify-content: space-between;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0 40px 0;
        }

        .detail-item {
            text-align: center;
            flex: 1;
        }

        .detail-label {
            display: block;
            font-size: 9pt;
            color: #6c757d;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .detail-value {
            display: block;
            font-weight: bold;
            font-size: 11pt;
            color: #2d9cdb;
        }

        .signature-section {
            margin-top: 50px;
            width: 100%;
            display: flex;
            justify-content: space-between;
        }

        .sig-box {
            width: 40%;
            text-align: center;
            font-size: 12pt;
        }

        .print-control {
            position: fixed;
            bottom: 30px;
            z-index: 999;
        }

        .btn-print-action {
            background-color: #2d9cdb;
            color: white;
            padding: 12px 35px;
            border-radius: 30px;
            border: none;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        @media print {
            body { background: white; padding: 0; }
            .paper-container { box-shadow: none; width: 100%; margin: 0; padding: 10mm; }
            .no-print { display: none !important; }
            .batch-detail-row { border: 1px solid #000; background: transparent !important; -webkit-print-color-adjust: exact; }
            .detail-value { color: #000 !important; }
        }
    </style>
</head>
<body>

    <div class="paper-container">
        <div class="kop-header">
            <div class="kop-logo">
                <img src="{{ asset('images/v1_151.png') }}" alt="Logo">
            </div>
            <div class="kop-instansi">
                <div class="instansi-title">KEMENTERIAN IMIGRASI DAN PEMASYARAKATAN REPUBLIK INDONESIA</div>
                <div class="instansi-title">DIREKTORAT JENDERAL IMIGRASI</div>
                <div class="instansi-title">KANTOR WILAYAH JAWA TIMUR</div>
                <div class="instansi-sub">KANTOR IMIGRASI KELAS I TPI TANJUNG PERAK</div>
                <div class="instansi-alamat">
                    Jalan Darmo Indah No. 21 Tandes, Surabaya 60186<br>
                    Laman: tanjungperak.imigrasi.go.id, Pos-el: kanim_tgperak@imigrasi.go.id
                </div>
            </div>
        </div>

        <div class="doc-title-area">
            <h3 class="doc-title">SURAT PENGANTAR PENGIRIMAN BERKAS</h3>
            <p style="font-size: 12pt;">NOMOR : {{ $batch->no_pengirim }}</p>
        </div>
        
        <div style="font-size: 12pt; text-align: justify; text-indent: 40px; margin-bottom: 25px;">
            Pada hari ini, <b>{{ \Carbon\Carbon::parse($batch->tgl_pengirim)->locale('id')->isoFormat('dddd') }}</b> 
            tanggal <b>{{ \Carbon\Carbon::parse($batch->tgl_pengirim)->locale('id')->isoFormat('D MMMM Y') }}</b>, 
            Kami yang bertanda tangan di bawah ini menyatakan bahwa telah dilakukan pengiriman berkas dari 
            <b>{{ $batch->petugas_kirim ?? Auth::user()->name }}</b> (<b>{{ $batch->asal_unit ?? 'Kanim' }}</b>) 
            dengan rincian sebagai berikut:
        </div>

        <div class="batch-detail-row">
            <div class="detail-item">
                <span class="detail-label">No Pengirim</span>
                <span class="detail-value">{{ $batch->no_pengirim }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Tanggal Pengirim</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($batch->tgl_pengirim)->format('Y-m-d') }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Tanggal Diterima</span>
                <span class="detail-value">{{ $batch->tgl_diterima ?? '-' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Status</span>
                <span class="detail-value">{{ strtoupper(str_replace('_', ' ', $batch->status)) }}</span>
            </div>
        </div>

        <p style="font-size: 12pt; margin-bottom: 40px;">Demikian surat pengantar ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>

        <div class="signature-section">
            <div class="sig-box">
                <p>PIHAK PERTAMA,</p>
                <p>Petugas Pengirim</p>
                <div style="height: 80px;"></div>
                <p><b>( {{ $batch->petugas_kirim ?? Auth::user()->name }} )</b></p>
            </div>
            <div class="sig-box">
                <p>PIHAK KEDUA,</p>
                <p>Petugas Arsip Penerima</p>
                <div style="height: 80px;"></div>
                <p>( ........................................ )</p>
            </div>
        </div>
    </div>

    <div class="print-control no-print">
        <button class="btn-print-action" onclick="window.print()">
            CETAK DOKUMEN
        </button>
    </div>

</body>
</html>