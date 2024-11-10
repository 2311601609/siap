<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use Session;
use DataTables;
use App\Libraries\PublicFunction;

class RefUserController extends Controller {

	public function index()
	{
		if(session('kdlevel')=='00'){
			return 1;
		}
	}

	public function data()
	{
		$sql = "
			select  a.id,
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
			order by a.id desc
		";

		$query = DB::table(DB::raw("($sql) a"))
				->selectRaw('a.*');

		$datatables = DataTables::of($query)
					->addIndexColumn()
					->addColumn('aksi', function($row){

						$crud = false;
						if(session('kdlevel')=='00'){ // staf teknis divisi
							$crud = true;
						}

						$crud_output = '';
						if($crud){
							$crud_output = '
								<center>
									<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
									<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
										<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>
										<a id="'.$row->id.'" class="dropdown-item hapus" href="javascript:;">Hapus Data</a>
										<a id="'.$row->id.'" class="dropdown-item reset" href="javascript:;">Reset</a>
									</div>
								</center>
							';
						}
						
						return $crud_output;

					})
					->rawColumns(['aksi'])
					->make(true);

		return $datatables;
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
		DB::connection()->getPdo()->beginTransaction();

		try{

			$lanjut = false;
			$error = false;

			$username = htmlspecialchars($request->input('username'));
			$nik = htmlspecialchars($request->input('nik'));
			$nama = htmlspecialchars($request->input('nama'));
			$email = htmlspecialchars($request->input('email'));
			$kdlevel = $request->input('kdlevel');
			$kdunit = $request->input('kdunit');
			$aktif = htmlspecialchars($request->input('aktif'));
			$id = $request->input('inp-id');
			$baru = $request->input('inp-rekambaru');
			if($id==null){
				$id = 0;
			}

			if(count($kdlevel)>0){
				
				if($baru=='1'){
				
					$password = md5('p4ssw0rd!');
					
					$rows = DB::select("
						SELECT	count(*) AS jml
						from t_user
						where username=? or nik=?
					",[
						$username,
						$nik
					]);
					
					if($rows[0]->jml==0){
						
						$id_user = DB::table('t_user')->insertGetId(
							array(
								'username' => $username,
								'pass' => $password,
								'nama' => $nama,
								'nik' => $nik,
								'email' => $email,
								'aktif' => $aktif,
								'foto' => 'no-image.png',
							)
						);
						
						if($id_user) {
							
							$arr_level = $kdlevel;
							
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
								
								if($kdunit!==null){
									
									$arr_perusahaan = $kdunit;
							
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
										$lanjut = true;
									}
									else{
										$error = 'Unit gagal disimpan!';
									}
									
								}
								else{
									$lanjut = true;
								}
								
							}
							else{
								$error = 'Level gagal disimpan!';
							}
							
						}
						else{
							$error = 'Proses simpan gagal. Hubungi Administrator.';
						}
					
					}
					else{
						$error = 'Username ini sudah ada!';
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
						$nama,
						$email,
						$aktif,
						$request->input('inp-id')
					]);
					
					if($update){
						
						$id_user = $id;
						$arr_level = $kdlevel;
							
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
							
							if($kdunit!==null){
								
								$arr_perusahaan = $kdunit;
						
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
									$lanjut = true;
								}
								else{
									$error = 'Unit gagal disimpan!';
								}
								
							}
							else{
								$lanjut = true;
							}
							
						}
						else{
							$error = 'Level gagal disimpan!';
						}
						
					}
					else{
						$error = 'Data gagal diubah!';
					}
					
				}
				
			}
			else{
				$error = 'Level belum dipilih';
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