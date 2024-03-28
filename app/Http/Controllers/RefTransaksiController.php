<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RefTransaksiController extends Controller {

	public function index(Request $request)
	{
		$aColumns = array('id','nmalur','nmtrans','jml');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  a.id,
							c.nmalur,
							a.nmtrans,
							nvl(b.jml,0) as jml
					from t_trans a
					left outer join(
						select  id_trans,
								count(rowid) as jml
						from t_trans_akun
						group by id_trans
					) b on(a.id=b.id_trans)
					left outer join t_alur c on(a.id_alur=c.id)
					order by a.id desc
					";
		
		/*
		 * Paging
		 */ 
		$sLimit = " ";
		if((isset($_GET['start']))&&(isset($_GET['length']))){
			$iDisplayStart=$_GET['start']+1;
			$iDisplayLength=$_GET['length'];
			$sSearch=$_GET['search'];
			if ((isset($sSearch)) && (isset( $iDisplayStart )) &&  ($iDisplayLength != '-1' )) 
			{
				$iDisplayEnd=$iDisplayStart+$iDisplayLength-1;
				$sLimit = " WHERE NO BETWEEN '$iDisplayStart' AND '$iDisplayEnd'";
			}
		}
		
		/*
		 * Ordering
		 */
		$sOrder = " ";
		if((isset($_GET['order'][0]['column']))&&(isset($_GET['order'][0]['dir']))){
			$iSortCol_0=$_GET['order'][0]['column'];
			$iSortDir_0=$_GET['order'][0]['dir'];
			if ( isset($iSortCol_0  ) )
			{
				//modified ordering
				for($i=0;$i<count($aColumns);$i++){
					if($iSortCol_0==$i){
						if($iSortDir_0=='asc'){
							$sOrder = " ORDER BY ".$aColumns[$i]." ASC ";
						}
						else{
							$sOrder = " ORDER BY ".$aColumns[$i]." DESC ";
						}
					}
				}
			}
		}
		
		//modified filtering
		$sWhere="";
		if(isset($_GET['search']['value'])){
			$sSearch=$_GET['search']['value'];
			if((isset($sSearch))&&($sSearch!='')){
				$sWhere=" where lower(nmtrans) like lower('".$sSearch."%') or lower(nmtrans) like lower('%".$sSearch."%')";
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
				$row->id,
				$row->nmalur,
				$row->nmtrans,
				'<div style="text-align:right;">'.number_format($row->jml).'</div>',
				$aksi
			);
		}
		
		return response()->json($output);
	}
	
	public function pilih(Request $request, $id)
	{
		$rows = DB::select("
			select  *
			from t_trans
			where id=?
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$detil = $rows[0];
			
			$rows = DB::select("
				select	REPLACE(kdakun, 'x', '0') as kdakun,
						kddk,
						panjang
				from t_trans_akun
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
					b.nmakun
			from t_trans_akun a
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
								<th>D/K</th>
							</tr>
						</thead>
						<tbody>';
			$i = 1;
			foreach($rows as $row){
				$detil .= '<tr>
								<td>'.$i++.'</td>
								<td>'.$row->kdakun.'</td>
								<td>'.$row->nmakun.'</td>
								<td>'.$row->kddk.'</td>
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
	
	public function detil1(Request $request, $id)
	{
		$rows = DB::select("
			select	a.kdakun,
					a.kddk,
					b.nmakun
			from t_trans_akun a
			left outer join t_akun b on(a.kdakun=b.kdakun)
			where a.id_trans=?
			order by a.kdakun asc
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$detil = '';
			$i = 0;
			foreach($rows as $row){
				$detil .= '<tr>
								<td>
									<input type="text" name="rincian['.$i.'][\'kdakun\']" value="'.$row->kdakun.'" readonly style="width:100px !important;">
								</td>
								<td>'.$row->nmakun.'</td>
								<td>
									<input type="text" name="rincian['.$i.'][\'kddk\']" value="'.$row->kddk.'" readonly style="width:50px !important;">
								</td>
								<td>
									<input style="text-align:right;width:150px !important;" type="text" name="rincian['.$i.'][\'nilai\']" class="val_num uang">
								</td>
						   </tr>';
				$i++;
			}
			
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
		if(count($request->input('rincian'))>0){
			
			$arr_rincian = $request->input('rincian');
			
			DB::beginTransaction();
			
			if($request->input('inp-rekambaru')=='1'){
			
				$id_trans = DB::table('t_trans')->insertGetId([
					'id_alur' => $request->input('id_alur'),
					'nmtrans' => $request->input('nmtrans'),
					'is_parent' => $request->input('is_parent'),
					'parent_id' => $request->input('id_trans'),
				]);
				
				if($id_trans){
					
					foreach($request->input('rincian') as $input){
						
						$panjang = (int)$input["'panjang'"];
						$kdakun = str_replace("0","",$input["'kdakun'"]);
						$kdakun = substr($kdakun,0,$panjang);
						$kdakun = str_pad($kdakun,6,"x");
						
						$arr_insert[] = "select ".$id_trans.",'".$kdakun."','".$input["'kddk'"]."',".$panjang." from dual";
					}
					
					$delete = DB::delete("
						delete from t_trans_akun
						where id_trans=?
					",[
						$id_trans
					]);
					
					$insert = DB::insert("
						insert into t_trans_akun(id_trans,kdakun,kddk,panjang)
						".implode(" union all ", $arr_insert)."
					");
					
					if($insert){
						DB::commit();
						return 'success';
					}
					else{
						return 'Data akun gagal disimpan!';
					}
					
				}
				else{
					return 'Data header gagal disimpan!';
				}
				
			}
			else{
				
				$update = DB::update("
					update t_trans
					set id_alur=?,
						nmtrans=?,
						is_parent=?,
						parent_id=?
					where id=?
				",[
					$request->input('id_alur'),
					$request->input('nmtrans'),
					$request->input('is_parent'),
					$request->input('id_trans'),
					$request->input('inp-id')
				]);
				
				foreach($request->input('rincian') as $input){
					
					$panjang = (int)$input["'panjang'"];
					$kdakun = str_replace("0","",$input["'kdakun'"]);
					$kdakun = substr($kdakun,0,$panjang);
					$kdakun = str_pad($kdakun,6,"x");
					
					$arr_insert[] = "select ".$request->input('inp-id').",'".$kdakun."','".$input["'kddk'"]."',".$panjang." from dual";
				}
				
				$delete = DB::delete("
					delete from t_trans_akun
					where id_trans=?
				",[
					$request->input('inp-id')
				]);
				
				$insert = DB::insert("
					insert into t_trans_akun(id_trans,kdakun,kddk,panjang)
					".implode(" union all ", $arr_insert)."
				");
				
				if($insert){
					DB::commit();
					return 'success';
				}
				else{
					return 'Data akun gagal disimpan!';
				}
				
			}
			
		}
		else{
			return 'Anda belum memilih kode akun!';
		}		
	}
	
	public function hapus(Request $request)
	{
		try{
			DB::beginTransaction();
			
			$delete = DB::delete("
				delete from t_trans_akun
				where id_trans=?
			",[
				$request->input('id')
			]);
			
			$delete = DB::delete("
				delete from t_trans
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
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya, hubungi Administrator!';
		}		
	}
	
}