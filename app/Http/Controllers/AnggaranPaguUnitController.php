<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AnggaranPaguUnitController extends Controller {

	public function index(Request $request)
	{
		$panjang = strlen(session('kdunit'));
		
		$arrLevel = ['03','05','08','11'];
		
		$and = "";
		if(in_array(session('kdlevel'), $arrLevel)){
			$and = " and substr(a.kdunit,1,".$panjang.")='".session('kdunit')."'";
		}
		
		$where = array();
		
		if(isset($_GET['kdsdana'])){
			if($_GET['kdsdana']!=='' && $_GET['kdsdana']!==null){
				$where[] = " a.kdsdana='".$_GET['kdsdana']."' ";
			}
		}
		
		if(isset($_GET['id_proyek'])){
			if($_GET['id_proyek']!=='' && $_GET['id_proyek']!==null){
				$where[] = " a.id_proyek='".$_GET['id_proyek']."' ";
			}
		}
		
		if(isset($_GET['kdunit'])){
			if($_GET['kdunit']!=='' && $_GET['kdunit']!==null){
				$where[] = " a.kdunit='".$_GET['kdunit']."' ";
			}
		}
		
		if(isset($_GET['kdakun'])){
			if($_GET['kdakun']!=='' && $_GET['kdakun']!==null){
				$where[] = " a.kdakun='".$_GET['kdakun']."' ";
			}
		}
		
		$str_where = "";
		if(count($where)>0){
			$str_where = " and ".implode(" and ", $where);
		}
		
		$aColumns = array('id','nmsdana','nmproyek','nmunit','kdakun','nmakun','pagu','realisasi','sisa');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  a.id,
							f.nmsdana,
							e.nmproyek,
							c.nmunit,
							a.kdakun,
							b.nmakun,
							a.nilai as pagu,
							nvl(d.nilai,0) as realisasi,
							a.nilai-nvl(d.nilai,0) as sisa
					from d_pagu a
					left join t_akun b on(a.kdakun=b.kdakun)
					left outer join t_unit c on(a.kdunit=c.kdunit)
					left outer join(
						select  a.thang,
								a.kdunit,
								a.kdsdana,
								a.id_proyek,
								c.kdakun,
								sum(c.nilai) as nilai
						from d_trans a
						left join t_alur b on(a.id_alur=b.id)
						left join d_trans_akun c on(a.id=c.id_trans)
						where b.menu=4 and c.kddk='D'
						group by a.thang,a.kdunit,a.kdsdana,a.id_proyek,c.kdakun
					) d on(a.thang=d.thang and a.kdunit=d.kdunit and a.kdsdana=d.kdsdana and a.id_proyek=d.id_proyek and a.kdakun=d.kdakun)
					left outer join t_proyek e on(a.id_proyek=e.id)
					left join t_sdana f on(a.kdsdana=f.kdsdana)
					where a.thang='".session('tahun')."' ".$and." ".$str_where."
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
				$sWhere=" where lower(nmunit) like lower('".$sSearch."%') or lower(nmunit) like lower('%".$sSearch."%')";
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
								<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>
								<a id="'.$row->id.'" class="dropdown-item hapus" href="javascript:;">Hapus Data</a>
							</div>
						</center>';
			}
			
			$output['aaData'][] = array(
				$row->no,
				$row->nmsdana,
				$row->nmproyek,
				$row->nmunit,
				$row->kdakun,
				$row->nmakun,
				'<div style="text-align:right;">'.number_format($row->pagu).'</div>',
				'<div style="text-align:right;">'.number_format($row->realisasi).'</div>',
				'<div style="text-align:right;">'.number_format($row->sisa).'</div>',
				$aksi
			);
		}
		
		return response()->json($output);
	}
	
	public function pilih(Request $request, $id)
	{
		$rows = DB::select("
			select  id,
					kdsdana,
					id_proyek,
					kdunit,
					nilai,
					kdakun
			from d_pagu a
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
				
			/*$rows = DB::select("
				SELECT	count(*) AS jml
				from d_pagu
				where thang=? and kdunit=? and kdakun=?
			",[
				session('tahun'),
				$request->input('kdunit'),
				$request->input('kdakun'),
			]);
			
			if($rows[0]->jml==0){*/
				
				$insert = DB::table('d_pagu')->insert([
					'thang' => session('tahun'),
					'kdsdana' => $request->input('kdsdana'),
					'id_proyek' => $request->input('id_proyek'),
					'kdunit' => $request->input('kdunit'),
					'kdakun' => $request->input('kdakun'),
					'nilai' => str_replace(",", "", $request->input('nilai')),
					'id_user' => DB::raw('sysdate')
				]);
				
				if($insert){
					return 'success';
				}
				else{
					return 'Data gagal disimpan!';
				}
				
			/*}
			else{
				return 'Duplikasi data!';
			}*/
			
		}
		else{
			
			$update = DB::update("
				update d_pagu
				set kdsdana=?,
					id_proyek=?,
					kdunit=?,
					kdakun=?,
					nilai=?,
					id_user=?,
					updated_at=sysdate
				where id=?
			",[
				$request->input('kdsdana'),
				$request->input('id_proyek'),
				$request->input('kdunit'),
				$request->input('kdakun'),
				str_replace(",", "", $request->input('nilai')),
				session('id_user'),
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
			delete from d_pagu
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
	
	public function sisaPagu()
	{
		$kdsdana = "";
		if(isset($_GET['kdsdana'])){
			$kdsdana = " and kdsdana='".$_GET['kdsdana']."' ";
		}
		
		$id_proyek = "";
		if(isset($_GET['id_proyek'])){
			$id_output = " and id_proyek='".$_GET['id_proyek']."' ";
		}
		
		$kdakun = "";
		if(isset($_GET['kdakun'])){
			$kdakun = " and kdakun='".$_GET['kdakun']."' ";
		}
		
		$kdakun1 = "";
		if(isset($_GET['kdakun'])){
			$kdakun1 = " and debet='".$_GET['kdakun']."' ";
		}
		
		$rows = DB::select("
			select  nvl(a.nilai,0) as pagu,
					nvl(b.nilai,0) as realisasi,
					nvl(a.nilai,0)-nvl(b.nilai,0) as sisa
			from(
				select  sum(nilai) as nilai
				from d_pagu
				where kdunit=? and thang=? ".$kdsdana." ".$id_proyek." ".$kdakun."
			) a,
			(
				select  sum(nilai) as nilai
				from d_trans
				where kdunit=? and thang=? ".$kdsdana." ".$id_proyek." ".$kdakun1."
			) b
		",[
			substr(session('kdunit'),0,4),
			session('tahun'),
			substr(session('kdunit'),0,4),
			session('tahun')
		]);
		
		return response()->json($rows[0]);
	}
	
	public function revisike(Request $request)
	{
		$rows = DB::select("
			select  nvl(max(revisike),0)+1 as revisike
			from d_pagu
			where thang=?
		",[
			session('tahun')
		]);
		
		if(count($rows)>0){
			return response()->json($rows[0]);
		}
	}
	
	public function simpanRevisi(Request $request)
	{
		DB::beginTransaction();
			
		$insert = DB::insert("
			insert into h_pagu(id_proyek,kdsdana,kdunit,thang,nodok,tgdok,revisike,kdakun,nilai,created_at,updated_at)
			select  id_proyek,
					kdsdana,
					kdunit,
					thang,
					nodok,
					tgdok,
					nvl(revisike,0) as revisike,
					kdakun,
					nilai,
					created_at,
					updated_at
			from d_pagu
			where thang=?
		",[
			session('tahun')
		]);
		
		$update = DB::update("
			update d_pagu
			set nodok=?,
				tgdok=to_date(?,'yyyy-mm-dd'),
				revisike=?
			where thang=?
		",[
			$request->input('nodok'),
			$request->input('tgdok'),
			$request->input('revisike'),
			session('tahun')
		]);
		
		if($update) {
			DB::commit();
			return 'success';
		}
		else {
			return 'Proses simpan revisi gagal. Hubungi Administrator.';
		}
	}
	
}