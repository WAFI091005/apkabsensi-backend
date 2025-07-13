<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kehadiran;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\LaporanExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    /* ===== JSON ===== */
    public function ringkasan(Request $r)
    {
        return response()->json($this->ringkasanData($r));
    }

    /* ===== PDF ===== */
    public function pdf(Request $r)
    {
        $data = $this->ringkasanData($r);
        $pdf  = Pdf::loadView('pdf.laporan', $data)->setPaper('a4', 'portrait');

        return $pdf->download(
            "laporan-{$data['range'][0]}-{$data['range'][1]}.pdf"
        );
    }

    /* ===== Excel ===== */
    public function excel(Request $r)
    {
        $data = $this->ringkasanData($r);

        return Excel::download(
            new LaporanExport($data),
            "laporan-{$data['range'][0]}-{$data['range'][1]}.xlsx"
        );
    }

    /* ===== shared logic ===== */
    private function ringkasanData(Request $r): array
    {
        $start = Carbon::parse($r->get('start', today()));
        $end   = Carbon::parse($r->get('end',   today()));
        $kelas = $r->get('kelas');

        $rows = Kehadiran::with('siswa')
            ->whereBetween('tanggal', [$start, $end])
            ->when($kelas, fn ($q) =>
                $q->whereHas('siswa', fn ($sq) => $sq->where('kelas', $kelas)))
            ->get();

        // total
        $total  = $rows->groupBy('status')->map->count();

        // per hari
        $harian = $rows->groupBy('tanggal')
                       ->map(fn ($set) => $set->groupBy('status')->map->count());

        return [
            'range'   => [$start->toDateString(), $end->toDateString()],
            'start'   => $start->toDateString(),
            'end'     => $end->toDateString(),
            'kelas'   => $kelas,
            'totals'  => [
                'hadir' => $total['hadir'] ?? 0,
                'izin'  => $total['izin']  ?? 0,
                'sakit' => $total['sakit'] ?? 0,
                'alpha' => $total['alpha'] ?? 0,
            ],
            'perHari' => $harian,
            'detail'  => $rows,   // jika front‑end mau tabel rinci
        ];
    }
}
