<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsenSession;
use App\Models\Kehadiran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AbsenSessionController extends Controller
{
    // Guru membuat session QR
    public function startSession(Request $request)
    {
        $request->validate([
            'kelas' => 'required|string'
        ]);

        $session = AbsenSession::create([
            'token' => Str::uuid(),
            'kelas' => strtolower($request->kelas),
            'expired_at' => now()->addMinutes(5),
            'created_by' => Auth::id()
        ]);

        return response()->json([
            'token' => $session->token,
            'kelas' => $session->kelas,
            'expired_at' => $session->expired_at
        ]);
    }

    // Siswa scan QR
    public function scan(Request $request)
{
    $request->validate([
        'token' => 'required|string'
    ]);

    $session = AbsenSession::where('token', $request->token)
        ->where('expired_at', '>', now())
        ->first();

    if (!$session) {
        return response()->json(['message' => 'QR tidak valid atau sudah expired'], 400);
    }

    $user = $request->user();

    if (!$user->siswa) {
        return response()->json(['message' => 'Kamu bukan siswa'], 403);
    }

    // Ambil kelas dari relasi siswa
    $kelasSiswa = strtolower(trim($user->siswa->kelas));
    $kelasQR = strtolower(trim($session->kelas));

    if ($kelasSiswa !== $kelasQR) {
        return response()->json(['message' => 'Kamu bukan bagian dari kelas ini'], 403);
    }

    $siswaId = $user->siswa->id;

    $sudahAbsen = Kehadiran::where('siswa_id', $siswaId)
        ->whereDate('tanggal', now())
        ->exists();

    if ($sudahAbsen) {
        return response()->json(['message' => 'Kamu sudah absen hari ini'], 409);
    }

    Kehadiran::create([
        'siswa_id' => $siswaId,
        'tanggal' => now(),
        'status' => 'hadir'
    ]);

    return response()->json(['message' => 'Berhasil absen!']);
}

}
