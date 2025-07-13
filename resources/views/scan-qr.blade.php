{{-- <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Scan QR</title>
  <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body>
  <div style="text-align: center; padding: 20px;">
    <h2>📷 Arahkan Kamera ke QR Code</h2>
    <div id="reader" style="width: 300px; margin: 0 auto;"></div>
    <div id="result" style="margin-top: 20px; font-weight: bold;"></div>
  </div>

  <script>
    function onScanSuccess(decodedText, decodedResult) {
      document.getElementById('result').innerText = "✅ Terbaca: " + decodedText;
      // TODO: Kirim ke API Laravel jika perlu
    }

    const html5QrCode = new Html5Qrcode("reader");

    Html5Qrcode.getCameras().then(devices => {
      if (devices && devices.length) {
        const cameraId = devices[0].id;
        html5QrCode.start(
          cameraId,
          { fps: 10, qrbox: 250 },
          onScanSuccess
        );
      } else {
        document.getElementById('result').innerText = "❌ Tidak ada kamera ditemukan";
      }
    }).catch(err => {
      document.getElementById('result').innerText = "❌ Gagal membuka kamera: " + err;
    });
  </script>
</body>
</html> --}}
