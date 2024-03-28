<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AnggaranTargetController extends Controller {

	public function index(Request $request)
	{
		$where = "";
		if(session('kdlevel')=='04'||session('kdlevel')=='05'){
			$where = " and a.kdunit='".session('kdunit')."' ";
		}
		elseif(session('kdlevel')=='06'||session('kdlevel')=='07'||session('kdlevel')=='08'||session('kdlevel')=='11'){
			$where = " and a.kdunit='".substr(session('kdunit'),0,4)."' ";
		}
		
		$aColumns = array('id','thang','nmunit','nilai');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  a.id,
							a.thang,
							c.nmunit,
							a.nilai
					from d_target a
					left outer join t_unit c on(a.kdunit=c.kdunit)
					where a.thang='".session('tahun')."' ".$where."
					order by a.id desc";
		
		/*
		 * Paging
		 */ 
		$sLimit = " ";
		if((isset($_GET['iDisplayStart']))&&(isset($_GET['iDisplayLength']))){
			$iDisplayStart=$_GET['iDisplayStart']+1;
			$iDisplayLength=$_GET['iDisplayLength'];
			$sSearch=$_GET['sSearch'];
			if (($sSearch=='') && (isset( $iDisplayStart )) &&  ($iDisplayLength != '-1' )) 
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
				$sWhere=" where lower(nmunit) like lower('".$sSearch."%') or lower(nmunit) like lower('%".$sSearch."%') or
								lower(uraian) like lower('".$sSearch."%') or lower(uraian) like lower('%".$sSearch."%') ";
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
							<button type="button" class="btn btn-raised btn-sm btn-icon btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
							<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
								<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>
								<a id="'.$row->id.'" class="dropdown-item hapus" href="javascript:;">Hapus Data</a>
							</div>
						</center>';
			}
			
			$output['aaData'][] = array(
				$row->no,
				$row->thang,
				$row->nmunit,
				'<div style="text-align:right;">'.number_format($row->nilai).'</div>',
				$aksi
			);
		}
		
		return response()->json($output);
	}
	
	public function pilih(Request $request, $id)
	{
		$rows = DB::select("
			select  id,
					kdunit,
					nilai
			from d_target a
			where a.id=?
		",[
			$id
		]);
		
		if(count($rows)>0){
			return response()->json($rows[0]);
		}
	}
	
	public function simpan(Request $request)
	{
		if($request->input('inp-rekambaru')=='1'){
				
			$rows = DB::select("
				SELECT	count(*) AS jml
				from d_target
				where thang=? and kdunit=?
			",[
				session('tahun'),
				$request->input('kdunit')
			]);
			
			if($rows[0]->jml==0){
				
				$insert = DB::table('d_target')->insert([
					'thang' => session('tahun'),
					'kdunit' => $request->input('kdunit'),
					'nilai' => str_replace(",", "", $request->input('nilai'))
				]);
				
				if($insert){
					return 'success';
				}
				else{
					return 'Data gagal disimpan!';
				}
				
			}
			else{
				return 'Duplikasi data!';
			}
			
		}
		else{
			
			$update = DB::update("
				update d_target
				set nilai=?
				where id=?
			",[
				str_replace(",", "", $request->input('nilai')),
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
	
	public function hapus(Request $request)
	{
		$delete = DB::delete("
			delete from d_target
			where id=?
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
	
}