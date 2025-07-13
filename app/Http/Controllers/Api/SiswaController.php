<?php

namespace App\Http\Controllers\Api;

use App\Models\Siswa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SiswaController extends Controller
{
        public function index()
    {
        return Siswa::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'kelas' => 'required',
        ]);

        $siswa = Siswa::create($request->all());

        return response()->json($siswa, 201);
    }

    public function kelasUnik()
{
    $kelas = \App\Models\Siswa::select('kelas')
        ->whereNotNull('kelas')
        ->distinct()
        ->orderBy('kelas')
        ->get();

    return response()->json($kelas);
}
// public function getByKelas($kelas)
// {
//     $data = Siswa::with('kehadiranHariIni')
//         ->where('kelas', $kelas)
//         ->get()
//         ->map(function ($siswa) {
//             return [
//                 'nama' => $siswa->nama,
//                 'absen' => optional($siswa->kehadiranHariIni)->status ?? null,
//             ];
//         });

//     return response()->json($data);
// }
public function getByKelas($kelas)
{
    $data = Siswa::with('user:id,name,email') // relasi ke users
        ->where('kelas', $kelas)
        ->get()
        ->map(function ($siswa) {
            return [
                'id' => $siswa->id,
                'nama_siswa' => $siswa->nama,
                'kelas' => $siswa->kelas,
                'email' => $siswa->user->email ?? null,
                'name' => $siswa->user->name ?? null,
                'nama' => $siswa->nama,
                'absen' => optional($siswa->kehadiranHariIni)->status ?? null,
            ];
        });

    return response()->json($data);
}

// app/Http/Controllers/Api/SiswaController.php
public function destroy($id)
{
    Siswa::findOrFail($id)->delete();
    return response()->json(['message'=>'Siswa dihapus']);
}


}
