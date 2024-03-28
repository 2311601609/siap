<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RefAkunController extends Controller {

	public function index(Request $request)
	{
		$aColumns = array('kdakun','nmakun','kddk','nmlap','lvl');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "kdakun";
		/* DB table to use */
		$sTable = "select  a.kdakun,
							a.nmakun,
							a.kddk,
							b.nmlap,
							a.lvl
					from t_akun a
					left outer join t_lap b on(a.kdlap=b.kdlap)
					order by to_number(a.kdakun) asc
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
				$sWhere=" where lower(kdakun) like lower('".$sSearch."%') or lower(kdakun) like lower('%".$sSearch."%') or
								lower(nmakun) like lower('".$sSearch."%') or lower(nmakun) like lower('%".$sSearch."%') or
								lower(nmlap) like lower('".$sSearch."%') or lower(nmlap) like lower('%".$sSearch."%')";
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
				$aksi='<center>
							<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
							<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
								<a id="'.$row->kdakun.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>
								<a id="'.$row->kdakun.'" class="dropdown-item hapus" href="javascript:;">Hapus Data</a>
							</div>
						</center>';
			}
			
			$output['aaData'][] = array(
				$row->kdakun,
				$row->nmakun,
				$row->kddk,
				$row->nmlap,
				$row->lvl,
				$aksi
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