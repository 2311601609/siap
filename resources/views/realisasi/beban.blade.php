@include('css')

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:0px solid #000;">
	<thead>
		<tr>
			<th colspan="9" class="">
				PERUMDA PEMBANGUNAN SARANA JAYA
				<br>
				RINCIAN REALISASI BEBAN TAHUN {{$tahun}}
				<br>
				<!--<span class="fs10">(Disajikan dalam jutaan Rupiah)</span>-->
			</th>
		</tr>
		<tr>
			<th colspan="9" class="">&nbsp;</th>
		</tr>
		<tr>
			<th class="bl bt plr3">Uraian</th>
			<th class="bl bt plr3 wd12">RKAP {{$tahun}}</th>
			<th class="bl bt plr3 wd12">Rencana {{$periode}} {{$tahun}}</th>
			<th class="bl bt plr3 wd12">Realisasi {{$periode}} {{$tahun}}</th>
			<th class="bl bt plr3 wd5">%</th>
			<th class="bl bt plr3 wd12">Rencana s.d {{$periode}} {{$tahun}}</th>
			<th class="bl bt plr3 wd12">Realisasi s.d {{$periode}} {{$tahun}}</th>
			<th class="bl bt plr3 wd5">%</th>
			<th class="bl bt br plr3 wd5">%</th>
		</tr>
		<tr>
			<td class="bl bt bb plr3 ac fs10">1</td>
			<td class="bl bt bb plr3 ac fs10">2</td>
			<td class="bl bt bb plr3 ac fs10">3</td>
			<td class="bl bt bb plr3 ac fs10">4</td>
			<td class="bl bt bb plr3 ac fs10">5 (4:3)</td>
			<td class="bl bt bb plr3 ac fs10">6</td>
			<td class="bl bt bb plr3 ac fs10">7</td>
			<td class="bl bt bb plr3 ac fs10">8 (7:6)</td>
			<td class="bl bt br bb plr3 ac fs10">9 (7:2)</td>
		</tr>
	</thead>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:0px solid #000;font-size:11px;">
	<tbody>		
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
			<td class="bl bt bb plr3 ac fb">Total</td>
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
