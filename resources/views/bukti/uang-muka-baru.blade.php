<html>
<head>
<title>Bukti Uang Muka {{ $nourut }}</title>
<body>	

<!--table header-->
<table width="100%" border="1" cellspacing="0" cellpadding="10" style="border: 1px solid #000;font-size:12px;">
	<thead>
		<tr>
			<th rowspan="2">PERUSAHAAN XYZ</th>
			<th rowspan="2">Pengeluaran Kas/ Bank untuk Uang Muka Kerja</th>
			<th>Dokumen No. : {{$nourut}}</th>
		</tr>
		<tr>
			<th>Tanggal Berlaku : {{$tgdok}}</th>
		</tr>
	</thead>
</table>
<br>
<table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-size:15px;">
	<thead>
		<tr>
			<th colspan="3">PENGELUARAN UANG MUKA KERJA</th>
		</tr>
	</thead>
</table>
<hr>
&nbsp;Sudah terima uang dari Perumda Sarana Jaya :
<br>
<br>
<table width="100%" border="0" cellspacing="0" cellpadding="4" style="border: 0px solid #000;font-size:15px;">
	<tbody>
		<tr>
			<td style="width:30%">Sebesar</td>
			<td style="width:5%">:</td>
			<td style="width:65%; border: 1px solid #000;">{{$nilai}}</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td style="width:30%">Terbilang</td>
			<td style="width:5%">:</td>
			<td style="width:65%; border: 1px solid #000;">{{$sejumlah}}</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td style="width:30%">Untuk Keperluan</td>
			<td style="width:5%">:</td>
			<td style="width:65%; border: 1px solid #000;">{{$uraian}}</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td style="width:30%">Nomor Mata Anggaran</td>
			<td style="width:5%">:</td>
			<td style="width:65%; border: 1px solid #000;">{{$kdakun}} - {{$nmakun}}</td>
		</tr>
		<tr>
			<td style="width:30%"></td>
			<td style="width:5%"></td>
			<td style="width:65%; border: 1px solid #000;">RKAP &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{number_format($pagu)}}</td>
		</tr>
		<tr>
			<td style="width:30%"></td>
			<td style="width:5%"></td>
			<td style="width:65%; border: 1px solid #000;">Realisasi : {{number_format($realisasi)}}</td>
		</tr>
		<tr>
			<td style="width:30%"></td>
			<td style="width:5%"></td>
			<td style="width:65%; border: 1px solid #000;">Sisa &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{number_format($sisa)}}</td>
		</tr>
	</tbody>
</table>
<br>
<br>
<table width="100%" border="0" cellspacing="0" cellpadding="4" style="border: 0px solid #000;font-size:15px;">
	<tbody>
		<tr>
			<td style="width:65%">Uang tersebut, kami pertanggungjawabkan pada tanggal</td>
			<td style="width:5%">:</td>
			<td style="width:35%; border: 1px solid #000;">{{$tgdok1}}</td>
		</tr>
	</tbody>
</table>
<br>
<br>
<table width="100%" border="0" cellspacing="0" cellpadding="4" style="border: 0px solid #000;font-size:15px;text-align:center;">
	<tbody>
		<tr>
			<td style="width:35%"></td>
			<td style="width:35%"></td>
			<td style="width:30%">Jakarta, {{$tgdok}}</td>
		</tr>
		<tr>
			<td style="width:35%">Menyetujui,</td>
			<td style="width:35%">Pemohon,</td>
			<td style="width:30%">Penerima</td>
		</tr>
		<tr>
			<td style="width:35%">SM. Divisi Keuangan dan Akuntansi</td>
			<td style="width:35%">SM. {{$nmunit}}</td>
			<td style="width:30%"></td>
		</tr>
		<tr>
			<td style="width:35%">&nbsp;</td>
			<td style="width:35%">&nbsp;</td>
			<td style="width:30%">&nbsp;</td>
		</tr>
		<tr>
			<td style="width:35%">&nbsp;</td>
			<td style="width:35%">&nbsp;</td>
			<td style="width:30%">&nbsp;</td>
		</tr>
		<tr>
			<td style="width:35%">&nbsp;</td>
			<td style="width:35%">&nbsp;</td>
			<td style="width:30%">&nbsp;</td>
		</tr>
		<tr>
			<td style="width:35%">&nbsp;</td>
			<td style="width:35%">&nbsp;</td>
			<td style="width:30%">&nbsp;</td>
		</tr>
		<tr>
			<td style="width:35%">{{$nama_ttd2}}</td>
			<td style="width:35%">{{$nama_ttd1}}</td>
			<td style="width:30%">&nbsp;</td>
		</tr>
	</tbody>
</table>

</body>
</html>