<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class KoreksiTransaksiController extends Controller {

	public function index(Request $request)
	{
		$aColumns = array('id','nmunit','nmalur','nmtrans','nourut','nilai','nmstatus','nmlevel');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  a.id,
							d.nmunit,
							b.nmalur,
							c.nmtrans,
							lpad(a.nourut,5,'0') as nourut,
							nvl(a.nilai,0) as nilai,
							e.nmstatus,
							f.nmlevel
					from d_trans a
					left join t_alur b on(a.id_alur=b.id)
					left join t_trans c on(a.kdtran=c.id)
					left join t_unit d on(a.kdunit=d.kdunit)
					left join t_alur_status e on(a.id_alur=e.id_alur and a.status=e.status)
					left join t_level f on(e.kdlevel=f.kdlevel)
					where a.thang='".session('tahun')."' and b.menu in(1,2,3,4,6)
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
				$sWhere=" where lower(nourut) like lower('".$sSearch."%') or lower(nourut) like lower('%".$sSearch."%') or nilai=".$sSearch." ";
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
			$aksi='<center>
						<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
						<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
							<a id="'.$row->id.'" class="dropdown-item proses" href="javascript:;">Ubah Data</a>
							<a id="'.$row->id.'" class="dropdown-item hapus" href="javascript:;">Hapus Data</a>
						</div>
					</center>';
			
			$output['aaData'][] = array(
				$row->no,
				$row->nmunit,
				$row->nmalur,
				$row->nmtrans,
				$row->nourut,
				'<div style="text-align:right;">'.number_format($row->nilai).'</div>',
				$row->nmstatus,
				$row->nmlevel,
				$aksi
			);
		}
		
		return response()->json($output);
	}
	
	public function pilih(Request $request, $id)
	{
		$rows = DB::select("
			select  a.id,
					a.id_alur,
					a.status,
					d.nmunit,
					b.nmalur,
					c.nmtrans,
					lpad(a.nourut,5,'0') as nourut,
					nvl(a.nilai,0) as nilai,
					e.nmstatus,
					f.nmlevel
			from d_trans a
			left join t_alur b on(a.id_alur=b.id)
			left join t_trans c on(a.kdtran=c.id)
			left join t_unit d on(a.kdunit=d.kdunit)
			left join t_alur_status e on(a.id_alur=e.id_alur and a.status=e.status)
			left join t_level f on(e.kdlevel=f.kdlevel)
			where a.id=?
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$id_alur = $rows[0]->id_alur;
			$status = $rows[0]->status;
			$detil = $rows[0];
			$data['error'] = false;
			$data['message'] = $detil;
			
			$rows = DB::select("
				select  a.status,
						a.nmstatus,
						b.nmlevel
				from t_alur_status a
				left join t_level b on(a.kdlevel=b.kdlevel)
				where a.id_alur=?
				order by a.nourut
			",[
				$id_alur
			]);
			
			$status1 = '<option value="">Pilih Data</option>';
			foreach($rows as $row){
				$cek = '';
				if($row->status==$status){
					$cek = 'selected';
				}
				$status1 .= '<option value="'.$row->status.'" '.$cek.'>'.$row->nmstatus.' - '.$row->nmlevel.'</option>';
			}
			
			$data['dropdown'] = $status1;
			
		}
		else{
			$data['error'] = true;
			$data['message'] = 'Data header tidak ditemukan!';
		}
		
		return response()->json($data);
	}
	
	public function simpan(Request $request)
	{
		$rows = DB::select("
			select	count(*) as jml
			from d_trans a
			where a.id=? and a.id_alur=? and a.status=?
		",[
			$request->input('inp-id'),
			$request->input('id_alur'),
			$request->input('status')
		]);
		
		if(count($rows)>0){
			
			DB::beginTransaction();
					
			$insert = DB::insert("
				insert into d_trans_histori(id_trans,id_alur,status,ket,id_user,created_at,updated_at)
				select	id,id_alur,status,ket,id_user,created_at,updated_at
				from d_trans
				where id=?
			",[
				$request->input('inp-id')
			]);
			
			if($insert){
				
				$update = DB::update("
					update d_trans
					set status=?,
						id_user=?,
						updated_at=sysdate
					where id=?
				",[
					$request->input('status1'),
					session('id_user'),
					$request->input('inp-id')
				]);
				
				if($update){
					DB::commit();
					return 'success';
				}
				else{
					return 'Status gagal diupdate!';
				}
				
			}
			else{
				return 'Data histori gagal disimpan!';
			}
			
		}
		else{
			return 'Data tidak ditemukan!';
		}		
	}
	
	public function hapus(Request $request)
	{
		DB::beginTransaction();
			
		$rows = DB::select("
			select	count(rowid) as jml
			from d_trans
			where id=?
		",[
			$request->input('id')
		]);
		
		if($rows[0]->jml==1){
			
			$delete = DB::delete("
				delete from d_trans_histori
				where id_trans=?
			",[
				$request->input('id')
			]);
			
			$delete = DB::delete("
				delete from d_trans_dok
				where id_trans=?
			",[
				$request->input('id')
			]);
			
			$delete = DB::delete("
				delete from d_trans_akun
				where id_trans=?
			",[
				$request->input('id')
			]);
			
			$delete = DB::delete("
				delete from d_trans
				where id=?
			",[
				$request->input('id')
			]);
			
			if($delete==true) {
				DB::commit();
				return 'success';
			}
			else {
				return 'Proses hapus gagal. Hubungi Administrator.';
			}
			
		}
		else{
			return 'Data tidak dapat dihapus karena sudah diproses!';
		}	
	}
	
}