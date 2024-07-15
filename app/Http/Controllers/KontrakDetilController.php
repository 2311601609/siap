<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests;
use DB;
use Session;
use DataTables;
use App\Libraries\PublicFunction;

class KontrakDetilController extends Controller
{
	protected $table;

	/**
	 * description 
	 */
	public function __construct()
	{
		$this->table = 'd_kontrak_dtl';
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
		$id_kontrak = 0;
		if(isset($_GET['id_kontrak'])){
			if($_GET['id_kontrak']!==null && $_GET['id_kontrak']!==''){
				$id_kontrak = $_GET['id_kontrak'];
			}
		}

		$sql = "
			select  a.id,
					a.nourut,
					a.bulan,
					a.nilai,
					a.tgjtempo,
					case
						when c.jenis='01'
							then a.tahun
						else
							'N/A'
					end as tahun,
					case
						when c.jenis='01'
							then b.nmbulan
						else
							'N/A'
					end as nmbulan,
					case
						when c.jenis='02'
							then a.fisik
						else
							null
					end as fisik
			from ".$this->table." a
			left join t_bulan b on(a.bulan=b.bulan)
			left join d_kontrak c on(a.id_kontrak=c.id)
			where a.id_kontrak=".$id_kontrak."
			order by a.nourut asc
		";

		$query = DB::table(DB::raw("($sql) a"))
				->selectRaw('a.*');

		$datatables = DataTables::of($query)
					->addIndexColumn()
					->editColumn('nilai', function($row){
						return number_format($row->nilai, 0, ',', '.');
					})
					->addColumn('aksi', function($row){

						$crud = true;

						$aksi = '<center>-</center>';
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
			$error ='';
			$id_kontrak = htmlspecialchars($request->input('id_kontrak'));
			$nourut = htmlspecialchars($request->input('nourut'));
			$tahun = htmlspecialchars($request->input('tahun'));
			$bulan = htmlspecialchars($request->input('bulan'));
			$tgjtempo = htmlspecialchars($request->input('tgjtempo'));
			$fisik = htmlspecialchars($request->input('fisik'));
			$nilai = htmlspecialchars($request->input('nilai'));
			$id = $request->input('inp-id');
			$baru = $request->input('inp-rekambaru');
			if($id==null){
				$id = 0;
			}

			$rows = DB::select("
				select	status
				from d_kontrak
				where id=?
			",[
				$id_kontrak
			]);

			if($rows[0]->status==0){

				if($baru){

					$insert = DB::table($this->table)->insert(array(
						'id_kontrak' => $id_kontrak,
						'nourut' => $nourut,
						'tahun' => $tahun,
						'bulan' => $bulan,
						'tgjtempo' => DB::raw("to_date('".$tgjtempo."','yyyy-mm-dd')"),
						'fisik' => str_replace(",", ".", str_replace(".", "", $fisik)),
						'nilai' => str_replace(",", ".", str_replace(".", "", $nilai)),
						'id_user' => session('id_user'),
					));
			
					if($insert) {
						$lanjut = true;
					}
					else{
						$error = "terjadi error, data tidak dapat disimpan";
					}
	
				}
				else{
					$update = DB::table($this->table)
					->where('id','=',$id)
					->update(array(
						'nourut' => $nourut,
						'tahun' => $tahun,
						'bulan' => $bulan,
						'tgjtempo' => DB::raw("to_date('".$tgjtempo."','yyyy-mm-dd')"),
						'fisik' => str_replace(",", ".", str_replace(".", "", $fisik)),
						'nilai' => str_replace(",", ".", str_replace(".", "", $nilai)),
						'id_user' => session('id_user'),
						'updated_at' => DB::raw('sysdate')
					));
			
					if($update) {
						$lanjut = true;
					}
					else{
						$error = "terjadi error, data tidak dapat disimpan";
					}
				}

			}
			else{
				$error = 'Data sudah diproses, tidak dapat ditambahkan atau diubah lagi.';
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
					id_kontrak,
					nourut,
					tahun,
					bulan,
					to_char(tgjtempo,'yyyy-mm-dd') as tgjtempo,
					fisik,
					nilai
			FROM ".$this->table."
			WHERE id = ?
		", [
			$id
		]);

		$output = (array)$rows[0];

		$output['fisik'] = number_format($output['fisik'], 2, ',', '.');
		$output['nilai'] = number_format($output['nilai'], 2, ',', '.');
		
		return response()->json($output);
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
			$error ='';

			$rows = DB::select("
				select	status
				from d_kontrak
				where id=(
					select	id_kontrak
					from d_kontrak_dtl
					where id=?
				)
			",[
				$id
			]);

			if($rows[0]->status==0){

				$delete = DB::table($this->table)->where('id','=',$id)->delete();
					
				if($delete) {
					$lanjut = true;
				} else {
					$error = 'Data gagal dihapus.';
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
}
