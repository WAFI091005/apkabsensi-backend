<?php
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SiswaController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\KehadiranController;
use App\Http\Controllers\Api\AbsenSessionController;


// 📢 Public Routes
Route::get('/hello', fn () => response()->json(['message' => 'Hello from API']));
Route::get('/siswa', [SiswaController::class, 'index']);
Route::get('/kehadiran/hari-ini', [KehadiranController::class, 'hariIni']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/kelas', [SiswaController::class, 'kelasUnik']);

// 🔒 Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', fn (Request $request) => $request->user()->load('siswa'));

    Route::post('/siswa', [SiswaController::class, 'store']);
    Route::post('/kehadiran', [KehadiranController::class, 'store']);
    Route::get('/kehadiran/{id}', [KehadiranController::class, 'bySiswa']);
    Route::get('/kelas/{kelas}/siswa', [SiswaController::class, 'getByKelas']);

    Route::get('/teman-sekelas', function (Request $request) {
        $user = $request->user();
        if (!$user->siswa) {
            return response()->json(['message' => 'Bukan siswa'], 403);
        }
        $kelas = $user->siswa->kelas;
        $teman = \App\Models\Siswa::where('kelas', $kelas)
            ->where('user_id', '!=', $user->id)
            ->with('user:id,name,email')
            ->get();
        return response()->json($teman);
    });

    Route::get('/riwayat-saya', function (Request $request) {
        $user = $request->user();
        if ($user->role !== 'siswa' || !$user->siswa) {
            return response()->json(['message' => 'Hanya siswa yang bisa melihat riwayat'], 403);
        }
        return response()->json($user->siswa->kehadiran()->latest()->get());
    });

    Route::post('/absen/start', [AbsenSessionController::class, 'startSession']);
    Route::post('/absen/scan', [AbsenSessionController::class, 'scan']);

    Route::get('/laporan', [LaporanController::class,'ringkasan']);
    Route::middleware('auth:sanctum')
    ->get('/laporan/pdf',   [LaporanController::class, 'pdf']);

Route::middleware('auth:sanctum')
    ->get('/laporan/excel', [LaporanController::class, 'excel']);

    // Optional: pindahkan ini kalau mau lindungi
    Route::get('/guru', function () {
        return response()->json([
            'data' => \App\Models\User::where('role', 'guru')->get()
        ]);
    });
});

Route::middleware('auth:sanctum')->post('/absen', function (Request $request) {
    $user = $request->user();

    if (!$user->siswa) {
        return response()->json(['message' => 'User tidak terhubung ke siswa'], 403);
    }

    $payload = $request->all();
    if (!isset($payload['kelas']) || !isset($payload['timestamp'])) {
        return response()->json(['message' => 'Data QR tidak lengkap'], 422);
    }

    $kelasDariQR = trim($payload['kelas']);
    $kelasUser = trim($user->siswa->kelas);

    if ($kelasDariQR !== $kelasUser) {
        return response()->json(['message' => 'QR ini bukan untuk kelas kamu'], 403);
    }

    $tanggal = now()->toDateString();

    $sudahAbsen = \App\Models\Kehadiran::where('siswa_id', $user->siswa->id)
        ->where('tanggal', $tanggal)
        ->exists();

    if ($sudahAbsen) {
        return response()->json(['message' => 'Kamu sudah absen hari ini'], 409);
    }

    // Simpan absensi
    \App\Models\Kehadiran::create([
        'siswa_id' => $user->siswa->id,
        'tanggal' => $tanggal,
        'status' => 'hadir',
    ]);

    // Kirim WA pakai logika yang sama dengan controller
    $controller = new \App\Http\Controllers\Api\KehadiranController();
    $siswa = $user->siswa;
    $pesan = "📢 Informasi Kehadiran Siswa\n\n"
        . "👤 Nama: {$siswa->nama}\n"
        . "🏫 Kelas: {$siswa->kelas}\n"
        . "📅 Tanggal: {$tanggal}\n"
        . "📌 Status: HADIR";

    $controller->kirimWADariRoute($siswa->no_hp_ortu, $pesan); // Gunakan versi publik dari fungsi WA

    return response()->json(['message' => 'Absensi berhasil']);
});

// routes/api.php
Route::middleware('auth:sanctum')->post('/import-siswa', function (Request $r) {
    $r->validate(['file' => 'required|file|mimes:xlsx,csv|max:2048']);
    Excel::import(new \App\Imports\SiswasImport, $r->file('file'));
    return response()->json(['message' => 'Import sukses'], 201);
});

Route::middleware('auth:sanctum')->patch('/me', function (Request $request) {
    $user = $request->user();

    // 1. validasi input
    $data = $request->validate([
        'name'  => ['required','string','max:255'],
        'photo' => ['nullable','image','max:2048'], // ≤ 2 MB
    ]);

    // 2. update nama
    $user->name = $data['name'];

    // 3. update foto (jika dikirim)
    if ($request->hasFile('photo')) {
        // hapus foto lama jika ada
        if ($user->photo_url) {
            $old = str_replace(url('/storage') . '/', '', $user->photo_url);
            Storage::disk('public')->delete($old);
        }

        $path = $request->file('photo')->store('avatars', 'public');
        $user->photo_url = url('/storage/' . $path);
    }

    $user->save();

    // 4. kirim balik data user (JSON)
    return response()->json($user, 200);
});

Route::middleware('auth:sanctum')->post('/ubah-sandi', function (Request $req) {
    $req->validate([
        'current_password' => 'required',
        'new_password' => 'required|min:6',
        'confirm_password' => 'same:new_password',
    ]);

    if (!Hash::check($req->current_password, $req->user()->password)) {
        return response()->json(['message' => 'Kata sandi lama salah'], 400);
    }

    $req->user()->update([
        'password' => Hash::make($req->new_password),
    ]);

    return response()->json(['message' => 'Kata sandi berhasil diubah']);
});

// 1. daftar kelas + total
Route::get('/kelas', function () {
    return \App\Models\Siswa::select('kelas')
        ->selectRaw('COUNT(*) as total')
        ->groupBy('kelas')
        ->orderBy('kelas')
        ->get()
        ->map(fn($k)=>['nama'=>$k->kelas,'total'=>$k->total]);
});

// 2. hapus siswa
Route::delete('/siswa/{id}', [SiswaController::class, 'destroy']);

Route::post('/kelas/{kelas}/absen-semua', [KehadiranController::class, 'absenSemua']);
