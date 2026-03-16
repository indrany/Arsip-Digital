{{-- resources/views/components/pagination-footer.blade.php --}}

@if(isset($data))
<div class="card-footer bg-white border-top-0 py-3" style="position: relative;">
    <div class="d-flex flex-column align-items-center">
        
        {{-- NAVIGASI UTAMA --}}
        <nav>
            <ul class="app-pagination-list">
                {{-- Panah Kiri --}}
                @if($data->onFirstPage())
                    <li class="page-item disabled"><span><i class="fas fa-chevron-left"></i></span></li>
                @else
                    <li class="page-item">
                        <a href="{{ $data->appends(request()->query())->previousPageUrl() }}">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                @endif

                {{-- List Angka Halaman (Versi Pasti Muncul) --}}
                @for ($i = 1; $i <= $data->lastPage(); $i++)
                @if ($i == $data->currentPage())
                    <li class="page-item active"><span>{{ $i }}</span></li>
                @else
                    <li class="page-item">
                        <a href="{{ $data->appends(request()->query())->url($i) }}">{{ $i }}</a>
                    </li>
                @endif
                @endfor

                {{-- Panah Kanan --}}
                @if($data->hasMorePages())
                    <li class="page-item">
                        <a href="{{ $data->appends(request()->query())->nextPageUrl() }}">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled"><span><i class="fas fa-chevron-right"></i></span></li>
                @endif
            </ul>
        </nav>

        {{-- INFO TEKS --}}
        <div style="font-size: 10px; color: #adb5bd; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-top: 5px;">
            Showing {{ $data->firstItem() ?? 0 }} to {{ $data->lastItem() ?? 0 }} of {{ $data->total() }} Entries
        </div>
    </div>

    {{-- DROPDOWN SHOW ENTRIES (POJOK KANAN) --}}
    <div style="position: absolute; right: 20px; bottom: 15px; display: flex; align-items: center; gap: 8px;" class="d-none d-md-flex">
        <label style="font-size: 11px; color: #6c757d; margin-bottom: 0; font-weight: bold;">SHOW:</label>
        <select style="border: none; background: #f8f9fa; font-size: 12px; font-weight: 800; border-radius: 6px; padding: 4px 8px; cursor: pointer; color: #3b82f6; box-shadow: 0 2px 4px rgba(0,0,0,0.05);" 
        onchange="window.location.href = window.location.pathname + '?per_page=' + this.value + '&page=1';">
        @foreach([5, 10, 25, 50, 100] as $val)
            <option value="{{ $val }}" {{ request('per_page', 5) == $val ? 'selected' : '' }}>{{ $val }}</option>
        @endforeach
    </select>
    </div>
</div>
@endif

<style>
    /* CSS RE-STYLE - MENGGUNAKAN NAMA CLASS FORMAL */
    .app-pagination-list {
        display: flex !important;
        flex-direction: row !important;
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
        gap: 6px !important;
    }

    .app-pagination-list .page-item a, 
    .app-pagination-list .page-item span {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 34px !important;
        height: 34px !important;
        text-decoration: none !important;
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        color: #64748b !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        transition: all 0.2s;
    }

    .app-pagination-list .page-item a:hover {
        background-color: #f1f5f9 !important;
        color: #3b82f6 !important;
    }

    .app-pagination-list .page-item.active span {
        background-color: #3b82f6 !important;
        color: #ffffff !important;
        border-color: #2563eb !important;
        box-shadow: 0 4px 10px rgba(59, 130, 246, 0.35) !important;
    }

    .app-pagination-list .page-item.disabled span {
        background-color: #f8fafc !important;
        color: #cbd5e1 !important;
    }
</style>

<script>
    function renderJSNav(containerId, currentPage, totalPages, infoId, start, end, total) {
        const container = document.getElementById(containerId);
        const info = document.getElementById(infoId);
        if(!container || !info) return;

        let html = '<ul class="pagination mb-0">';
        // Panah Kiri
        html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link" onclick="changeDetailPage(${currentPage - 1})"><i class="fas fa-chevron-left"></i></a></li>`;
        
        // Render Angka
        let maxPage = Math.max(1, totalPages);
        for (let i = 1; i <= maxPage; i++) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" onclick="changeDetailPage(${i})">${i}</a></li>`;
        }
        
        // Panah Kanan
        html += `<li class="page-item ${currentPage === totalPages || totalPages === 0 ? 'disabled' : ''}"><a class="page-link" onclick="changeDetailPage(${currentPage + 1})"><i class="fas fa-chevron-right"></i></a></li>`;
        html += '</ul>';

        container.innerHTML = html;
        info.innerHTML = total === 0 ? `RESULT 0 - 0 OF 0` : `RESULT ${start} - ${end} OF ${total}`;
    }
</script>