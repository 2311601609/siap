<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class KasKecilProsesController extends Controller {

	public function index(Request $request)
	{
		$panjang = strlen(session('kdunit'));
		
		$aColumns = array('id','nourut','nmtrans','pks','nilai','status','is_final');
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
								a.uraian,
								nvl(a.nilai,0) as nilai,
								c.nmstatus as status,
								decode(c.is_unit,null,
									1,
									decode(substr(a.kdunit,1,".$panjang."),'".session('kdunit')."',
										1,
										0
									)
								) as akses,
								c.is_final
						from d_trans a
						left outer join t_alur b on(a.id_alur=b.id)
						left outer join t_alur_status c on(a.id_alur=c.id_alur and a.status=c.status)
						left outer join t_unit d on(a.kdunit=d.kdunit)
						left outer join t_penerima e on(a.id_penerima=e.id)
						left outer join t_level g on(c.kdlevel=g.kdlevel)
						left outer join t_trans h on(a.kdtran=h.id)
						where b.menu=6 and a.thang='".session('tahun')."' and c.kdlevel='".session('kdlevel')."'
					) a
					where a.akses=1 and nvl(a.is_final,'0')<>'1'
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
								lower(nourut) like lower('".$sSearch."%') or lower(nourut) like lower('%".$sSearch."%')";
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
			$aksi = '';
			if($row->is_final!=='1'){
				$aksi='<center>
						<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
						<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
							<a id="'.$row->id.'" class="dropdown-item proses" href="javascript:;">Proses Data</a>
							<a class="dropdown-item" href="bukti/kas-kecil/'.$row->id.'" target="_blank">Cetak Bukti</a>
						</div>
					</center>';
			}
			
			$output['aaData'][] = array(
				$row->no,
				$row->nourut,
				$row->nmtrans,
				$row->pks,
				'<div style="text-align:right;">'.number_format($row->nilai).'</div>',
				$row->status,
				$aksi
			);
		}
		
		return response()->json($output);
	}
	
	public function monitoring(Request $request)
	{
		$panjang = strlen(session('kdunit'));
		
		$arrLevel = ['03','05','08','11'];
		
		$and = "";
		if(in_array(session('kdlevel'), $arrLevel)){
			$and = " and substr(a.kdunit,1,".$panjang.")='".session('kdunit')."'";
		}
		
		$and1 = "";
		if(isset($_GET['status'])){
			if($_GET['status']!==null && $_GET['status']!==''){
				$and1 = " and a.status=".$_GET['status'];
			}
		}
		
		$aColumns = array('id','nourut','nmtrans','pks','nilai','status');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  a.id,
							lpad(a.nourut,5,'0') as nourut,
							d.nmunit,
							e.nama,
							h.nmtrans,
							a.nodok as pks,
							to_char(a.tgdok1,'dd-mm-yyyy') as tgjtempo,
							a.uraian,
							nvl(a.nilai,0) as nilai,
							g.nmlevel||'<br>'||c.nmstatus as status
					from d_trans a
					left outer join t_alur b on(a.id_alur=b.id)
					left outer join t_alur_status c on(a.id_alur=c.id_alur and a.status=c.status)
					left outer join t_unit d on(a.kdunit=d.kdunit)
					left outer join t_penerima e on(a.id_penerima=e.id)
					left outer join t_level g on(c.kdlevel=g.kdlevel)
					left outer join t_trans h on(a.kdtran=h.id)
					where b.menu=6 and a.thang='".session('tahun')."' ".$and." ".$and1."
					order by a.nourut desc
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
								lower(nourut) like lower('".$sSearch."%') or lower(nourut) like lower('%".$sSearch."%')";
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
							<a id="'.$row->id.'" class="dropdown-item proses" href="javascript:;">Lihat Data</a>
							<a class="dropdown-item" href="bukti/kas-kecil/'.$row->id.'" target="_blank">Cetak Bukti</a>
						</div>
					</center>';
			
			$output['aaData'][] = array(
				$row->no,
				$row->nourut,
				$row->nmtrans,
				$row->pks,
				'<div style="text-align:right;">'.number_format($row->nilai).'</div>',
				$row->status,
				$aksi
			);
		}
		
		return response()->json($output);
	}
	
	public function pilih(Request $request, $id)
	{
		$rows = DB::select("
			select  a.id,
					lpad(a.nourut,5,'0') as nourut,
					b.nmalur,
					c.nmunit,
					d.nama as nmpelanggan,
					e.nmtrans,
					a.nodok as nopks,
					to_char(a.tgdok,'yyyy-mm-dd') as tgpks,
					to_char(a.tgdok1,'yyyy-mm-dd') as tgjtempo,
					a.uraian,
					nvl(a.nilai,0) as nilai,
					a.debet||' : '||nvl(f.nmakun,0) as debet,
					nvl(i.nmakun,0) as kredit,
					a.id_alur,
					a.status
			from d_trans a
			left outer join t_alur b on(a.id_alur=b.id)
			left outer join t_unit c on(a.kdunit=c.kdunit)
			left outer join t_penerima d on(a.id_penerima=d.id)
			left outer join t_trans e on(a.kdtran=e.id)
			left outer join t_akun f on(a.debet=f.kdakun)
			left outer join t_akun i on(a.kredit=i.kdakun)
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
				select  *
				from t_alur_status_dtl
				where id_alur_status=(
					select id
					from t_alur_status
					where id_alur=? and status=?
				)
				order by nourut asc
			",[
				$id_alur,
				$status
			]);
			
			$status = '<option value="">Pilih Data</option>';
			foreach($rows as $row){
				$status .= '<option value="'.$row->id_alur_status_lanjut.'">'.$row->dropdown.'</option>';
			}
			
			$data['dropdown'] = $status;
			
			$rows = DB::select("
				select  to_char(a.updated_at,'dd-mm-yyyy hh24:mi:ss') as tanggal,
						b.nmstatus,
						c.nmlevel,
						d.nama,
						a.ket
				from(
					select  a.id_alur,
							a.status,
							a.id_user,
							a.ket,
							a.updated_at
					from d_trans a
					where a.id=?

					union all

					select  a.id_alur,
							a.status,
							a.id_user,
							a.ket,
							a.updated_at
					from d_trans_histori a
					where a.id_trans=?
				) a
				left join t_alur_status b on(a.id_alur=b.id_alur and a.status=b.status)
				left join t_level c on(b.kdlevel=c.kdlevel)
				left join t_user d on(a.id_user=d.id)
				order by a.updated_at
			",[
				$id,
				$id
			]);
			
			$catatan = '';
			foreach($rows as $row){
				$catatan .= '<tr>
								<td>'.$row->tanggal.'</td>
								<td>'.$row->nmstatus.'</td>
								<td>'.$row->nmlevel.'</td>
								<td>'.$row->ket.'</td>
							</tr>';
			}
			
			$data['catatan'] = $catatan;
			
			$rows = DB::select("
				select	a.kdakun,
						b.nmakun,
						a.nilai,
						a.kddk
				from d_trans_akun a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.id_trans=?
				order by a.kddk,a.kdakun
			",[
				$id
			]);
			
			$akun = '';
			foreach($rows as $row){
				
				if($row->kddk=='D'){
					$debet = number_format($row->nilai);
					$kredit = '';
				}
				else{
					$kredit = number_format($row->nilai);
					$debet = '';
				}
				
				$akun .= '<tr>
							<td>'.$row->kdakun.'</td>
							<td>'.$row->nmakun.'</td>
							<td style="text-align:right;">'.$debet.'</td>
							<td style="text-align:right;">'.$kredit.'</td>
						  </tr>';
			}
			
			$data['akun'] = $akun;
			
		}
		else{
			$data['error'] = true;
			$data['message'] = 'Data header tidak ditemukan!';
		}
		
		return response()->json($data);
	}
	
	public function simpan(Request $request)
	{
		DB::beginTransaction();
		
		$rows = DB::select("
			select	a.*,
					b.kdakun
			from d_trans a
			left join(
				select	id_trans,kdakun,nilai
				from d_trans_akun
				where kddk='D' and grup=1
			) b on(a.id=b.id_trans)
			where a.id=? and a.id_alur=? and a.status=?
		",[
			$request->input('inp-id'),
			$request->input('id_alur'),
			$request->input('status')
		]);
		
		if(count($rows)>0){
			
			$kdakun = $rows[0]->kdakun;
			$nilai = $rows[0]->nilai;
			
			$rows = DB::select("
				select	*
				from t_alur_status
				where id=?
			",[
				$request->input('status1')
			]);
			
			if(count($rows)>0){
				
				$lanjut = true;
				
				if($rows[0]->is_final=='1'){
					
					$delete = DB::delete("
						delete from d_trans_akun
						where id_trans=? and grup=0
					",[
						$request->input('inp-id')
					]);
					
					if($kdakun=='920000'){ //jika tarik uang ke kas bp
						
						$query_insert = "
							select	".$request->input('inp-id')." as id_trans,
									'111300' as kdakun,
									'D' as kddk,
									".$nilai." as nilai,
									0 as grup
							from dual
							
							union all
							
							select	".$request->input('inp-id')." as id_trans,
									'920000' as kdakun,
									'K' as kddk,
									".$nilai." as nilai,
									0 as grup
							from dual
						";
						
					}
					else{
						
						$query_insert = "
							select	".$request->input('inp-id')." as id_trans,
									'910000' as kdakun,
									'D' as kddk,
									".$nilai." as nilai,
									0 as grup
							from dual
							
							union all
							
							select	".$request->input('inp-id')." as id_trans,
									'111300' as kdakun,
									'K' as kddk,
									".$nilai." as nilai,
									0 as grup
							from dual
						";
					
					}
					
					$insert = DB::insert("
						insert into d_trans_akun(id_trans,kdakun,kddk,nilai,grup)
						".$query_insert."
					");
					
					if(!$insert){
						$lanjut = false;
						$error = 'Akun kontra pos gagal disimpan!';
					}
					
				}
				
				if($lanjut){
					
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
							set id_alur=?,
								status=?,
								id_user=?,
								updated_at=sysdate,
								ket=?
							where id=?
						",[
							$rows[0]->id_alur,
							$rows[0]->status,
							session('id_user'),
							$request->input('ket'),
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
					return $error;
				}
				
			}
			else{
				return 'Status tidak ditemukan!';
			}
			
		}
		else{
			return 'Data tidak ditemukan!';
		}		
	}
	
}