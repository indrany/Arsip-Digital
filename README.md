# SAPA - Sistem Arsip Paspor Akuntabel

[![Laravel Version](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Database](https://img.shields.io/badge/Database-MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)

**SAPA (Sistem Arsip Paspor Akuntabel)** adalah inovasi sistem manajemen kearsipan berbasis web yang dikembangkan untuk **Kantor Imigrasi Kelas I TPI Tanjung Perak**. Sistem ini mentransformasi pengelolaan berkas fisik paspor dari metode konvensional menjadi digital, terintegrasi, dan terstruktur.

---

## 🚀 Fitur-Fitur Utama

### 📊 1. Dashboard Monitoring & Statistik
* **Statistik Real-time:** Menampilkan total berkas terdaftar, berkas yang sedang dipinjam, serta kuota rak penyimpan.
* **Peringatan Kapasitas Rak (*Capacity Warning*):** Notifikasi otomatis saat kapasitas lemari/rak fisik hampir penuh.
* **Grafik Aktivitas:** Visualisasi grafik bulanan untuk sirkulasi berkas masuk, dipinjam, dan diselesaikan.

### 🗄️ 2. Master Rak & Otomasi Penempatan Berkas (*Auto-Slotting*)
* **Manajemen Lokasi Digital:** Pengelolaan data lemari, nomor rak, dan kapasitas per loker.
* **Penomoran Koordinat Otomatis:** Sistem secara otomatis menentukan koordinat lokasi simpan fisik berkas (*Lemari / Rak / No. Urut*) saat berkas diverifikasi penerimaannya.

### 📦 3. Pengiriman & Penerimaan Berkas (Batching & Barcode)
* **Pengiriman Berkas Batch:** Unit kerja (seperti LANTASKIM/INTELDAKIM) dapat mengelompokkan berkas permohonan ke dalam batch pengiriman ke Seksi TIKIM.
* **Verifikasi Pemindaian Barcode:** Seksi TIKIM melakukan validasi penerimaan berkas fisik secara akurat menggunakan *barcode scanner*.
* **Otomasi Dokumen Resmi:** Fitur cetak Surat Pengantar Pengiriman dan Berita Acara Penerimaan secara langsung.

### 🔍 4. Pencarian Berkas & Pelacakan Pemohon
* **Pencarian Cepat:** Pencarian berkas secara efisien berdasarkan Nomor Permohonan Paspor, Nama Pemohon, atau rentang tanggal.
* **Detail Riwayat Berkas:** Menampilkan riwayat pergerakan berkas, lokasi koordinat fisik rak, serta status ketersediaan (*Tersimpan / Dipinjam / Dimusnahkan*).

### 🔄 5. Manajemen Sirkulasi Peminjaman Berkas
* **Pengajuan & Persetujuan Pinjam:** Alur peminjaman berkas antar divisi/seksi dengan kontrol persetujuan.
* **Pelacakan Posisi Berkas:** Memantau berkas yang sedang keluar, nama peminjam, tanggal pinjam, dan estimasi waktu pengembalian.
* **Berita Acara Peminjaman:** Cetak dokumen Berita Acara Peminjaman dan Pengembalian Berkas fisik secara otomatis.

### 🗑️ 6. Pengelolaan Pemusnahan Arsip Berkontrol
* **Filter Retensi Arsip:** Identifikasi berkas yang telah melewati masa simpan/retensi.
* **Verifikasi Berjenjang:** Pengajuan pemusnahan oleh TIKIM dan persetujuan (*approval*) oleh ADMIN.
* **Arsip Digital BA Pemusnahan:** Fitur unggah dan simpan berkas pindaian (PDF) Berita Acara Pemusnahan untuk transparansi audit.

### 👥 7. Hak Akses Berbasis Peran (*Role-Based Access Control / RBAC*)
Pembagian kewenangan dan fitur berdasarkan unit kerja internal:
* **ADMIN:** Akses penuh seluruh sistem, manajemen pengguna, dan *approval* pemusnahan.
* **TIKIM (Arsip):** Kelola rak, penerimaan berkas, peminjaman, dan pengajuan pemusnahan.
* **UNIT KERJA (LANTASKIM, INTELDAKIM, INTALTUSKIM):** Pengiriman berkas permohonan ke TIKIM dan pengajuan peminjaman berkas.

---

## 👥 Matriks Hak Akses

| Role / Unit | Dashboard | Master Rak | Pengiriman | Penerimaan | Pencarian | Pinjam Berkas | Pemusnahan | User Mgmt |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| **ADMIN** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ (Approval) | ✅ |
| **TIKIM** | ✅ | ✅ | ❌ | ✅ | ✅ | ✅ | ✅ (Pengajuan) | ❌ |
| **LANTASKIM** | ✅ | ❌ | ✅ | ❌ | ❌ | ✅ | ❌ | ❌ |
| **INTELDAKIM** | ✅ | ❌ | ✅ | ❌ | ❌ | ✅ | ❌ | ❌ |
| **INTALTUSKIM** | ✅ | ❌ | ✅ | ❌ | ❌ | ✅ | ❌ | ❌ |

---

## 📋 Tata Cara Penggunaan (Alur Kerja Operasional)

### 1. Inisialisasi Master Rak (Awal Penggunaan)
1. Login sebagai **ADMIN** atau **TIKIM**.
2. Masuk ke menu **Master Rak Loker**.
3. Tambahkan data Lemari dan Rak fisik yang tersedia beserta kapasitas maksimum lokernya.

### 2. Alur Pengiriman Berkas dari Unit Kerja
1. Login sebagai akun Unit Kerja (misal: **LANTASKIM**).
2. Masuk ke menu **Pengiriman Berkas**.
3. Buat **Batch Pengiriman Baru**, lalu input data pemohon atau impor berkas permohonan paspor yang akan diserahkan ke Seksi TIKIM.
4. Klik **Kirim Batch** dan cetak **Surat Pengantar Pengiriman**.

### 3. Alur Penerimaan Berkas & Auto-Slotting (Seksi TIKIM)
1. Login sebagai **TIKIM**.
2. Masuk ke menu **Penerimaan Berkas**.
3. Pilih Batch Pengiriman yang diterima, lalu lakukan pemindaian barcode fisik menggunakan *Barcode Scanner* untuk verifikasi.
4. Sistem secara otomatis menjalankan **Auto-Slotting** untuk memberikan koordinat lokasi penyimpan (*Contoh: Lemari A - Rak 2 - Slot 15*).
5. Cetak **Berita Acara Penerimaan** sebagai bukti penyerahan berkas fisik.

### 4. Alur Peminjaman & Pengembalian Berkas
1. **Pengajuan:** Unit Kerja mengajukan peminjaman berkas melalui menu **Pinjam Berkas** dengan memasukkan Nomor Permohonan / Nama Pemohon beserta alasan peminjaman.
2. **Persetujuan & Penyerahan:** Petugas **TIKIM** menyetujui pengajuan, mengambil berkas fisik sesuai koordinat rak, lalu menyerahkan berkas dan mencetak **Berita Acara Peminjaman**.
3. **Pengembalian:** Setelah selesai dipinjam, Petugas TIKIM memproses pengembalian berkas pada sistem untuk mengembalikan status berkas menjadi *Tersimpan*.

### 5. Alur Pemusnahan Berkas Retensi
1. Login sebagai **TIKIM**, masuk ke menu **Pemusnahan Arsip**.
2. Sistem secara otomatis memfilter berkas yang telah memasuki masa retensi akhir.
3. Petugas TIKIM membuat **Pengajuan Pemusnahan**.
4. **ADMIN** meninjau daftar berkas dan memberikan persetujuan (**Approval**).
5. Setelah fisik berkas dimusnahkan, unggah berkas pindaian **Berita Acara Pemusnahan (PDF)** sebagai rekam jejak digital.

---

## 🛠️ Instalasi & Penggunaan

1. **Clone Repositori**
```bash
git clone https://github.com/indrany/Arsip-Digital.git
cd Arsip-Digital
```

2. **Install Dependensi Composer**
```bash
composer install
```

3. **Konfigurasi Lingkungan (.env)**
Salin berkas `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Atur koneksi database pada berkas `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sapa_imigrasi
DB_USERNAME=root
DB_PASSWORD=
```

4. **Generate Application Key**
```bash
php artisan key:generate
```

5. **Jalankan Migrasi Database & Seeder**
```bash
php artisan migrate --seed
```

6. **Jalankan Server Lokal**
```bash
php artisan serve
```
Akses aplikasi melalui peramban di `http://localhost:8000`.

---

## 📜 Lisensi

Proyek ini dikembangkan untuk lingkungan internal **Kantor Imigrasi Kelas I TPI Tanjung Perak**. Hak cipta dilindungi undang-undang.
