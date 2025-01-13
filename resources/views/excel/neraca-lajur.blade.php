<html>
<head>
<style>
    
</style>
<body>
<table width="100%" cellspacing="0" cellpadding="1" style="border: 1px solid #000;font-size:12px; width: 100%;">
    <tbody>
        <tr>
            <td colspan="10" style="text-align:left;font-size: 15px;background-color: #FFF;">PERUSAHAAN XYZ</td>
        </tr>
        <tr>
            <td colspan="10" style="text-align:left;font-size: 15px;background-color: #FFF;">TRIAL BALANCE PERIODE 01 JANUARI - {{ $params['tgl_pelaporan'] }}</td>
        </tr>
    </tbody>
</table>

<table cellspacing="0" cellpadding="1" style="border: 1px solid #000;font-size:12px; width: 100%;">
    <thead>
        <tr>
            <th rowspan="2" style="background-color: #7CB9E8;width: 100px;text-align:center;">No. COA</th>
            <th rowspan="2" style="background-color: #7CB9E8;width: 350px;text-align:center;">Uraian</th>
            <th colspan="2" style="background-color: #7CB9E8;width: 200px;text-align:center;">SALDO AWAL</th>
            <th colspan="2" style="background-color: #7CB9E8;width: 100px;text-align:center;">ADJUSMENT</th>
            <th colspan="2" style="background-color: #7CB9E8;width: 250px;text-align:center;">LABA (RUGI)</th>
            <th colspan="2" style="background-color: #7CB9E8;width: 250px;text-align:center;">NERACA</th>
        </tr>
        <tr>
            <th style="background-color: #7CB9E8;width: 100px;text-align:center;">Debet</th>
            <th style="background-color: #7CB9E8;width: 100px;text-align:center;">Kredit</th>
            <th style="background-color: #7CB9E8;width: 100px;text-align:center;">Debet</th>
            <th style="background-color: #7CB9E8;width: 100px;text-align:center;">Kredit</th>
            <th style="background-color: #7CB9E8;width: 100px;text-align:center;">Debet</th>
            <th style="background-color: #7CB9E8;width: 100px;text-align:center;">Kredit</th>
            <th style="background-color: #7CB9E8;width: 100px;text-align:center;">Debet</th>
            <th style="background-color: #7CB9E8;width: 100px;text-align:center;">Kredit</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
        
        <tr>
            <td>{{ $row->kdakun }}</td>
            <td>{{ $row->nmakun }}</td>
            <td style="text-align:right;">{{ number_format($row->sawal_debet) }}</td>
            <td style="text-align:right;">{{ number_format($row->sawal_kredit) }}</td>
            <td style="text-align:right;">{{ number_format($row->mutasi_debet) }}</td>
            <td style="text-align:right;">{{ number_format($row->mutasi_kredit) }}</td>
            <td style="text-align:right;">{{ number_format($row->lr_debet) }}</td>
            <td style="text-align:right;">{{ number_format($row->lr_kredit) }}</td>
            <td style="text-align:right;">{{ number_format($row->nr_debet) }}</td>
            <td style="text-align:right;">{{ number_format($row->nr_kredit) }}</td>
        </tr>

        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2" style="text-align:center;font-size: 15px;background-color: #72A0C1;">Total</td>
            <td style="text-align:right;background-color: #72A0C1;">{{ number_format($totals->sawal_debet) }}</td>
            <td style="text-align:right;background-color: #72A0C1;">{{ number_format($totals->sawal_kredit) }}</td>
            <td style="text-align:right;background-color: #72A0C1;">{{ number_format($totals->mutasi_debet) }}</td>
            <td style="text-align:right;background-color: #72A0C1;">{{ number_format($totals->mutasi_kredit) }}</td>
            <td style="text-align:right;background-color: #72A0C1;">{{ number_format($totals->lr_debet) }}</td>
            <td style="text-align:right;background-color: #72A0C1;">{{ number_format($totals->lr_kredit) }}</td>
            <td style="text-align:right;background-color: #72A0C1;">{{ number_format($totals->nr_debet) }}</td>
            <td style="text-align:right;background-color: #72A0C1;">{{ number_format($totals->nr_kredit) }}</td>
        </tr>
        <tr>
            <td colspan="6" style="text-align:center;font-size: 15px;background-color: #72A0C1;">Ikhtisar Laba (Rugi)</td>
            <td style="text-align:right;background-color: #72A0C1;">{{ number_format($totals->lr_sisa_debet) }}</td>
            <td style="text-align:right;background-color: #72A0C1;">{{ number_format($totals->lr_sisa_kredit) }}</td>
            <td style="text-align:right;background-color: #72A0C1;">{{ number_format($totals->nr_sisa_debet) }}</td>
            <td style="text-align:right;background-color: #72A0C1;">{{ number_format($totals->nr_sisa_kredit) }}</td>
        </tr>
        <tr>
            <td colspan="6" style="text-align:center;font-size: 15px;background-color: #72A0C1;">Total Ikhtisar Laba (Rugi)</td>
            <td style="text-align:right;background-color: #72A0C1;">{{ number_format($totals->lr_sisa1_debet) }}</td>
            <td style="text-align:right;background-color: #72A0C1;">{{ number_format($totals->lr_sisa1_kredit) }}</td>
            <td style="text-align:right;background-color: #72A0C1;">{{ number_format($totals->nr_sisa1_debet) }}</td>
            <td style="text-align:right;background-color: #72A0C1;">{{ number_format($totals->nr_sisa1_kredit) }}</td>
        </tr>
    </tfoot>
</table>
</body>
</html>