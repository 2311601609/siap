@include('css')

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:0px solid #000;">
	<thead>
		<tr>
			<th colspan="9" class="">
				RINCIAN REALISASI PENDAPATAN PENGELOLAAN TAHUN {{$tahun}}
				<br>
				<!--<span class="fs10">(Disajikan dalam jutaan Rupiah)</span>-->
			</th>
		</tr>
		<tr>
			<th colspan="9" class="">&nbsp;</th>
		</tr>
		<tr>
			<th class="bl bt plr3">Uraian</th>
			<th class="wd12 bl bt plr3">RKAP {{$tahun}}</th>
			<th class="wd12 bl bt plr3">Rencana TW.II {{$tahun}}</th>
			<th class="wd12 bl bt plr3">Realisasi TW.II {{$tahun}}</th>
			<th class="wd5 bl bt plr3">%</th>
			<th class="wd12 bl bt plr3">Rencana s.d TW.II {{$tahun}}</th>
			<th class="wd12 bl bt plr3">Realisasi s.d TW.II {{$tahun}}</th>
			<th class="wd5 bl bt plr3">%</th>
			<th class="wd5 bl bt br plr3">%</th>
		</tr>
		<tr>
			<td class="bl bt ac fs10">1</td>
			<td class="bl bt ac fs10">2</td>
			<td class="bl bt ac fs10">3</td>
			<td class="bl bt ac fs10">4</td>
			<td class="bl bt ac fs10">5</td>
			<td class="bl bt ac fs10">6</td>
			<td class="bl bt ac fs10">7</td>
			<td class="bl bt ac fs10">8</td>
			<td class="bl bt br ac fs10">9</td>
		</tr>
	</thead>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:0px solid #000;font-size:11px;">
	<tbody>
		<tr>
			<td class="bl bt al fb plr3 fb">Pengelolaan Aset</td>
			<td class="wd12 bl bt al plr3">&nbsp;</td>
			<td class="wd12 bl bt al plr3">&nbsp;</td>
			<td class="wd12 bl bt al plr3">&nbsp;</td>
			<td class="wd5 bl bt al plr3">&nbsp;</td>
			<td class="wd12 bl bt al plr3">&nbsp;</td>
			<td class="wd12 bl bt al plr3">&nbsp;</td>
			<td class="wd5 bl bt al plr3">&nbsp;</td>
			<td class="wd5 bl bt br al plr3">&nbsp;</td>
		</tr>
		@foreach($rows2 as $row)
		<tr>
			<td class="bl al plr3">{{ $row['uraian'] }}</td>
			<td class="bl ar plr3">{{ $row['rkap'] }}</td>
			<td class="bl ar plr3">{{ $row['rctw'] }}</td>
			<td class="bl ar plr3">{{ $row['rltw'] }}</td>
			<td class="bl ar plr3">{{ $row['psn1'] }}</td>
			<td class="bl ar plr3">{{ $row['rcsdtw'] }}</td>
			<td class="bl ar plr3">{{ $row['rlsdtw'] }}</td>
			<td class="bl ar plr3">{{ $row['psn2'] }}</td>
			<td class="bl br ar plr3">{{ $row['psn3'] }}</td>
		</tr>
		@endforeach
		<tr>
			<td class="bl bt ac plr3 fb">Jumlah</td>
			<td class="bl bt ar plr3">0</td>
			<td class="bl bt ar plr3">0</td>
			<td class="bl bt ar plr3">0</td>
			<td class="bl bt ar plr3">0</td>
			<td class="bl bt ar plr3">0</td>
			<td class="bl bt ar plr3">0</td>
			<td class="bl bt ar plr3">0</td>
			<td class="bl bt br ar plr3">0</td>
		</tr>
		<tr>
			<td class="bl bt bb ac plr3 fb">Total Beban Pokok Penjualan</td>
			<td class="bl bt bb ar plr3">0</td>
			<td class="bl bt bb ar plr3">0</td>
			<td class="bl bt bb ar plr3">0</td>
			<td class="bl bt bb ar plr3">0</td>
			<td class="bl bt bb ar plr3">0</td>
			<td class="bl bt bb ar plr3">0</td>
			<td class="bl bt bb ar plr3">0</td>
			<td class="bl bt br bb ar plr3">0</td>
		</tr>
	</tbody>
</table>
