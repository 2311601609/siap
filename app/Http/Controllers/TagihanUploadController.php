<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests;
use DB;
use Session;
use DataTables;
use App\Libraries\PublicFunction;

class TagihanUploadController extends Controller
{
	protected $table;

	/**
	 * description 
	 */
	public function __construct()
	{
		$this->table = 'd_upload_tagihan';
	}

	/**
	 * description 
	 */
	public function index()
	{
		if(session('kdlevel')=='12'){
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
					a.kdunit,
					a.ket,
					b.nmunit,
					a.nmfile,
					a.nmfile_asli,
					to_char(a.created_at,'dd/mm/yyyy hh24:mi:ss') as created_at,
					nvl(c.jml,0) as jml
			from d_upload_tagihan a
			left join t_unit b on(a.kdunit=b.kdunit)
			left join(
				select  id_upload_tagihan,
						count(*) as jml
				from d_upload_tagihan_dtl
				group by id_upload_tagihan
			) c on(a.id=c.id_upload_tagihan)
			where a.thang='".session('tahun')."'
			order by a.id desc
		";

		$query = DB::table(DB::raw("($sql) a"))
				->selectRaw('a.*');

		$datatables = DataTables::of($query)
					->addIndexColumn()
					->editColumn('jml', function($row){
						return number_format($row->jml, 0, ',', '.');
					})
					->addColumn('aksi', function($row){

						$crud = false;
						if(session('kdlevel')=='12'){ // staf teknis divisi
							$crud = true;
						}

						$crud_output = '';
						if($crud){
							$crud_output = '<a id="'.$row->id.'" class="dropdown-item hapus" href="javascript:;">Hapus Data</a>';
						}

						$aksi = '
							<center>
								<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="ft-edit"></i></button>
								<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
									'.$crud_output.'
									<a class="dropdown-item"
										href="#/tagihan/upload-detil?
										id_upload_tagihan='.$row->id.'&
										nmfile='.$row->nmfile.'&
										back=tagihan/upload">
										Rincian Data
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
			$kdunit = htmlspecialchars($request->input('kdunit'));
			$ket = htmlspecialchars($request->input('ket'));
			$nmfile = htmlspecialchars($request->input('nmfile'));
			$nmfile_baru = htmlspecialchars($request->input('nmfile_baru'));
			$thang = session('tahun');
			$id = $request->input('inp-id');
			$baru = $request->input('inp-rekambaru');
			if($id==null){
				$id = 0;
			}

			if($baru){

				$id_upload_tagihan = DB::table($this->table)->insertGetId(array(
					'kdunit' => $kdunit,
					'ket' => $ket,
					'thang' => $thang,
					'nmfile' => $nmfile_baru,
					'nmfile_asli' => $nmfile,
					'id_user' => session('id_user'),
				));
		
				if($id_upload_tagihan) {

					$insert_detil = DB::insert("
						insert into ".$this->table."_dtl(id_upload_tagihan,kolom1,kolom2,kolom3)
						select	".$id_upload_tagihan.",
								kolom1,
								kolom2,
								kolom3
						from temp_upload_tagihan
						where id_user=? and nmfile=?
					",[
						session('id_user'),
						$nmfile_baru
					]);

					if($insert_detil){

						$delete = DB::table('temp_upload_tagihan')
						->where('id_user', session('id_user'))
						->where('nmfile', $nmfile_baru)
						->delete();

						if($delete){
							$lanjut = true;
						}
						else{
							$error = 'Data temporari gagal dihapus.';
						}

					}
					else{
						$error = 'Data detil gagal disimpan.';
					}

				}
				else{
					$error = "Data header gagal disimpan.";
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

			$delete = DB::table($this->table.'_dtl')->where('id_upload_tagihan','=',$id)->delete();
				
			$delete = DB::table($this->table)->where('id','=',$id)->delete();
			
			if($delete) {
				$lanjut = true;
			} else {
				$error = "Gagal menghapus data";		
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
	public function upload(Request $request)
	{
		try {

			if (!empty($_FILES)) {
				
				$file = $request->file('file');
				$fileName = $_FILES['file']['name'];
				$tempFile = $_FILES['file']['tmp_name'];
				$fileTypes = ['csv','CSV']; //File extensions
				$fileParts = pathinfo($_FILES['file']['name']);
				$fileExt = $fileParts['extension'];
				$fileSize = $_FILES['file']['size'];				
				$error = $_FILES['file']['error'];
				
				if($error === UPLOAD_ERR_OK){

					//cek tipe file
					if (in_array($fileExt, $fileTypes)) {
						
						//cek ukuran file
						if($fileSize>0){

							$timestamp = time();

							$file_name_baru = session('id_user').'_'.$timestamp.'.'.$fileExt;

							$lanjut = true;

							$arr_insert = array();
							if (($handle = fopen($file, "r")) !== FALSE) {
								
								while (($rows = fgetcsv($handle, 1000, ",")) !== FALSE) {
									
									if(count($rows)==3){

										/*
										validasi tambahan
										if(strlen($rows[0])>18){
											$lanjut = false;
											$error = 'Panjang kolom NIP tidak valid! baris ke-'.$i;
											break;
										}

										/*if(strlen($rows[1])>16){
											$lanjut = false;
											$error = 'Panjang kolom NIK tidak valid! baris ke-'.$i;
											break;
										}*/

										$arr_insert[] = "
											select	'".$file_name_baru."',
													'".htmlspecialchars($rows[0])."',
													'".htmlspecialchars($rows[1])."',
													'".htmlspecialchars($rows[2])."',
													".session('id_user')."
											from dual
										";
										
									}
									else{
										$lanjut = false;
										$error = 'Format jumlah kolom file detail tidak valid! baris ke-'.$i;
										break;
									}
									
								}
								
								fclose($handle);

								if($lanjut){

									$delete = DB::table('temp_upload_tagihan')
									->where('id_user', session('id_user'))
									->delete();

									$insert = DB::insert("
										insert into temp_upload_tagihan(
											nmfile,
											kolom1,
											kolom2,
											kolom3,
											id_user
										)
										".implode(" union all ", $arr_insert)."
									");

									if($lanjut){
										$data['success'] = true;
										$data['message'] = '';
										$data['nmfile'] = $file_name_baru;
										return response()->json($data, 200);
									}
									else{
										$data['success'] = false;
										$data['message'] = 'File detil gagal diinsert.';
										return response()->json($data, 500);
									}

								}
								else{
									$data['success'] = false;
									$data['message'] = $error;
									return response()->json($data, 500);
								}

							}
							else{
								$data['success'] = false;
								$data['message'] = 'File detil tidak valid.';
								return response()->json($data, 500);
							}
							
						}
						else{
							$data['success'] = false;
							$data['message'] = 'Ukuran file tidak valid.';
							return response()->json($data, 500);
						}

					}
					else{
						$data['success'] = false;
						$data['message'] = 'Tipe file tidak valid.';
						return response()->json($data, 500);
					}

				}
				else{
					$data['success'] = false;
					$data['message'] = 'Error file upload: ' . $error;
					return response()->json($data, 500);
				}

			}
			else{
				$data['success'] = false;
				$data['message'] = 'Tidak ada file yang diupload.';
				return response()->json($data, 500);
			}

        }
		catch(\Exception $e) {

			if(PublicFunction::errorLog($request, substr($e->getMessage(),0,255))){
				$data['message'] = 'Kesalahan lainnya, hubungi Administrator.';
			}
			else{
				$data['message'] = 'Kesalahan lainnya, hubungi Administrator. Log error gagal disimpan.';
			}

			$data['success'] = false;

			return response()->json($data, 500);

		}
	}

}
