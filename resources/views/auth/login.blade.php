<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Inter:400,500,600&display=swap" rel="stylesheet" />

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <title>Sistem Arsip Imigrasi - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="login-container">

        <!-- Bagian kiri -->
        <div class="system-info">
            <img src="{{ asset('images/v1_150.png') }}" alt="Logo Kementerian Imigrasi dan Pemasyarakatan" class="system-logo">

            <h1 class="system-title">
                SISTEM ARSIP IMIGRASI <br> TANJUNG PERAK
            </h1>
        </div>

        <!-- Bagian kanan -->
        <div class="login-box">

            <img src="{{ asset('images/v1_151.png') }}" alt="Logo Imigrasi Kecil" class="login-logo-small">

            <div class="login-header">
                <h2 class="login-title">Masuk ke Akun Anda</h2>
                <p class="login-subtitle">Selamat datang! Silahkan masukan akun anda</p>
            </div>

            <!-- FORM LOGIN -->
            @if (session('success'))
                <div style="background: #dcfce7; color: #15803d; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 13px; text-align: center; border: 1px solid #bbf7d0;">
                    {{ session('success') }}
                </div>
            @endif
            <form action="{{ route('login.post') }}" method="POST" class="login-form">
                @csrf

                <div class="form-group">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email"
                        placeholder="Masukkan email anda" 
                        required
                    >
                </div>

                <div class="form-group">
                <label for="password">Password</label>
                <div style="position: relative; display: flex; align-items: center;">
                    <input 
                        type="password" id="password" name="password" placeholder="••••••••" required style="padding-right: 40px; width: 100%;">
                    <i class="fa-regular fa-eye" 
                    id="togglePassword" onclick="togglePassword('password', this)"
                    style="position: absolute; right: 15px; cursor: pointer; color: #667085;">
                    </i>
                </div>
                </div>

                <button type="submit" class="btn-primary">Masuk</button>
            </form>
            <div class="text-center mt-3">
                <p class="small text-muted" style="font-size: 14px;">
                    Belum punya akun? <a href="{{ route('register') }}" class="text-primary fw-bold">Daftar di sini</a></p>
            </div>
        </div>

    </div>
    <script>
    function togglePassword(inputId, icon) {
        const passwordInput = document.getElementById(inputId);
        
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            passwordInput.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }
</script>
</body>
</html>
