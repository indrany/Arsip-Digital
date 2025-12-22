<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Handle an incoming authentication attempt (Login).
     */
    public function login(Request $request)
    {
        // 1. Validasi Input
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        // Tambahkan ini untuk tes:
    // dd(Auth::attempt($credentials));

        // 2. Coba Otentikasi
        if (Auth::attempt($credentials)) {
            // Jika otentikasi berhasil
            $request->session()->regenerate();

            // Arahkan ke rute 'dashboard'
            return redirect()->intended(route('dashboard'));
        }

        // 3. Jika otentikasi gagal
        return back()->withErrors([
            'email' => 'Email atau Password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Log the user out of the application (Logout).
     */
    public function logout(Request $request)
    {
        Auth::logout(); // Hapus sesi otentikasi

        $request->session()->invalidate(); // Batalkan sesi yang ada
        $request->session()->regenerateToken(); // Buat token CSRF baru

        // Arahkan ke halaman login
        return redirect()->route('login');
    }
}