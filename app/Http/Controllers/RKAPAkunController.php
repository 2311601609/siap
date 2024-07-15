<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests;
use DB;
use Session;
use DataTables;
use App\Libraries\PublicFunction;

class RKAPAkunController extends Controller
{
	protected $table;

	/**
	 * description 
	 */
	public function __construct()
	{
		$this->table = 'd_rkap_akun';
	}

	private function getDetail($id)
	{
		$rows = DB::select("
			SELECT	*
			FROM d_rkap
			WHERE id = ?
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
		$id_rkap = 0;
		if(isset($_GET['id_rkap'])){
			if($_GET['id_rkap']!==null && $_GET['id_rkap']!==''){
				$id_rkap = $_GET['id_rkap'];
			}
		}

		$sql = "
			select  a.id,
					c.nmsdana,
					d.uraian as nmprogram,
					e.uraian as nmgiat,
					f.nmproyek,
					a.kdakun,
					a.nilai,
					b.status
			from d_rkap_akun a
			left join d_rkap b on(a.id_rkap=b.id)
			left join t_sdana c on(a.kdsdana=c.kdsdana)
			left join t_program d on(a.id_program=d.id)
			left join t_kegiatan e on(a.id_giat=e.id)
			left join t_proyek f on(a.id_proyek=f.id)
			where a.id_rkap=".$id_rkap."
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
						if(session('kdlevel')=='11'){ // staf teknis divisi
							if($row->status==0){ //rekam
								$crud = true;
							}
						}

						$aksi = '';
						if($crud){
							$aksi = '
								<center>
									<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
									<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
										<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>
										<a id="'.$row->id.'" class="dropdown-item hapus" href="javascript:;">Hapus Data</a>
									</div>
								</center>';
						}
						
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
			$lanjut = false;
			$error = '';

			$id_rkap = htmlspecialchars($request->input('id_rkap'));
			$kdsdana = htmlspecialchars($request->input('kdsdana'));
			$program = htmlspecialchars($request->input('id_program'));
			$kegiatan = htmlspecialchars($request->input('id_giat'));
			$id_proyek = htmlspecialchars($request->input('id_proyek'));
			$kdakun = htmlspecialchars($request->input('kdakun'));
			$nilai = $request->input('nilai');
			$id = $request->input('inp-id');
			$baru = $request->input('inp-rekambaru');
			if($id==null){
				$id = 0;
			}

			if(	$id_rkap!==null && $id_rkap!=='' &&
				$kdsdana!==null && $kdsdana!=='' &&
				$program!==null && $program!=='' &&
				$kegiatan!==null && $kegiatan!=='' &&
				$id_proyek!==null && $id_proyek!=='' &&
				$kdakun!==null && $kdakun!=='' &&
				$nilai!==null && $nilai!==''
			){

				$arr_keys = array_keys($nilai);

				if(count($arr_keys)>0){

					$total = 0;
					for($i=0;$i<count($arr_keys);$i++){
						
						if($nilai[$arr_keys[$i]]!=='' && $nilai[$arr_keys[$i]]!==null){

							$total += str_replace(".", "", $nilai[$arr_keys[$i]]);

						}

					}

					$status = $this->getDetail($id_rkap)->status;

					if($status==0){

						// cek apakah program baru atau lama
						$arr_program = explode("|", $program);
						if(count($arr_program)==1){

							$nmprogram = $arr_program[0];

							$id_program = DB::table('t_program')->insertGetId([
								'kdunit' => session('kdunit'),
								'uraian' => $nmprogram,
								'id_user' => session('id_user')
							]);

						}
						else{
							$id_program = $arr_program[0];
						}

						// cek apakah kegiatan baru atau lama
						$arr_kegiatan = explode("|", $kegiatan);
						if(count($arr_kegiatan)==1){

							$nmgiat = $arr_kegiatan[0];

							$id_giat = DB::table('t_kegiatan')->insertGetId([
								'id_program' => $id_program,
								'uraian' => $nmgiat,
								'id_user' => session('id_user')
							]);

						}
						else{
							$id_giat = $arr_kegiatan[0];
						}

						DB::table('d_rkap_akun_bulan')->where('id_rkap_akun', $id)->delete();

						DB::table('d_rkap_akun')->where('id', $id)->delete();

						$id_rkap_akun = DB::table($this->table)->insertGetId(array(
							'id_rkap' => $id_rkap,
							'kdsdana' => $kdsdana,
							'kdakun' => $kdakun,
							'id_program' => $id_program,
							'id_giat' => $id_giat,
							'id_proyek' => $id_proyek,
							'nilai' => $total,
							'id_user' => session('id_user'),
						));

						if($id_rkap_akun){

							$arr_insert = array();
							for($i=0;$i<count($arr_keys);$i++){
					
								if($nilai[$arr_keys[$i]]!=='' && $nilai[$arr_keys[$i]]!==null){
		
									$arr_insert[] = [
										'id_rkap_akun' => $id_rkap_akun,
										'bulan' => $arr_keys[$i],
										'nilai' => str_replace(".", "", $nilai[$arr_keys[$i]])
									];
		
								}
		
							}

							$insert = DB::table('d_rkap_akun_bulan')->insert($arr_insert);

							if($insert){
								$lanjut = true;
							}
							else{
								$error = 'Data detil akun per bulan gagal disimpan.';
							}

						}
						else{
							$error = 'Data header akun gagal disimpan.';
						}

					}
					else{
						$error = 'Data sudah diproses, tidak dapat ditambah/ubah lagi.';
					}

				}
				else{
					$error = 'Nilai belum diisi.';
				}

			}
			else{
				$error = 'Parameter tidak valid.';
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

		$rows = DB::select("
			select  a.id,
					a.id_rkap,
					a.kdsdana,
					a.kdakun,
					a.id_proyek,
					a.id_program||'|'||b.uraian as id_program,
					a.id_giat||'|'||c.uraian as id_giat,
					a.nilai
			from d_rkap_akun a
			left join t_program b on(a.id_program=b.id)
			left join t_kegiatan c on(a.id_giat=c.id)
			where a.id=?
		", [
			$id
		]);
		
		return response()->json($rows[0]);
	}

	/**
	 * description 
	 */
	public function bulan()
	{
		$id = $_GET['id'];

		$rows = DB::select("
			select  a.bulan,
					a.nmbulan,
					b.nilai
			from t_bulan a
			left join(
				
				select  a.*
				from d_rkap_akun_bulan a
				where a.id_rkap_akun=?

			) b on(a.bulan=b.bulan)
			order by a.bulan
		", [
			$id
		]);

		$output = '<div class="col-md-6">';
		for($i=0;$i<6;$i++){

			$output .= '<fieldset class="form-group">
							<label for="nilai">'.$rows[$i]->nmbulan.'</label>
							<input type="text" id="nilai'.$rows[$i]->bulan.'" name="nilai['.$rows[$i]->bulan.']" class="form-control uang" style="text-align: right;" value="'.$rows[$i]->nilai.'">
						</fieldset>';

		}
		$output .= '</div>';

		$output .= '<div class="col-md-6">';
		for($i=6;$i<12;$i++){

			$output .= '<fieldset class="form-group">
							<label for="nilai">'.$rows[$i]->nmbulan.'</label>
							<input type="text" id="nilai'.$rows[$i]->bulan.'" name="nilai['.$rows[$i]->bulan.']" class="form-control uang" style="text-align: right;" value="'.$rows[$i]->nilai.'">
						</fieldset>';

		}
		$output .= '</div>';
		
		return $output;
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

			$rows = DB::select("
				select	id_rkap
				from d_rkap_akun
				where id=?
			",[
				$id
			]);

			if(count($rows)>0){

				$id_rkap = $rows[0]->id_rkap;

				$status = $this->getDetail($id_rkap)->status;

				if($status==0){

					$delete = DB::table($this->table.'_bulan')->where('id_rkap_akun','=',$id)->delete();

					$delete = DB::table($this->table)->where('id','=',$id)->delete();
					
					if($delete) {
						$lanjut = true;
					} else {
						$error = "Gagal menghapus data";		
					}

				}
				else{
					$error = 'Data sudah diproses, tidak dapat dihapus lagi.';
				}

			}
			else{
				$error = 'Data header tidak ditemukan.';
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
