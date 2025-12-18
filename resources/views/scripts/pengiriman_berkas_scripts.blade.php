{{-- KRUSIAL: Memuat jQuery dan JsBarcode di sini karena error '$ is not defined' muncul --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script> 

<script>
    $(document).ready(function() {

        // --- 1. INISIALISASI VARIABEL DOM DALAM READY FUNCTION ---
        const inputNomor = $('#nomor_pemohon');
        const barcodeCanvas = document.getElementById('barcode-canvas'); 
        const barcodeNumberSpan = $('#barcode-number');
        const btnPrintBarcode = $('#btn-print-barcode');
        const btnTambahPermohonan = $('#btn-tambah-permohonan');
        const btnSimpanPengiriman = $('#btn-simpan-pengiriman');
        const barcodeArea = $('#barcode-area'); 
        
        let listBerkas = []; 
        let currentPermohonanData = null; 

        // Set CSRF Token untuk semua AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Mencegah Submit Form Default
        $('#form-permohonan').on('submit', function(e) {
            e.preventDefault(); 
            return false;      
        });
        
        // --- FUNGSI UTILITY: GENERATE BARCODE ---
        function generateBarcode(nomor_pengiriman) {
            if (typeof JsBarcode === 'undefined' || !barcodeCanvas) {
                console.error("KRUSIAL: Persiapan Barcode gagal.");
                return;
            }
            
            try {
                // Bersihkan canvas sebelum menggambar yang baru
                const context = barcodeCanvas.getContext('2d');
                if (context) {
                    context.clearRect(0, 0, barcodeCanvas.width, barcodeCanvas.height);
                }
                
                barcodeNumberSpan.text(nomor_pengiriman.toUpperCase());

                JsBarcode(barcodeCanvas, nomor_pengiriman, {
                    format: "CODE128", 
                    displayValue: false, 
                    width: 2,
                    height: 50,
                    margin: 0
                });
                
                // Tampilkan elemen
                barcodeArea.css('display', 'block'); 
                $(barcodeCanvas).css('display', 'block'); 
                
            } catch (error) {
                console.error("Gagal membuat barcode:", error);
            }
        }

        // --- FUNGSI UTILITY: PRINT BARCODE (REVISI FINAL) ---
        function printBarcode() {
            // 1. Ambil Data Gambar Barcode dari Canvas
            if (!barcodeCanvas || barcodeCanvas.toDataURL() === 'data:,' || listBerkas.length === 0) {
                 alert('Gagal mencetak: Barcode tidak ditemukan atau daftar kosong.');
                 return;
            }
            // Ubah canvas menjadi data URL (PNG)
            const barcodeImageURL = barcodeCanvas.toDataURL("image/png");
            const barcodeNumber = barcodeNumberSpan.text();
            
            // 2. Siapkan Konten Cetak (Menggunakan IMG tag)
            const printContent = `
                <div id="print-area-content" style="text-align: left; margin: 10px;">
                    <h6 style="margin: 0 0 5px 0; font-size: 10px; font-weight: normal; color: #000;">Barcode Berkas Pengiriman</h6>
                    <img src="${barcodeImageURL}" style="width: 100%; max-width: 250px; height: auto; display: block; margin-bottom: 5px;">
                    <span style="display: block; font-weight: bold; font-size: 14px;">${barcodeNumber}</span>
                </div>
            `;
            
            // 3. Buka Jendela Cetak dan Tulis Konten
            const printWindow = window.open('', '_blank', 'height=300,width=400');
            
            printWindow.document.write('<html><head><title>Cetak Barcode</title>');
            
            // Menyuntikkan CSS untuk tampilan cetak minimalis
            printWindow.document.write(`
                <style>
                    body { 
                        font-family: Arial, sans-serif; 
                        margin: 0; 
                        padding: 0; 
                        background-color: #fff;
                    } 
                    @media print {
                        body {
                            margin: 5px; 
                        }
                    }
                </style>
            `);
            
            printWindow.document.write('</head><body>');
            printWindow.document.write(printContent);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            
            // 4. Panggil Cetak dengan penundaan
            setTimeout(function () {
                printWindow.print();
                printWindow.close();
            }, 300); 
        }

        
        // --- FUNGSI UTILITY: UPDATE TABEL (RENDER) ---
        function renderTable() {
            const tbody = $('#permohonan-list'); 
            tbody.empty();

            if (listBerkas.length === 0) {
                // Kosongkan tampilan jika list kosong
                tbody.append(`
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada permohonan yang ditambahkan.</td>
                    </tr>
                `);
                
                barcodeArea.css('display', 'none'); 
                barcodeNumberSpan.text('');
            } else {
                
                // Generate barcode untuk nomor permohonan pertama (nomor pengiriman master)
                generateBarcode(listBerkas[0].no_permohonan);

                listBerkas.forEach((data, index) => {
                    tbody.append(`
                        <tr>
                            <td>${data.no_permohonan}</td>
                            <td>${data.tanggal_permohonan}</td>
                            <td>${data.nama}</td>
                            <td>${data.tempat_lahir || '-'}</td> 
                            <td>${data.tanggal_lahir || '-'}</td> 
                            <td>
                                <button type="button" class="btn btn-sm btn-danger btn-hapus" data-index="${index}">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </td>
                        </tr>
                    `);
                });
            }
        }

        // --- Fungsi Utama: PENCARIAN & TAMBAH PERMOHONAN (Handler AJAX) ---
        function handleCariDanTambah() {
            const nomor_permohonan = inputNomor.val().trim(); 
            
            if (!nomor_permohonan) {
                alert('Nomor Permohonan harus diisi!');
                return;
            }

            btnTambahPermohonan.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Mencari...');
            
            // Cek duplikasi sebelum AJAX
            const isDuplicate = listBerkas.some(b => b.no_permohonan === nomor_permohonan);
            if (isDuplicate) {
                alert('Berkas dengan nomor permohonan ini sudah ada dalam daftar!');
                renderTable(); // Render ulang untuk memastikan barcode tampil (jika ada item lain)
                inputNomor.val('');
                btnTambahPermohonan.prop('disabled', false).html('<i class="fas fa-plus"></i> Tambah');
                return;
            }

            // AJAX Call
            $.ajax({
                url: "{{ route('cari-permohonan') }}", // Ganti dengan route yang benar
                type: "POST",
                data: {
                    nomor_permohonan: nomor_permohonan
                },
                success: function(response) {
                    currentPermohonanData = response.data; 
                    
                    // KRUSIAL: Memastikan data yang di-push memiliki 5 kolom data
                    listBerkas.push({
                        no_permohonan: currentPermohonanData.no_permohonan,
                        tanggal_permohonan: currentPermohonanData.tanggal_permohonan,
                        nama: currentPermohonanData.nama,
                        tempat_lahir: currentPermohonanData.tempat_lahir,
                        tanggal_lahir: currentPermohonanData.tanggal_lahir,
                        // Tambahkan kolom lain yang diperlukan oleh backend di sini
                    });
                    
                    renderTable(); // Memanggil renderTable akan otomatis memanggil generateBarcode
                    inputNomor.val('');
                    currentPermohonanData = null; 

                    $('#status-alert').html('<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i> Data pemohon berhasil ditambahkan ke daftar.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    setTimeout(() => $('#status-alert').empty(), 3000);
                },
                error: function(xhr) {
                    const errorMessage = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan saat mencari data.';
                    alert(errorMessage);
                    
                    if(listBerkas.length === 0) {
                        barcodeArea.css('display', 'none'); 
                    }
                },
                complete: function() {
                    btnTambahPermohonan.prop('disabled', false).html('<i class="fas fa-plus"></i> Tambah');
                }
            });
        }
        
        // --- EVENT LISTENERS ---
        inputNomor.on('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); 
                handleCariDanTambah();
            }
        });

        btnTambahPermohonan.on('click', handleCariDanTambah);
        
        // Event listener untuk Print Barcode
        btnPrintBarcode.on('click', printBarcode);
        
        // Event listener Hapus
        $('#permohonan-list').on('click', '.btn-hapus', function() {
            const index = $(this).data('index');
            listBerkas.splice(index, 1); 
            renderTable();
        });

        // Event listener Simpan
        btnSimpanPengiriman.on('click', function() {
            if (listBerkas.length === 0) {
                alert('Tidak ada berkas yang akan dikirim.');
                return;
            }
            
            // Implementasikan logika AJAX Simpan Pengiriman di sini:
            /*
            $.ajax({
                url: "{{ route('pengiriman-berkas.store') }}",
                type: 'POST',
                data: {
                    // Kirim array nomor permohonan ke backend
                    nomor_permohonan_list: listBerkas.map(b => b.no_permohonan)
                },
                success: function(response) {
                    alert('Pengiriman berhasil disimpan!');
                    // Redirect atau reset form
                },
                error: function() {
                    alert('Gagal menyimpan pengiriman.');
                }
            });
            */
            
            // Contoh alert untuk demo:
            alert('Fungsi Simpan Berkas berjalan. Mengirim ' + listBerkas.length + ' berkas.');
        });
        
        // Inisialisasi awal
        renderTable(); 
    });
</script>