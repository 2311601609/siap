<html>
<head>
<style>
    
</style>
<body>
<table width="100%" cellspacing="0" cellpadding="1" style="border: 1px solid #000;font-size:12px; width: 100%;">
    <tbody>
        <tr>
            <td colspan="13" style="text-align:center;font-size: 20px;background-color: #FFD700;">PERUMDA PEMBANGUNAN SARANA JAYA</td>
        </tr>
        <tr>
            <td colspan="13" style="text-align:center;font-size: 20px;background-color: #FFD700;">Laporan Buku Besar</td>
        </tr>
        <tr>
            <td colspan="13" style="text-align:center;font-size: 20px;background-color: #FFD700;">Tahun Anggaran {{ $params['thang'] }}</td>
        </tr>
    </tbody>
</table>

<table cellspacing="0" cellpadding="1" style="border: 1px solid #000;font-size:12px; width: 100%;">
    <thead>
        <tr>
            <th style="background-color: #7FFF00;width: 100px;text-align:center;">Kode Akun/ COA</th>
            <th style="background-color: #7FFF00;width: 350px;text-align:center;">Nama Akun/ COA</th>
            <th style="background-color: #7FFF00;width: 200px;text-align:center;">Jenis</th>
            <th style="background-color: #7FFF00;width: 100px;text-align:center;">Tanggal</th>
            <th style="background-color: #7FFF00;width: 250px;text-align:center;">Unit</th>
            <th style="background-color: #7FFF00;width: 250px;text-align:center;">Proyek</th>
            <th style="background-color: #7FFF00;width: 150px;text-align:center;">Bukti</th>
            <th style="background-color: #7FFF00;width: 150px;text-align:center;">No. Voucher</th>
            <th style="background-color: #7FFF00;width: 200px;text-align:center;">Penerima</th>
            <th style="background-color: #7FFF00;width: 200px;text-align:center;">Remark</th>
            <th style="background-color: #7FFF00;width: 150px;text-align:center;">Debet</th>
            <th style="background-color: #7FFF00;width: 150px;text-align:center;">Kredit</th>
            <th style="background-color: #7FFF00;width: 100px;text-align:center;">Saldo</th>
        </tr>
    </thead>
    <tbody>
        @foreach($values as $value)
        
        <tr>
            <td>{{ $value['kdakun'] }}</td>
            <td>{{ $value['nmakun'] }}</td>
            <td>{{ $value['jenis'] }}</td>
            <td>{{ $value['tgdok'] }}</td>
            <td>{{ $value['unit'] }}</td>
            <td>{{ $value['proyek'] }}</td>
            <td>{{ $value['nodok'] }}</td>
            <td>{{ $value['novoucher'] }}</td>
            <td>{{ $value['penerima'] }}</td>
            <td>{{ $value['uraian'] }}</td>
            <td>{{ $value['debet'] }}</td>
            <td>{{ $value['kredit'] }}</td>
            <td>{{ $value['saldo'] }}</td>
        </tr>

        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="10" style="text-align:center;font-size: 15px;background-color: #FFD700;">Total</td>
            <td style="text-align:right;font-size: 15px;background-color: #FFD700;">{{ $params['debet'] }}</td>
            <td style="text-align:right;font-size: 15px;background-color: #FFD700;">{{ $params['kredit'] }}</td>
            <td style="text-align:right;font-size: 15px;background-color: #FFD700;"></td>
        </tr>
    </tfoot>
</table>
</body>
</html>