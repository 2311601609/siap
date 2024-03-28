@include('css')

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:0px solid #000;">
	<thead>
		<tr>
			<th colspan="9" class="">
				PERUMDA PEMBANGUNAN SARANA JAYA
				<br>
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
			<th class="wd12 bl bt plr3">Rencana {{$periode}} {{$tahun}}</th>
			<th class="wd12 bl bt plr3">Realisasi {{$periode}} {{$tahun}}</th>
			<th class="wd5 bl bt plr3">%</th>
			<th class="wd12 bl bt plr3">Rencana s.d {{$periode}} {{$tahun}}</th>
			<th class="wd12 bl bt plr3">Realisasi s.d {{$periode}} {{$tahun}}</th>
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
		@foreach($rows as $row)
		<tr>
			<td class="bl al plr3">{{ ucwords($row['uraian']) }}</td>
			<td class="wd12 bl ar plr3">{{ number_format($row['rkap'],0,",",".") }}</td>
			<td class="wd12 bl ar plr3">{{ number_format($row['rctw'],0,",",".") }}</td>
			<td class="wd12 bl ar plr3">{{ number_format($row['rltw'],0,",",".") }}</td>
			<td class="wd5 bl ar plr3">{{ $row['psn1'] }}</td>
			<td class="wd12 bl ar plr3">{{ number_format($row['rcsdtw'],0,",",".") }}</td>
			<td class="wd12 bl ar plr3">{{ number_format($row['rlsdtw'],0,",",".") }}</td>
			<td class="wd5 bl ar plr3">{{ $row['psn2'] }}</td>
			<td class="wd5 bl br ar plr3">{{ $row['psn3'] }}</td>
		</tr>
		@endforeach
		<tr>
			<td class="bl bt bb ac plr3 fb">Jumlah</td>
			<td class="bl bt bb plr3 ar">{{ number_format($total['rkap'],0,",",".") }}</td>
			<td class="bl bt bb plr3 ar">{{ number_format($total['rctw'],0,",",".") }}</td>
			<td class="bl bt bb plr3 ar">{{ number_format($total['rltw'],0,",",".") }}</td>
			<td class="bl bt bb plr3 ar">{{ $total['psn1'] }}</td>
			<td class="bl bt bb plr3 ar">{{ number_format($total['rcsdtw'],0,",",".") }}</td>
			<td class="bl bt bb plr3 ar">{{ number_format($total['rlsdtw'],0,",",".") }}</td>
			<td class="bl bt bb plr3 ar">{{ $total['psn2'] }}</td>
			<td class="bl bt br bb plr3 ar">{{ $total['psn3'] }}</td>
		</tr>
	</tbody>
</table>
