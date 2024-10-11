<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DataTables;
use App\Libraries\PublicFunction;

class PembukuanSaldoAwalController extends Controller {

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
			order by a.id desc
		";

		$query = DB::table(DB::raw("($sql) a"))
				->selectRaw('a.*');

		$datatables = DataTables::of($query)
					->addIndexColumn()
					->editColumn('debet', function($row){
						return number_format($row->debet, 0, ',', '.');
					})
					->editColumn('kredit', function($row){
						return number_format($row->kredit, 0, ',', '.');
					})
					->addColumn('aksi', function($row){

						$crud = false;
						if(session('kdlevel')=='00'){ // staf teknis divisi
							$crud = true;
						}

						$crud_output = '';
						if($crud){
							$crud_output='<center>
										<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
										<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
											<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>
											<a id="'.$row->id.'" class="dropdown-item hapus" href="javascript:;">Hapus Data</a>
										</div>
									</center>';
						}
						
						return $crud_output;

					})
					->rawColumns(['aksi'])
					->make(true);

		return $datatables;
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
		DB::connection()->getPdo()->beginTransaction();

		try{
			$lanjut = false;
			$error = '';

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
						$lanjut = true;
					}
					else{
						$error = 'Data gagal disimpan!';
					}
					
				}
				else{
					$error = 'Duplikasi data!';
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
					$lanjut = true;
				}
				else{
					$error = 'Data gagal disimpan!';
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
	
	public function hapus(Request $request)
	{
		DB::connection()->getPdo()->beginTransaction();

		try{
			$lanjut = false;
			$error = '';

			$delete = DB::delete("
				delete from d_sawal
				where id=?
			",[
				$request->input('id')
			]);
			
			if($delete==true) {
				$lanjut = true;
			}
			else {
				$error = 'Proses hapus gagal. Hubungi Administrator.';
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
	
}