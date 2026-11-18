<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #252944; }
        h1 { font-size: 17px; margin-bottom: 4px; }
        p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background: #dbeafe; }
        tfoot td { font-weight: bold; background: #f1f5f9; }
    </style>
</head>
<body>
    <h1>Travel Tour — Laporan Transaksi</h1>
    <p>Periode: {{ $dari }} s.d. {{ $sampai }}</p>
    <p>Pemesanan Lunas: {{ $jumlah }} | Total Peserta: {{ $totalPeserta }} orang</p>
    <table>
        <thead>
            <tr>
                <th>No</th><th>Tanggal</th><th>Kode</th><th>Pelanggan</th><th>Paket</th><th>Peserta</th><th>Metode</th><th>Invoice</th><th>Nominal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaksi as $i => $p)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $p->tanggal_bayar?->format('d/m/Y') }}</td>
                    <td>{{ $p->pemesanan?->kode_pemesanan }}</td>
                    <td>{{ $p->pemesanan?->pemesan?->name }}</td>
                    <td>{{ $p->pemesanan?->paket?->nama_paket }}</td>
                    <td>{{ $p->pemesanan?->jumlah_peserta }}</td>
                    <td>{{ $p->metode_pembayaran ?? 'QRIS' }}</td>
                    <td>{{ $p->nomor_invoice }}</td>
                    <td>Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">TOTAL</td>
                <td>{{ $totalPeserta }}</td>
                <td colspan="2"></td>
                <td>Rp {{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>