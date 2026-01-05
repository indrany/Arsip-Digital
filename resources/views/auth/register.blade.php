<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css?family=Inter:400,500,600&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <title>Sistem Arsip Imigrasi - Daftar</title>

    <style>
        /* Style tambahan untuk fitur lihat password */
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-wrapper input {
            padding-right: 40px !important; /* Ruang untuk icon */
        }
        .toggle-password {
            position: absolute;
            right: 14px;
            cursor: pointer;
            color: #667085;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="login-container">

        <div class="system-info">
            <img src="{{ asset('images/v1_150.png') }}" alt="Logo Kementerian Imigrasi dan Pemasyarakatan" class="system-logo">

            <h1 class="system-title">
                SISTEM ARSIP IMIGRASI <br> TANJUNG PERAK
            </h1>
        </div>
        <div class="login-box" style="padding-top: 20px; margin-left: 0; transform: none; width: 380px;"> 
    
    <img src="{{ asset('images/v1_151.png') }}" alt="Logo Imigrasi Kecil" style="width: 45px; height: 45px; margin-bottom: 10px; display: block; margin-left: auto; margin-right: auto;">

    <div class="login-header" style="margin-bottom: 15px; text-align: center;">
        <h2 class="login-title" style="font-size: 20px; margin-bottom: 2px; line-height: 1;">Daftar Akun Baru</h2>
        <p class="login-subtitle" style="font-size: 12px; margin-top: 0;">Silakan lengkapi data pendaftaran</p>
    </div>
{{-- Tambahkan ini tepat di atas tag <form> --}}
@if ($errors->any())
    <div style="background: #fee2e2; color: #b91c1c; padding: 8px; border-radius: 6px; margin-bottom: 10px; font-size: 11px;">
        <ul style="margin: 0; padding-left: 15px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
    <form action="{{ route('register.post') }}" method="POST" class="login-form">
    @csrf

    <div class="form-group" style="margin-bottom: 8px;">
        <label style="font-size: 11px; margin-bottom: 2px;">Nama Lengkap</label>
        <input type="text" name="name" placeholder="Nama Lengkap" style="height: 32px; font-size: 13px;" required>
    </div>

    <div class="form-group" style="margin-bottom: 8px;">
        <label style="font-size: 11px; margin-bottom: 2px;">Username</label>
        <input type="text" name="username" placeholder="Username" style="height: 32px; font-size: 13px;" required>
    </div>

    <div class="form-group" style="margin-bottom: 8px;">
        <label style="font-size: 11px; margin-bottom: 2px;">Email</label>
        <input type="email" name="email" placeholder="email@gmail.com" style="height: 32px; font-size: 13px;" required>
    </div>

    <div class="form-group" style="margin-bottom: 8px;">
        <label style="font-size: 11px; margin-bottom: 2px;">Unit Kerja / Role</label>
        <select name="role" style="width: 100%; height: 32px; border-radius: 8px; border: 1px solid #D0D5DD; padding: 0 10px; font-size: 13px; color: #667085;" required>
            <option value="admin">Kanim (Admin)</option>
            <option value="ukk">UKK</option>
            <option value="ulp">ULP</option>
            <option value="lantaskim">LANTASKIM</option>
        </select>
    </div>

    <div class="form-group" style="margin-bottom: 12px;">
        <label style="font-size: 11px; margin-bottom: 2px;">Password</label>
        <div style="position: relative; display: flex; align-items: center;">
            <input type="password" id="password" name="password" placeholder="••••••••" style="height: 32px; font-size: 13px; width: 100%; padding-right: 35px;" required>
            <i class="fa-regular fa-eye" onclick="togglePassword('password', this)" style="position: absolute; right: 10px; cursor: pointer; font-size: 14px; color: #667085;"></i>
        </div>
    </div>

    <div class="form-group" style="margin-bottom: 15px;">
        <label style="font-size: 11px; margin-bottom: 2px;">Konfirmasi Password</label>
        <div style="position: relative; display: flex; align-items: center;">
            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••" style="height: 32px; font-size: 13px; width: 100%; padding-right: 35px;" required>
            <i class="fa-regular fa-eye" onclick="togglePassword('password_confirmation', this)" style="position: absolute; right: 10px; cursor: pointer; font-size: 14px; color: #667085;"></i>
        </div>
    </div>

    <button type="submit" class="btn-primary" style="height: 38px; font-size: 14px;">Daftar Sekarang</button>
    </form>
    <div class="text-center" style="margin-top: 10px;">
        <p style="font-size: 12px;">Sudah punya akun? <a href="{{ route('login') }}" style="color: #1366D9; font-weight: 600; text-decoration: none;">Masuk di sini</a></p>
    </div>
</div>
    </div>

    <script>
        // Fungsi untuk toggle lihat password
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-regular", "fa-eye");
                icon.classList.add("fa-solid", "fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-solid", "fa-eye-slash");
                icon.classList.add("fa-regular", "fa-eye");
            }
        }
    </script>
</body>
</html>