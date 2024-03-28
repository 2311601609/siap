@include('css')

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:0px solid #000;">
	<thead>
		<tr>
			<th>PERUMDA PEMBANGUNAN SARANA JAYA</th>
		</tr>
		<tr>
			<th>LAPORAN REALISASI INVESTASI</th>
		</tr>
		<!--<tr>
			<th class="ac fs10">(Disajikan dalam jutaan rupiah)</th>
		</tr>-->
	</thead>
</table>
<br>
<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:0px solid #000;font-size:11px;">
	<thead>
		<tr>
			<th colspan="3" rowspan="2" class="bl bt">URAIAN</th>
			<th colspan="2" class="bl bt">Anggaran</th>
			<th rowspan="2" class="wd10 bl bt">Realisasi Triwulan </th>
			<th rowspan="2" class="wd10 bl bt">Realisasi s.d. Triwulan</th>
			<th rowspan="2" class="wd5 bl bt">%</th>
			<th rowspan="2" class="wd5 bl br bt">%</th>
		</tr>
		<tr>
			<th class="wd10 bl bt">RKAP 2019</th>
			<th class="wd10 bl bt">Triwulan</th>
		</tr>
		<tr>
			<td colspan="3" class="bl bt ac">1</td>
			<td class="bl bt ac">2</td>
			<td class="bl bt ac">3</td>
			<td class="bl bt ac">4</td>
			<td class="bl bt ac">5</td>
			<td class="bl bt ac">6</td>
			<td class="bl bt br ac">7</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td colspan="3" class="bl bt plr3 al vt">INVESTASI</td>
			<td class="bl bt plr3 ar vt">&nbsp;</td>
			<td class="bl bt plr3 ar vt">&nbsp;</td>
			<td class="bl bt plr3 ar vt">&nbsp;</td>
			<td class="bl bt plr3 ar vt">&nbsp;</td>
			<td class="bl bt plr3 ar vt">&nbsp;</td>
			<td class="bl bt br plr3 ar vt">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" class="bl plr3 al vt">Alat Produksi</td>
			<td class="bl plr3 ar vt">&nbsp;</td>
			<td class="bl plr3 ar vt">&nbsp;</td>
			<td class="bl plr3 ar vt">&nbsp;</td>
			<td class="bl plr3 ar vt">&nbsp;</td>
			<td class="bl plr3 ar vt">&nbsp;</td>
			<td class="bl br plr3 ar vt">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" class="bl plr3 al vt">Pengembangan Lingkungan Baru</td>
			<td class="bl ad2 ar vt">&nbsp;</td>
			<td class="bl plr3 ar vt">&nbsp;</td>
			<td class="bl plr3 ar vt">&nbsp;</td>
			<td class="bl plr3 ar vt">&nbsp;</td>
			<td class="bl plr3 ar vt">&nbsp;</td>
			<td class="bl br plr3 ar vt">&nbsp;</td>
		</tr>
		<tr>
			<td class="wd2 bl plr3 ac vt">{{ $itm[0]['idx'] }}</td>
			<td colspan="2" class="plr3 al vt">{{ $itm[0]['val'] }}</td>
			<td class="bl plr3 ar vt">&nbsp;</td>
			<td class="bl plr3 ar vt">&nbsp;</td>
			<td class="bl plr3 ar vt">&nbsp;</td>
			<td class="bl plr3 ar vt">&nbsp;</td>
			<td class="bl plr3 ar vt">&nbsp;</td>
			<td class="bl br plr3 ar vt">&nbsp;</td>
		</tr>
		@foreach ($rows1 as $row)
		<tr>
			<td class="bl plr3 al vt">&nbsp;</td>
			<td class="wd2 plr3 al vt">{{ $row['kode'] }}</td>
			<td class="plr3 al vt">{{ $row['uraian'] }}</td>
			<td class="bl plr3 ar vt">{{ $row['rkap'] }}</td>
			<td class="bl plr3 ar vt">{{ $row['tw'] }}</td>
			<td class="bl plr3 ar vt">{{ $row['rtw'] }}</td>
			<td class="bl plr3 ar vt">{{ $row['sdtw'] }}</td>
			<td class="bl plr3 ar vt">{{ $row['psn1'] }}</td>
			<td class="bl br plr3 ar vt">{{ $row['psn2'] }}</td>
		</tr>
		@endforeach
		<tr>
			<td colspan="3" class="bl bt bb plr3 ar vt">Subjumlah Investasi Dana PD. PSJ</td>
			<td class="bl bt bb plr3 ar vt">0</td>
			<td class="bl bt bb plr3 ar vt">0</td>
			<td class="bl bt bb plr3 ar vt">0</td>
			<td class="bl bt bb plr3 ar vt">0</td>
			<td class="bl bt bb plr3 ar vt">0</td>
			<td class="bl bt br bb plr3 ar vt">0</td>
		</tr>
		
		<tr>
			<td class="bl plr3 ac vt">{{ $itm[1]['idx'] }}</td>
			<td colspan="2" class="plr3 al vt">{{ $itm[1]['val'] }}</td>
			<td class="bl plr3 ar vt">&nbsp;</td>
			<td class="bl plr3 ar vt">&nbsp;</td>
			<td class="bl plr3 ar vt">&nbsp;</td>
			<td class="bl plr3 ar vt">&nbsp;</td>
			<td class="bl plr3 ar vt">&nbsp;</td>
			<td class="bl br plr3 ar vt">&nbsp;</td>
		</tr>
		@foreach ($rows2 as $row)
		<tr>
			<td class="bl plr3 al vt">&nbsp;</td>
			<td class="plr3 al vt">{{ $row['kode'] }}</td>
			<td class="plr3 al vt">{{ $row['uraian'] }}</td>
			<td class="bl plr3 ar vt">{{ $row['rkap'] }}</td>
			<td class="bl plr3 ar vt">{{ $row['tw'] }}</td>
			<td class="bl plr3 ar vt">{{ $row['rtw'] }}</td>
			<td class="bl plr3 ar vt">{{ $row['sdtw'] }}</td>
			<td class="bl plr3 ar vt">{{ $row['psn1'] }}</td>
			<td class="bl br plr3 ar vt">{{ $row['psn2'] }}</td>
		</tr>
		@endforeach
		<tr>
			<td colspan="3" class="bl bt bb plr3 ar vt">Subjumlah Investasi Dana PMD</td>
			<td class="bl bt bb plr3 ar vt">0</td>
			<td class="bl bt bb plr3 ar vt">0</td>
			<td class="bl bt bb plr3 ar vt">0</td>
			<td class="bl bt bb plr3 ar vt">0</td>
			<td class="bl bt bb plr3 ar vt">0</td>
			<td class="bl bt br bb plr3 ar vt">0</td>
		</tr>
		<tr>
			<td colspan="3" class="bl plr3 al vt">Barang Inventaris</td>
			<td class="bl plr3 ar vt">&nbsp;</td>
			<td class="bl plr3 ar vt">&nbsp;</td>
			<td class="bl plr3 ar vt">&nbsp;</td>
			<td class="bl plr3 ar vt">&nbsp;</td>
			<td class="bl plr3 ar vt">&nbsp;</td>
			<td class="bl br plr3 ar vt">&nbsp;</td>
		</tr>
		@foreach ($rows3 as $row)
		<tr>
			<td class="bl plr3 al vt">{{ $row['idx'] }}</td>
			<td colspan="2" class=" plr3 al vt">{{ $row['val'] }}</td>
			<td class="bl plr3 ar vt">{{ $row['rkap'] }}</td>
			<td class="bl plr3 ar vt">{{ $row['tw'] }}</td>
			<td class="bl plr3 ar vt">{{ $row['rtw'] }}</td>
			<td class="bl plr3 ar vt">{{ $row['sdtw'] }}</td>
			<td class="bl plr3 ar vt">{{ $row['psn1'] }}</td>
			<td class="bl br plr3 ar vt">{{ $row['psn2'] }}</td>
		</tr>
		@endforeach
		<tr>
			<td colspan="3" class="bl bt plr3 ar vt">Subjumlah Barang Inventaris</td>
			<td class="bl bt plr3 ar vt">0</td>
			<td class="bl bt plr3 ar vt">0</td>
			<td class="bl bt plr3 ar vt">0</td>
			<td class="bl bt plr3 ar vt">0</td>
			<td class="bl bt plr3 ar vt">0</td>
			<td class="bl bt br plr3 ar vt">0</td>
		</tr>
		<tr>
			<td colspan="3" class="bl bt bb plr3 ar vt">Jumlah Investasi</td>
			<td class="bl bt bb plr3 ar vt">0</td>
			<td class="bl bt bb plr3 ar vt">0</td>
			<td class="bl bt bb plr3 ar vt">0</td>
			<td class="bl bt bb plr3 ar vt">0</td>
			<td class="bl bt bb plr3 ar vt">0</td>
			<td class="bl bt br bb plr3 ar vt">0</td>
		</tr>
	</tbody>
</table>
