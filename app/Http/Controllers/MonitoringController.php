<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MonitoringController extends Controller {

	public function realPendapatan(Request $request, $param)
	{
		$data['data'] = 'Data tidak ditemukan';
		$data['grafik1'] = 'Data tidak ditemukan';
		$data['grafik2'] = 'Data tidak ditemukan';
		
		$rows = DB::select("
			select  a.kdunit,
					a.nmunit,
					a.nmunit1,
					nvl(c.nilai,0) as target,
					nvl(b.nilai,0) as realisasi
			from t_unit a
			left join(
				select  substr(a.kdunit,1,4) as kdunit,
						sum(a.nilai_bersih) as nilai
				from d_trans a
				left join t_alur c on(a.id_alur=c.id)
				where c.menu in(1,2) and a.thang=?
				group by substr(a.kdunit,1,4)
			) b on(a.kdunit=b.kdunit)
			left join(
				select  substr(a.kdunit,1,4) as kdunit,
						sum(a.nilai) as nilai
				from d_target a
				where a.thang=?
				group by substr(a.kdunit,1,4)
			) c on(a.kdunit=c.kdunit)
			where length(a.kdunit)=4
			order by a.kdunit
		",[
			session('tahun'),
			session('tahun')
		]);
		
		if(count($rows)>0){
			
			$i=1;
			$total1=0;
			$total2=0;
			$tabel = '';
			foreach($rows as $row){
			
				//bentuk tabel
				$tabel.='<tr>
							<td>'.$i++.'</td>
							<td>'.$row->nmunit.'</td>
							<td style="text-align:right;">'.number_format($row->target).'</td>
							<td style="text-align:right;">'.number_format($row->realisasi).'</td>
						</tr>';
						
				$total1+=$row->target;
				$total2+=$row->realisasi;
				
				//isi data grafik1
				$arr_data['grafik1'][]="{name:'".$row->nmunit1."', y:".$row->target."}";
				
				//isi data grafik2
				$arr_data['grafik2']['param'][]="'".$row->nmunit1."'";
				$arr_data['grafik2']['realisasi'][]=$row->realisasi;
				
			}
			
			$data['data'] = $tabel;
			$data['total1'] = number_format($total1);
			$data['total2'] = number_format($total2);
			
			$grafik1="	<script>
						function numberWithCommas(x) {
							return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
						}
						
						jQuery('#grafik1').highcharts({

							chart: {
								plotBackgroundColor: null,
								plotBorderWidth: null,
								plotShadow: false,
								backgroundColor:'rgba(255, 255, 255, 0.0)',
								type: 'pie'
							},
							title: {
								text: 'Target Per Unit/Divisi'
							},
							tooltip: {
								pointFormat: ' {series.name}: <b>{point.y}</b>'
							},
							plotOptions: {
								pie: {
									allowPointSelect: true,
									cursor: 'pointer',
									dataLabels: {
										enabled: false
									},
									showInLegend: true,
									borderWidth: 0 // < set this option
								}
							},
							series: [{
								colorByPoint: true,
								data: [".implode(",", $arr_data['grafik1'])."]
							}]
							
						});
						</script>";
						
			$grafik2="	<script>
						function numberWithCommas(x) {
							return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
						}
						
						jQuery('#grafik2').highcharts({
							chart: {
								type: 'column',
								backgroundColor:'rgba(255, 255, 255, 0.0)'
							},
							title: {
								text: 'Realisasi Per Unit/Divisi'
							},
							xAxis: {
								categories: [".implode(",", $arr_data['grafik2']['param'])."],
								crosshair: true,
								labels: {
									style: {
										color: 'black'
									}
								}
							},
							yAxis: {
								min: 0,
								title: {
									text: 'Jumlah'
								},
								labels: {
									style: {
										color: 'black'
									}
								}
							},
							tooltip: {
								pointFormat: '{series.name}: <b>{point.y} ,-</b>'
							},
							plotOptions: {
								column: {
									pointPadding: 0.2,
									borderWidth: 0
								}
							},
							series: [{
								name: 'Realisasi',
								data: [".implode(",", $arr_data['grafik2']['realisasi'])."]
							}]
						});
						</script>";
						
			$data['grafik1'] = $grafik1;
			$data['grafik2'] = $grafik2;
			
		}
		
		return response()->json($data);
		
	}
	
	public function realBelanja(Request $request, $param)
	{
		$data['data'] = 'Data tidak ditemukan';
		$data['grafik1'] = 'Data tidak ditemukan';
		$data['grafik2'] = 'Data tidak ditemukan';
		
		$rows = DB::select("
			select  a.kdunit,
					a.nmunit,
					a.nmunit1,
					nvl(c.nilai,0) as pagu,
					nvl(b.nilai,0) as realisasi,
					nvl(c.nilai,0)-nvl(b.nilai,0) as sisa
			from t_unit a
			left join(
				select  substr(a.kdunit,1,4) as kdunit,
						sum(a.nilai_bersih) as nilai
				from d_trans a
				left join t_alur c on(a.id_alur=c.id)
				where c.menu=4 and a.thang=?
				group by substr(a.kdunit,1,4)
			) b on(a.kdunit=b.kdunit)
			left join(
				select  substr(a.kdunit,1,4) as kdunit,
						sum(a.nilai) as nilai
				from d_pagu a
				where a.thang=?
				group by substr(a.kdunit,1,4)
			) c on(a.kdunit=c.kdunit)
			where length(a.kdunit)=4
			order by a.kdunit
		",[
			session('tahun'),
			session('tahun')
		]);
		
		if(count($rows)>0){
			
			$i=1;
			$total1=0;
			$total2=0;
			$total3=0;
			$tabel = '';
			foreach($rows as $row){
			
				//bentuk tabel
				$tabel.='<tr>
							<td>'.$i++.'</td>
							<td>'.$row->nmunit.'</td>
							<td style="text-align:right;">'.number_format($row->pagu).'</td>
							<td style="text-align:right;">'.number_format($row->realisasi).'</td>
							<td style="text-align:right;">'.number_format($row->sisa).'</td>
						</tr>';
						
				$total1+=$row->pagu;
				$total2+=$row->realisasi;
				$total3+=$row->sisa;
				
				//isi data grafik1
				$arr_data['grafik1'][]="{name:'".$row->nmunit1."', y:".$row->pagu."}";
				
				//isi data grafik2
				$arr_data['grafik2']['param'][]="'".$row->nmunit1."'";
				$arr_data['grafik2']['realisasi'][]=$row->realisasi;
				
			}
			
			$data['data'] = $tabel;
			$data['total1'] = number_format($total1);
			$data['total2'] = number_format($total2);
			$data['total3'] = number_format($total3);
			
			$grafik1="	<script>
						function numberWithCommas(x) {
							return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
						}
						
						jQuery('#grafik1').highcharts({

							chart: {
								plotBackgroundColor: null,
								plotBorderWidth: null,
								plotShadow: false,
								backgroundColor:'rgba(255, 255, 255, 0.0)',
								type: 'pie'
							},
							title: {
								text: 'Pagu Per Unit/Divisi'
							},
							tooltip: {
								pointFormat: ' {series.name}: <b>{point.y}</b>'
							},
							plotOptions: {
								pie: {
									allowPointSelect: true,
									cursor: 'pointer',
									dataLabels: {
										enabled: false
									},
									showInLegend: true,
									borderWidth: 0 // < set this option
								}
							},
							series: [{
								colorByPoint: true,
								data: [".implode(",", $arr_data['grafik1'])."]
							}]
							
						});
						</script>";
						
			$grafik2="	<script>
						function numberWithCommas(x) {
							return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
						}
						
						jQuery('#grafik2').highcharts({
							chart: {
								type: 'column',
								backgroundColor:'rgba(255, 255, 255, 0.0)'
							},
							title: {
								text: 'Realisasi Per Unit/Divisi'
							},
							xAxis: {
								categories: [".implode(",", $arr_data['grafik2']['param'])."],
								crosshair: true,
								labels: {
									style: {
										color: 'black'
									}
								}
							},
							yAxis: {
								min: 0,
								title: {
									text: 'Jumlah'
								},
								labels: {
									style: {
										color: 'black'
									}
								}
							},
							tooltip: {
								pointFormat: '{series.name}: <b>{point.y} ,-</b>'
							},
							plotOptions: {
								column: {
									pointPadding: 0.2,
									borderWidth: 0
								}
							},
							series: [{
								name: 'Realisasi',
								data: [".implode(",", $arr_data['grafik2']['realisasi'])."]
							}]
						});
						</script>";
						
			$data['grafik1'] = $grafik1;
			$data['grafik2'] = $grafik2;
			
		}
		
		return response()->json($data);
		
	}
	
	public function saldoKas(Request $request, $param)
	{
		$data['data'] = 'Data tidak ditemukan';
		
		$rows = DB::select("
			select  a.nmakun,
					nvl(c.sawal,0) as sawal,
					nvl(b.debet,0) as debet,
					nvl(b.kredit,0) as kredit,
					nvl(c.sawal,0)+nvl(b.debet,0)-nvl(b.kredit,0) as saldo
			from t_akun a
			left join(
				
				select	a.kdakun,
						sum(decode(a.kddk,'D',a.nilai,0)) as debet,
						sum(decode(a.kddk,'K',a.nilai,0)) as kredit
				from(
				
					select	a.kdakun,
							a.kddk,
							sum(a.nilai) as nilai
					from d_trans_akun a
					left join d_trans b on(a.id_trans=b.id)
					where b.thang=?
					group by a.kdakun,a.kddk
					
				) a
				group by a.kdakun
				
			) b on(a.kdakun=b.kdakun)
			left join(
				select  kdakun,
						sum(decode(kddk,'D',nilai,0))-sum(decode(kddk,'K',nilai,0)) as sawal
				from d_sawal a
				where thang=?
				group by kdakun
			) c on(a.kdakun=c.kdakun)
			where substr(a.kdakun,1,3)='111' and substr(a.kdakun,1,4)<>'1112' and a.lvl=6
			order by a.kdakun
		",[
			session('tahun'),
			session('tahun'),
		]);
		
		if(count($rows)>0){
			
			$i=1;
			$total1=0;
			$total2=0;
			$total3=0;
			$total4=0;
			$tabel = '';
			foreach($rows as $row){
			
				//bentuk tabel
				$tabel.='<tr>
							<td>'.$i++.'</td>
							<td>'.$row->nmakun.'</td>
							<td style="text-align:right;">'.number_format($row->sawal).'</td>
							<td style="text-align:right;">'.number_format($row->debet).'</td>
							<td style="text-align:right;">'.number_format($row->kredit).'</td>
							<td style="text-align:right;">'.number_format($row->saldo).'</td>
						</tr>';
						
				$total1+=$row->sawal;
				$total2+=$row->debet;
				$total3+=$row->kredit;
				$total4+=$row->saldo;
				
			}
			
			$data['data'] = $tabel;
			$data['total1'] = number_format($total1);
			$data['total2'] = number_format($total2);
			$data['total3'] = number_format($total3);
			$data['total4'] = number_format($total4);
			
		}
		
		return response()->json($data);
		
	}

}