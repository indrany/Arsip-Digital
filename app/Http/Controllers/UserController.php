<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        // Mengambil semua user kecuali yang sedang login (opsional, agar admin tidak menghapus dirinya sendiri)
        $users = User::all();
        return view('user.index', compact('users'));
    }

    public function store(Request $request)
    {
    $request->validate([
        'username' => 'required|unique:users,name', // Sesuaikan kolom 'name' di tabel users
        'nama_lengkap' => 'required',
        'password' => 'required|min:6',
        'role' => 'required',
    ], [
        'username.unique' => 'Username sudah terdaftar!',
    ]);

    \App\Models\User::create([
        'name' => $request->username,
        'nama_lengkap' => $request->nama_lengkap,
        'password' => bcrypt($request->password),
        'role' => $request->role,
        'is_active' => $request->is_active ?? 1,
        // email bisa dikosongkan atau diisi default null jika di database diizinkan null
    ]);

    return back()->with('success', 'User berhasil ditambahkan!');
    }

    // --- INI FUNGSI YANG HARUS DITAMBAHKAN AGAR TIDAK ERROR LAGI ---
    public function updateStatus(User $user)
    {
        // Membalikkan status: jika 1 jadi 0, jika 0 jadi 1
        $user->is_active = !$user->is_active;
        $user->save();

        $pesan = $user->is_active ? 'diaktifkan' : 'dimatikan';
        return redirect()->back()->with('success', "User {$user->name} berhasil {$pesan}.");
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Validasi agar username tetap unik tapi mengabaikan ID user ini sendiri
        $request->validate([
            'username' => 'required|unique:users,name,' . $id, 
            'nama_lengkap' => 'required',
            'role' => 'required',
        ]);
    
        $user->name = $request->username; // Sesuaikan jika kolomnya 'name' atau 'username'
        $user->nama_lengkap = $request->nama_lengkap;
        $user->role = $request->role;
        $user->is_active = $request->is_active;
    
        // Hanya update password jika diisi
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }
    
        $user->save();
        return back()->with('success', 'Data user berhasil diperbarui!');
    }
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}