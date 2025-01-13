<html>
<head>
<title>Bukti Kas Kecil {{ $nourut }}</title>
<body>	

<!--table header-->
<table width="100%" border="0" cellspacing="0" cellpadding="10" style="border: 1px solid #000;font-size:10px;">
	<thead>
		<tr>
			<th>PEMERINTAH DAERAH KHUSUS IBUKOTA JAKARTA<br><u>PERUSAHAAN XYZ</u></th>
			<th>&nbsp;</th>
			<th style="text-align:left;font-size:8px;">Lembar :<br>1. Sub Div Akuntansi<br>2. Arsip</th>
		</tr>
		<tr>
			<th colspan="3">{{$judul}}</th>
		</tr>
		<tr>
			<th colspan="3">NO. {{$nourut}}, TGL. {{$tgdok}}</th>
		</tr>
	</thead>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="5" style="border: 1px solid #000;font-size:10px;">
	<tbody>
		<tr>
			<td rowspan="3" style="text-align:center;">BENDAHARAWAN / KASIR HARAP {{$untuk}}</td>
			<td style="text-align:right">Bank</td>
			<td style="text-align:left;width:150px;">{{$nmbank}}</td>
		</tr>
		<tr>
			<td style="text-align:right">No.Rek.</td>
			<td>{{$norek}}</td>
		</tr>
		<tr>
			<td style="text-align:right">Rp.</td>
			<td style="text-align:right;">{{$nilai}},-</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td>{{$terdiri1}}</td>
			<td style="text-align:right;"></td>
			<td style="text-align:right;width:150px;"></td>
		</tr>
		<tr>
			<td>1. {{$nodok}}</td>
			<td style="text-align:right;">Rp.</td>
			<td style="text-align:right;width:150px;">{{$nilai}},-</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td style="text-align:right;"></td>
			<td style="text-align:right;width:150px;"></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td style="text-align:right;"></td>
			<td style="text-align:right;width:150px;"></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td style="text-align:right;"></td>
			<td style="text-align:right;width:150px;"></td>
		</tr>
		<tr>
			<td style="text-align:right;">JUMLAH</td>
			<td style="text-align:right;">Rp.</td>
			<td style="text-align:right;width:150px;border-top: 1px solid #000;">{{$nilai}}</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td style="text-align:right;"></td>
			<td style="text-align:right;width:150px;"></td>
		</tr>
		<tr>
			<td colspan="3" style="text-align:left;font-size:14px;">Terbilang : {{$sejumlah}}</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td style="text-align:right;"></td>
			<td style="text-align:right;width:150px;"></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td style="text-align:right;"></td>
			<td style="text-align:right;width:150px;"></td>
		</tr>
	</tbody>
</table>
<br>
<table width="100%" border="0" cellspacing="0" cellpadding="4" style="border: 0px solid #000;font-size:11px;text-align:center;">
	<tbody>
		<tr>
			<td style="width:35%"></td>
			<td style="width:30%"></td>
			<td style="width:35%">Jakarta, {{$tgdok}}</td>
		</tr>
		<tr>
			<td style="width:35%">Mengetahui,</td>
			<td style="width:30%"></td>
			<td style="width:35%"></td>
		</tr>
		<tr>
			<td style="width:35%">Direktur Utama</td>
			<td style="width:30%"></td>
			<td style="width:35%">SM. Divisi Keuangan dan Akuntansi</td>
		</tr>
		<tr>
			<td style="width:35%">&nbsp;</td>
			<td style="width:30%">&nbsp;</td>
			<td style="width:35%">&nbsp;</td>
		</tr>
		<tr>
			<td style="width:35%">&nbsp;</td>
			<td style="width:30%">&nbsp;</td>
			<td style="width:35%">&nbsp;</td>
		</tr>
		<tr>
			<td style="width:35%">&nbsp;</td>
			<td style="width:30%">&nbsp;</td>
			<td style="width:35%">&nbsp;</td>
		</tr>
		<tr>
			<td style="width:35%">&nbsp;</td>
			<td style="width:30%">&nbsp;</td>
			<td style="width:35%">&nbsp;</td>
		</tr>
		<tr>
			<td style="width:35%">{{$nama_ttd2}}</td>
			<td style="width:30%"></td>
			<td style="width:35%">{{$nama_ttd1}}</td>
		</tr>
	</tbody>
</table>

</body>
</html>