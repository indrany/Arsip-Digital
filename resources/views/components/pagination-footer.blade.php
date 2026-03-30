{{-- resources/views/components/pagination-footer.blade.php --}}

@if(isset($data))
<div class="card-footer bg-white border-top-0 py-3">
    
    {{-- BAGIAN SHOW (Disembunyikan dulu, nanti dipindah pakai JS ke targetId) --}}
    <div id="source-show-{{ $targetId ?? 'default' }}" class="d-none">
        <div class="d-flex align-items-center gap-2">
            <label class="small fw-bold text-muted mb-0" style="font-size: 11px;">SHOW</label>
            <select class="form-select form-select-sm shadow-none" 
                    style="font-size: 11px; font-weight: 800; border-radius: 6px; width: 70px; color: #3b82f6; background: #f8f9fa; border: 1px solid #e2e8f0;" 
                    onchange="window.location.href = window.location.pathname + '?per_page=' + this.value + '&page=1';">
                @foreach([5, 10, 25, 50, 100] as $val)
                    <option value="{{ $val }}" {{ request('per_page', 10) == $val ? 'selected' : '' }}>{{ $val }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const targetId = "{{ $targetId ?? '' }}";
            const source = document.getElementById("source-show-{{ $targetId ?? 'default' }}");
            
            if (targetId && source) {
                const targetHeader = document.getElementById(targetId);
                if (targetHeader) {
                    // Paksa targetHeader jadi Flexbox biar Judul di kiri, SHOW di kanan
                    targetHeader.classList.add('d-flex', 'justify-content-between', 'align-items-center');
                    
                    // Ambil isi div source dan tempel ke target
                    const dropdown = source.firstElementChild;
                    targetHeader.appendChild(dropdown);
                }
            }
        });
    </script>

    <div class="d-flex flex-column align-items-center">
        {{-- NAVIGASI ANGKA --}}
        <nav>
            <ul class="app-pagination-list mb-2">
                @if($data->onFirstPage())
                    <li class="page-item disabled"><span><i class="fas fa-chevron-left"></i></span></li>
                @else
                    <li class="page-item"><a href="{{ $data->appends(request()->query())->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a></li>
                @endif

                @foreach ($data->getUrlRange(max(1, $data->currentPage() - 2), min($data->lastPage(), $data->currentPage() + 2)) as $page => $url)
                    @if ($page == $data->currentPage())
                        <li class="page-item active"><span>{{ $page }}</span></li>
                    @else
                        <li class="page-item"><a href="{{ $data->appends(request()->query())->url($page) }}">{{ $page }}</a></li>
                    @endif
                @endforeach

                @if($data->hasMorePages())
                    <li class="page-item"><a href="{{ $data->appends(request()->query())->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a></li>
                @else
                    <li class="page-item disabled"><span><i class="fas fa-chevron-right"></i></span></li>
                @endif
            </ul>
        </nav>

        {{-- INFO TEKS --}}
        <div style="font-size: 10px; color: #adb5bd; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">
            Showing {{ $data->firstItem() ?? 0 }} to {{ $data->lastItem() ?? 0 }} of {{ $data->total() }} Entries
        </div>
    </div>
</div>
@endif

<style>
    .app-pagination-list { display: flex !important; flex-direction: row !important; list-style: none !important; padding: 0 !important; margin: 0 !important; gap: 6px !important; }
    .app-pagination-list .page-item a, .app-pagination-list .page-item span { display: flex !important; align-items: center !important; justify-content: center !important; width: 34px !important; height: 34px !important; text-decoration: none !important; background-color: #ffffff !important; border: 1px solid #e2e8f0 !important; border-radius: 8px !important; color: #64748b !important; font-size: 12px !important; font-weight: 700 !important; transition: all 0.2s; }
    .app-pagination-list .page-item a:hover { background-color: #f1f5f9 !important; color: #3b82f6 !important; }
    .app-pagination-list .page-item.active span { background-color: #3b82f6 !important; color: #ffffff !important; border-color: #2563eb !important; box-shadow: 0 4px 8px rgba(59, 130, 246, 0.2) !important; }
    .app-pagination-list .page-item.disabled span { background-color: #f8fafc !important; color: #cbd5e1 !important; border-color: #f1f5f9 !important; }
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