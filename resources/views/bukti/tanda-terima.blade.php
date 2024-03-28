<html>
<head>
<title>Bukti Tanda Terima {{ $nourut }}</title>
<body>	

<!--table header-->
<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border: 1px solid #000;font-size:12px;">
	<thead>
		<tr>
			<th colspan="3"><h3>TANDA TERIMA & CHECKLIST KELENGKAPAN TA.{{$thang}}</h3></th>
		</tr>
		<tr>
			<th colspan="3"><h3>BUKTI UANG KELUAR {{$nourut}}</h3></th>
		</tr>
		<tr>
			<th colspan="3"><h3>{{$nmunit}}</h3></th>
		</tr>
	</thead>
</table>
<!--table content-->
<table width="100%" border="1" cellspacing="0" cellpadding="3" style="border: 1px solid #000;font-size:11px;">
	<thead>
		<tr>
			<th colspan="2" rowspan="2">Uraian</th>
			<th rowspan="2">File/ Softcopy</th>
			<th colspan="2">Lampiran Fisik</th>
			<th rowspan="2">Keterangan</th>
		</tr>
		<tr>
			<th>Ada</th>
			<th>Tidak</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="wd15 pl3 vt" colspan="6">{{$nmtrans}}</td>
		</tr>
		{!!$detil!!}
		
	</tbody>
</table>
<br>
<br>
<table width="100%" border="0" cellspacing="0" cellpadding="3" style="border: none;font-size:11px;">
	<tbody>
		<tr>
			<td class="wd15 pl3 vt" style="text-align:center;">{{$nmunit}}</td>
			<td class="wd15 pl3 vt" style="text-align:center;">Petugas Verifikasi</td>
		</tr>
		<tr>
			<td class="wd15 pl3 vt" style="text-align:center;">&nbsp;</td>
			<td class="wd15 pl3 vt" style="text-align:center;">&nbsp;</td>
		</tr>
		<tr>
			<td class="wd15 pl3 vt" style="text-align:center;">&nbsp;</td>
			<td class="wd15 pl3 vt" style="text-align:center;">&nbsp;</td>
		</tr>
		<tr>
			<td class="wd15 pl3 vt" style="text-align:center;">&nbsp;</td>
			<td class="wd15 pl3 vt" style="text-align:center;">&nbsp;</td>
		</tr>
		<tr>
			<td class="wd15 pl3 vt" style="text-align:center;"></td>
			<td class="wd15 pl3 vt" style="text-align:center;"></td>
		</tr>
		<tr>
			<td class="wd15 pl3 vt" style="text-align:center;"></td>
			<td class="wd15 pl3 vt" style="text-align:center;"></td>
		</tr>
		<tr>
			<td class="wd15 pl3 vt" style="text-align:center;">{{$nmrekam}}</td>
			<td class="wd15 pl3 vt" style="text-align:center;">{{$nmverifikasi}}</td>
		</tr>
	</tbody>
</table>

</body>
</html>