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
    
    <style>
        /* Mencegah Double Scrollbar */
        html, body {
            height: 100%;
            margin: 0;
            overflow: hidden; /* Scroll dikelola oleh container saja */
        }

        .main-container {
            height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* Sidebar Tetap/Fixed */
        .sidebar {
            width: 250px; /* Sesuaikan dengan lebar di main.css kamu */
            flex-shrink: 0;
            height: 100vh;
            overflow-y: auto;
        }

        /* Area Konten Utama */
        .content-wrapper {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow-y: auto; /* Scrollbar muncul di sini saja */
            background-color: #f4f7fe;
        }

        .main-content {
            flex: 1 0 auto; /* Menekan footer ke bawah */
            padding-bottom: 2rem;
        }

        /* Footer Rapih & Menempel Sempurna */
        .footer-siardig {
            background-color: #ffffff;
            border-top: 1px solid #e3e6f0;
            padding: 20px 0;
            width: 100%;
            margin-top: auto;
        }
    </style>
    
</head>
<body>
<div class="v1_203 main-container">
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
                    <li class="{{ Request::routeIs('dashboard') ? 'active' : '' }}">
                        <a href="{{ route('dashboard') }}">
                            <div class="menu-icon"><i class="fas fa-home"></i></div> Dashboard
                        </a>
                    </li>
                    @if(strtoupper(Auth::user()->role) == 'ADMIN')
                    <li class="{{ Request::is('users*') ? 'active' : '' }}">
                        <a href="{{ route('users.index') }}">
                            <div class="menu-icon"><i class="fas fa-user-gear"></i></div> Manajemen User
                        </a>
                    </li>
                    @endif
                    @if(in_array(strtoupper(Auth::user()->role), ['ADMIN', 'TIKIM']))
                    <li class="{{ Request::is('rak-loker*') ? 'active' : '' }}">
                        <a href="{{ route('rak-loker.index') }}">
                            <div class="menu-icon"><i class="fas fa-boxes-stacked"></i></div> Master Rak Loker
                        </a>
                    </li>
                    @endif
                    @if(strtoupper(Auth::user()->role) != 'TIKIM')
                    <li class="{{ Request::is('pengiriman-berkas*') ? 'active' : '' }}">
                        <a href="{{ route('pengiriman-berkas.index') }}">
                            <div class="menu-icon"><i class="fas fa-paper-plane"></i></div> Pengiriman Berkas
                        </a>
                    </li>
                    @endif
                    @if(in_array(strtoupper(Auth::user()->role), ['ADMIN', 'TIKIM']))
                    <li class="{{ Request::is('penerimaan-berkas*') ? 'active' : '' }}">
                        <a href="{{ route('penerimaan-berkas.index') }}"> 
                            <div class="menu-icon"><i class="fas fa-file-import"></i></div> Penerimaan Berkas
                        </a>
                    </li>
                    @endif
                    @if(strtoupper(Auth::user()->role) == 'ADMIN')
                    <li class="{{ Request::is('pencarian-berkas*') ? 'active' : '' }}">
                        <a href="{{ route('pencarian-berkas.index') }}">
                            <div class="menu-icon"><i class="fas fa-search"></i></div> Pencarian Berkas
                        </a>
                    </li>
                    @endif
                    <li class="{{ Request::is('pinjam-berkas*') ? 'active' : '' }}">
                        <a href="{{ route('pinjam-berkas.index') }}">
                            <div class="menu-icon"><i class="fas fa-file-signature"></i></div> Pinjam Berkas
                        </a>
                    </li>
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
    <div class="content-wrapper">
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

        {{-- FOOTER RAPID --}}
        <footer class="footer-siardig">
            <div class="text-center">
                <span class="text-muted small">
                    &copy; 2026 <strong>SIARDIG</strong> - Sistem Arsip Digital | Kantor Imigrasi Kelas I TPI Tanjung Perak
                </span>
            </div>
        </footer>
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