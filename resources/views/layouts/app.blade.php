<!DOCTYPE html>
<html lang="en">
<head>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
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
<div class="v1_204 sidebar" id="main-sidebar">
    <div>
        {{-- Bagian Atas Sidebar --}}
<div class="v1_205 sidebar-header">
    <div class="v1_207 sidebar-logo-area">
        {{-- Kotak Logo (Gambar Garuda) --}}
        <div class="v1_208"></div> 
        
        {{-- Tulisan Judul Sistem --}}
        <span class="sidebar-system-title">Sistem Arsip Digital</span>
    </div>
</div>

        {{-- Navigasi Menu --}}
        <nav class="sidebar-nav">
            <ul>
                {{-- Dashboard --}}
                <li class="{{ Request::routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <div class="menu-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        </div> 
                        Dashboard
                    </a>
                </li>

                {{-- Pengiriman Berkas --}}
                <li class="{{ Request::is('pengiriman-berkas*') ? 'active' : '' }}">
                    <a href="{{ route('pengiriman-berkas.index') }}">
                        <div class="menu-icon">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 4V20M12 4L18 10M12 4L6 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18 17H20C21.1046 17 22 17.8954 22 19V20C22 21.1046 21.1046 22 20 22H4C2.89543 22 2 21.1046 2 20V19C2 17.8954 2.89543 17 4 17H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div> 
                        Pengiriman Berkas
                    </a>
                </li>

                {{-- Penerimaan Berkas --}}
                <li class="{{ Request::is('penerimaan-berkas*') ? 'active' : '' }}">
                    <a href="{{ route('penerimaan-berkas.index') }}">
                        <div class="menu-icon">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 20V4M12 20L18 14M12 20L6 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18 17H20C21.1046 17 22 17.8954 22 19V20C22 21.1046 21.1046 22 20 22H4C2.89543 22 2 21.1046 2 20V19C2 17.8954 2.89543 17 4 17H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div> 
                        Penerimaan Berkas
                    </a>
                </li>

                {{-- Pencarian Berkas --}}
                <li class="{{ Request::is('pencarian-berkas*') ? 'active' : '' }}">
                    <a href="{{ route('pencarian-berkas.index') }}">
                        <div class="menu-icon">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11 19C15.4183 19 19 15.4183 19 11C19 6.58172 15.4183 3 11 3C6.58172 3 3 6.58172 3 11C3 15.4183 6.58172 19 11 19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M20.9999 21L18.4999 18.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div> 
                        Pencarian Berkas
                    </a>
                </li>

                {{-- Pinjam Berkas --}}
                <li class="{{ Request::is('pinjam-berkas*') ? 'active' : '' }}">
                    <a href="/pinjam-berkas">
                        <div class="menu-icon">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6C5.44772 2 5 2.44772 5 3V21C5 21.5523 5.44772 22 6 22H18C18.5523 22 19 21.5523 19 21V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 2V8H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div> 
                        Pinjam Berkas
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    {{-- Logout Button --}}
    <div class="v1_353 logout-bottom">
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
        <a href="{{ route('logout') }}" class="logout-button" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <div class="menu-icon">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 3H20C20.5523 3 21 3.44772 21 4V20C21 20.5523 20.5523 21 20 21H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 17L15 12L10 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 12H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            Log Out
        </a>
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