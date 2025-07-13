<!DOCTYPE html>
<html>
<head>
  <style>
    body { font-family: sans-serif; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px;}
    th,td { border: 1px solid #ccc; padding: 4px; text-align: center; }
    th { background: #f0f0f0; }
    h2 { text-align: center; margin: 0; }
  </style>
</head>
<body>
  <h2>Laporan Kehadiran {{ $kelas ? "Kelas $kelas" : '' }}</h2>
  <p>Periode: {{ $start }} – {{ $end }}</p>

  <table>
    <thead>
      <tr>
        <th>Tanggal</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Alpha</th>
      </tr>
    </thead>
    <tbody>
      @foreach($perHari as $tgl=>$st)
        <tr>
          <td>{{ $tgl }}</td>
          <td>{{ $st['hadir'] ?? 0 }}</td>
          <td>{{ $st['izin']  ?? 0 }}</td>
          <td>{{ $st['sakit'] ?? 0 }}</td>
          <td>{{ $st['alpha'] ?? 0 }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
