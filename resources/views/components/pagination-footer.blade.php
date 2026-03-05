{{-- resources/views/components/pagination-footer.blade.php --}}

@if(isset($data))
    {{-- BAGIAN 1: UNTUK TABEL UTAMA (PHP / LARAVEL DRIVEN) --}}
    <div class="card-footer bg-white border-top-0 py-4 position-relative">
        <div class="d-flex flex-column align-items-center">
            
            {{-- 1. Navigasi Angka & Panah Samping (Lengkap) --}}
            <div class="custom-main-pagination w-100">
                {{ $data->appends(request()->query())->links() }}
            </div>

        </div>

        {{-- DROPDOWN SHOW --}}
        <div class="position-absolute end-0 bottom-0 mb-4 me-4 d-none d-md-block">
            <div class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0">Show:</label>
                <select class="form-select form-select-sm border-0 bg-light fw-bold" 
                        style="width: 80px; border-radius: 8px; cursor: pointer; height: 35px;" 
                        onchange="let url = new URL(window.location.href); url.searchParams.set('per_page', this.value); url.searchParams.set('page', 1); window.location.href = url.href;">
                    @foreach([10, 25, 50, 100] as $val)
                        <option value="{{ $val }}" {{ request('per_page') == $val ? 'selected' : '' }}>{{ $val }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
@else
    {{-- BAGIAN 2: UNTUK MODAL DETAIL (JS / AJAX DRIVEN) --}}
    <div class="d-flex flex-column align-items-center mt-4 gap-2 pb-4">
        {{-- Angka & Panah Samping --}}
        <div id="det_pagination_links" class="custom-main-pagination"></div>
        {{-- Teks Info Showing --}}
        <div id="det_pagination_info" class="text-muted text-uppercase" style="font-size: 10px; letter-spacing: 1px;"></div>
    </div>
@endif

<style>
    /* 1. CSS Global: Center */
    .custom-main-pagination nav {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        width: 100% !important;
    }

    .custom-main-pagination .pagination { 
        display: flex !important; 
        justify-content: center !important; 
        gap: 6px !important; 
        list-style: none !important; 
        padding: 0 !important; 
        margin: 0 0 10px 0 !important;
    }
    
    /* 2. PENGHAPUSAN TOMBOL TEKS (Previous / Next) */
    /* Ini untuk menghapus tombol Previous/Next yang ada tulisannya di Laravel */
    .custom-main-pagination nav div.flex.justify-between.flex-1.sm\:hidden,
    .custom-main-pagination nav div.hidden.sm\:flex-1.sm\:flex.sm\:items-center.sm\:justify-between > div:first-child {
        display: none !important;
    }

    /* Membalik urutan agar teks info Showing berada di bawah tombol angka */
    .custom-main-pagination nav > div:last-child {
        display: flex !important;
        flex-direction: column-reverse !important;
        align-items: center !important;
        width: 100% !important;
        gap: 10px;
    }

    /* Merapikan Teks Info "Showing..." di bawah */
    .custom-main-pagination nav p.text-sm.text-gray-700 {
        display: block !important;
        margin: 0 !important;
        font-size: 10px !important;
        text-transform: uppercase !important;
        color: #6c757d !important;
        letter-spacing: 1px !important;
    }

    /* 3. Desain Tombol Kotak (Panah samping angka tetap muncul di sini) */
    .custom-main-pagination .page-link,
    .custom-main-pagination nav span.relative.z-0 a,
    .custom-main-pagination nav span.relative.z-0 span,
    .custom-main-pagination nav a,
    .custom-main-pagination nav span {
        border-radius: 8px !important;
        border: 1px solid #e2e8e0 !important;
        color: #64748b !important;
        padding: 0 !important;
        font-weight: 700 !important;
        font-size: 13px !important;
        transition: all 0.2s;
        text-decoration: none !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        background-color: white !important;
        min-width: 38px;
        height: 38px;
        cursor: pointer;
    }

    /* Biru Aktif */
    .custom-main-pagination .page-item.active .page-link,
    .custom-main-pagination nav span[aria-current="page"] > span {
        background-color: #3b82f6 !important;
        border-color: #2563eb !important;
        color: white !important;
        box-shadow: 0 4px 10px -2px rgba(59, 130, 246, 0.4) !important;
        cursor: default;
    }
</style>

<script>
    /**
     * FUNGSI JS UNTUK MODAL
     * Perbaikan: Menambah parameter start, end, dan total agar teks info akurat
     */
    function renderJSNav(containerId, currentPage, totalPages, infoId, start, end, total) {
        const container = document.getElementById(containerId);
        const info = document.getElementById(infoId);
        if(!container || !info) return;

        let html = '<ul class="pagination pagination-sm mb-0">';
        
        // 1. Panah Kiri
        let prevDisabled = (currentPage === 1) ? 'disabled' : '';
        let prevAction = (currentPage === 1) ? '' : `onclick="changeDetailPage(${currentPage - 1})"`;
        html += `<li class="page-item ${prevDisabled}"><a class="page-link" ${prevAction}><i class="fas fa-chevron-left"></i></a></li>`;

        // 2. Render Angka
        if (totalPages <= 1) {
            html += `<li class="page-item active"><span class="page-link">1</span></li>`;
        } else {
            for (let i = 1; i <= totalPages; i++) {
                let activeClass = (i === currentPage) ? 'active' : '';
                let clickAction = (i === currentPage) ? '' : `onclick="changeDetailPage(${i})"`;
                html += `<li class="page-item ${activeClass}"><a class="page-link" ${clickAction}>${i}</a></li>`;
            }
        }

        // 3. Panah Kanan
        let nextDisabled = (currentPage === totalPages || totalPages === 0) ? 'disabled' : '';
        let nextAction = (currentPage === totalPages || totalPages === 0) ? '' : `onclick="changeDetailPage(${currentPage + 1})"`;
        html += `<li class="page-item ${nextDisabled}"><a class="page-link" ${nextAction}><i class="fas fa-chevron-right"></i></a></li>`;

        html += '</ul>';

        container.innerHTML = html;

        // 4. REVISI: Teks Info (RESULT 1 - 10 OF 12)
        // Kalau data kosong, tampilkan 0
        if (total === 0) {
            info.innerHTML = `RESULT <b>0 - 0</b> OF <b>0</b>`;
        } else {
            info.innerHTML = `RESULT <b>${start} - ${end}</b> OF <b>${total}</b>`;
        }
    }
</script>