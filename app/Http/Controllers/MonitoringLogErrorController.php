<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests;
use DB;
use Session;
use DataTables;
use App\Libraries\PublicFunction;

class MonitoringLogErrorController extends Controller
{
	protected $table;

	/**
	 * description 
	 */
	public function __construct()
	{
		$this->table = 'temp_log_error';
	}

	/**
	 * description 
	 */
	public function index()
	{
		if(session('kdlevel')=='00' || session('kdlevel')=='99'){
			return 1;
		}
	}

	/**
	 * description 
	 */
	public function data()
	{
		$sql = "
			select  a.id,
					a.username,
					a.url,
					a.ip_address,
					to_char(a.created_at,'yyyy-mm-dd hh24:mi:ss') as created_at
			from temp_log_error a
			order by a.id desc
		";

		$query = DB::table(DB::raw("($sql) a"))
				->selectRaw('a.*');

		$datatables = DataTables::of($query)
					->addIndexColumn()
					->addColumn('aksi', function($row){

						$aksi = '
							<center>
								<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="ft-zoom-in"></i></button>
								<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
									<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Lihat Data</a>
								</div>
							</center>
						';
						
						return $aksi;

					})
					->rawColumns(['aksi'])
					->make(true);

		return $datatables;
	}

	/**
	 * description 
	 */
	public function pilih()
	{
		$id = $_GET['id'];

		$rows = DB::select("
			SELECT	a.id,
					a.username,
					a.url,
					a.ip_address,
					to_char(a.created_at,'yyyy-mm-dd hh24:mi:ss') as created_at,
					a.body,
					a.error
			FROM ".$this->table." a
			WHERE a.id = ?
		", [$id]);
		
		return response()->json($rows[0]);
	}
	
	/**
	 * description 
	 */
	public function hapus(Request $request)
	{
		DB::connection()->getPdo()->beginTransaction();

		try{
			$id = $request->id;
			$lanjut = false;
			$error = '';

			$now = new \DateTime();
			$timestamp = $now->format('YmdHis');

			$rows = DB::select("
				select count(*) as jml
				from ".$this->table."
			");

			if($rows[0]->jml>0){

				$insert = DB::insert("
					create table ".$this->table."_".$timestamp." as
					select	*
					from ".$this->table."
				");

			}

			DB::delete("
				truncate table ".$this->table."
			");

			$lanjut = true;

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

}
