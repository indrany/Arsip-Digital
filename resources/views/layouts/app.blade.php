<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Arsip Digital | @yield('title', 'Dashboard')</title>
    
    {{-- KRUSIAL: META TAG CSRF UNTUK KEAMANAN AJAX --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Memuat Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Memuat Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    {{-- Memuat CSS KUSTOM --}}
    <link rel="stylesheet" href="{{ asset('css/main.css') }}"> 
</head>
<body>

    <div class="v1_203 main-container">
        
        {{-- SIDEBAR --}}
        <div class="v1_204 sidebar">
            <div>
                {{-- Logo dan Title --}}
                <div class="v1_205">
                    <div class="v1_206">
                        <div class="v1_207 sidebar-logo-area">
                            <div class="v1_208"></div> {{-- Placeholder Logo --}}
                            <span class="sidebar-system-title">Sistem Arsip Digital</span>
                        </div>
                    </div>
                </div>

                {{-- Navigasi Menu --}}
                <nav class="sidebar-nav">
                    <ul>
                        {{-- Pastikan class 'active' ada di <li> yang sedang aktif --}}
                        <li class="{{ Request::routeIs('dashboard') ? 'active' : '' }}">
                            <a href="{{ route('dashboard') }}">
                                <span class="menu-icon dashboard-icon"></span>
                                Dashboard
                            </a>
                        </li>
                        <li class="{{ Request::routeIs('pengiriman-berkas.*') ? 'active' : '' }}">
                            <a href="{{ route('pengiriman-berkas.index') }}">
                                <span class="menu-icon kirim-icon"></span>
                                Pengiriman Berkas
                            </a>
                        </li>
                        <li class="{{ Request::routeIs('penerimaan-berkas.*') ? 'active' : '' }}">
                            <a href="{{ route('penerimaan-berkas.index') }}">
                                <span class="menu-icon terima-icon"></span>
                                Penerimaan Berkas
                            </a>
                        </li>
                        <li class="{{ Request::routeIs('pencarian-berkas.*') ? 'active' : '' }}">
                            <a href="{{ route('pencarian-berkas.index') }}">
                                <span class="menu-icon cari-icon"></span>
                                Pencarian Berkas
                            </a>
                        </li>
                        <li class="{{ Request::routeIs('pinjam-berkas.*') ? 'active' : '' }}">
                            <a href="{{ route('pinjam-berkas.index') }}">
                                <span class="menu-icon pinjam-icon"></span>
                                Pinjam Berkas
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            {{-- Logout Button --}}
            <div class="v1_353 logout-bottom">
                <a href="{{ route('logout') }}" class="logout-button" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <span class="menu-icon logout-icon"></span>
                    Log Out
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>

        {{-- CONTENT WRAPPER --}}
        <div class="content-wrapper">
            
            {{-- HEADER TOP (Fixed) --}}
            <div class="header-top">
                <div class="v1_221 header-background"></div>
                
                <div class="title-area">
                    <span class="v1_230">@yield('page-title', 'Selamat Datang')</span>
                    <span class="v1_232">@yield('page-subtitle', 'Sistem Arsip Digital')</span>
                </div>

                <div class="header-right-tools">
                    <div class="admin-profile-container">
                        <span class="admin-role-text">Admin</span>
                    </div>
                </div>
            </div>

            {{-- KONTEN UTAMA --}}
            <main class="main-content">
                @yield('content')
            </main>
        </div>

    </div>

    {{-- Bootstrap JS (Pastikan sudah dimuat) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- KRUSIAL: JQUERY (Dimuat sekali di layout) --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> 
    
    {{-- KRUSIAL: JsBarcode (Dimuat sekali di layout) --}}
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>

    <script>
        // KRUSIAL: Setup AJAX global untuk mengirim CSRF Token secara otomatis
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    {{-- Scripts spesifik halaman akan dimuat di sini --}}
    @stack('scripts')
    
</body>
</html>