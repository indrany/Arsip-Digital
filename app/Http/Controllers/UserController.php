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
            'username' => 'required|unique:users,name',
            'nama_lengkap' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:TIKIM,LANTASKIM,INTELDAKIM,INTELTUSKIM,ADMIN',
            'is_active' => 'required'
        ]);

        User::create([
            'name' => $request->username,
            'nama_lengkap' => $request->nama_lengkap,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
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

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'nama_lengkap' => 'required',
            'role' => 'required',
            'is_active' => 'required'
        ]);

        $data = [
            'nama_lengkap' => $request->nama_lengkap,
            'role' => $request->role,
            'is_active' => $request->is_active,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}