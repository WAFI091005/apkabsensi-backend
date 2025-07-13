<?php

namespace App\Http\Controllers\Api;

use App\Models\Siswa;
use App\Models\Kehadiran;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class KehadiranController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:hadir,izin,sakit,alpha',
        ]);

        $sudahAbsen = Kehadiran::where('siswa_id', $request->siswa_id)
            ->where('tanggal', $request->tanggal)
            ->exists();

        if ($sudahAbsen) {
            return response()->json([
                'message' => 'Siswa sudah melakukan absen pada tanggal ini.'
            ], 409);
        }

        $kehadiran = Kehadiran::create($request->all());

        // Kirim WA jika statusnya bukan "hadir"
$siswa = Siswa::find($request->siswa_id);

if ($siswa && $siswa->no_hp_ortu) {
    $status = strtoupper($request->status);
    
    $pesan = "📢 Informasi Kehadiran Siswa\n\n"
        . "👤 Nama: {$siswa->nama}\n"
        . "🏫 Kelas: {$siswa->kelas}\n"
        . "📅 Tanggal: {$request->tanggal}\n"
        . "📌 Status: {$status}";

    $this->kirimWA($siswa->no_hp_ortu, $pesan);
}


        return response()->json($kehadiran, 201);
    }

    public function bySiswa($id)
    {
        return Kehadiran::where('siswa_id', $id)->get();
    }

    public function hariIni()
    {
        $today = now()->toDateString();
        $data = Kehadiran::with('siswa')
            ->whereDate('tanggal', $today)
            ->get();

        return response()->json([
            'today' => $today,
            'count' => $data->count(),
            'data' => $data,
        ]);
    }

    public function absenSemua($kelas)
    {
        $tanggal = now()->toDateString();

        $siswaList = Siswa::where('kelas', $kelas)->get();

        foreach ($siswaList as $siswa) {
            $sudahAbsen = Kehadiran::where('siswa_id', $siswa->id)
                ->where('tanggal', $tanggal)
                ->exists();

            if (!$sudahAbsen) {
                Kehadiran::create([
                    'siswa_id' => $siswa->id,
                    'tanggal' => $tanggal,
                    'status' => 'hadir'
                ]);

                if ($siswa->no_hp_ortu) {
                    $pesan = "Anak Anda {$siswa->nama} telah hadir di sekolah hari ini ($tanggal).";
                    $this->kirimWA($siswa->no_hp_ortu, $pesan);
                }
            }
        }

        return response()->json(['message' => 'Absensi massal berhasil']);
    }

private function kirimWA($nomor, $pesan)
{
    \Log::info("▶️ Mencoba kirim WA ke $nomor dengan pesan:\n$pesan");

    try {
        $response = Http::post('http://localhost:3000/send-message', [
            'to' => $nomor,
            'message' => $pesan
        ]);

        \Log::info("✅ Response dari API WA: " . $response->body());
    } catch (\Exception $e) {
        \Log::error("❌ Gagal kirim WA ke $nomor: " . $e->getMessage());
    }
}
// Bisa dipanggil dari luar, seperti route closure
public function kirimWADariRoute($nomor, $pesan)
{
    $this->kirimWA($nomor, $pesan);
}


}
