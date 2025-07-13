<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Siswa;
use Illuminate\Validation\Rule; // <<< Tambahkan ini

class AuthController extends Controller
{
    /**
     * Handle user registration.
     * Mendaftarkan user baru dengan role 'siswa' atau 'guru',
     * dan membuat entri Siswa jika role-nya 'siswa'.
     */
    public function register(Request $request)
    {
        // 1. Validasi Request:
        // Memastikan semua input yang diterima sesuai dengan aturan yang ditentukan.
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users|max:255',
            'password' => 'required|string|min:8',
            'role' => ['required', 'string', Rule::in(['siswa', 'guru'])], // Menggunakan Rule::in
            'kelas' => 'required_if:role,siswa|string|max:255',
            'no_hp_ortu' => 'nullable|string|max:20',
            // <<< BARIS INI YANG DIPERBAIKI UNTUK VALIDASI NIS >>>
            'nis' => [
                Rule::requiredIf($request->input('role') === 'siswa'), // NIS wajib jika role adalah 'siswa'
                'nullable', // Membolehkan NIS kosong/null jika tidak wajib
                'numeric', // NIS harus angka
                'unique:siswas,nis' // Pastikan NIS unik di tabel siswas
            ],
        ]);

        // 2. Membuat entri User baru di tabel 'users'
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        // 3. Jika role yang didaftarkan adalah 'siswa', maka buat juga entri di tabel 'siswas'
        if ($request->role === 'siswa') {
            Siswa::create([
                'user_id' => $user->id,
                'nama' => $request->name,
                'kelas' => $request->kelas,
                'no_hp_ortu' => $request->no_hp_ortu,
                'nis' => $request->nis,
            ]);
        }

        // 4. Mengembalikan respon JSON setelah registrasi berhasil
        return response()->json(['message' => 'User created successfully', 'user' => $user->load('siswa')], 201);
    }

    /**
     * Handle user login.
     * Mengotentikasi user dan membuat token API Sanctum.
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Login gagal. Email atau kata sandi salah.'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * Handle user logout.
     * Menghapus semua token API Sanctum user yang sedang login.
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logout berhasil.']);
    }
}