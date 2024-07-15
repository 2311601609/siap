<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests;
use DB;
use Session;
use DataTables;
use App\Libraries\PublicFunction;

class KontrakController extends Controller
{
	protected $table;

	/**
	 * description 
	 */
	public function __construct()
	{
		$this->table = 'd_kontrak';
	}

	private function getDetail($id)
	{
		$rows = DB::select("
			SELECT	a.id,
					a.kdunit,
					b.nmunit,
					a.id_penerima,
					a.tipe,
					a.jenis,
					a.nodok,
					to_char(a.tgdok,'yyyy-mm-dd') as tgdok,
					to_char(a.tgjtempo,'yyyy-mm-dd') as tgjtempo,
					to_char(a.tgmulai,'yyyy-mm-dd') as tgmulai,
					a.ket,
					a.jmlbayar,
					a.nilai,
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
			$arr_where[] = "substr(a.kdunit,1,2)m='".session('kdunit')."'";
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
					c.nama as vendor,
					b.nmunit,
					c.nama,
					a.nodok,
					to_char(a.tgdok,'dd-mm-yyyy') as tgdok,
					to_char(a.tgjtempo,'dd-mm-yyyy') as tgjtempo,
					a.nilai,
					a.status,
					e.uraian as nmstatus
			from d_kontrak a
			left join t_unit b on(a.kdunit=b.kdunit)
			left join t_penerima c on(a.id_penerima=c.id)
			left join t_status_kontrak e on(a.status=e.status)
			".$where."
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
										href="#/kontrak/rekam/detil?
										id_kontrak='.$row->id.'&
										nodok='.$row->nodok.'&
										back=kontrak/rekam">
										Rincian Bayar
									</a>
									<a id="'.$row->id.'" class="dropdown-item"
										href="#/kontrak/rekam/lampiran?
										id_kontrak='.$row->id.'&
										nodok='.$row->nodok.'&
										back=kontrak/rekam">
										Lampiran
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
			$lanjut = false;
			$error = '';
			$id_penerima = htmlspecialchars($request->input('id_penerima'));
			$id_proyek = htmlspecialchars($request->input('id_proyek'));
			$tipe = htmlspecialchars($request->input('tipe'));
			$nodok = htmlspecialchars($request->input('nodok'));
			$tgdok = htmlspecialchars($request->input('tgdok'));
			$tgjtempo = htmlspecialchars($request->input('tgjtempo'));
			$ket = htmlspecialchars($request->input('ket'));
			$jenis = htmlspecialchars($request->input('jenis'));
			$kdakun_rek = htmlspecialchars($request->input('kdakun_rek'));
			$nilai = str_replace(".", "", htmlspecialchars($request->input('nilai')));
			$jmlbayar =str_replace(".", "", htmlspecialchars($request->input('jmlbayar')));
			$tgmulai = htmlspecialchars($request->input('tgmulai'));
			$id = $request->input('inp-id');
			$baru = $request->input('inp-rekambaru');
			if($id==null){
				$id = 0;
			}

			if($baru){

				$id_kontrak = DB::table($this->table)->insertGetId(array(
					'kdunit' => session('kdunit'),
					'id_penerima' => $id_penerima,
					'id_proyek' => $id_proyek,
					'tipe' => $tipe,
					'nodok' => $nodok,
					'kdakun_rek' => $kdakun_rek,
					'tgdok' => DB::raw("to_date('".$tgdok."','yyyy-mm-dd')"),
					'tgjtempo' => DB::raw("to_date('".$tgjtempo."','yyyy-mm-dd')"),
					'tgmulai' => DB::raw("to_date('".$tgmulai."','yyyy-mm-dd')"),
					'ket' => $ket,
					'jenis' => $jenis,
					'nilai' => $nilai,
					'jmlbayar' => $jmlbayar,
					'status' => 0,
					'id_user' => session('id_user'),
				));
		
				if($id_kontrak) {

					$insert_detil = DB::insert("
						insert into d_kontrak_dtl(id_kontrak,tahun,bulan,nilai,tgjtempo,fisik,nourut,id_user)
						select  ".$id_kontrak." as id_kontrak,
								a.tahun,
								a.bulan,
								round(".$nilai."/".$jmlbayar.",2) as nilai,
								to_date(a.tahun||'-'||a.bulan||'-'||TO_CHAR(TO_DATE('".$tgmulai."','yyyy-mm-dd'),'dd'),'yyyy-mm-dd') as tgjtempo,
								round(100/".$jmlbayar.",2) as fisik,
								row_number() over(order by a.tahun,a.bulan) as nourut,
								".session('id_user')." as id_user
						from(

							WITH params AS (
								SELECT TO_DATE('".$tgmulai."', 'YYYY-MM-DD') AS start_date, ".$jmlbayar." AS num_months FROM dual
							),
							date_series AS (
								SELECT 
									ADD_MONTHS(start_date, LEVEL - 1) AS generated_date
								FROM 
									params
								CONNECT BY LEVEL <= num_months
							)
							SELECT
								TO_CHAR(generated_date, 'YYYY') AS tahun,
								TO_CHAR(generated_date, 'MM') AS bulan
							FROM 
								date_series
								
						) a
					");

					if($insert_detil){
						$lanjut = true;
					}
					else{
						$error = 'Data detil pembayaran gagal disimpan.';
					}

				}
				else{
					$error = "Data header usulan gagal disimpan.";
				}

			}
			else{

				$detail = $this->getDetail($id);

				if($detail->status==0){

					$update = DB::table($this->table)
					->where('id','=',$id)
					->update(array(
						'id_penerima' => $id_penerima,
						'id_proyek' => $id_proyek,
						'tipe' => $tipe,
						'nodok' => $nodok,
						'kdakun_rek' => $kdakun_rek,
						'tgdok' => DB::raw("to_date('".$tgdok."','yyyy-mm-dd')"),
						'tgjtempo' => DB::raw("to_date('".$tgjtempo."','yyyy-mm-dd')"),
						'ket' => $ket,
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

		$rows = DB::select("
			SELECT	id,
					kdunit,
					id_penerima,
					id_proyek,
					tipe,
					jenis,
					nodok,
					to_char(tgdok,'yyyy-mm-dd') as tgdok,
					to_char(tgjtempo,'yyyy-mm-dd') as tgjtempo,
					to_char(tgmulai,'yyyy-mm-dd') as tgmulai,
					ket,
					jmlbayar,
					nilai
			FROM ".$this->table."
			WHERE id = ?
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

			$status = $this->getDetail($id)->status;

			if($status==0){

				$delete = DB::table($this->table.'_dtl')->where('id_kontrak','=',$id)->delete();

				$delete = DB::table($this->table.'_dok')->where('id_kontrak','=',$id)->delete();
				
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

			$rows = DB::select("
				select  a.total,
						nvl(b.nilai,0) as nilai_kontrak
				from(
				
					select  id_kontrak,
							sum(nilai) as total
					from d_kontrak_dtl
					where id_kontrak=?
					group by id_kontrak
					
				) a
				left join d_kontrak b on(a.id_kontrak=b.id)
			",[
				$id
			]);

			if(count($rows)>0){

				if($rows[0]->total==$rows[0]->nilai_kontrak){

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
						$lanjut = true;
					}
					else{
						$error = 'Data gagal disimpan, coba refresh halaman terlebih dahulu.';
					}

				}
				else{
					$error = 'Total nilai angsuran tidak sama dengan nilai kontrak.';
				}

			}
			else{
				$error = 'Data kontrak tidak ditemukan.';
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
