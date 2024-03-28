<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PembukuanSaldoAwalController extends Controller {

	public function index(Request $request)
	{
		$aColumns = array('id','nmproyek','kdakun','debet','kredit','tgsawal','created_at');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  	a.id,
							b.nmproyek,
							a.kdakun,
							decode(a.kddk,'D',a.nilai,0) as debet,
							decode(a.kddk,'K',a.nilai,0) as kredit,
							to_char(a.tgsawal,'dd-mm-yyyy') as tgsawal,
							to_char(a.created_at,'dd-mm-yyyy hh24:mi:ss') as created_at
					from d_sawal a
					left outer join t_proyek b on(a.id_proyek=b.id)
					left outer join t_akun c on(a.kdakun=c.kdakun)
					where a.thang='".session('tahun')."'
					order by a.id desc";
		
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
				$sWhere=" where lower(nmproyek) like lower('".$sSearch."%') or lower(nmproyek) like lower('%".$sSearch."%') or
								lower(kdakun) like lower('".$sSearch."%') or lower(kdakun) like lower('%".$sSearch."%') ";
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
			if(session('kdlevel')=='00' || session('kdlevel')=='04'){
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
				$row->nmproyek,
				$row->kdakun,
				'<div style="text-align:right;">'.number_format($row->debet).'</div>',
				'<div style="text-align:right;">'.number_format($row->kredit).'</div>',
				$row->tgsawal,
				$row->created_at,
				$aksi
			);
		}
		
		return response()->json($output);
	}
	
	public function total()
	{
		$and = "";
		if(isset($_GET['param'])){
			if($_GET['param']!==''){
				$sSearch = $_GET['param'];
				$and = "  and lower(b.nmproyek) like lower('".$sSearch."%') or lower(b.nmproyek) like lower('%".$sSearch."%') or
							  lower(a.kdakun) like lower('".$sSearch."%') or lower(a.kdakun) like lower('%".$sSearch."%') ";
			}
		}
		
		$rows = DB::select("
			select  sum(decode(a.kddk,'D',a.nilai,0)) as debet,
					sum(decode(a.kddk,'K',a.nilai,0)) as kredit
			from d_sawal a
			left outer join t_proyek b on(a.id_proyek=b.id)
			left outer join t_akun c on(a.kdakun=c.kdakun)
			where a.thang='".session('tahun')."' ".$and."
		");
		
		return response()->json(array(
			'debet' => number_format($rows[0]->debet),
			'kredit' => number_format($rows[0]->kredit)
		));
	}
	
	public function pilih(Request $request, $id)
	{
		$rows = DB::select("
			select  id,
					id_proyek,
					kdakun,
					kddk,
					abs(nilai) as nilai,
					to_char(tgsawal,'yyyy-mm-dd') as tgsawal,
					case
						when nilai<0
							then '-'
						else
							''
					end as kdplus
			from d_sawal
			where id=?
		",[
			$id
		]);
		
		if(count($rows)>0){
			$data['error'] = false;
			$data['message'] = $rows[0];
		}
		else{
			$data['error'] = true;
			$data['message'] = 'Data akun tidak ditemukan!';
		}
		
		return response()->json($data);
	}
	
	public function simpan(Request $request)
	{
		if($request->input('inp-rekambaru')=='1'){
			
			$rows = DB::select("
				SELECT	count(*) AS jml
				from d_sawal
				where thang=? and kdakun=? and id_proyek=?
			",[
				session('tahun'),
				$request->input('kdakun'),
				$request->input('id_proyek'),
			]);
			
			if($rows[0]->jml==0){
				
				$insert = DB::table('d_sawal')->insert([
					'thang' => session('tahun'),
					'kdakun' => $request->input('kdakun'),
					'kddk' => $request->input('kddk'),
					'nilai' => $request->input('kdplus').str_replace(",", "", $request->input('nilai')),
					'tgsawal' => $request->input('tgsawal'),
					'id_proyek' => $request->input('id_proyek'),
					'id_user' => session('id_user')
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
				update d_sawal
				set kddk=?,
					nilai=?,
					tgsawal=?,
					id_user=?,
					updated_at=sysdate
				where id=?
			",[
				$request->input('kddk'),
				$request->input('kdplus').str_replace(",", "", $request->input('nilai')),
				$request->input('tgsawal'),
				session('id_user'),
				$request->input('inp-id')
			]);
			
			if($update){
				return 'success';
			}
			else{
				return 'Data gagal disimpan!';
			}
			
		}
			
	}
	
	public function hapus(Request $request)
	{
		$delete = DB::delete("
			delete from d_sawal
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