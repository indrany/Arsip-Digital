<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Arsip Digital | @yield('title', 'Dashboard')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}"> 
</head>
<body>
<div class="v1_203 main-container d-flex">
    {{-- SIDEBAR --}}
    <div class="v1_204 sidebar" id="main-sidebar">
        <div>
            <div class="v1_205 sidebar-header">
                <div class="v1_207 sidebar-logo-area">
                    <div class="v1_208"></div> 
                    <span class="sidebar-system-title">Sistem Arsip Digital</span>
                </div>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    {{-- 1. Dashboard --}}
                    <li class="{{ Request::routeIs('dashboard') ? 'active' : '' }}">
                        <a href="{{ route('dashboard') }}">
                            <div class="menu-icon"><i class="fas fa-home"></i></div> Dashboard
                        </a>
                    </li>
            
                    {{-- 2. Pengiriman Berkas --}}
                    <li class="{{ Request::is('pengiriman-berkas*') ? 'active' : '' }}">
                        <a href="{{ route('pengiriman-berkas.index') }}">
                            <div class="menu-icon"><i class="fas fa-paper-plane"></i></div> Pengiriman Berkas
                        </a>
                    </li>
            
                    {{-- 
                       MODUL PENERIMAAN: Hanya muncul jika Role adalah 'ARSIP' atau 'ADMIN' (Case Insensitive).
                       Jika login sebagai 'ukk' atau 'ulp', menu ini akan hilang total.
                    --}}
                    @if(Auth::check() && in_array(strtoupper(trim(Auth::user()->role)), ['ARSIP', 'ADMIN']))
                    <li class="{{ Request::is('penerimaan-berkas*') ? 'active' : '' }}">
                        <a href="{{ route('penerimaan-berkas.index') }}"> 
                            <i class="fas fa-file-import"></i> Penerimaan Berkas
                        </a>
                    </li>
                    @endif
            
                    {{-- 3. Pencarian Berkas --}}
                    <li class="{{ Request::is('pencarian-berkas*') ? 'active' : '' }}">
                        <a href="{{ route('pencarian-berkas.index') }}">
                            <div class="menu-icon"><i class="fas fa-search"></i></div> Pencarian Berkas
                        </a>
                    </li>
            
                    {{-- 4. Pinjam Berkas: Muncul untuk semua role agar sidebar memiliki 4 menu --}}
                    {{-- Menu Pinjam Berkas (Sudah Ada) --}}
                    <li class="{{ Request::is('pinjam-berkas*') ? 'active' : '' }}">
                        <a href="{{ route('pinjam-berkas.index') }}">
                            <div class="menu-icon">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6C5.44772 2 5 2.44772 5 3V21C5 21.5523 5.44772 22 6 22H18C18.5523 22 19 21.5523 19 21V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 2V8H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div> 
                            Pinjam Berkas
                        </a>
                    </li>

                    {{-- TAMBAHAN: Menu Manajemen User (Hanya muncul jika Role ADMIN) --}}
                    @if(auth()->user()->role == 'ADMIN')
                    <li class="{{ Request::is('users*') ? 'active' : '' }}">
                        <a href="{{ route('users.index') }}">
                            <div class="menu-icon">
                                {{-- Ikon User Gear --}}
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="8.5" cy="7" r="4"></circle>
                                    <circle cx="19" cy="11" r="2"></circle>
                                    <path d="M19 8v1"></path>
                                    <path d="M19 13v1"></path>
                                </svg>
                            </div> 
                            Manajemen User
                        </a>
                    </li>
                    @endif
                </ul>
            </nav>
        </div>
        <div class="v1_353 logout-bottom">
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
            <a href="{{ route('logout') }}" class="logout-button" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <div class="menu-icon"><i class="fas fa-sign-out-alt"></i></div> Log Out
            </a>
        </div>
    </div>

    {{-- CONTENT WRAPPER --}}
    <div class="content-wrapper flex-grow-1">
        <div class="header-top">
            <div class="v1_221 header-background"></div>
            <div class="title-area">
                <span class="v1_230">@yield('page-title')</span>
                <span class="v1_232">@yield('page-subtitle')</span>
            </div>
            <div class="header-right-tools">
                <div class="admin-profile-container">
                    <span class="admin-role-text">
                        {{ Auth::user()->name }} ({{ Auth::user()->unit_kerja ?? 'Kanim' }})
                    </span>
                </div>
            </div>
        </div>
        <main class="main-content p-4">
            <div class="container-fluid">
                @yield('content')
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>
@stack('scripts')
</body>
</html>