<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RefProyekController extends Controller {

	public function index(Request $request)
	{
		$aColumns = array('id','kdproyek','nmproyek','jenis');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  a.id,
							a.kdproyek,
							a.nmproyek,
							decode(a.is_proyek,'1','Proyek','Non Proyek') as jenis
					from t_proyek a
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
				$sWhere=" where lower(nmproyek) like lower('".$sSearch."%') or lower(nmproyek) like lower('%".$sSearch."%')";
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
			if(session('kdlevel')=='00' || session('kdlevel')=='01'){
				$aksi='<center>
							<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
							<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
								<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>
								<a id="'.$row->id.'" class="dropdown-item hapus" href="javascript:;">Hapus Data</a>
							</div>
						</center>';
			}
			
			$output['aaData'][] = array(
				$row->no,
				$row->kdproyek,
				$row->nmproyek,
				$row->jenis,
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
				from t_proyek a
				where a.id=?
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
		if($request->input('inp-rekambaru')=='1'){
				
			$insert = DB::table('t_proyek')->insert([
				'kdproyek' => $request->input('kdproyek'),
				'nmproyek' => $request->input('nmproyek'),
				'alamat' => $request->input('alamat'),
				'deskripsi' => $request->input('deskripsi'),
				'ket' => $request->input('ket'),
				'is_proyek' => $request->input('is_proyek')
			]);
			
			if($insert){
				return 'success';
			}
			else{
				return 'Data gagal disimpan!';
			}
			
		}
		else{
			
			$update = DB::update("
				update t_proyek
				set kdproyek=?,
					nmproyek=?,
					alamat=?,
					deskripsi=?,
					ket=?,
					is_proyek=?
				where id=?
			",[
				$request->input('kdproyek'),
				$request->input('nmproyek'),
				$request->input('alamat'),
				$request->input('deskripsi'),
				$request->input('ket'),
				$request->input('is_proyek'),
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
		try{
			$rows = DB::select("
				select	count(*) as jml
				from d_trans
				where id_proyek=?
			",[
				$request->input('id')
			]);
			
			if($rows[0]->jml==0){
				
				$delete = DB::delete("
					delete from t_proyek
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
			else{
				return 'Data tidak dapat dihapus karena sudah digunakan di transaksi.';
			}
			
		}
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya, hubungi Administrator!';
		}		
	}
	
}