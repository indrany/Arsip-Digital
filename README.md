# SAPA - Sistem Arsip Paspor Akuntabel

**SAPA (Sistem Arsip Paspor Akuntabel)** adalah inovasi sistem manajemen kearsipan berbasis web yang dikembangkan untuk **Kantor Imigrasi Kelas I TPI Tanjung Perak**. Sistem ini mentransformasi pengelolaan berkas fisik paspor dari metode konvensional menjadi digital, terintegrasi, dan terstruktur.

---

## 📌 Latar Belakang & Masalah

Setiap harinya Kantor Imigrasi Kelas I TPI Tanjung Perak mengelola ribuan dokumen negara berupa berkas permohonan paspor. Manajemen arsip konvensional menimbulkan beberapa kendala:
1. **Kesulitan Lokasi Penyimpanan:** Belum ada pemetaan ketersediaan kapasitas rak secara *real-time*.
2. **Risiko Ketidakteraturan Data:** Potensi selisih antara jumlah berkas yang diterima dengan yang tersimpan di rak.
3. **Lamanya Waktu Pencarian (*Retrieval Time*):** Proses pencarian kembali berkas fisik memakan waktu lama karena tidak adanya koordinat digital.
4. **Validasi Pemusnahan Rumit:** Kesulitan sinkronisasi antara berkas fisik dan data retensi arsip.

**SAPA** hadir sebagai solusi digitalisasi kearsipan yang dilengkapi dengan fitur **Otomasi Penempatan Berkas (*Auto-Slotting*)** dan **Pemindaian Barcode**.

---

## ✨ Fitur Utama

- 📊 **Dashboard Monitoring:** Menampilkan statistik total data pemohon, berkas dipinjam, peringatan kapasitas rak, serta grafik aktivitas bulanan.
- 👥 **Manajemen User & Hak Akses (RBAC):** Pembagian kewenangan berdasarkan unit kerja (`ADMIN`, `TIKIM`, `LANTASKIM`, `INTELDAKIM`, `INTALTUSKIM`).
- 🗄️ **Master Rak Loker & Auto-Slotting:** Pengelolaan kapasitas lemari/rak secara digital. Sistem secara otomatis menentukan koordinat lokasi berkas (*Lemari / Rak / No. Urut*).
- 📦 **Pengiriman & Penerimaan Berkas (Batching & Barcode Scan):** Fitur pengiriman batch dari unit kerja ke seksi Arsip (TIKIM), cetak barcode, serta verifikasi pemindaian fisik.
- 📑 **Cetak Surat Pengantar & Berita Acara:** Otomasi pembuatan dokumen resmi pengiriman, peminjaman, dan pengembalian arsip.
- 🔍 **Pencarian Berkas & Detail Pemohon:** Pencarian cepat berdasarkan nomor permohonan atau nama, lengkap dengan penyaringan rentang tanggal.
- 🔄 **Manajemen Peminjaman Berkas:** Modul pelacakan sirkulasi peminjaman berkas antar divisi lengkap dengan notifikasi ketersediaan berkas.
- 🗑️ **Pemusnahan Arsip Berkontrol:** Prosedur pengajuan pemusnahan berkas retensi oleh TIKIM dan verifikasi/persetujuan oleh ADMIN, lengkap dengan pengunggahan pindaian Berita Acara (PDF).

---

## 👥 Hak Akses / Role Management

| Role | Dashboard | Master Rak | Pengiriman Berkas | Penerimaan Berkas | Pencarian Berkas | Pinjam Berkas | Pemusnahan Arsip | Manajemen User |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| **ADMIN** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ (Approval) | ✅ |
| **TIKIM** | ✅ | ✅ | ❌ | ✅ | ✅ | ✅ | ✅ (Pengajuan) | ❌ |
| **LANTASKIM** | ✅ | ❌ | ✅ | ❌ | ❌ | ✅ | ❌ | ❌ |
| **INTELDAKIM** | ✅ | ❌ | ✅ | ❌ | ❌ | ✅ | ❌ | ❌ |
| **INTALTUSKIM** | ✅ | ❌ | ✅ | ❌ | ❌ | ✅ | ❌ | ❌ |

---

## 🏗️ Arsitektur & Teknologi

- **Framework:** Laravel 10.x
- **Bahasa Pemrograman:** PHP 8.1+
- **Database:** MySQL / MariaDB
- **Web Server:** Nginx / Apache (XAMPP / Ubuntu Server)
- **Frontend:** Blade Templating, HTML5, CSS3, JavaScript
- **Metodologi Pengembangan:** Waterfall (*Analysis, Design, Coding, Testing, Implementation*)

---

## 🛠️ Panduan Instalasi

1. **Clone Repositori**
   ```bash
   git clone [https://github.com/username/sapa-imigrasi.git](https://github.com/username/sapa-imigrasi.git)
   cd sapa-imigrasi