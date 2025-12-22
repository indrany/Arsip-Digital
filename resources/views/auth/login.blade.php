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
                    <input 
                        type="password" 
                        id="password" 
                        name="password"
                        placeholder="••••••••" 
                        required
                    >
                </div>

                <button type="submit" class="btn-primary">Masuk</button>
            </form>
        </div>

    </div>
</body>
</html>
