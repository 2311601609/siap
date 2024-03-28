<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RefUserController extends Controller {

	public function index(Request $request)
	{
		try{
			$aColumns = array('id','username','nama','nik','nmlevel','nmunit','aktif');
			/* Indexed column (used for fast and accurate table cardinality) */
			$sIndexColumn = "id";
			/* DB table to use */
			$sTable = "select  a.id,
								a.username,
								a.nama,
								a.nik,
								d.nmlevel,
								e.nmunit,
								decode(a.aktif,'1','Aktif','0','Tidak Aktif','Default') as aktif
						from t_user a
						left outer join t_user_level b on(a.id=b.id_user and b.status='1')
						left outer join t_user_unit c on(a.id=c.id_user and c.status='1')
						left outer join t_level d on(b.kdlevel=d.kdlevel)
						left outer join t_unit e on(c.kdunit=e.kdunit)
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
					$sWhere=" where lower(nama) like lower('".$sSearch."%') or lower(nama) like lower('%".$sSearch."%') or
									lower(nik) like lower('".$sSearch."%') or lower(nik) like lower('%".$sSearch."%') or
									lower(username) like lower('".$sSearch."%') or lower(username) like lower('%".$sSearch."%')";
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
								<a id="'.$row->id.'" class="dropdown-item reset" href="javascript:;">Reset</a>
							</div>
						</center>';
				}
				
				$output['aaData'][] = array(
					$row->no,
					$row->username,
					$row->nama,
					$row->nik,
					$row->nmlevel,
					$row->nmunit,
					$row->aktif,
					$aksi
				);
			}
			
			return response()->json($output);
		}
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya!';
		}
	}
	
	public function pilih(Request $request, $id)
	{
		try{
			$rows = DB::select("
				select	id,
						nama,
						nik,
						username,
						email,
						aktif
				from t_user
				where id=?
			",[
				$id
			]);
			
			if(count($rows)>0){
				
				$data = (array)$rows[0];
				
				$rows = DB::select("
					select	*
					from t_user_level
					where id_user=?
				",[
					$id
				]);
				
				$arr_level = array();
				foreach($rows as $row){
					$arr_level[] = $row->kdlevel;
				}
				
				$data['kdlevel'] = implode(",", $arr_level);
				
				$data['kdunit'] = '';
				
				$rows = DB::select("
					select	*
					from t_user_unit
					where id_user=?
				",[
					$id
				]);
				
				if(count($rows)>0){
					
					$arr_usaha = array();
					foreach($rows as $row){
						$arr_usaha[] = $row->kdunit;
					}
					
					$data['kdunit'] = implode(",", $arr_usaha);
					
				}
				
				$data['error'] = false;
				$data['message'] = '';
				return response()->json($data);
				
			}
			else{
				$data['error'] = true;
				$data['message'] = 'Data tidak ditemukan!';
				return response()->json($data);
			}
			
		}
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya!';
		}
	}
	
	public function simpan(Request $request)
	{
		try{
			DB::beginTransaction();
			
			if(count($request->input('kdlevel'))>0){
				
				if($request->input('inp-rekambaru')=='1'){
				
					$password = md5('p4ssw0rd!');
					
					$rows = DB::select("
						SELECT	count(*) AS jml
						from t_user
						where username=? or nik=?
					",[
						$request->input('username'),
						$request->input('nik')
					]);
					
					if($rows[0]->jml==0){
						
						$id_user = DB::table('t_user')->insertGetId(
							array(
								'username' => $request->input('username'),
								'pass' => $password,
								'nama' => $request->input('nama'),
								'nik' => $request->input('nik'),
								'email' => $request->input('email'),
								'aktif' => $request->input('aktif'),
								'foto' => 'no-image.png',
							)
						);
						
						if($id_user) {
							
							$arr_level = $request->input('kdlevel');
							
							$arr_insert = array();
							for($i=0;$i<count($arr_level);$i++){
								$aktif_level = '0';
								if($i==0){
									$aktif_level = '1';
								}
								$arr_insert[] = "select ".$id_user.",'".$arr_level[$i]."','".$aktif_level."' from dual";
							}
							
							$insert = DB::insert("
								insert into t_user_level(id_user,kdlevel,status)
								".implode(" union all ", $arr_insert)."
							");
							
							if($insert){
								
								if($request->input('kdunit')!==null){
									
									$arr_perusahaan = $request->input('kdunit');
							
									$arr_insert1 = array();
									for($j=0;$j<count($arr_perusahaan);$j++){
										$aktif_usaha = '0';
										if($j==0){
											$aktif_usaha = '1';
										}
										$arr_insert1[] = "select ".$id_user.",'".$arr_perusahaan[$j]."','".$aktif_usaha."' from dual";
									}
									
									$insert1 = DB::insert("
										insert into t_user_unit(id_user,kdunit,status)
										".implode(" union all ", $arr_insert1)."
									");
									
									if($insert){
										DB::commit();
										return 'success';
									}
									else{
										return 'Unit gagal disimpan!';
									}
									
								}
								else{
									DB::commit();
									return 'success';
								}
								
							}
							else{
								return 'Level gagal disimpan!';
							}
							
						}
						else{
							return 'Proses simpan gagal. Hubungi Administrator.';
						}
					
					}
					else{
						return 'Username ini sudah ada!';
					}
					
				}
				else{
					
					$update = DB::update("
						update t_user
						set nama=?,
							email=?,
							aktif=?
						where id=?
					",[
						$request->input('nama'),
						$request->input('email'),
						$request->input('aktif'),
						$request->input('inp-id')
					]);
					
					if($update){
						
						$id_user = $request->input('inp-id');
						$arr_level = $request->input('kdlevel');
							
						$arr_insert = array();
						for($i=0;$i<count($arr_level);$i++){
							$aktif_level = '0';
							if($i==0){
								$aktif_level = '1';
							}
							$arr_insert[] = "select ".$id_user.",'".$arr_level[$i]."','".$aktif_level."' from dual";
						}
						
						$delete = DB::delete("
							delete from t_user_level
							where id_user=?
						",[
							$id_user
						]);
						
						$insert = DB::insert("
							insert into t_user_level(id_user,kdlevel,status)
							".implode(" union all ", $arr_insert)."
						");
						
						if($insert){
							
							if($request->input('kdunit')!==null){
								
								$arr_perusahaan = $request->input('kdunit');
						
								$arr_insert1 = array();
								for($j=0;$j<count($arr_perusahaan);$j++){
									$aktif_usaha = '0';
									if($j==0){
										$aktif_usaha = '1';
									}
									$arr_insert1[] = "select ".$id_user.",'".$arr_perusahaan[$j]."','".$aktif_usaha."' from dual";
								}
								
								$delete1 = DB::delete("
									delete from t_user_unit
									where id_user=?
								",[
									$id_user
								]);
								
								$insert1 = DB::insert("
									insert into t_user_unit(id_user,kdunit,status)
									".implode(" union all ", $arr_insert1)."
								");
								
								if($insert){
									DB::commit();
									return 'success';
								}
								else{
									return 'Unit gagal disimpan!';
								}
								
							}
							else{
								DB::commit();
								return 'success';
							}
							
						}
						else{
							return 'Level gagal disimpan!';
						}
						
					}
					else{
						return 'Data gagal diubah!';
					}
					
				}
				
			}
			else{
				return 'Level belum dipilih';
			}
						
		}
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya!';
		}		
	}
	
	public function hapus(Request $request)
	{
		try{
			DB::beginTransaction();
			
			$delete = DB::delete("
				delete from t_user_level
				where id_user=?
			",[
				$request->input('id')
			]);
			
			$delete = DB::delete("
				delete from t_user_unit
				where id_user=?
			",[
				$request->input('id')
			]);
			
			$delete = DB::delete("
				delete from t_user
				where id=?
			",[
				$request->input('id')
			]);
			
			if($delete==true){
				DB::commit();
				return 'success';
			}
			else {
				return 'Proses hapus gagal. Hubungi Administrator.';
			}
			
		}
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya!';
		}		
	}
	
	public function reset(Request $request)
	{
		try{
			$password = md5('p4ssw0rd!');
			
			$update = DB::update("
				update t_user
				set pass=?
				where id=?
			",[
				$password,
				$request->input('id')
			]);
			
			if($update==true){
				return 'success';
			}
			else {
				return 'Proses reset gagal. Hubungi Administrator.';
			}
			
		}
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya!';
		}		
	}
	
}