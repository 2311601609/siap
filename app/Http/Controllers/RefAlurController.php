<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RefAlurController extends Controller {

	public function index(Request $request)
	{
		$aColumns = array('id','nmalur','nmlevel','status','nmstatus','proses');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  a.id,
							b.nmalur,
							c.nmlevel,
							a.status,
							a.nmstatus,
							nvl(d.proses,'') as proses
					from t_alur_status a
					left outer join t_alur b on(a.id_alur=b.id)
					left outer join t_level c on(a.kdlevel=c.kdlevel)
					left outer join(
						select  a.id_alur_status,
							   listagg(b.nmstatus, ',') within group(order by a.nourut) as proses
						from t_alur_status_dtl a
						left join t_alur_status b on(a.id_alur_status_lanjut=b.id)
						group by a.id_alur_status
					) d on(a.id=d.id_alur_status)
					order by a.id_alur,a.nourut asc
				";
		
		/*
		 * Paging
		 */ 
		$sLimit = " ";
		if((isset($_GET['iDisplayStart']))&&(isset($_GET['iDisplayLength']))){
			$iDisplayStart=$_GET['iDisplayStart']+1;
			$iDisplayLength=$_GET['iDisplayLength'];
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
				$sWhere=" where lower(nmalur) like lower('".$sSearch."%') or lower(nmalur) like lower('%".$sSearch."%')";
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
			$aksi='';
			if(session('kdlevel')=='00'){
				/*$aksi='<center>
							<button type="button" class="btn btn-raised btn-sm btn-icon btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
							<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
								<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>
								<a id="'.$row->id.'" class="dropdown-item hapus" href="javascript:;">Hapus Data</a>
							</div>
						</center>';*/
			}
			
			$arr_proses = explode(",", $row->proses);
			$proses = '<ul>';
			for($j=0; $j<count($arr_proses); $j++){
				$proses .= '<li>'.$arr_proses[$j].'</li>';
			}
			$proses .= '</ul>';
			
			$output['aaData'][] = array(
				$row->id,
				$row->nmalur,
				$row->nmlevel,
				$row->status,
				$row->nmstatus,
				$proses
			);
		}
		
		return response()->json($output);
	}
	
	public function pilih(Request $request, $id)
	{
		try{
			$rows = DB::select("
				select  a.*
				from t_akun a
				where a.kdakun=?
			",[
				$id
			]);
			
			if(count($rows)>0){
				return response()->json($rows[0]);
			}
			
		}
		catch(\Exception $e){
			return 'Kesalahan lainnya!';
		}
	}
	
	public function simpan(Request $request)
	{
		try{
			if($request->input('inp-rekambaru')=='1'){
				
				$rows = DB::select("
					select count(*) as jml
					from t_akun
					where kdakun=?
				",[
					$request->input('kdakun')
				]);
				
				if($rows[0]->jml==0){
					
					$insert = DB::table('t_akun')->insert([
						'kdakun' => $request->input('kdakun'),
						'nmakun' => $request->input('nmakun'),
						'kddk' => $request->input('kddk'),
						'kdlap' => $request->input('kdlap'),
						'lvl' => $request->input('lvl'),
					]);
					
					if($insert){
						return 'success';
					}
					else{
						return 'Data gagal disimpan!';
					}
					
				}
				else{
					return 'Duplikasi kode akun!';
				}
				
			}
			else{
				
				$update = DB::update("
					update t_akun
					set nmakun=?,
						kddk=?,
						kdlap=?,
						lvl=?
					where kdakun=?
				",[
					$request->input('nmakun'),
					$request->input('kddk'),
					$request->input('kdlap'),
					$request->input('lvl'),
					$request->input('inp-id')
				]);
				
				if($update){
					return 'success';
				}
				else{
					return 'Data gagal diubah!';
				}
				
			}			
		}
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya, hubungi Administrator!';
		}		
	}
	
	public function hapus(Request $request)
	{
		try{
			$delete = DB::delete("
				delete from t_akun
				where kdakun=?
			",[
				$request->input('id')
			]);
			
			if($delete==true) {
				return 'success';
			}
			else {
				return 'Proses hapus gagal. Hubungi Administrator.';
			}
			
		}
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya, hubungi Administrator!';
		}		
	}
	
}