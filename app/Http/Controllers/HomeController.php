<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HomeController extends Controller {

	//index
	public function total()
	{
		$rows = DB::select("
			select  a.jml1,
					round(a.nilai1/1000000) as nilai1,
					b.jml2,
					round(b.nilai2/1000000) as nilai2,
					c.jml3,
					round(c.nilai3/1000000) as nilai3,
					d.jml4,
					round(d.nilai4/1000000) as nilai4
			from(
				select  count(a.id) as jml1,
						sum(a.nilai) as nilai1
				from d_trans a
				left outer join t_alur c on(a.id_alur=c.id)
				where a.thang='".session('tahun')."' and c.menu=1
			) a,
			(
				select  count(a.id) as jml2,
						sum(a.nilai) as nilai2
				from d_trans a
				left outer join t_alur c on(a.id_alur=c.id)
				where a.thang='".session('tahun')."' and c.menu=2
			) b,
			(
				select  count(a.id) as jml3,
						sum(a.nilai) as nilai3
				from d_trans a
				left outer join t_alur c on(a.id_alur=c.id)
				where a.thang='".session('tahun')."' and c.menu=3
			) c,

			(
				select  count(a.id) as jml4,
						sum(a.nilai) as nilai4
				from d_trans a
				left outer join t_alur c on(a.id_alur=c.id)
				where a.thang='".session('tahun')."' and c.menu=4
			) d
		");
		
		return response()->json($rows[0]);
	}
	
	public function data(Request $request)
	{
		$panjang = strlen(session('kdunit'));
		
		$aColumns = array('id','nourut','nmunit','nama','nmtrans','nilai','status','waktu','url');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select    a.*
					from(
						select  a.id,
								lpad(a.nourut,5,'0') as nourut,
								d.nmunit,
								e.nama,
								h.nmtrans,
								a.nodok as pks,
								to_char(a.tgdok1,'dd-mm-yyyy') as tgjtempo,
								nvl(a.nilai,0) as nilai,
								c.nmstatus as status,
								decode(c.is_unit,null,
									1,
									decode(substr(a.kdunit,1,".$panjang."),'".session('kdunit')."',
										1,
										0
									)
								) as akses,
								i.url,
								round(24*(sysdate-a.updated_at),2) as waktu
						from d_trans a
						left outer join t_alur b on(a.id_alur=b.id)
						left outer join t_alur_status c on(a.id_alur=c.id_alur and a.status=c.status)
						left outer join t_unit d on(a.kdunit=d.kdunit)
						left outer join t_penerima e on(a.id_penerima=e.id)
						left outer join t_level g on(c.kdlevel=g.kdlevel)
						left outer join t_trans h on(a.kdtran=h.id)
						left outer join t_alur_menu i on(b.menu=i.menu)
						where a.thang='".session('tahun')."' and c.kdlevel='".session('kdlevel')."'  and a.id_alur<>10 and c.is_final is null
					) a
					where a.akses=1
					order by a.id desc
					";
		
		/*
		 * Paging
		 */ 
		$sLimit = " ";
		if((isset($_GET['iDisplayStart']))&&(isset($_GET['iDisplayLength']))){
			$iDisplayStart=$_GET['iDisplayStart']+1;
			$iDisplayLength=$_GET['iDisplayLength'];
			$sSearch=$_GET['sSearch'];
			if ((isset( $iDisplayStart )) &&  ($iDisplayLength != '-1' )) 
			{
				$iDisplayEnd=$iDisplayStart+$iDisplayLength-1;
				$sLimit = " WHERE NO BETWEEN '$iDisplayStart' AND '$iDisplayEnd'";
			}
		}
		
		/*
		 * Ordering
		 */
		$sOrder = " ";
		if((isset($_GET['iSortCol_0']))&&(isset($_GET['sSortDir_0']))){
			$iSortCol_0=$_GET['iSortCol_0'];
			$iSortDir_0=$_GET['sSortDir_0'];
			if ( isset($iSortCol_0  ) )
			{		
				//modified ordering
				for($i=0;$i<count($aColumns);$i++){
					if($iSortCol_0==$i){
						if($iSortDir_0=='asc'){
							$sOrder = " ORDER BY ".$aColumns[$i]." DESC ";
						}
						else{
							$sOrder = " ORDER BY ".$aColumns[$i]." ASC ";
						}
					}
				}
			}
		}
		
		//modified filtering
		$sWhere="";
		if(isset($_GET['sSearch'])){
			$sSearch=$_GET['sSearch'];
			if((isset($sSearch))&&($sSearch!='')){
				$sWhere=" where lower(pks) like lower('".$sSearch."%') or lower(pks) like lower('%".$sSearch."%') or
								lower(nourut) like lower('".$sSearch."%') or lower(nourut) like lower('%".$sSearch."%') ";
			}
		}
		
		/* Data set length after filtering */
		$iFilteredTotal = 0;
		$rows = DB::select("
			SELECT COUNT(*) as JUMLAH FROM (".$sTable.") qry
		");
		$result = (array)$rows[0];
		if($result){
			$iFilteredTotal = $result['jumlah'];
		}
		
		/* Total data set length */
		$iTotal = 0;
		$rows = DB::select("
			SELECT COUNT(".$sIndexColumn.") as JUMLAH FROM (".$sTable.") qry
		");
		$result = (array)$rows[0];
		if($result){
			$iTotal = $result['jumlah'];
		}

		/*
		 * Format Output
		 */
		$sEcho="";
		if(isset($_GET['sEcho'])){
			$sEcho=$_GET['sEcho'];
		}
		$output = array(
			"sEcho" => intval($sEcho),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iFilteredTotal,
			"aaData" => array()
		);
		
		$str=str_replace(" , ", " ", implode(", ", $aColumns));
		
		$sQuery = "SELECT * FROM ( SELECT ROWNUM AS NO,".$str." FROM ( SELECT * FROM (".$sTable.") ".$sOrder.") ".$sWhere." ) a ".$sLimit." ";
		
		$rows = DB::select($sQuery);
		
		foreach( $rows as $row )
		{
			$aksi='<a href="#/'.$row->url.'?id_trans='.$row->nourut.'" title="Proses data ini?">'.number_format($row->waktu,2).' jam</a>';
			
			$output['aaData'][] = array(
				$row->no,
				$row->nourut,
				$row->nmunit,
				$row->nama,
				$row->nmtrans,
				'<div style="text-align:right;">'.number_format($row->nilai).'</div>',
				$row->status,
				$aksi
			);
		}
		
		return response()->json($output);
	}
	
}