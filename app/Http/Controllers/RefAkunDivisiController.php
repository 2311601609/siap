<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests;
use DB;
use Session;
use DataTables;
use App\Libraries\PublicFunction;

class RefAkunDivisiController extends Controller
{
	protected $table;

	/**
	 * description 
	 */
	public function __construct()
	{
		$this->table = 't_akun_unit';
	}

	/**
	 * description 
	 */
	public function index()
	{
		if(session('kdlevel')=='00'){
			return 1;
		}
	}

	/**
	 * description 
	 */
	public function data()
	{
		$arr_where = array();
		if(session('kdlevel')=='03'){
			$arr_where[] = "substr(a.kdunit,1,2)='".session('kdunit')."'";
		}
		elseif(session('kdlevel')=='05' || session('kdlevel')=='08' || session('kdlevel')=='11'){
			$arr_where[] = "a.kdunit='".session('kdunit')."'";
		}

		$where = "";
		if(count($arr_where)>0){
			$where = " where ".implode(" and ", $arr_where);
		}

		$sql = "
			select  a.id,
					a.kdunit,
					b.nmunit,
					a.kdakun,
					c.nmakun,
					to_char(a.created_at,'dd-mm-yyyy hh24:mi:ss') as created_at
			from t_akun_unit a
			left join t_unit b on(a.kdunit=b.kdunit)
			left join t_akun c on(a.kdakun=c.kdakun)
			".$where."
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
									<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="ft-edit"></i></button>
									<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
										<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>
										<a id="'.$row->id.'" class="dropdown-item hapus" href="javascript:;">Hapus Data</a>
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

	/**
	 * description 
	 */
	public function simpan(Request $request)
	{
		DB::connection()->getPdo()->beginTransaction();

		try{
			$lanjut = false;
			$error = false;
			$kdunit = htmlspecialchars($request->input('kdunit'));
			$kdakun = htmlspecialchars($request->input('kdakun'));
			$id = $request->input('inp-id');
			$baru = $request->input('inp-rekambaru');
			if($id==null){
				$id = 0;
			}

			if($baru){

				$rows = DB::select("
					select	*
					from t_akun_unit
					where kdunit=? and kdakun=?
				",[
					$kdunit,
					$kdakun
				]);

				if(count($rows)==0){

					$insert = DB::table($this->table)->insert(array(
						'kdunit' => $kdunit,
						'kdakun' => $kdakun,
						'id_user' => session('id_user'),
					));
			
					if($insert) {
						$lanjut = true;
					}
					else{
						$error = "Data gagal disimpan.";
					}

				}
				else{
					$error = 'Duplikasi data.';
				}

			}
			else{

				$update = DB::table($this->table)
				->where('id','=',$id)
				->update(array(
					'kdunit' => $kdunit,
					'kdakun' => $kdakun,
				));
		
				if($update) {
					$lanjut = true;
				}
				else{
					$error = "Data gagal diubah.";
				}

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

	/**
	 * description 
	 */
	public function pilih()
	{
		$id = $_GET['id'];

		$rows = DB::table($this->table)->where('id', $id)->first();
		
		return response()->json($rows);
	}
	
	/**
	 * description 
	 */
	public function hapus(Request $request)
	{
		try{
			$id = $request->id;

			$delete = DB::table($this->table)->where('id','=',$id)->delete();
				
			if($delete) {
				return "success";
			} else {
				return "Gagal menghapus data";		
			}

		}
		catch(\Exception $e){

			if(PublicFunction::errorLog($request, substr($e->getMessage(),0,255))){
				return 'Kesalahan lainnya, hubungi Administrator.';
			}
			else{
				return 'Kesalahan lainnya, hubungi Administrator. Log error gagal disimpan.';
			}

		}
	}
}
