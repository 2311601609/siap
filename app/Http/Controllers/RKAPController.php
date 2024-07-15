<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests;
use DB;
use Session;
use DataTables;
use App\Libraries\PublicFunction;

class RKAPController extends Controller
{
	protected $table;

	/**
	 * description 
	 */
	public function __construct()
	{
		$this->table = 'd_rkap';
	}

	private function getDetail($id)
	{
		$rows = DB::select("
			SELECT	a.id,
					b.nmunit,
					a.nourut,
					a.nodok,
					to_char(a.tgdok,'yyyy-mm-dd') as tgdok,
					a.ket,
					a.status
			FROM ".$this->table." a
			LEFT JOIN t_unit b on(a.kdunit=b.kdunit)
			WHERE a.id = ?
		", [
			$id
		]);

		return $rows[0];
	}

	/**
	 * description 
	 */
	public function index()
	{
		if(session('kdlevel')=='11'){
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
			$where = " and ".implode(" and ", $arr_where);
		}

		$sql = "
			select  a.id,
					a.kdunit,
					b.nmunit,
					a.nourut,
					a.nodok,
					to_char(a.tgdok,'dd-mm-yyyy') as tgdok,
					a.status,
					c.uraian as nmstatus,
					to_char(a.created_at,'dd-mm-yyyy hh24:mi:ss') as created_at,
					nvl(d.jml,0) as jml,
					nvl(d.nilai,0) as nilai
			from d_rkap a
			left join t_unit b on(a.kdunit=b.kdunit)
			left join t_status_rkap c on(a.status=c.status)
			left join(

				select	id_rkap,
						count(*) as jml,
						sum(nilai) as nilai
				from d_rkap_akun
				group by id_rkap

			) d on(a.id=d.id_rkap)
			where a.thang='".session('tahun')."' ".$where."
			order by a.id desc
		";

		$query = DB::table(DB::raw("($sql) a"))
				->selectRaw('a.*');

		$datatables = DataTables::of($query)
					->addIndexColumn()
					->editColumn('nilai', function($row){
						return number_format($row->nilai, 0, ',', '.');
					})
					->addColumn('aksi', function($row){

						$crud = false;
						$proses = false;
						if(session('kdlevel')=='11'){ // staf teknis divisi
							if($row->status==0){ //rekam
								$crud = true;
								$proses = true;
							}
						}
						elseif(session('kdlevel')=='08'){ // manager (JM)
							if($row->status==1){
								$proses = true;
							}
						}
						elseif(session('kdlevel')=='05'){ // general manager (SM)
							if($row->status==2){
								$proses = true;
							}
						}
						elseif(session('kdlevel')=='04'){ // keuangan
							if($row->status==3){
								$proses = true;
							}
						}

						$crud_output = '';
						if($crud){
							$crud_output = '<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>
											<a id="'.$row->id.'" class="dropdown-item hapus" href="javascript:;">Hapus Data</a>';
						}

						$proses_output = '';
						if($proses){
							$proses_output = '<a id="'.$row->id.'" class="dropdown-item proses" href="javascript:;">Proses Data</a>';
						}

						$aksi = '
							<center>
								<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="ft-edit"></i></button>
								<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
									'.$crud_output.'
									'.$proses_output.'
									<a id="'.$row->id.'" class="dropdown-item"
										href="#/rka/usulan/akun?
										id_rkap='.$row->id.'&
										nourut='.$row->nourut.'&
										back=rka/usulan">
										Rincian Akun
									</a>
									<a id="'.$row->id.'" class="dropdown-item"
										href="#/rka/usulan/perubahan?
										id_rkap='.$row->id.'&
										nourut='.$row->nourut.'&
										back=rka/usulan">
										Histori Perubahan
									</a>
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
	public function simpan(Request $request)
	{
		DB::connection()->getPdo()->beginTransaction();

		try{
			$lanjut;
			$error = false;
			$nourut = htmlspecialchars($request->input('nourut'));
			$nodok = htmlspecialchars($request->input('nodok'));
			$tgdok = htmlspecialchars($request->input('tgdok'));
			$ket = htmlspecialchars($request->input('ket'));
			$id = $request->input('inp-id');
			$baru = $request->input('inp-rekambaru');
			if($id==null){
				$id = 0;
			}

			if($baru){

				$rows = DB::select("
					select	id,
							status
					from d_rkap
					where id=(
						select	max(id) as id
						from d_rkap
						where kdunit=? and thang=?
					)
				",[
					session('kdunit'),
					session('tahun')
				]);

				$next = true;
				$copy = false;
				if(count($rows)>0){

					$id_rkap_lama = $rows[0]->id;

					if($rows[0]->status==0 || $rows[0]->status==1 || $rows[0]->status==2){
						$next = false;
						$error = 'Pengajuan sedang dalam proses, tidak dapat membuat usulan baru.';
					}
					else{
						$copy = true;
					}

				}

				if($next){

					$id_rkap = DB::table($this->table)->insertGetId(array(
						'kdunit' => session('kdunit'),
						'thang' => session('tahun'),
						'nourut' => $nourut,
						'nodok' => $nodok,
						'tgdok' => $tgdok,
						'ket' => $ket,
						'status' => 0,
						'id_user' => session('id_user'),
					));
			
					if($id_rkap) {

						if($copy){

							$insert_detil = DB::insert("
								insert into d_rkap_akun(id_rkap,kdsdana,kdakun,id_proyek,id_program,id_giat,nilai,id_user)
								select	".$id_rkap." as id_rkap,
										a.kdsdana,
										a.kdakun,
										a.id_proyek,
										a.id_program,
										a.id_giat,
										a.nilai,
										".session('id_user')."
								from d_rkap_akun a
								where a.id_rkap=".$id_rkap_lama."
							");

							if($insert_detil){
								$lanjut = true;
							}
							else{
								$error = 'Data detil per akun gagal disimpan.';
							}

						}
						else{
							$lanjut = true;
						}

					}
					else{
						$error = "Data header usulan gagal disimpan.";
					}

				}

			}
			else{

				$detail = $this->getDetail($id);

				if($detail->status==0){

					$update = DB::table($this->table)
					->where('id','=',$id)
					->update(array(
						'nodok' => $nodok,
						'tgdok' => $tgdok,
						'ket' => $ket,
						'status' => 0,
						'id_user' => session('id_user'),
					));
			
					if($update) {
						$lanjut = true;
					}
					else{
						$error = "terjadi error, data tidak dapat disimpan";
					}

				}
				else{
					$error = 'Data sudah diproses, tidak dapat diubah lagi.';
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
		
		return response()->json($this->getDetail($id));
	}

	/**
	 * description 
	 */
	public function detil()
	{
		$id = $_GET['id'];

		$output = $this->getDetail($id);

		$dropdown = '';
		if(session('kdlevel')=='11'){ // staf teknis divisi
			if($output->status==0){ //rekam
				$dropdown = '<option value="1">Kirim data</option>';
			}
		}
		elseif(session('kdlevel')=='08'){ // manager (JM)
			if($output->status==1){
				$dropdown = '<option value="2">Teruskan</option>
							  <option value="0">Kembalikan</option>';
			}
		}
		elseif(session('kdlevel')=='05'){ // general manager (SM)
			if($output->status==2){
				$dropdown = '<option value="3">Setuju</option>
							  <option value="1">Kembalikan</option>';
			}
		}
		elseif(session('kdlevel')=='04'){ // keuangan
			if($output->status==3){
				$dropdown = '<option value="4">Final</option>
							  <option value="2">Kembalikan</option>';
			}
		}

		$output->dropdown_status = $dropdown;
		
		return response()->json($output);
	}

	/**
	 * description 
	 */
	public function detilSimpan(Request $request)
	{
		DB::connection()->getPdo()->beginTransaction();

		try{
			$lanjut = false;
			$error = '';
			$status_lama = htmlspecialchars($request->input('status_lama'));
			$status = htmlspecialchars($request->input('status'));
			$ket_tolak = htmlspecialchars($request->input('ket_tolak'));
			$id = $request->input('inp-id');
			$baru = $request->input('inp-rekambaru');
			if($id==null){
				$id = 0;
			}

			$insert = DB::insert("
				insert into h_rkap(
						id,
						kdunit,
						thang,
						nourut,
						nodok,
						tgdok,
						ket,
						status,
						status_ket,
						id_user,
						created_at,
						updated_at
				)
				select  id,
						kdunit,
						thang,
						nourut,
						nodok,
						tgdok,
						ket,
						status,
						status_ket,
						id_user,
						created_at,
						updated_at
				from d_rkap
				where id=?
			",[
				$id
			]);

			if($insert){

				$update = DB::table($this->table)
				->where('id','=',$id)
				->where('status','=',$status_lama)
				->update(array(
					'status' => $status,
					'status_ket' => $ket_tolak,
					'id_user' => session('id_user'),
					'updated_at' => DB::raw('sysdate')
				));

				if($update){

					$next = true;

					if($status==4){

						$delete = DB::delete("
							delete
							from d_pagu
							where kdunit||thang in(
								select	kdunit||thang
								from d_rkap
								where id=?
							)
						",[
							$id
						]);

						$insert = DB::insert("
							insert into d_pagu(
									kdunit,
									thang,
									revisike,
									nodok,
									tgdok,
									id_proyek,
									id_program,
									id_giat,
									kdsdana,
									kdakun,
									nilai,
									id_user,
									created_at,
									updated_at
							)
							select  b.kdunit,
									b.thang,
									b.nourut as revisike,
									b.nodok,
									b.tgdok,
									a.id_proyek,
									a.id_program,
									a.id_giat,
									a.kdsdana,
									a.kdakun,
									a.nilai,
									? as id_user,
									sysdate as created_at,
									sysdate as updated_at
							from d_rkap_akun a
							left join d_rkap b on(a.id_rkap=b.id)
							where a.id_rkap=?
						",[
							session('id_user'),
							$id
						]);

						if(!$insert){
							$error = 'Data pagu final gagal disimpan.';
						}

					}

					if($next){
						$lanjut = true;
					}

				}
				else{
					$error = 'Data gagal disimpan, coba refresh halaman terlebih dahulu.';
				}

			}
			else{
				$error = 'Data histori gagal disimpan.';
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
	public function nourut()
	{
		$rows = DB::select("
			select  nvl(max(nourut),0)+1 as nourut
			from d_rkap
			where kdunit=? and thang=?
		",[
			session('kdunit'),
			session('tahun')
		]);
		
		return response()->json($rows[0]);
	}
	
	/**
	 * description 
	 */
	public function hapus(Request $request)
	{
		try{
			$id = $request->id;

			$rows = DB::select("
				SELECT	*
				FROM ".$this->table."_akun
				WHERE id_rkap = ?
			", [
				$id
			]);

			if(count($rows)==0){

				$status = $this->getDetail($id)->status;

				if($status==0){

					$delete = DB::table($this->table)->where('id','=',$id)->delete();
				
					if($delete) {
						return "success";
					} else {
						return "Gagal menghapus data";		
					}

				}
				else{
					return 'Data sudah diproses, tidak dapat dihapus lagi.';
				}

			}
			else{
				return 'Data tidak dapat dihapus karena sudah ada rincian COA-nya.';
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
