<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use App\Models\User;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validasi Input - Sekarang pakai username (bukan email)
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // 2. Cari user berdasarkan username (kolom 'name' di database)
        $user = User::where('name', $request->username)->first();

        if ($user) {
            // 3. Cek apakah akun TIDAK AKTIF
            if ($user->is_active == 0) {
                return back()->withErrors([
                    'username' => 'Akun Anda telah dinonaktifkan. Silakan hubungi Administrator.',
                ])->withInput($request->only('username'));
            }
        }

        // 4. Proses Login menggunakan Auth::attempt
        // Kita mencocokkan kolom 'name' di DB dengan input 'username' dari form
        $credentials = [
            'name'     => $request->username,
            'password' => $request->password
        ];

        if (Auth::attempt($credentials, $request->has('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        // 5. Jika login gagal
        return back()->withErrors([
            'username' => 'Username atau password yang Anda masukkan salah.',
        ])->withInput($request->only('username'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}