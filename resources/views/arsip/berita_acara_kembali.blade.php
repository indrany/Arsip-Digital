<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Pengembalian - {{ $data->permohonan->no_permohonan }}</title>
    <style>
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

        /* Kop Surat Sama Persis */
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

        /* Narasi Berita Acara */
        .content-area {
            text-align: justify;
            margin-top: 10px;
            line-height: 1.6;
        }

        .data-info-list {
            margin: 20px 0 20px 60px;
            list-style: none;
        }

        .data-info-list li {
            margin-bottom: 10px;
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
                <div class="instansi-sub">KANTOR IMIGRASI KELAS I TPI TANJUNG PERAK</div>
                <div class="instansi-alamat">
                    Jalan Darmo Indah No. 21 Tandes, Surabaya 60186<br>
                    Telepon(031)7315570 Faksimili(031)7329835<br>
                    Laman: tanjungperak.imigrasi.go.id; Email: kanim_tgperak@imigrasi.go.id
                </div>
            </div>
        </div>

        <div class="doc-title-area">
            <div class="doc-title">BERITA ACARA PENGEMBALIAN ARSIP</div>
            <div class="doc-number"><b>Nomor : {{ $no_kembali }}</b></div>
        </div>
        <div class="content-area">
            <p>Pada hari ini {{ \Carbon\Carbon::parse($data->tgl_kembali)->locale('id')->isoFormat('dddd') }} 
               tanggal {{ \Carbon\Carbon::parse($data->tgl_kembali)->format('d') }} 
               bulan {{ \Carbon\Carbon::parse($data->tgl_kembali)->locale('id')->isoFormat('MMMM') }} 
               tahun {{ \Carbon\Carbon::parse($data->tgl_kembali)->format('Y') }}, 
               yang bertanda tangan di bawah ini:</p>

            <table style="margin: 15px 0; border-collapse: collapse; font-size: 12pt; width: 100%;">
                <tr>
                    <td style="width: 180px; padding-bottom: 5px; vertical-align: top;">Nama</td>
                    <td style="width: 20px; padding-bottom: 5px; vertical-align: top;">:</td>
                    <td style="padding-bottom: 5px; vertical-align: top;">{{ $data->nama_personil }}</td>
                </tr>
                <tr>
                    <td style="padding-bottom: 5px; vertical-align: top;">Jabatan</td>
                    <td style="padding-bottom: 5px; vertical-align: top;">:</td>
                    <td style="padding-bottom: 5px; vertical-align: top;">{{ strtoupper($data->nama_peminjam) }}</td>
                </tr>
                <tr>
                    <td style="padding-bottom: 5px; vertical-align: top;">Nomor Permohonan</td>
                    <td style="padding-bottom: 5px; vertical-align: top;">:</td>
                    <td style="padding-bottom: 5px; vertical-align: top;">{{ $data->permohonan->no_permohonan }}</td>
                </tr>
            </table>

            <p>Dengan ini mengajukan pengembalian arsip dengan nomor permohonan tersebut. Demikian surat permohonan ini saya buat dengan sebenar - benarnya. Apabila dikemudian hari saya terbukti memberikan data tidak benar dan atau menyalahgunakan arsip tersebut maka saya siap bertanggung jawab secara pribadi dan di proses sesuai dengan hukum Undang - undang Keimigrasian Republik Indonesia. Atas perhatiannya saya mengucapkan terima kasih.</p>
        </div>
        <table class="table-ttd" style="width: 100%; margin-top: 50px; text-align:center">
            <tr>
                <td colspan="2" style="padding-bottom: 20px; font-size: 12pt;">
                    Mengetahui / Menyetujui:
                </td>
            </tr>
            <tr>
                <td class="td-ttd" style="width: 50%;">    
                <p>Peminjam</p>
                    <div style="height: 80px;"></div>
                    <p>( {{ $data->nama_personil }} )</p>
                </td>
                <td class="td-ttd" style="width: 50%;">
                    <p>Arsip</p>
                    <div style="height: 80px;"></div>
                    {{-- Sekarang namanya otomatis sesuai yang diinput saat +pinjam --}}
                    <p>( {{ $data->petugas_arsip ?? '....................................' }} )</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="print-control no-print">
        <button class="btn-print-action" onclick="window.print()">CETAK FORMULIR</button>
    </div>

</body>
</html>