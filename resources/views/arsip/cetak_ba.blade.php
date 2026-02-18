<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Berita Acara Pemusnahan - {{ $ba->no_berita_acara }}</title>
    <style>
        /* Base Styling Formal sesuai Surat Pengantar */
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

        .halaman-cetak {
            background: white;
            width: 210mm; 
            min-height: 297mm;
            padding: 15mm 20mm;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            font-size: 12pt; 
        }

        /* Kop Header Identik */
        .kop-header {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
        }

        .kop-logo { flex: 0 0 120px; text-align: left; }
        .kop-logo img { width: 110px; height: auto; }

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
            margin: 20px 0;
        }

        .doc-title {
            text-decoration: underline;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Data Tabel Rincian */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 10pt;
        }

        .data-table th, .data-table td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        .data-table th {
            background-color: #f2f2f2 !important;
            text-align: center;
            text-transform: uppercase;
            -webkit-print-color-adjust: exact;
        }

        .batch-info-list {
            margin: 15px 0 20px 40px;
            list-style: none;
        }

        .batch-info-list li {
            margin-bottom: 8px;
            display: flex;
        }

        .label-list {
            width: 200px;
            display: inline-block;
        }

        .table-ttd {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
            table-layout: fixed; 
        }

        .td-ttd {
            vertical-align: top;
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
            @page { size: portrait; margin: 0; }
            body { background: white; padding: 0; }
            .halaman-cetak { box-shadow: none; width: 100%; margin: 0; padding: 15mm 20mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="halaman-cetak">
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
                    Telepon (031)7315570 Faksmili (031)7329835<br>
                    Laman: tanjungperak.imigrasi.go.id, E-mail: kanim_tgperak@imigrasi.go.id
                </div>
            </div>
        </div>

        <div class="doc-title-area">
            <h3 class="doc-title">BERITA ACARA PEMUSNAHAN ARSIP</h3>
            <p style="font-weight: bold; margin-top: 5px;">Nomor: {{ $ba->no_berita_acara }}</p>
        </div>

        <div style="font-size: 12pt; text-align: justify; text-indent: 40px; margin-bottom: 20px;">
            Pada hari ini, <b>{{ \Carbon\Carbon::parse($ba->tgl_pemusnahan)->locale('id')->isoFormat('dddd') }}</b> 
            tanggal <b>{{ \Carbon\Carbon::parse($ba->tgl_pemusnahan)->locale('id')->isoFormat('D MMMM Y') }}</b>, 
            telah dilakukan pemusnahan dokumen arsip keimigrasian Kantor Imigrasi Kelas I TPI Tanjung Perak dengan rincian sebagai berikut:
        </div>

        <ul class="batch-info-list">
            <li><span class="label-list">Periode Dokumen</span>: <b>{{ \Carbon\Carbon::parse($ba->filter_mulai)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($ba->filter_selesai)->format('d/m/Y') }}</b></li>
            <li><span class="label-list">Jumlah Dokumen</span>: <b>{{ $ba->jumlah_dokumen }} Berkas</b></li>
            <li><span class="label-list">Metode Pemusnahan</span>: <b>Pencacahan / Pembakaran</b></li>
        </ul>

        <p style="font-weight: bold; margin-bottom: 10px;">Daftar Rincian Berkas:</p>
        <table class="data-table">
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>Nomor Permohonan</th>
                    <th>Nama Pemohon</th>
                    <th>Jenis Berkas</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($permohonan as $index => $p)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $p->no_permohonan }}</td>
                    <td>{{ $p->nama }}</td>
                    <td>{{ $p->jenis_permohonan }}</td>
                    <td style="text-align: center;">{{ \Carbon\Carbon::parse($p->tanggal_permohonan)->format('d/m/Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p style="font-size: 12pt; margin-top: 20px; text-indent: 40px;">Demikian Berita Acara ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p>

        <table class="table-ttd">
            <tr>
                <td class="td-ttd"></td>
                <td class="td-ttd" style="padding-bottom: 20px;">
                    Surabaya, {{ \Carbon\Carbon::parse($ba->tgl_pemusnahan)->locale('id')->isoFormat('D MMMM Y') }}
                </td>
            </tr>
            <tr>
                <td class="td-ttd">
                    <p>Saksi I,</p>
                    <p>Petugas Arsip/TIKIM</p>
                </td>
                <td class="td-ttd">
                    <p>Mengetahui,</p>
                    <p>Atasan Langsung</p>
                </td>
            </tr>
            <tr>
                <td style="height: 80px;"></td> 
                <td style="height: 80px;"></td>
            </tr>
            <tr>
                <td class="td-ttd" style="font-weight: bold;">
                    ( ............................................................ )
                </td>
                <td class="td-ttd" style="font-weight: bold;">
                    ( ............................................................ )
                </td>
            </tr>
            <tr>
                <td class="td-ttd" style="font-size: 11pt; padding-top: 5px;">
                    NIP. ............................................................
                </td>
                <td class="td-ttd" style="font-size: 11pt; padding-top: 5px;">
                    NIP. ............................................................
                </td>
            </tr>
        </table>
    </div>

    <div class="print-control no-print">
        <button class="btn-print-action" onclick="window.print()">
            DOWNLOAD BERITA ACARA
        </button>
    </div>

</body>
</html>