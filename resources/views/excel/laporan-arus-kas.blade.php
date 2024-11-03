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
            <td colspan="10" style="text-align:left;font-size: 15px;background-color: #FFF;">LAPORAN ARUS KAS PERIODE {{ $params['tgl_pelaporan'] }}</td>
        </tr>
    </tbody>
</table>

<table cellspacing="0" cellpadding="1" style="border: 1px solid #000;font-size:12px; width: 100%;">
    <thead>
        <tr>
            <th style="background-color: #7CB9E8;width: 350px;text-align:center;">Uraian</th>
            <th style="background-color: #7CB9E8;width: 200px;text-align:center;">RKA {{ $params['thang'] }}</th>
            <th style="background-color: #7CB9E8;width: 100px;text-align:center;">REALISASI BULANAN</th>
            <th style="background-color: #7CB9E8;width: 250px;text-align:center;">REALISASI S.D BULAN</th>
        </tr>
    </thead>
    <tbody>

        <tr>
            <td colspan="4">&nbsp;</td>
        </tr>

        <tr>
            <td colspan="4"><b>ARUS KAS DARI AKTIVITAS OPERASI</b></td>
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
            <td>{{ $row->nmsubkategori }}</td>
            <td style="text-align:right;">0</td>
            <td style="text-align:right;">{{ number_format($row->nilai) }}</td>
            <td style="text-align:right;">{{ number_format($row->nilai_sd) }}</td>
        </tr>

        @endforeach

        <tr>
            <td style="background-color: #72A0C1;text-align:center;">Kas Bersih Digunakan Untuk Aktivitas Operasi</td>
            <td style="background-color: #72A0C1;text-align:right;">0</td>
            <td style="background-color: #72A0C1;text-align:right;">{{ number_format($total1) }}</td>
            <td style="background-color: #72A0C1;text-align:right;">{{ number_format($total1_sd) }}</td>
        </tr>

        <tr>
            <td colspan="4">&nbsp;</td>
        </tr>

        <tr>
            <td colspan="4"><b>ARUS KAS DARI AKTIVITAS INVESTASI</b></td>
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
            <td>{{ $row->nmsubkategori }}</td>
            <td style="text-align:right;">0</td>
            <td style="text-align:right;">{{ number_format($row->nilai) }}</td>
            <td style="text-align:right;">{{ number_format($row->nilai_sd) }}</td>
        </tr>

        @endforeach

        <tr>
            <td style="background-color: #72A0C1;text-align:center;">Kas Bersih Digunakan untuk Aktivitas Investasi</td>
            <td style="background-color: #72A0C1;text-align:right;">0</td>
            <td style="background-color: #72A0C1;text-align:right;">{{ number_format($total2) }}</td>
            <td style="background-color: #72A0C1;text-align:right;">{{ number_format($total2_sd) }}</td>
        </tr>

        <tr>
            <td colspan="4">&nbsp;</td>
        </tr>

        <tr>
            <td colspan="4"><b>ARUS KAS DARI AKTIVITAS PENDANAAN</b></td>
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
            <td>{{ $row->nmsubkategori }}</td>
            <td style="text-align:right;">0</td>
            <td style="text-align:right;">{{ number_format($row->nilai) }}</td>
            <td style="text-align:right;">{{ number_format($row->nilai_sd) }}</td>
        </tr>

        @endforeach

        <tr>
            <td style="background-color: #72A0C1;text-align:center;">Kas Bersih Diperolehan dari (Digunakan untuk) Aktivitas Pendanaan</td>
            <td style="background-color: #72A0C1;text-align:right;">0</td>
            <td style="background-color: #72A0C1;text-align:right;">{{ number_format($total3) }}</td>
            <td style="background-color: #72A0C1;text-align:right;">{{ number_format($total3_sd) }}</td>
        </tr>

        <tr>
            <td colspan="4">&nbsp;</td>
        </tr>

        @php
            $selisih = $total1 + $total2 + $total3;
            $selisih_sd = $total1_sd + $total2_sd + $total3_sd;
        @endphp

        <tr>
            <td style="background-color: #6495ED;text-align:center;color: #FFF;">KENAIKAN (PENURUNAN) BERSIH KAS DAN SETARA KAS</td>
            <td style="background-color: #6495ED;text-align:right;color: #FFF;">0</td>
            <td style="background-color: #6495ED;text-align:right;color: #FFF;">
                @if($selisih>0)
                    ({{ number_format(abs($selisih)) }})
                @else
                    {{ number_format(abs($selisih)) }}
                @endif
            </td>
            <td style="background-color: #6495ED;text-align:right;color: #FFF;">
                @if($selisih_sd>0)
                    ({{ number_format(abs($selisih_sd)) }})
                @else
                    {{ number_format(abs($selisih_sd)) }}
                @endif
            </td>
        </tr>
        <tr>
            <td style="background-color: #6495ED;text-align:center;color: #FFF;">KAS DAN SETARA KAS PADA AWAL PERIODE</td>
            <td style="background-color: #6495ED;text-align:right;color: #FFF;">0</td>
            <td style="background-color: #6495ED;text-align:right;color: #FFF;">{{ number_format($rows4->saldo_bulan_lalu) }}</td>
            <td style="background-color: #6495ED;text-align:right;color: #FFF;">{{ number_format($rows4->saldo_awal_tahun) }}</td>
        </tr>
        <tr>
            <td style="background-color: #6495ED;text-align:center;color: #FFF;">KAS DAN SETARA KAS PADA AKHIR PERIODE</td>
            <td style="background-color: #6495ED;text-align:right;color: #FFF;">0</td>
            <td style="background-color: #6495ED;text-align:right;color: #FFF;">
                @if($selisih>0)
                    {{ number_format($rows4->saldo_bulan_lalu - abs($selisih)) }}
                @else
                    {{ number_format($rows4->saldo_bulan_lalu + abs($selisih)) }}
                @endif
            </td>
            <td style="background-color: #6495ED;text-align:right;color: #FFF;">
                @if($selisih_sd>0)
                    {{ number_format($rows4->saldo_awal_tahun - abs($selisih_sd)) }}
                @else
                    {{ number_format($rows4->saldo_awal_tahun + abs($selisih_sd)) }}
                @endif
            </td>
        </tr>

    </tbody>
</table>
</body>
</html>