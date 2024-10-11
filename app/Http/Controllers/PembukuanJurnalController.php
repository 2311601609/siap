<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use clsTinyButStrong;
use DataTables;
use App\Libraries\PublicFunction;
use App\Exports\NeracaLajurExport;
use Maatwebsite\Excel\Facades\Excel;

class PembukuanJurnalController extends Controller {
	
	private function query()
	{
		$query = "
			select  a.id_trans,
					b.nourut,
					b.id_alur,
					b.id_proyek,
					b.id_penerima,
					b.kdunit,
					b.kdtran,
					b.uraian,
					a.kdakun,
					a.kddk,
					b.nodok,
					a.nilai,
					case
						when c.menu=1 /* tagihan */
							then b.tgrekam
						when c.menu in(2,3) /* penerimaan dan umk */
							then b.tgcek
						when c.menu=4 and a.grup in('0','1','2') /* buk rekam */
							then b.tgrekam
						when c.menu=4 and a.grup='3' /* buk bayar */
							then b.tgcek
						else /* kas kecil dan penyesuaian */
							nvl(b.tgdok,b.tgrekam)
					end as tgdok,
					c.neraca1
			from d_trans_akun a
			left join d_trans b on(a.id_trans=b.id)
			left join t_alur c on(b.id_alur=c.id)
			where b.thang='".session('tahun')."'
		";
		
		return $query;
	}
	
	public function index(Request $request, $tgawal, $tgakhir)
	{
		$where = "";
		$where1 = "";
		if($tgawal!=='xxx' && $tgakhir!=='xxx'){
			$where = " and a.tgsawal between to_date('".$tgawal." 00:00:00','yyyy-mm-dd hh24:mi:ss') and to_date('".$tgakhir." 23:59:59','yyyy-mm-dd hh24:mi:ss') ";
			$where1 = " and a.tgdok between to_date('".$tgawal." 00:00:00','yyyy-mm-dd hh24:mi:ss') and to_date('".$tgakhir." 23:59:59','yyyy-mm-dd hh24:mi:ss') ";
		}
		
		$rows = DB::select("
			select  a.kdakun,
					b.nmakun,
					sum(decode(a.kddk,'D',a.nilai,0)) as debet,
					sum(decode(a.kddk,'K',a.nilai,0)) as kredit
			from(
				/* saldo awal */
				select  to_char(a.tgsawal,'YYYY') as thang,
						to_char(a.tgsawal,'MM') as periode,
						a.kddk,
						a.kdakun,
						sum(a.nilai) as nilai
				from d_sawal a
				where a.thang='".session('tahun')."' ".$where."
				group by to_char(a.tgsawal,'YYYY'),
						to_char(a.tgsawal,'MM'),
						a.kdakun,
						a.kddk
				
				union all
				
				/* transaksi berjalan termasuk pajak */
				select  to_char(a.tgdok,'yyyy') as thang,
						to_char(a.tgdok,'mm') as periode,
						a.kddk,
						a.kdakun,
						sum(a.nilai) as nilai
				from(
					".$this->query()."
				) a
				where a.neraca1=1 ".$where1."
				group by to_char(a.tgdok,'yyyy'),
						 to_char(a.tgdok,'mm'),
						 a.kddk,
						 a.kdakun
				
			) a
			left join t_akun b on(a.kdakun=b.kdakun)
			group by a.kdakun,b.nmakun
			order by a.kdakun,b.nmakun
		");
		
		$data = '';
		$total_debet = 0;
		$total_kredit = 0;
		foreach($rows as $row){
			$data .= '<tr>
						<td>'.$row->kdakun.'</td>
						<td>'.$row->nmakun.'</td>
						<td style="text-align:right;">'.number_format($row->debet,0).'</td>
						<td style="text-align:right;">'.number_format($row->kredit,0).'</td>
					  </tr>';
			$total_debet += $row->debet;
			$total_kredit += $row->kredit;
		}
		
		return response()->json(array(
			'data' => $data,
			'total_debet' => number_format($total_debet,0),
			'total_kredit' => number_format($total_kredit,0)
		));
	}

	public function neracaPercobaan()
	{
		if (request()->has('tgawal') && request()->has('tgakhir')) {

			$tgawal = $_GET['tgawal'];
			$tgakhir = $_GET['tgakhir'];

			if($tgawal!=='' && $tgakhir!==''){

				$where = " and a.tgsawal between to_date('".$tgawal." 00:00:00','yyyy-mm-dd hh24:mi:ss') and to_date('".$tgakhir." 23:59:59','yyyy-mm-dd hh24:mi:ss') ";
				$where1 = " and a.tgdok between to_date('".$tgawal." 00:00:00','yyyy-mm-dd hh24:mi:ss') and to_date('".$tgakhir." 23:59:59','yyyy-mm-dd hh24:mi:ss') ";
				
				$sql = "
					select  a.kdakun,
							b.nmakun,
							sum(decode(a.kddk,'D',a.nilai,0)) as debet,
							sum(decode(a.kddk,'K',a.nilai,0)) as kredit
					from(
						/* saldo awal */
						select  to_char(a.tgsawal,'YYYY') as thang,
								to_char(a.tgsawal,'MM') as periode,
								a.kddk,
								a.kdakun,
								sum(a.nilai) as nilai
						from d_sawal a
						where a.thang='".session('tahun')."' ".$where."
						group by to_char(a.tgsawal,'YYYY'),
								to_char(a.tgsawal,'MM'),
								a.kdakun,
								a.kddk
						
						union all
						
						/* transaksi berjalan termasuk pajak */
						select  to_char(a.tgdok,'yyyy') as thang,
								to_char(a.tgdok,'mm') as periode,
								a.kddk,
								a.kdakun,
								sum(a.nilai) as nilai
						from(
							".$this->query()."
						) a
						where a.neraca1=1 ".$where1."
						group by to_char(a.tgdok,'yyyy'),
								to_char(a.tgdok,'mm'),
								a.kddk,
								a.kdakun
						
					) a
					left join t_akun b on(a.kdakun=b.kdakun)
					group by a.kdakun,b.nmakun
					order by a.kdakun,b.nmakun
				";

				$query = DB::table(DB::raw("($sql) a"))
						->selectRaw('a.*');

				$datatables = DataTables::of($query)
							->editColumn('debet', function($row){
								return number_format($row->debet, 0, ',', '.');
							})
							->editColumn('kredit', function($row){
								return number_format($row->kredit, 0, ',', '.');
							})
							->make(true);

				return $datatables;

			}

		}
	}

	public function neracaPercobaanTotal()
	{
		if (request()->has('tgawal') && request()->has('tgakhir')) {

			$tgawal = $_GET['tgawal'];
			$tgakhir = $_GET['tgakhir'];

			if($tgawal!=='' && $tgakhir!==''){

				$where = " and a.tgsawal between to_date('".$tgawal." 00:00:00','yyyy-mm-dd hh24:mi:ss') and to_date('".$tgakhir." 23:59:59','yyyy-mm-dd hh24:mi:ss') ";
				$where1 = " and a.tgdok between to_date('".$tgawal." 00:00:00','yyyy-mm-dd hh24:mi:ss') and to_date('".$tgakhir." 23:59:59','yyyy-mm-dd hh24:mi:ss') ";
				
				$sql = "
					select  sum(decode(a.kddk,'D',a.nilai,0)) as debet,
							sum(decode(a.kddk,'K',a.nilai,0)) as kredit
					from(
						/* saldo awal */
						select  to_char(a.tgsawal,'YYYY') as thang,
								to_char(a.tgsawal,'MM') as periode,
								a.kddk,
								a.kdakun,
								sum(a.nilai) as nilai
						from d_sawal a
						where a.thang='".session('tahun')."' ".$where."
						group by to_char(a.tgsawal,'YYYY'),
								to_char(a.tgsawal,'MM'),
								a.kdakun,
								a.kddk
						
						union all
						
						/* transaksi berjalan termasuk pajak */
						select  to_char(a.tgdok,'yyyy') as thang,
								to_char(a.tgdok,'mm') as periode,
								a.kddk,
								a.kdakun,
								sum(a.nilai) as nilai
						from(
							".$this->query()."
						) a
						where a.neraca1=1 ".$where1."
						group by to_char(a.tgdok,'yyyy'),
								to_char(a.tgdok,'mm'),
								a.kddk,
								a.kdakun
						
					) a
					left join t_akun b on(a.kdakun=b.kdakun)
				";

				$rows = DB::select($sql);

				return response()->json($rows[0]);

			}

		}
	}
	
	public function neracaExcel(Request $request, $tgawal, $tgakhir)
	{
		$where = "";
		$where1 = "";
		if($tgawal!=='xxx' && $tgakhir!=='xxx'){
			$where = " and a.tgsawal between to_date('".$tgawal." 00:00:00','yyyy-mm-dd hh24:mi:ss') and to_date('".$tgakhir." 23:59:59','yyyy-mm-dd hh24:mi:ss') ";
			$where1 = " and a.tgdok between to_date('".$tgawal." 00:00:00','yyyy-mm-dd hh24:mi:ss') and to_date('".$tgakhir." 23:59:59','yyyy-mm-dd hh24:mi:ss') ";
		}
		
		$rows = DB::select("
			select  a.kdakun,
					b.nmakun,
					sum(decode(a.kddk,'D',a.nilai,0)) as debet,
					sum(decode(a.kddk,'K',a.nilai,0)) as kredit
			from(
				/* saldo awal */
				select  to_char(a.tgsawal,'YYYY') as thang,
						to_char(a.tgsawal,'MM') as periode,
						a.kddk,
						a.kdakun,
						sum(a.nilai) as nilai
				from d_sawal a
				where a.thang='".session('tahun')."' ".$where."
				group by to_char(a.tgsawal,'YYYY'),
						to_char(a.tgsawal,'MM'),
						a.kdakun,
						a.kddk
				
				union all
				
				/* transaksi berjalan termasuk pajak */
				select  to_char(a.tgdok,'yyyy') as thang,
						to_char(a.tgdok,'mm') as periode,
						a.kddk,
						a.kdakun,
						sum(a.nilai) as nilai
				from(
					".$this->query()."
				) a
				where a.neraca1=1 ".$where1."
				group by to_char(a.tgdok,'yyyy'),
						 to_char(a.tgdok,'mm'),
						 a.kddk,
						 a.kdakun
				
			) a
			left join t_akun b on(a.kdakun=b.kdakun)
			group by a.kdakun,b.nmakun
			order by a.kdakun,b.nmakun
		");
		
		$tot_debet = 0;
		$tot_kredit = 0;
		$values = array();
		foreach($rows as $row) {
			
			$val = (object) array(
				'kdakun' => $row->kdakun,
				'nmakun' => $row->nmakun,
				'debet' => number_format($row->debet,2),
				'kredit' => number_format($row->kredit,2)
			);

			$values[] = $val;

			$tot_debet += $row->debet;
			$tot_kredit += $row->kredit;
			
		}

		$param[] = array(
			'tgawal' => $tgawal,
			'tgakhir' => $tgakhir,
			'debet' => number_format($tot_debet,2),
			'kredit' => number_format($tot_kredit,2),
		);

		$TBS = new clsTinyButStrong();
		$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);	
		
		//load template in folder /doc
		$TBS->LoadTemplate('tbs_template/'.'template_neraca_percobaan.xlsx');
		
		$TBS->Plugin(OPENTBS_SELECT_SHEET,'Sheet1');
		$TBS->MergeBlock('p', $param);
		$TBS->MergeBlock('v', $values);
		
		//download file
		header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		$TBS->Show(OPENTBS_DOWNLOAD,'Neraca_Percobaan.xlsx');
		
	}
	
	public function neracaPenyesuaian(Request $request)
	{
		$where = "";
		if(isset($_GET['periode'])){
			if($_GET['periode']!==''){
				$where = "where a.periode<='".$_GET['periode']."'";
			}
		}
		
		$rows = DB::select("
			select  a.kdakun,
					b.nmakun,
					a.debet,
					a.kredit,
					a.debet1,
					a.kredit1,
					a.debet2,
					a.kredit2
			from(
				select  nvl(a.kdakun,b.kdakun) as kdakun,
						nvl(a.debet,0) as debet,
						nvl(a.kredit,0) as kredit,
						nvl(b.debet,0) as debet1,
						nvl(b.kredit,0) as kredit1,
						nvl(a.debet,0)+nvl(b.debet,0) as debet2,
						nvl(a.kredit,0)+nvl(b.kredit,0) as kredit2
				from(
					/* neraca saldo */
					select  a.kdakun,
							sum(decode(a.kddk,'D',a.nilai,0)) as debet,
							sum(decode(a.kddk,'K',a.nilai,0)) as kredit
					from(
						
						/* saldo awal */
						select  to_char(a.tgsawal,'YYYY') as thang,
								to_char(a.tgsawal,'MM') as periode,
								a.kddk,
								a.kdakun,
								sum(a.nilai) as nilai
						from d_sawal a
						where a.thang='".session('tahun')."'
						group by to_char(a.tgsawal,'YYYY'),
								to_char(a.tgsawal,'MM'),
								a.kdakun,
								a.kddk
						
						union all
						
						/* transaksi berjalan termasuk pajak */
						select  to_char(a.tgdok,'yyyy') as thang,
								to_char(a.tgdok,'mm') as periode,
								a.kddk,
								a.kdakun,
								sum(a.nilai) as nilai
						from(
							".$this->query()."
						) a
						where a.neraca1=1
						group by to_char(a.tgdok,'yyyy'),
								 to_char(a.tgdok,'mm'),
								 a.kddk,
								 a.kdakun
						
					) a
					".$where."
					group by a.kdakun
				) a
				full outer join(
					/* penyesuaian */
					select  a.kdakun,
							sum(decode(a.kddk,'D',a.nilai,0)) as debet,
							sum(decode(a.kddk,'K',a.nilai,0)) as kredit
					from(
						select  to_char(tgdok,'yyyy') as thang,
								to_char(tgdok,'mm') as periode,
								c.kddk,
								c.kdakun,
								sum(c.nilai) as nilai
						from d_trans a
						left join t_alur b on(a.id_alur=b.id)
						left join d_trans_akun c on(a.id=c.id_trans)
						where thang='".session('tahun')."' and b.neraca1=0
						group by to_char(tgdok,'yyyy'),
								 to_char(tgdok,'mm'),
								 c.kddk,
								 c.kdakun
					) a
					".$where."
					group by a.kdakun
				) b on(a.kdakun=b.kdakun)
			) a
			left join t_akun b on(a.kdakun=b.kdakun)
			order by a.kdakun
		");
		
		$data = '';
		$total_debet = 0;
		$total_kredit = 0;
		$total_debet1 = 0;
		$total_kredit1 = 0;
		$total_debet2 = 0;
		$total_kredit2 = 0;
		foreach($rows as $row){
			$data .= '<tr>
						<td>'.$row->kdakun.'</td>
						<td>'.$row->nmakun.'</td>
						<td style="text-align:right;">'.number_format($row->debet,0).'</td>
						<td style="text-align:right;">'.number_format($row->kredit,0).'</td>
						<td style="text-align:right;">'.number_format($row->debet1,0).'</td>
						<td style="text-align:right;">'.number_format($row->kredit1,0).'</td>
						<td style="text-align:right;">'.number_format($row->debet2,0).'</td>
						<td style="text-align:right;">'.number_format($row->kredit2,0).'</td>
					  </tr>';
			$total_debet += $row->debet;
			$total_kredit += $row->kredit;
			$total_debet1 += $row->debet1;
			$total_kredit1 += $row->kredit1;
			$total_debet2 += $row->debet2;
			$total_kredit2 += $row->kredit2;
		}
		
		return response()->json(array(
			'data' => $data,
			'total_debet' => number_format($total_debet,0),
			'total_kredit' => number_format($total_kredit,0),
			'total_debet1' => number_format($total_debet1,0),
			'total_kredit1' => number_format($total_kredit1,0),
			'total_debet2' => number_format($total_debet2,0),
			'total_kredit2' => number_format($total_kredit2,0)
		));
	}
	
	public function neracaLajur(Request $request, $periode)
	{
		$rows = DB::select("
			select  a.*,
					d.nmakun,
					nvl(b.debet,0) as debet1,
					nvl(b.kredit,0) as kredit1,
					nvl(b.saldo,0) as saldo,
					nvl(c.debet,0) as debet2,
					nvl(c.kredit,0) as kredit2
			from(
				select  a.kdakun,
						sum(a.debet) as debet,
						sum(a.kredit) as kredit
				from d_buku_besar a
				where a.thang=? and a.periode<=?
				group by a.kdakun
			) a
			left join(
				select  a.kdakun,
						sum(a.debet) as debet,
						sum(a.kredit) as kredit,
						decode(substr(a.kdakun,1,1),'1',sum(a.debet)-sum(a.kredit),0) as saldo
				from d_buku_besar a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.thang=? and a.periode<=? and b.kdlap='NR'
				group by a.kdakun
			) b on(a.kdakun=b.kdakun)
			left join(
				select  a.kdakun,
						sum(a.debet) as debet,
						sum(a.kredit) as kredit
				from d_buku_besar a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.thang=? and a.periode<=? and b.kdlap='LR'
				group by a.kdakun
			) c on(a.kdakun=c.kdakun)
			left join t_akun d on(a.kdakun=d.kdakun)
			order by a.kdakun
		",[
			session('tahun'),
			$periode,
			session('tahun'),
			$periode,
			session('tahun'),
			$periode
		]);
		
		$data = '';
		$total_debet = 0;
		$total_kredit = 0;
		$total_debet1 = 0;
		$total_kredit1 = 0;
		$total_saldo = 0;
		$total_debet2 = 0;
		$total_kredit2 = 0;
		foreach($rows as $row){
			$data .= '<tr>
						<td>'.$row->kdakun.'</td>
						<td>'.$row->nmakun.'</td>
						<td style="text-align:right;">'.number_format($row->debet,0).'</td>
						<td style="text-align:right;">'.number_format($row->kredit,0).'</td>
						<td style="text-align:right;">'.number_format($row->debet1,0).'</td>
						<td style="text-align:right;">'.number_format($row->kredit1,0).'</td>
						<td style="text-align:right;">'.number_format($row->saldo,0).'</td>
						<td style="text-align:right;">'.number_format($row->debet2,0).'</td>
						<td style="text-align:right;">'.number_format($row->kredit2,0).'</td>
					  </tr>';
			$total_debet += $row->debet;
			$total_kredit += $row->kredit;
			$total_debet1 += $row->debet1;
			$total_kredit1 += $row->kredit1;
			$total_debet2 += $row->debet2;
			$total_kredit2 += $row->kredit2;
			$total_saldo += $row->saldo;
			
		}
		
		//hitung rugi laba
		if($total_debet1>$total_kredit1){
			$total_kredit3 = $total_debet1-$total_kredit1;
			$total_debet3 = 0;
		}
		else{
			$total_debet3 = $total_kredit1-$total_debet1;
			$total_kredit3 = 0;
		}
		
		if($total_debet2>$total_kredit2){
			$total_kredit4 = $total_debet2-$total_kredit2;
			$total_debet4 = 0;
		}
		else{
			$total_debet4 = $total_kredit2-$total_debet2;
			$total_kredit4 = 0;
		}
		
		//hitung total akhir
		$total_debet5 = $total_debet1 + $total_debet3;
		$total_kredit5 = $total_kredit1 + $total_kredit3;
		$total_debet6 = $total_debet2 + $total_debet4;
		$total_kredit6 = $total_kredit2 + $total_kredit4;
		
		return response()->json(array(
			'data' => $data,
			'total_debet' => number_format($total_debet,0),
			'total_kredit' => number_format($total_kredit,0),
			'total_debet1' => number_format($total_debet1,0),
			'total_kredit1' => number_format($total_kredit1,0),
			'total_debet2' => number_format($total_debet2,0),
			'total_kredit2' => number_format($total_kredit2,0),
			'total_debet3' => number_format($total_debet3,0),
			'total_kredit3' => number_format($total_kredit3,0),
			'total_debet4' => number_format($total_debet4,0),
			'total_kredit4' => number_format($total_kredit4,0),
			'total_debet5' => number_format($total_debet5,0),
			'total_kredit5' => number_format($total_kredit5,0),
			'total_debet6' => number_format($total_debet6,0),
			'total_kredit6' => number_format($total_kredit6,0),
			'total_saldo' => number_format($total_saldo,0),
		));
	}

	public function neracaLajurExcel(Request $request, $periode)
	{
		$rows = DB::select("
			select  a.*,
					d.nmakun,
					nvl(b.debet,0) as debet1,
					nvl(b.kredit,0) as kredit1,
					nvl(b.saldo,0) as saldo,
					nvl(c.debet,0) as debet2,
					nvl(c.kredit,0) as kredit2
			from(
				select  a.kdakun,
						sum(a.debet) as debet,
						sum(a.kredit) as kredit
				from d_buku_besar a
				where a.thang=? and a.periode<=?
				group by a.kdakun
			) a
			left join(
				select  a.kdakun,
						sum(a.debet) as debet,
						sum(a.kredit) as kredit,
						decode(substr(a.kdakun,1,1),'1',sum(a.debet)-sum(a.kredit),0) as saldo
				from d_buku_besar a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.thang=? and a.periode<=? and b.kdlap='NR'
				group by a.kdakun
			) b on(a.kdakun=b.kdakun)
			left join(
				select  a.kdakun,
						sum(a.debet) as debet,
						sum(a.kredit) as kredit
				from d_buku_besar a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.thang=? and a.periode<=? and b.kdlap='LR'
				group by a.kdakun
			) c on(a.kdakun=c.kdakun)
			left join t_akun d on(a.kdakun=d.kdakun)
			order by a.kdakun
		",[
			session('tahun'),
			$periode,
			session('tahun'),
			$periode,
			session('tahun'),
			$periode
		]);
		
		$data = '';
		$total_debet = 0;
		$total_kredit = 0;
		$total_debet1 = 0;
		$total_kredit1 = 0;
		$total_saldo1 = 0;
		$total_debet2 = 0;
		$total_kredit2 = 0;
		foreach($rows as $row){
			
			$val = (object) array(
				'kdakun' => $row->kdakun,
				'nmakun' => $row->nmakun,
				'debet' => number_format($row->debet,2),
				'kredit' => number_format($row->kredit,2),
				'debet1' => number_format($row->debet1,2),
				'kredit1' => number_format($row->kredit1,2),
				'saldo1' => number_format($row->saldo,2),
				'debet2' => number_format($row->debet2,2),
				'kredit2' => number_format($row->kredit2,2)
			);

			$values[] = $val;
			
			$total_debet += $row->debet;
			$total_kredit += $row->kredit;
			$total_debet1 += $row->debet1;
			$total_kredit1 += $row->kredit1;
			$total_debet2 += $row->debet2;
			$total_kredit2 += $row->kredit2;
			$total_saldo1 += $row->saldo;
			
		}
		
		//hitung rugi laba
		if($total_debet1>$total_kredit1){
			$total_kredit3 = $total_debet1-$total_kredit1;
			$total_debet3 = 0;
		}
		else{
			$total_debet3 = $total_kredit1-$total_debet1;
			$total_kredit3 = 0;
		}
		
		if($total_debet2>$total_kredit2){
			$total_kredit4 = $total_debet2-$total_kredit2;
			$total_debet4 = 0;
		}
		else{
			$total_debet4 = $total_kredit2-$total_debet2;
			$total_kredit4 = 0;
		}
		
		//hitung total akhir
		$total_debet5 = $total_debet1 + $total_debet3;
		$total_kredit5 = $total_kredit1 + $total_kredit3;
		$total_debet6 = $total_debet2 + $total_debet4;
		$total_kredit6 = $total_kredit2 + $total_kredit4;

		$param[] = array(
			'periode' => $periode.' '.session('tahun'),
			'debet' => number_format($total_debet,2),
			'kredit' => number_format($total_kredit,2),
			'debet1' => number_format($total_debet1,2),
			'kredit1' => number_format($total_kredit1,2),
			'saldo1' => number_format($total_saldo1,2),
			'debet2' => number_format($total_debet2,2),
			'kredit2' => number_format($total_kredit2,2),
			'debet3' => number_format($total_debet3,2),
			'kredit3' => number_format($total_kredit3,2),
			'debet4' => number_format($total_debet4,2),
			'kredit4' => number_format($total_kredit4,2),
			'debet5' => number_format($total_debet5,2),
			'kredit5' => number_format($total_kredit5,2),
			'debet6' => number_format($total_debet6,2),
			'kredit6' => number_format($total_kredit6,2),
		);

		$TBS = new clsTinyButStrong();
		$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);	
		
		//load template in folder /doc
		$TBS->LoadTemplate('tbs_template/'.'template_neraca_lajur.xlsx');
		
		$TBS->Plugin(OPENTBS_SELECT_SHEET,'Sheet1');
		$TBS->MergeBlock('p', $param);
		$TBS->MergeBlock('v', $values);
		
		//download file
		header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		$TBS->Show(OPENTBS_DOWNLOAD,'Neraca_Lajur.xlsx');
		
	}

	public function neracaLajurBaru()
	{
		if (request()->has('lvl') && request()->has('periode')) {

			$lvl = explode(",", $_GET['lvl']);
			$periode = $_GET['periode'];

			if($lvl!=='' && $periode!==''){

				$arr_query = [];
				for($i=0;$i<count($lvl);$i++){

					$substr = $lvl[$i];
					$zero = 6 - $substr;
					$nol = str_repeat('0', $zero);

					$arr_query[] = "
						select  SUBSTR(a.kdakun,1,".$substr.")||'".$nol."' as kdakun,
								SUM(a.sawal_debet) AS sawal_debet,
								SUM(a.sawal_kredit) AS sawal_kredit,
								SUM(a.mutasi_debet) AS mutasi_debet,
								SUM(a.mutasi_kredit) AS mutasi_kredit,
								SUM(a.lr_debet) AS lr_debet,
								SUM(a.lr_kredit) AS lr_kredit,
								SUM(a.nr_debet) AS nr_debet,
								SUM(a.nr_kredit) AS nr_kredit
						from d_buku_besar_dtl a
						where a.thang='".session('tahun')."' and a.periode<='".$periode."'
						group by substr(a.kdakun,1,".$substr.")
					";

				}

				$sql = "
					select  a.*,
							b.nmakun
					from(
						".implode(" union all ", $arr_query)."
					) a
					left join t_akun b on(a.kdakun=b.kdakun)
					order by a.kdakun
				";

				$query = DB::table(DB::raw("($sql) a"))
						->selectRaw('a.*');

				$datatables = DataTables::of($query)
							->editColumn('sawal_debet', function($row){
								return number_format($row->sawal_debet, 0, ',', '.');
							})
							->editColumn('sawal_kredit', function($row){
								return number_format($row->sawal_kredit, 0, ',', '.');
							})
							->editColumn('mutasi_debet', function($row){
								return number_format($row->mutasi_debet, 0, ',', '.');
							})
							->editColumn('mutasi_kredit', function($row){
								return number_format($row->mutasi_kredit, 0, ',', '.');
							})
							->editColumn('lr_debet', function($row){
								return number_format($row->lr_debet, 0, ',', '.');
							})
							->editColumn('lr_kredit', function($row){
								return number_format($row->lr_kredit, 0, ',', '.');
							})
							->editColumn('nr_debet', function($row){
								return number_format($row->nr_debet, 0, ',', '.');
							})
							->editColumn('nr_kredit', function($row){
								return number_format($row->nr_kredit, 0, ',', '.');
							})
							->make(true);

				return $datatables;

			}

		}
	}

	public function neracaLajurTotalBaru()
	{
		if (request()->has('periode')) {

			$periode = $_GET['periode'];

			if($periode!==''){

				$sql = "
					select	a.*,
							a.lr_debet + a.lr_sisa_debet as lr_sisa1_debet,
							a.lr_kredit + a.lr_sisa_kredit as lr_sisa1_kredit,
							a.nr_debet + a.nr_sisa_debet as nr_sisa1_debet,
							a.nr_kredit + a.nr_sisa_kredit as nr_sisa1_kredit
					from(

						select	a.*,
								case
									when a.lr_debet > a.lr_kredit
										then 0
									else
										a.lr_kredit - a.lr_debet
								end as lr_sisa_debet,
								case
									when a.lr_debet > a.lr_kredit
										then a.lr_debet - a.lr_kredit
									else
										0
								end as lr_sisa_kredit,
								case
									when a.nr_debet > a.nr_kredit
										then 0
									else
										a.nr_kredit - a.nr_debet
								end as nr_sisa_debet,
								case
									when a.nr_debet > a.nr_kredit
										then a.nr_debet - a.nr_kredit
									else
										0
								end as nr_sisa_kredit
						from(

							select  SUM(a.sawal_debet) AS sawal_debet,
									SUM(a.sawal_kredit) AS sawal_kredit,
									SUM(a.mutasi_debet) AS mutasi_debet,
									SUM(a.mutasi_kredit) AS mutasi_kredit,
									SUM(a.lr_debet) AS lr_debet,
									SUM(a.lr_kredit) AS lr_kredit,
									SUM(a.nr_debet) AS nr_debet,
									SUM(a.nr_kredit) AS nr_kredit
							from d_buku_besar_dtl a
							where a.thang='".session('tahun')."' and a.periode='".$periode."'

						) a

					) a
				";

				$rows = DB::select($sql);

				return response()->json($rows[0]);

			}

		}
	}

	public function neracaLajurExcelBaru()
	{
		if (request()->has('lvl') && request()->has('periode')) {

			$lvl = explode(",", $_GET['lvl']);
			$periode = $_GET['periode'];

			if($lvl!=='' && $periode!==''){

				$arr_query = [];
				for($i=0;$i<count($lvl);$i++){

					$substr = $lvl[$i];
					$zero = 6 - $substr;
					$nol = str_repeat('0', $zero);

					$arr_query[] = "
						select  SUBSTR(a.kdakun,1,".$substr.")||'".$nol."' as kdakun,
								SUM(a.sawal_debet) AS sawal_debet,
								SUM(a.sawal_kredit) AS sawal_kredit,
								SUM(a.mutasi_debet) AS mutasi_debet,
								SUM(a.mutasi_kredit) AS mutasi_kredit,
								SUM(a.lr_debet) AS lr_debet,
								SUM(a.lr_kredit) AS lr_kredit,
								SUM(a.nr_debet) AS nr_debet,
								SUM(a.nr_kredit) AS nr_kredit
						from d_buku_besar_dtl a
						where a.thang='".session('tahun')."' and a.periode<='".$periode."'
						group by substr(a.kdakun,1,".$substr.")
					";

				}

				$sql = "
					select  a.*,
							b.nmakun
					from(
						".implode(" union all ", $arr_query)."
					) a
					left join t_akun b on(a.kdakun=b.kdakun)
					order by a.kdakun
				";

				$rows = DB::select($sql);

				if(count($rows)>0){

					$data = $rows;

					$sql = "
						select	a.*,
								a.lr_debet + a.lr_sisa_debet as lr_sisa1_debet,
								a.lr_kredit + a.lr_sisa_kredit as lr_sisa1_kredit,
								a.nr_debet + a.nr_sisa_debet as nr_sisa1_debet,
								a.nr_kredit + a.nr_sisa_kredit as nr_sisa1_kredit
						from(

							select	a.*,
									case
										when a.lr_debet > a.lr_kredit
											then 0
										else
											a.lr_kredit - a.lr_debet
									end as lr_sisa_debet,
									case
										when a.lr_debet > a.lr_kredit
											then a.lr_debet - a.lr_kredit
										else
											0
									end as lr_sisa_kredit,
									case
										when a.nr_debet > a.nr_kredit
											then 0
										else
											a.nr_kredit - a.nr_debet
									end as nr_sisa_debet,
									case
										when a.nr_debet > a.nr_kredit
											then a.nr_debet - a.nr_kredit
										else
											0
									end as nr_sisa_kredit
							from(

								select  SUM(a.sawal_debet) AS sawal_debet,
										SUM(a.sawal_kredit) AS sawal_kredit,
										SUM(a.mutasi_debet) AS mutasi_debet,
										SUM(a.mutasi_kredit) AS mutasi_kredit,
										SUM(a.lr_debet) AS lr_debet,
										SUM(a.lr_kredit) AS lr_kredit,
										SUM(a.nr_debet) AS nr_debet,
										SUM(a.nr_kredit) AS nr_kredit
								from d_buku_besar_dtl a
								where a.thang='".session('tahun')."' and a.periode='".$periode."'

							) a

						) a
					";

					$rows = DB::select($sql);

					if(count($rows)>0){

						$total = $rows[0];

						$rows = DB::select("
							select	to_char(last_day(to_date('".session('tahun')."-".$periode."-01','yyyy-mm-dd')),'dd') as tgl_pelaporan,
									upper(nmbulan) as nmbulan
							from t_bulan
							where bulan='".$periode."'
						");

						$bulan = "N/A";
						$tgl_pelaporan = "N/A";
						if(count($rows)>0){
							$bulan = $rows[0]->nmbulan;
							$tgl_pelaporan = $rows[0]->tgl_pelaporan.' '.$bulan.' '.session('tahun');
						}

						$param = array(
							'thang' => session('tahun'),
							'periode' => $periode,
							'bulan' => $bulan,
							'tgl_pelaporan' => $tgl_pelaporan,
						);

						$output = [
							'params' => $param,
							'rows' => $data,
							'totals' => $total
						];

						//return view('excel.neraca-lajur', $output);

						return Excel::download(new NeracaLajurExport($output), 'neraca-lajur-'.session('tahun').'-'.$periode.'.xlsx');

					}
					else{
						return 'Total data tidak ditemukan.';
					}

				}
				else{
					return 'Baris data tidak ditemukan.';
				}

			}

		}
	}
	
}