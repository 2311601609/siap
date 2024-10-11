<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PembukuanJurnalPController extends Controller {

	public function index(Request $request)
	{
		$aColumns = array('id','nourut','nmalur','nmproyek','nama','nobukti','uraian','nilai','status');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  	a.id as id_trans,
							a.id,
							lpad(a.nourut,5,'0') as nourut,
							a.notrans,
							a.thang,
							d.nmunit,
							b.nmalur,
							e.nama,
							a.nodok as nobukti,
							to_char(a.tgdok,'dd-mm-yyyy') as tgbukti,
							a.uraian,
							h.nmproyek,
							nvl(f.nilai,0) as nilai,
							g.nmlevel||'<br>'||c.nmstatus as status
					from d_trans a
					left outer join t_alur b on(a.id_alur=b.id)
					left outer join t_alur_status c on(a.id_alur=c.id_alur and a.status=c.status)
					left outer join t_unit d on(a.kdunit=d.kdunit)
					left outer join t_penerima e on(a.id_penerima=e.id)
					left outer join t_level g on(c.kdlevel=g.kdlevel)
					left join t_proyek h on(a.id_proyek=h.id)
					left outer join(
						select  id_trans,
								sum(nilai) as nilai
						from d_trans_akun
						WHERE kddk='D'
						group by id_trans
					) f on(a.id=f.id_trans)
					where a.thang='".session('tahun')."' and a.id_alur=10
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
				$sWhere=" where lower(nmproyek) like lower('".$sSearch."%') or lower(nmproyek) like lower('%".$sSearch."%') or
								lower(nourut) like lower('".$sSearch."%') or lower(nourut) like lower('%".$sSearch."%') or
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
							<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
							<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
								<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>
								<a id="'.$row->id.'" class="dropdown-item hapus" href="javascript:;">Hapus Data</a>
							</div>
						</center>';
			}
			
			$output['aaData'][] = array(
				$row->id,
				$row->nourut,
				$row->nmalur,
				$row->nmproyek,
				$row->nobukti,
				$row->uraian,
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
					lpad(nourut,5,'0') as nourut,
					notrans,
					id_alur,
					id_output,
					id_penerima,
					id_proyek,
					nodok as nobukti,
					to_char(tgdok,'yyyy-mm-dd') as tgbukti,
					uraian,
					kdtran
			from d_trans
			where id=?
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$detil = $rows[0];
			
			$rows = DB::select("
				select	*
				from d_trans_dok
				where id_trans=?
			",[
				$id
			]);
			
			if(count($rows)>0){
				session(['upload_lampiran' => $rows[0]->nmfile]);
			}
			
			$rows = DB::select("
				select	kdakun,
						kddk,
						nilai
				from d_trans_akun
				where id_trans=?
			",[
				$id
			]);
			
			if(count($rows)>0){
				$data['error'] = false;
				$data['message'] = $detil;
				$data['akun'] = $rows;
				$data['x'] = count($rows);
			}
			else{
				$data['error'] = true;
				$data['message'] = 'Data akun tidak ditemukan!';
			}
			
		}
		else{
			$data['error'] = true;
			$data['message'] = 'Data header tidak ditemukan!';
		}
		
		return response()->json($data);
	}
	
	public function detil(Request $request, $id)
	{
		$rows = DB::select("
			select	a.kdakun,
					a.kddk,
					b.nmakun,
					a.nilai
			from d_trans_akun a
			left outer join t_akun b on(a.kdakun=b.kdakun)
			where a.id_trans=?
			order by a.kdakun asc
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$detil = '<table class="table table-bordered">
						<thead>
							<tr>
								<th>No</th>
								<th>Akun</th>
								<th>Uraian</th>
								<th>Debet</th>
								<th>Kredit</th>
							</tr>
						</thead>
						<tbody>';
			$i = 1;
			foreach($rows as $row){
				
				if($row->kddk=='D'){
					$debet = number_format($row->nilai);
					$kredit = '';
				}
				else{
					$debet = '';
					$kredit = number_format($row->nilai);
				}
				
				$detil .= '<tr>
								<td>'.$i++.'</td>
								<td>'.$row->kdakun.'</td>
								<td>'.$row->nmakun.'</td>
								<td style="text-align:right;">'.$debet.'</td>
								<td style="text-align:right;">'.$kredit.'</td>
						   </tr>';
			}
			
			$detil .= '</tbody></table>';
			
			$data['error'] = false;
			$data['message'] = $detil;
			
		}
		else{
			$data['error'] = true;
			$data['message'] = 'Data akun tidak ditemukan!';
		}
		
		return response()->json($data);
	}
	
	public function simpan(Request $request)
	{
		DB::connection()->getPdo()->beginTransaction();

		try{
			$lanjut = false;
			$error = '';
		
			if(count($request->input('rincian'))>0){
				
				$arr_rincian = $request->input('rincian');
				
				if($request->input('inp-rekambaru')=='1'){
				
					$id_trans = DB::table('d_trans')->insertGetId([
						'thang' => session('tahun'),
						'kdunit' => session('kdunit'),
						'id_alur' => 10,
						'nourut' => $request->input('nourut'),
						'kdtran' => $request->input('kdtran'),
						'nodok' => $request->input('nobukti'),
						'tgdok' => $request->input('tgbukti'),
						'uraian' => $request->input('uraian'),
						'id_proyek' => $request->input('id_proyek'),
						'status' => 0,
						'id_user' => session('id_user')
					]);
					
					if($id_trans){
						
						foreach($request->input('rincian') as $input){
							$arr_insert[] = "select ".$id_trans.",'".$input["'kdakun'"]."','".$input["'kddk'"]."',".str_replace(",", "", $input["'nilai'"]).",".session('id_user')." from dual";
						}
						
						$delete = DB::delete("
							delete from d_trans_akun
							where id_trans=?
						",[
							$id_trans
						]);
						
						$insert = DB::insert("
							insert into d_trans_akun(id_trans,kdakun,kddk,nilai,id_user)
							".implode(" union all ", $arr_insert)."
						");
						
						if($insert){
							$lanjut = true;
						}
						else{
							$error = 'Data akun gagal disimpan!';
						}
						
					}
					else{
						$error = 'Data header gagal disimpan!';
					}
					
				}
				else{
					
					$update = DB::update("
						update d_trans
						set kdtran=?,
							nodok=?,
							tgdok=?,
							uraian=?,
							id_proyek=?,
							id_user=?,
							updated_at=sysdate
						where id=?
					",[
						$request->input('kdtran'),
						$request->input('nobukti'),
						$request->input('tgbukti'),
						$request->input('uraian'),
						$request->input('id_proyek'),
						session('id_user'),
						$request->input('inp-id')
					]);
					
					if($update){
						
						foreach($request->input('rincian') as $input){
							$arr_insert[] = "select ".$request->input('inp-id').",'".$input["'kdakun'"]."','".$input["'kddk'"]."',".str_replace(",", "", $input["'nilai'"]).",".session('id_user')." from dual";
						}
						
						$delete = DB::delete("
							delete from d_trans_akun
							where id_trans=?
						",[
							$request->input('inp-id')
						]);
						
						$insert = DB::insert("
							insert into d_trans_akun(id_trans,kdakun,kddk,nilai,id_user)
							".implode(" union all ", $arr_insert)."
						");
						
						if($insert){
							$lanjut = true;
						}
						else{
							$error = 'Data gagal disimpan!';
						}
						
					}
					else{
						$error = 'Data gagal diubah!';
					}
					
				}
				
			}
			else{
				$error = 'Anda belum memilih kode akun!';
			}

			if($lanjut){
				DB::connection()->getPdo()->commit();
				return 'success';
			}
			else{
				DB::connection()->getPdo()->rollBack();
				return $error;
			}

		}
		catch(\Exception $e){
			DB::connection()->getPdo()->rollBack();

			if(PublicFunction::errorLog($request, substr($e->getMessage(),0,255))){
				return 'Kesalahan lainnya, hubungi Administrator.';
			}
			else{
				return 'Kesalahan lainnya, hubungi Administrator. Log error gagal disimpan.';
			}
		}
	}
	
	public function hapus(Request $request)
	{
		DB::connection()->getPdo()->beginTransaction();

		try{
			
			$delete = DB::delete("
				delete from d_trans_akun
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
				delete from d_trans_histori
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
			
			if($delete){
				DB::connection()->getPdo()->commit();
				return 'success';
			}
			else{
				DB::connection()->getPdo()->rollBack();
				return $error;
			}
			
		}
		catch(\Exception $e){
			DB::connection()->getPdo()->rollBack();

			if(PublicFunction::errorLog($request, substr($e->getMessage(),0,255))){
				return 'Kesalahan lainnya, hubungi Administrator.';
			}
			else{
				return 'Kesalahan lainnya, hubungi Administrator. Log error gagal disimpan.';
			}
		}		
	}
	
}