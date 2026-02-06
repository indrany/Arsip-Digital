<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Tanda Terima - {{ $data->permohonan->no_permohonan }}</title>
    <style>
        /* Base Styling Formal disamakan dengan Surat Pengantar */
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

        /* Kop Surat Identik dengan Surat Pengantar */
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
            margin: 30px 0;
        }

        .doc-title {
            text-decoration: underline;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Detail Data Styling */
        .data-info-list {
            margin: 20px 0 40px 60px;
            list-style: none;
        }

        .data-info-list li {
            margin-bottom: 12px;
            display: flex;
        }

        .label-list {
            width: 180px;
            display: inline-block;
        }

        /* Styling Tanda Tangan */
        .table-ttd {
            width: 100%;
            margin-top: 60px;
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
            @page { size: A4 portrait; margin: 0; }
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
                    Laman: tanjungperak.imigrasi.go.id, Pos-el: kanim_tgperak@imigrasi.go.id
                </div>
            </div>
        </div>

        <div class="doc-title-area">
            <h3 class="doc-title">SURAT TANDA TERIMA PEMINJAMAN BERKAS</h3>
        </div>

        <div style="font-size: 12pt; text-align: justify; text-indent: 40px; margin-bottom: 25px;">
            Telah diserahkan berkas fisik permohonan dari Petugas Arsip kepada peminjam dengan rincian detail sebagai berikut:
        </div>

        <ul class="data-info-list">
            <li><span class="label-list">Nomor Permohonan</span>: <b>{{ $data->permohonan->no_permohonan }}</b></li>
            <li><span class="label-list">Nama Pemilik Berkas</span>: <b>{{ strtoupper($data->permohonan->nama) }}</b></li>
            <li><span class="label-list">Nama Peminjam</span>: <b>{{ strtoupper($data->nama_personil) }}</b></li>
            <li><span class="label-list">Unit Kerja Peminjam</span>: <b>{{ strtoupper($data->nama_peminjam) }}</b></li>
            <li><span class="label-list">Tanggal Peminjaman</span>: <b>{{ \Carbon\Carbon::parse($data->tgl_pinjam)->locale('id')->isoFormat('D MMMM Y') }}</b></li>
        </ul>

        <p style="font-size: 12pt; margin-bottom: 40px; text-indent: 40px;">
            Demikian surat tanda terima ini dibuat sebagai bukti penyerahan berkas fisik peminjaman yang sah untuk dipergunakan sebagaimana mestinya.
        </p>

        <table class="table-ttd">
            <tr>
                <td class="td-ttd"></td>
                <td class="td-ttd" style="padding-bottom: 20px;">
                    Surabaya, {{ \Carbon\Carbon::parse($data->tgl_pinjam)->locale('id')->isoFormat('D MMMM Y') }}
                </td>
            </tr>
            <tr>
                <td class="td-ttd">
                    <p>Pihak Pertama,</p>
                    <p>Penerima / Peminjam</p>
                </td>
                <td class="td-ttd">
                    <p>Pihak Kedua,</p>
                    <p>Petugas Arsip</p>
                </td>
            </tr>
            <tr>
                <td style="height: 90px;"></td> 
                <td style="height: 90px;"></td>
            </tr>
            <tr>
                <td class="td-ttd" style="font-weight: bold;">
                    ( {{ strtoupper($data->nama_personil) }} )
                </td>
                <td class="td-ttd" style="font-weight: bold;">
                    ( {{ strtoupper(auth()->user()->nama_lengkap ?? auth()->user()->name) }} )
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
            CETAK DOKUMEN
        </button>
    </div>

</body>
</html>