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
