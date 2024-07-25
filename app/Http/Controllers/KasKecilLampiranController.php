<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests;
use DB;
use Session;
use DataTables;
use App\Libraries\PublicFunction;
use Storage;

class KasKecilLampiranController extends Controller
{
	protected $table;

	/**
	 * description 
	 */
	public function __construct()
	{
		$this->table = 'd_trans_dok';
	}

	/**
	 * description 
	 */
	public function index()
	{
		if(session('kdlevel')=='10'){
			return 1;
		}
	}

	/**
	 * description 
	 */
	public function data()
	{
		$id_trans = 0;
		if(isset($_GET['id_trans'])){
			if($_GET['id_trans']!==null && $_GET['id_trans']!==''){
				$id_trans = $_GET['id_trans'];
			}
		}

		$sql = "
			select  a.id,
					a.ket,
					to_char(a.created_at,'dd-mm-yyyy hh24:mi:ss') as created_at,
					a.nmfile
			from ".$this->table." a
			where a.id_trans=".$id_trans."
			order by a.id desc
		";

		$query = DB::table(DB::raw("($sql) a"))
				->selectRaw('a.*');

		$datatables = DataTables::of($query)
					->addIndexColumn()
					->addColumn('aksi', function($row){

						$crud = true;

						$aksi = '<center>-</center>';
						if($crud){
							$aksi = '
								<center>
									<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
									<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
										<a id="'.$row->id.'" class="dropdown-item hapus" href="javascript:;">Hapus Data</a>
										<a id="'.$row->id.'" class="dropdown-item" href="kas-kecil-lampiran/download?nmfile='.$row->nmfile.'" target="_blank">Unduh Data</a>
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
			$id_trans = htmlspecialchars($request->input('id_trans'));
			$ket = htmlspecialchars($request->input('ket'));
			$nmfile = htmlspecialchars($request->input('nmfile_baru'));
			$id = $request->input('inp-id');
			$baru = $request->input('inp-rekambaru');
			if($id==null){
				$id = 0;
			}

			if($nmfile!==null && $nmfile!==''){

				$rows = DB::select("
					select	status
					from d_trans
					where id=?
				",[
					$id_trans
				]);

				if($rows[0]->status==1){

					$insert = DB::table($this->table)->insert(array(
						'id_trans' => $id_trans,
						'ket' => $ket,
						'nmfile' => $nmfile,
						'id_user' => session('id_user'),
						'created_at' => DB::raw('sysdate')
					));
			
					if($insert) {
						$lanjut = true;
					}
					else{
						$error = "terjadi error, data tidak dapat disimpan";
					}

				}
				else{
					$error = 'Data sudah diproses, tidak dapat ditambahkan atau diubah lagi.';
				}

			}
			else{
				$error = 'File belum diupload.';
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
	public function hapus(Request $request)
	{
		DB::connection()->getPdo()->beginTransaction();

		try{
			$id = $request->id;
			$lanjut = false;
			$error ='';

			$rows = DB::select("
				select	status
				from d_trans
				where id=(
					select	id_trans
					from d_trans_dok
					where id=?
				)
			",[
				$id
			]);

			if($rows[0]->status==1){

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

	public function upload(Request $request)
	{
		try {

			if (!empty($_FILES)) {
				
				$file = $request->file('file');
				$fileName = $_FILES['file']['name'];
				$tempFile = $_FILES['file']['tmp_name'];
				$fileTypes = ['pdf','PDF']; //File extensions
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

							//cek folder lokasi upload file satker di ftp
							$pathFTP = 'kas-kecil/';
							$kirimftp = Storage::disk('public')->put($pathFTP.$file_name_baru, fopen($file, 'r+'));
							
							if($kirimftp){
								
								$data['success'] = true;
								$data['message'] = 'File berhasil diupload.';
								$data['nmfile'] = $file_name_baru;
								return response()->json($data, 200);

							}
							else{
								$data['success'] = false;
								$data['message'] = 'File gagal diupload ke server FTP.';
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

	public function download()
	{
		if(isset($_GET['nmfile'])){
			if($_GET['nmfile']!==null && $_GET['nmfile']!==''){

				$path = 'public/kas-kecil/' . $_GET['nmfile'];
				if (!Storage::exists($path)) {
					abort(404);
				}
				return response()->download(storage_path('app/'.$path));

			}
			else{
				return 'File tidak ditemukan.';	
			}
		}
		else{
			return 'File tidak ditemukan.';
		}
	}
}
