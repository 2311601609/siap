<html>
<head>
<title>Bukti Uang Masuk {{ $nourut }}</title>
<body>	

<!--table header-->
<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border: 1px solid #000;font-size:12px;">
	<thead>
		<tr>
			<th colspan="3">&nbsp;</th>
		</tr>
		<tr>
			<th colspan="3"><h3>PERUSAHAAN XYZ<br/>PEMERINTAH DAERAH KHUSUS IBUKOTA JAKARTA</h3></th>
		</tr>
		<tr>
			<th colspan="3">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="wd10 pl3 vt" style="width:20%;">Nomor</td>
			<td class="wd2 vt" style="width:2%;">:</td>
			<td class="pl3 vt">{{$nourut}}</td>
		</tr>
		<tr>
			<td class="pl3 vt">Tahun</td>
			<td class=" t">:</td>
			<td class="pl3 vt">{{$thang}}</td>
		</tr>
		<tr>
			<td class="pl3 vt">No. Bukti Masuk</td>
			<td class="vt">:</td>
			<td class="pl3 vt">{{$nodok}}</td>
		</tr>
	</tbody>
	<thead>
		<tr>
			<th colspan="3"><h3>BUKTI BANK MASUK</h3></th>
		</tr>
		<tr>
			<th colspan="3">&nbsp;</th>
		</tr>
	</thead>
</table>

<!--table content-->
<table width="100%" border="0" cellspacing="0" cellpadding="3" style="border: 1px solid #000;font-size:11px;">
	<tbody>
		<tr>
			<td class="wd15 pl3 vt">Tahun Anggaran</td>
			<td class="wd5 ar vt">:</td>
			<td class="wd15 pl3 vt" style="border-right: 1px solid #000;">{{$thang}}</td>
			<td colspan="4" class="bl pl3">&nbsp;</td>
		</tr>
		<tr>
			<td class="pl3 vt">Otorisasi Penerimaan</td>
			<td class="ar vt">:</td>
			<td class="pl3 vt" style="border-right: 1px solid #000;">...........</td>
			<td colspan="4" class="bl pl3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" style="border-right: 1px solid #000;">&nbsp;</td>
			<td rowspan="2" class="wd15 bl pl3 vt">Dari</td>
			<td rowspan="2" class="ar vt">:</td>
			<td rowspan="2" colspan="2" class="pl3 vt">{{$nmpenerima}}</td>
		</tr>
		<tr>
			<td colspan="3" style="border-right: 1px solid #000;">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3" style="border-right: 1px solid #000;"><u>Kode Mata Anggaran Penerimaan :</u></td>
			<td rowspan="3" class="bl pl3 vt">Uang Sejumlah</td>
			<td rowspan="3" class="wd2 ar vt">:</td>
			<td rowspan="3" colspan="2" class="pl3 vt" style="border: 1px solid #000;">{{$sejumlah}}</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3" style="border-right: 1px solid #000;">Pendapatan</td>
		</tr>
		<tr>
			<td colspan="3" style="border-right: 1px solid #000;">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" style="border-right: 1px solid #000;">&nbsp;</td>
			<td colspan="4" class="bl">&nbsp;</td>
		</tr>
		<tr>
			<td class="pl3">{{$kdakun}}</td>
			<td class="pl3">Rp</td>
			<td class="pl3 ar" style="text-align:right;border-right: 1px solid #000;">{{$nilai_bersih}}</td>
			<td rowspan="4" class="bl pl3 vt">Untuk</td>
			<td rowspan="4" class="pl3 ar vt">:</td>
			<td rowspan="4" colspan="2" class="pl3 vt">{{$uraian}}</td>
		</tr>
		<tr>
			<td class="pl3">....................</td>
			<td class="vt">Rp</td>
			<td class="pl3 ar" style="border-right: 1px solid #000;">....................</td>
		</tr>
		<tr>
			<td class="pl3">....................</td>
			<td class="vt">Rp</td>
			<td class="pl3 ar" style="border-right: 1px solid #000;">....................</td>
		</tr>
		<tr>
			<td class="pl3">....................</td>
			<td class="vt">Rp</td>
			<td class="pl3 ar" style="border-right: 1px solid #000;">....................</td>
		</tr>
		<tr>
			<td class="pl3">....................</td>
			<td class="vt">Rp</td>
			<td class="pl3 ar" style="border-right: 1px solid #000;">....................</td>
			<td rowspan="4" class="bl pl3 vt">Dasar Penerimaan</td>
			<td rowspan="4" class="pl3 ar vt">:</td>
			<td rowspan="4" colspan="2" class="pl3 vt">Terlampir</td>
		</tr>
		<tr>
			<td class="pl3 vt">....................</td>
			<td class="vt">Rp</td>
			<td class="pl3 ar" style="border-right: 1px solid #000;">....................</td>
		</tr>
		<tr>
			<td class="pl3 vt">....................</td>
			<td class="vt">Rp</td>
			<td class="pl3 ar" style="border-right: 1px solid #000;">....................</td>
		</tr>
		<tr>
			<td class="pl3 vt">....................</td>
			<td class="vt">Rp</td>
			<td class="pl3 ar" style="border-right: 1px solid #000;">....................</td>
		</tr>
		<tr>
			<td colspan="3" style="border-right: 1px solid #000;">&nbsp;</td>
			<td colspan="4" class="bl">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" rowspan="2" class="pl3 vt" style="border-right: 1px solid #000;"><u>Kode Mata Anggaran Pengembalian :</u></td>
			<td colspan="3" class="bl pl3 ac"></td>
			<td colspan="1" class="pl3">&nbsp;</td>
		</tr>
		<tr>
			<td class="bl pl3">Bank</td>
			<td class="pl3 ar vt">:</td>
			<td colspan="2" class="pl3">{{$bank}}</td>
		</tr>
		<tr>
			<td class="pl3 vt">{{$kdakun_pajak1}}</td>
			<td class="vt">Rp</td>
			<td class="pl3 ar vt" style="text-align:right;border-right: 1px solid #000;">{{$nilai_pajak1}}</td>
			<td class="bl pl3 vt">No. Rek</td>
			<td class="ar vt">:</td>
			<td colspan="2" class="pl3">{{$norek}}</td>
		</tr>
		<tr>
			<td class="pl3">{{$kdakun_pajak2}}</td>
			<td class="pl3">Rp</td>
			<td class="pl3 ar" style="text-align:right;border-right: 1px solid #000;">{{$nilai_pajak2}}</td>
			<td class="bl pl3">No. Cek</td>
			<td class="ar vt">:</td>
			<td class="vt">{{$nocek}}</td>
			<td class="pl3 al">&nbsp;</td>
		</tr>
		<tr>
			<td class="pl3">{{$kdakun_pajak3}}</td>
			<td class="pl3">Rp</td>
			<td class="pl3 ar" style="text-align:right;border-right: 1px solid #000;">{{$nilai_pajak3}}</td>
			<td colspan="3" class="bl pl3">&nbsp;</td>
			<td class="pl3 al"></td>
		</tr>
		<tr>
			<td class="pl3">{{$kdakun_pajak4}}</td>
			<td class="pl3">Rp</td>
			<td class="pl3 ar" style="text-align:right;border-right: 1px solid #000;">{{$nilai_pajak4}}</td>
			<td colspan="3" class="bl pl3 ac"></td>
			<td class="pl3 ac" style="text-align:center;"></td>
		</tr>
		<tr>
			<td colspan="3" class="pl3" style="border-right: 1px solid #000;">&nbsp;</td>
			<td class="bl pl3 vt"></td>
			<td class="ar vt"></td>
			<td class="pl3 vt"></td>
			<td rowspan="4" class="pl3 ac vt" style="text-align:center;"></td>
		</tr>
		<tr>
			<td rowspan="2" class="pl3 vt">Jumlah yang harus diterima</td>
			<td rowspan="2" class="pl3 vt">Rp</td>
			<td rowspan="2" class="pl3 ar vt" style="border-right: 1px solid #000;text-align:right;">{{$nilai}}</td>
			<td class="bl pl3 vt"></td>
			<td class="ar vt"></td>
			<td class="pl3 vt"></td>
		</tr>
		<tr>
			<td class="bl pl3 vt"></td>
			<td class="ar vt"></td>
			<td class="pl3"></td>
		</tr>
		<tr>
			<td colspan="3" class="pl3" style="border-right: 1px solid #000;">&nbsp;</td>
			<td class="bl pl3"></td>
			<td class="ar vt"></td>
			<td class="pl3 vt"></td>
		</tr>
	</tbody>
</table>
<!--table content-->
<table width="100%" border="0" cellspacing="0" cellpadding="3" style="border: 1px solid #000;font-size:11px;">
	<tbody>
		<tr>
			<td colspan="3" class="pl3" style="border-top: 1px solid #000;text-align:center;"></td>
			<td colspan="2" class="bl pl3" style="border-top: 1px solid #000;">&nbsp;</td>
			<td class="pl3" style="border-top: 1px solid #000;">&nbsp;</td>
			<td class="pl3" style="text-align:center;border-top: 1px solid #000;">Jakarta, {{$tgdok}}</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3 ac vt" style="text-align:center;">Senior Manajer Divisi Keuangan & Akuntansi</td>
			<td colspan="2" class="bl pl3">&nbsp;</td>
			<td class="pl3">&nbsp;</td>
			<td class="pl3 ac vt" style="text-align:center;">Junior Manajer Perbendaharaan & Perpajakan</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3 ac vt" style="text-align:center;"></td>
			<td colspan="2" class="bl pl3">&nbsp;</td>
			<td class="pl3">&nbsp;</td>
			<td class="pl3 ac vt" style="text-align:center;"></td>
		</tr>
		<tr>
			<td colspan="3" class="pl3 ac vt" style="text-align:center;"></td>
			<td colspan="2" class="bl pl3">&nbsp;</td>
			<td class="pl3">&nbsp;</td>
			<td class="pl3 ac vt" style="text-align:center;"></td>
		</tr>
		<tr>
			<td colspan="3" class="pl3 ac vt" style="text-align:center;"></td>
			<td colspan="2" class="bl pl3">&nbsp;</td>
			<td class="pl3">&nbsp;</td>
			<td class="pl3 ac vt" style="text-align:center;"></td>
		</tr>
		<tr>
			<td colspan="3" class="pl3 ac vt" style="text-align:center;"></td>
			<td colspan="2" class="bl pl3">&nbsp;</td>
			<td class="pl3">&nbsp;</td>
			<td class="pl3 ac vt" style="text-align:center;"></td>
		</tr>
		<tr>
			<td colspan="3" class="pl3 ac" style="">&nbsp;</td>
			<td colspan="2" class="bl pl3">&nbsp;</td>
			<td class="pl3 ac">Telah diperiksa dan dibukukan dalam Buku Bank Masuk dengan nomor: <br>{{$nobuku}}</td>
			<td class="pl3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3 ac" style="text-align:center;">({{$nama_ttd2}})</td>
			<td rowspan="2" colspan="3" class="bl vm"></td>
			<td class="pl3 ac vt" style="text-align:center;">({{$nama_ttd1}})</td>
		</tr>
	</tbody>
</table>
</body>
</html>