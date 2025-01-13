<html>
<head>
<title>Bukti Uang Keluar {{ $nourut }}</title>
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
			<td class="pl3 vt">No. Bukti Tagihan</td>
			<td class="vt">:</td>
			<td class="pl3 vt">{{$nodok}}</td>
		</tr>
	</tbody>
	<thead>
		<tr>
			<th colspan="3"><h3>BUKTI UANG KELUAR</h3></th>
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
			<td class="pl3 vt">Otorisasi Pengeluaran</td>
			<td class="ar vt">:</td>
			<td class="pl3 vt" style="border-right: 1px solid #000;">...........</td>
			<td colspan="4" class="bl pl3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" style="border-right: 1px solid #000;">&nbsp;</td>
			<td rowspan="2" class="wd15 bl pl3 vt">Kepada</td>
			<td rowspan="2" class="ar vt">:</td>
			<td rowspan="2" colspan="2" class="pl3 vt">{{$nmpenerima}}</td>
		</tr>
		<tr>
			<td colspan="3" style="border-right: 1px solid #000;">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3" style="border-right: 1px solid #000;"><u>Kode Mata Anggaran Pengeluaran :</u></td>
			<td rowspan="3" class="bl pl3 vt">Uang Sejumlah</td>
			<td rowspan="3" class="wd2 ar vt">:</td>
			<td rowspan="3" colspan="2" class="pl3 vt" style="border: 1px solid #000;">{{$sejumlah}}</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3" style="border-right: 1px solid #000;">Beban Anggaran Belanja</td>
		</tr>
		<tr>
			<td colspan="3" style="border-right: 1px solid #000;">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" style="border-right: 1px solid #000;">&nbsp;</td>
			<td colspan="4" class="bl">&nbsp;</td>
		</tr>
		<tr>
			<td class="pl3">{{$buk[0]['kdakun']}}</td>
			<td class="pl3">Rp</td>
			<td class="pl3 ar" style="text-align:right;border-right: 1px solid #000;">{{$buk[0]['nilai']}}</td>
			<td rowspan="4" class="bl pl3 vt">Untuk</td>
			<td rowspan="4" class="pl3 ar vt">:</td>
			<td rowspan="4" colspan="2" class="pl3 vt">{{$uraian}}</td>
		</tr>
		<tr>
			<td class="pl3">{{$buk[1]['kdakun']}}</td>
			<td class="vt">Rp</td>
			<td class="pl3 ar" style="text-align:right;border-right: 1px solid #000;">{{$buk[1]['nilai']}}</td>
		</tr>
		<tr>
			<td class="pl3">{{$buk[2]['kdakun']}}</td>
			<td class="vt">Rp</td>
			<td class="pl3 ar" style="text-align:right;border-right: 1px solid #000;">{{$buk[2]['nilai']}}</td>
		</tr>
		<tr>
			<td class="pl3">{{$buk[3]['kdakun']}}</td>
			<td class="vt">Rp</td>
			<td class="pl3 ar" style="text-align:right;border-right: 1px solid #000;">{{$buk[3]['nilai']}}</td>
		</tr>
		<tr>
			<td class="pl3">{{$buk[4]['kdakun']}}</td>
			<td class="vt">Rp</td>
			<td class="pl3 ar" style="text-align:right;border-right: 1px solid #000;">{{$buk[4]['nilai']}}</td>
			<td class="bl pl3 vt">Dasar Pembayaran</td>
			<td class="pl3 ar vt">:</td>
			<td colspan="2" class="pl3 vt">Terlampir</td>
		</tr>
		<tr>
			<td class="pl3 vt">{{$buk[5]['kdakun']}}</td>
			<td class="vt">Rp</td>
			<td class="pl3 ar" style="text-align:right;border-right: 1px solid #000;">{{$buk[5]['nilai']}}</td>
			<td class="bl pl3 vt">Nama Proyek</td>
			<td class="pl3 ar vt">:</td>
			<td colspan="2" class="pl3 vt">{{$proyek}}</td>
		</tr>
		<tr>
			<td class="pl3 vt">{{$buk[6]['kdakun']}}</td>
			<td class="vt">Rp</td>
			<td class="pl3 ar" style="text-align:right;border-right: 1px solid #000;">{{$buk[6]['nilai']}}</td>
			<td class="bl pl3 vt"></td>
			<td class="pl3 ar vt"></td>
			<td colspan="2" class="pl3 vt"></td>
		</tr>
		<tr>
			<td class="pl3 vt">{{$buk[7]['kdakun']}}</td>
			<td class="vt">Rp</td>
			<td class="pl3 ar" style="text-align:right;border-right: 1px solid #000;">{{$buk[7]['nilai']}}</td>
			<td class="bl pl3 vt"></td>
			<td class="pl3 ar vt"></td>
			<td colspan="2" class="pl3 vt"></td>
		</tr>
		<tr>
			<td colspan="3" style="border-right: 1px solid #000;">&nbsp;</td>
			<td colspan="4" class="bl">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" rowspan="2" class="pl3 vt" style="border-right: 1px solid #000;"><u>Kode Mata Anggaran Pengembalian :</u></td>
			<td colspan="3" class="bl pl3 ac">KASIR/BENDAHARA:</td>
			<td colspan="1" class="pl3">&nbsp;</td>
		</tr>
		<tr>
			<td class="bl pl3">Pada tanggal</td>
			<td class="pl3 ar vt">:</td>
			<td colspan="2" class="pl3">{{$tgcek1}}</td>
		</tr>
		<tr>
			<td class="pl3 vt">{{$kdakun_pajak1}}</td>
			<td class="vt">Rp</td>
			<td class="pl3 ar vt" style="text-align:right;border-right: 1px solid #000;">{{$nilai_pajak1}}</td>
			<td class="bl pl3 vt">Cash/Cek</td>
			<td class="ar vt">:</td>
			<td colspan="2" class="pl3">{{$nocek}}</td>
		</tr>
		<tr>
			<td class="pl3">{{$kdakun_pajak2}}</td>
			<td class="pl3">Rp</td>
			<td class="pl3 ar" style="text-align:right;border-right: 1px solid #000;">{{$nilai_pajak2}}</td>
			<td class="bl pl3">Tanda tangan</td>
			<td class="ar vt">:</td>
			<td class="vt">....................</td>
			<td class="pl3 al">Jakarta, {{$tgrekam}}</td>
		</tr>
		<tr>
			<td class="pl3">{{$kdakun_pajak3}}</td>
			<td class="pl3">Rp</td>
			<td class="pl3 ar" style="text-align:right;border-right: 1px solid #000;">{{$nilai_pajak3}}</td>
			<td colspan="3" class="bl pl3">&nbsp;</td>
			<td class="pl3 al" style="text-align:center;">PERUMDA PEMBANGUNAN SARANA JAYA</td>
		</tr>
		<tr>
			<td class="pl3">{{$kdakun_pajak4}}</td>
			<td class="pl3">Rp</td>
			<td class="pl3 ar" style="text-align:right;border-right: 1px solid #000;">{{$nilai_pajak4}}</td>
			<td colspan="3" class="bl pl3 ac"><u>PENERIMA UANG</u></td>
			<td class="pl3 ac" style="text-align:center;">{{$jabatan}}</td>
		</tr>
		<tr>
			<td class="pl3">{{$kdakun_pajak5}}</td>
			<td class="pl3">Rp</td>
			<td class="pl3 ar" style="text-align:right;border-right: 1px solid #000;">{{$nilai_pajak5}}</td>
			<td class="bl pl3 vt"></td>
			<td class="ar vt"></td>
			<td class="pl3 vt"></td>
			<td class="pl3 ac vt"></td>
		</tr>
		<tr>
			<td class="pl3">{{$kdakun_pajak6}}</td>
			<td class="pl3">Rp</td>
			<td class="pl3 ar" style="text-align:right;border-right: 1px solid #000;">{{$nilai_pajak6}}</td>
			<td class="bl pl3 vt">Nama</td>
			<td class="ar vt">:</td>
			<td class="pl3 vt">....................</td>
			<td class="pl3 ac vt"></td>
		</tr>
		<tr>
			<td class="pl3"></td>
			<td class="pl3"></td>
			<td class="pl3 ar" style="text-align:right;border-right: 1px solid #000;"></td>
			<td class="bl pl3 vt"></td>
			<td class="ar vt"></td>
			<td class="pl3 vt"></td>
			<td class="pl3 ac vt"></td>
		</tr>
		<tr>
			<td class="pl3">Jumlah yang harus dibayar</td>
			<td class="pl3">Rp</td>
			<td class="pl3 ar" style="text-align:right;border-right: 1px solid #000;">{{$nilai}}</td>
			<td class="bl pl3 vt">KTP/SIM No.</td>
			<td class="ar vt">:</td>
			<td class="pl3 vt">....................</td>
			<td class="pl3 ac vt"></td>
		</tr>
		<tr>
			<td class="pl3"></td>
			<td class="pl3"></td>
			<td class="pl3 ar" style="text-align:right;border-right: 1px solid #000;"></td>
			<td class="bl pl3 vt">Alamat</td>
			<td class="ar vt">:</td>
			<td class="pl3 vt">....................</td>
			<td class="pl3 ac vt"></td>
		</tr>
		<tr>
			<td class="pl3"></td>
			<td class="pl3"></td>
			<td class="pl3 ar" style="text-align:right;border-right: 1px solid #000;"></td>
			<td class="bl pl3 vt">Tanda Tangan</td>
			<td class="ar vt">:</td>
			<td class="pl3 vt">....................</td>
			<td class="pl3 ac vt"></td>
		</tr>
		<tr>
			<td colspan="3" class="pl3" style="border-right: 1px solid #000;">&nbsp;</td>
			<td colspan="2" class="bl">&nbsp;</td>
			<td class="">&nbsp;</td>
			<td class="vb ac" style="text-align:center;">{{$nama_ttd3}}</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3" style="border-right: 1px solid #000;">&nbsp;</td>
			<td colspan="2" class="bl pl3">&nbsp;</td>
			<td class="pl3">&nbsp;</td>
			<td class="pl3 ac vb" style="text-align:center;">{{$nip_ttd3}}</td>
		</tr>
		<tr>
			<td colspan="3" class="bb pl3" style="border-right: 1px solid #000;">&nbsp;</td>
			<td colspan="2" class="bl bb pl3">&nbsp;</td>
			<td class="bb pl3">&nbsp;</td>
			<td class="bb pl3">&nbsp;</td>
		</tr>
	</tbody>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="3" style="border: 1px solid #000;font-size:11px;">
	<tbody>
		<tr>
			<td colspan="3" class="pl3" style="border-top: 1px solid #000;text-align:center;">Penanggung Jawab</td>
			<td colspan="2" class="bl pl3" style="border-top: 1px solid #000;">&nbsp;</td>
			<td class="pl3" style="border-top: 1px solid #000;">&nbsp;</td>
			<td class="pl3" style="border-top: 1px solid #000;">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3 ac vt" style="text-align:center;">{{$nmunit}}</td>
			<td colspan="2" class="bl pl3">&nbsp;</td>
			<td class="pl3">&nbsp;</td>
			<td class="pl3 ac vt" style="text-align:center;">Senior Manajer Divisi Keuangan & Akuntansi</td>
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
			<td class="pl3">Telah diperiksa dan dibukukan dalam Buku Kas/Bank dengan nomor: <br>{{$nobuku}}</td>
			<td class="pl3 ac vt" style="text-align:center;"></td>
		</tr>

		<tr>
			<td colspan="3" class="pl3 ac vt" style="text-align:center;">({{$nama_ttd1}})</td>
			<td colspan="2" class="bl pl3">&nbsp;</td>
			<td class="pl3">&nbsp;</td>
			<td class="pl3 ac vt" style="text-align:center;">({{$nama_ttd2}})</td>
		</tr>
	</tbody>
</table>

</body>
</html>