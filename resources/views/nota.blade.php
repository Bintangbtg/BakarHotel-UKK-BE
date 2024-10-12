<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Pemesanan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            width: 80%;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            text-align: center;
            color: #2c3e50;
        }
        h3 {
            color: #34495e;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #2c3e50;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #2980b9;
            color: #fff;
        }
        tr:nth-child(even) {
            background-color: #ecf0f1;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ $dataNota['nama_hotel'] }}</h1>
        <h2>Nota Pemesanan</h2>
        <p><strong>Tanggal Transaksi:</strong> {{ $dataNota['tgl_transaksi'] }}</p>
        <p><strong>Nama Pemesan:</strong> {{ $dataNota['nama_pemesan'] }}</p>
        <p><strong>Email Pemesan:</strong> {{ $dataNota['email_pemesan'] }}</p>
        <p><strong>Tanggal Check-In:</strong> {{ $dataNota['tgl_check_in'] }}</p>
        <p><strong>Tanggal Check-Out:</strong> {{ $dataNota['tgl_check_out'] }}</p>
        <p><strong>Jumlah Kamar:</strong> {{ $dataNota['jumlah_kamar'] }}</p>

        <h3>Detail Kamar:</h3>
        <table>
            <thead>
                <tr>
                    <th>ID Kamar</th>
                    <th>Tanggal Akses</th>
                    <th>Harga</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dataNota['detail_kamar'] as $detail)
                <tr>
                    <td>{{ $detail['id_kamar'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($detail['tgl_akses'])->format('d-m-Y') }}</td>
                    <td>{{ number_format($detail['harga'], 0, ',', '.') }} IDR</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <p>Terima kasih telah memesan di {{ $dataNota['nama_hotel'] }}. Selamat datang!</p>
        </div>
    </div>
</body>
</html>