<html>
<head>
<style>
    
</style>
<body>
<table width="100%" cellspacing="0" cellpadding="1" style="border: 1px solid #000;font-size:12px; width: 100%;">
    <tbody>
        <tr>
            <td colspan="10" style="text-align:left;font-size: 15px;background-color: #FFF;">PERUMDA PEMBANGUNAN SARANA JAYA</td>
        </tr>
        <tr>
            <td colspan="10" style="text-align:left;font-size: 15px;background-color: #FFF;">LAPORAN POSISI KEUANGAN PERIODE {{ $params['tgl_pelaporan'] }}</td>
        </tr>
    </tbody>
</table>

<table cellspacing="0" cellpadding="1" style="border: 1px solid #000;font-size:12px; width: 100%;">
    <thead>
        <tr>
            <th style="background-color: #7CB9E8;width: 100px;text-align:center;">No. COA</th>
            <th style="background-color: #7CB9E8;width: 350px;text-align:center;">Uraian</th>
            <th style="background-color: #7CB9E8;width: 200px;text-align:center;">RKA {{ $params['thang'] }}</th>
            <th style="background-color: #7CB9E8;width: 100px;text-align:center;">REALISASI BULANAN</th>
            <th style="background-color: #7CB9E8;width: 250px;text-align:center;">REALISASI S.D BULAN</th>
        </tr>
    </thead>
    <tbody>

        <tr>
            <td colspan="5">&nbsp;</td>
        </tr>

        @php
            $total1 = 0;
            $total1_sd = 0;
        @endphp
        
        @foreach($rows1 as $row)
        
        @php
            $total1 += $row->nilai;
            $total1_sd += $row->nilai_sd;
        @endphp

        <tr>
            <td>{{ $row->kdakun }}</td>
            <td>{{ $row->nmakun }}</td>
            <td style="text-align:right;">0</td>
            <td style="text-align:right;">{{ number_format($row->nilai) }}</td>
            <td style="text-align:right;">{{ number_format($row->nilai_sd) }}</td>
        </tr>

        @endforeach

        <tr>
            <td style="background-color: #72A0C1;text-align:center;" colspan="2">SUB TOTAL ASET LANCAR</td>
            <td style="background-color: #72A0C1;text-align:right;">0</td>
            <td style="background-color: #72A0C1;text-align:right;">{{ number_format($total1) }}</td>
            <td style="background-color: #72A0C1;text-align:right;">{{ number_format($total1_sd) }}</td>
        </tr>

        <tr>
            <td colspan="5">&nbsp;</td>
        </tr>

        @php
            $total2 = 0;
            $total2_sd = 0;
        @endphp
        
        @foreach($rows2 as $row)
        
        @php
            $total2 += $row->nilai;
            $total2_sd += $row->nilai_sd;
        @endphp

        <tr>
            <td>{{ $row->kdakun }}</td>
            <td>{{ $row->nmakun }}</td>
            <td style="text-align:right;">0</td>
            <td style="text-align:right;">{{ number_format($row->nilai) }}</td>
            <td style="text-align:right;">{{ number_format($row->nilai_sd) }}</td>
        </tr>

        @endforeach

        <tr>
            <td style="background-color: #72A0C1;text-align:center;" colspan="2">SUB TOTAL ASET TIDAK LANCAR</td>
            <td style="background-color: #72A0C1;text-align:right;">0</td>
            <td style="background-color: #72A0C1;text-align:right;">{{ number_format($total2) }}</td>
            <td style="background-color: #72A0C1;text-align:right;">{{ number_format($total2_sd) }}</td>
        </tr>
        <tr>
            <td style="background-color: #72A0C1;text-align:center;" colspan="2">TOTAL ASET</td>
            <td style="background-color: #72A0C1;text-align:right;">0</td>
            <td style="background-color: #72A0C1;text-align:right;">{{ number_format($total1 + $total2) }}</td>
            <td style="background-color: #72A0C1;text-align:right;">{{ number_format($total1_sd + $total2_sd) }}</td>
        </tr>

        <tr>
            <td colspan="5">&nbsp;</td>
        </tr>

        @php
            $total3 = 0;
            $total3_sd = 0;
        @endphp
        
        @foreach($rows3 as $row)
        
        @php
            $total3 += $row->nilai;
            $total3_sd += $row->nilai_sd;
        @endphp

        <tr>
            <td>{{ $row->kdakun }}</td>
            <td>{{ $row->nmakun }}</td>
            <td style="text-align:right;">0</td>
            <td style="text-align:right;">{{ number_format($row->nilai) }}</td>
            <td style="text-align:right;">{{ number_format($row->nilai_sd) }}</td>
        </tr>

        @endforeach

        <tr>
            <td style="background-color: #72A0C1;text-align:center;" colspan="2">SUB TOTAL LIABILITAS LANCAR</td>
            <td style="background-color: #72A0C1;text-align:right;">0</td>
            <td style="background-color: #72A0C1;text-align:right;">{{ number_format($total3) }}</td>
            <td style="background-color: #72A0C1;text-align:right;">{{ number_format($total3_sd) }}</td>
        </tr>

        <tr>
            <td colspan="5">&nbsp;</td>
        </tr>

        @php
            $total4 = 0;
            $total4_sd = 0;
        @endphp
        
        @foreach($rows4 as $row)
        
        @php
            $total4 += $row->nilai;
            $total4_sd += $row->nilai_sd;
        @endphp

        <tr>
            <td>{{ $row->kdakun }}</td>
            <td>{{ $row->nmakun }}</td>
            <td style="text-align:right;">0</td>
            <td style="text-align:right;">{{ number_format($row->nilai) }}</td>
            <td style="text-align:right;">{{ number_format($row->nilai_sd) }}</td>
        </tr>

        @endforeach

        <tr>
            <td style="background-color: #72A0C1;text-align:center;" colspan="2">SUB TOTAL LIABILITAS TIDAK LANCAR</td>
            <td style="background-color: #72A0C1;text-align:right;">0</td>
            <td style="background-color: #72A0C1;text-align:right;">{{ number_format($total4) }}</td>
            <td style="background-color: #72A0C1;text-align:right;">{{ number_format($total4_sd) }}</td>
        </tr>
        
        <tr>
            <td colspan="5">&nbsp;</td>
        </tr>

        @php
            $total5 = 0;
            $total5_sd = 0;
        @endphp
        
        @foreach($rows5 as $row)
        
        @php
            $nilai = $row->nilai;
            $nilai_sd = $row->nilai_sd;

            $total5 += $nilai;
            $total5_sd += $nilai_sd;
        @endphp

        <tr>
            <td>{{ $row->kdakun }}</td>
            <td>{{ $row->nmakun }}</td>
            <td style="text-align:right;">0</td>
            <td style="text-align:right;">{{ number_format($nilai) }}</td>
            <td style="text-align:right;">{{ number_format($nilai_sd) }}</td>
        </tr>

        @endforeach

        <tr>
            <td style="background-color: #72A0C1;text-align:center;" colspan="2">SUB TOTAL EKUITAS</td>
            <td style="background-color: #72A0C1;text-align:right;">0</td>
            <td style="background-color: #72A0C1;text-align:right;">{{ number_format($total5) }}</td>
            <td style="background-color: #72A0C1;text-align:right;">{{ number_format($total5_sd) }}</td>
        </tr>
        <tr>
            <td style="background-color: #72A0C1;text-align:center;" colspan="2">TOTAL LIABILITAS DAN EKUITAS</td>
            <td style="background-color: #72A0C1;text-align:right;">0</td>
            <td style="background-color: #72A0C1;text-align:right;">{{ number_format($total3 + $total4 + $total5) }}</td>
            <td style="background-color: #72A0C1;text-align:right;">{{ number_format($total3_sd + $total4_sd + $total5_sd) }}</td>
        </tr>

    </tbody>
</table>
</body>
</html>