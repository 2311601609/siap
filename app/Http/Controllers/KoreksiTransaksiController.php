<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DataTables;
use App\Libraries\PublicFunction;

class KoreksiTransaksiController extends Controller {

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
					d.nmunit,
					b.nmalur,
					c.nmtrans,
					lpad(a.nourut,5,'0') as nourut,
					nvl(a.nilai,0) as nilai,
					e.nmstatus,
					f.nmlevel
			from d_trans a
			left join t_alur b on(a.id_alur=b.id)
			left join t_trans c on(a.kdtran=c.id)
			left join t_unit d on(a.kdunit=d.kdunit)
			left join t_alur_status e on(a.id_alur=e.id_alur and a.status=e.status)
			left join t_level f on(e.kdlevel=f.kdlevel)
			where a.thang='".session('tahun')."' and b.menu in(1,2,3,4,6)
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

						$aksi='<center>
									<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
									<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
										<a id="'.$row->id.'" class="dropdown-item proses" href="javascript:;">Ubah Data</a>
										<a id="'.$row->id.'" class="dropdown-item hapus" href="javascript:;">Hapus Data</a>
									</div>
								</center>';
						
						return $aksi;

					})
					->rawColumns(['aksi'])
					->make(true);

		return $datatables;
	}
	
	public function pilih(Request $request, $id)
	{
		$rows = DB::select("
			select  a.id,
					a.id_alur,
					a.status,
					d.nmunit,
					b.nmalur,
					c.nmtrans,
					lpad(a.nourut,5,'0') as nourut,
					nvl(a.nilai,0) as nilai,
					e.nmstatus,
					f.nmlevel
			from d_trans a
			left join t_alur b on(a.id_alur=b.id)
			left join t_trans c on(a.kdtran=c.id)
			left join t_unit d on(a.kdunit=d.kdunit)
			left join t_alur_status e on(a.id_alur=e.id_alur and a.status=e.status)
			left join t_level f on(e.kdlevel=f.kdlevel)
			where a.id=?
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$id_alur = $rows[0]->id_alur;
			$status = $rows[0]->status;
			$detil = $rows[0];
			$data['error'] = false;
			$data['message'] = $detil;
			
			$rows = DB::select("
				select  a.status,
						a.nmstatus,
						b.nmlevel
				from t_alur_status a
				left join t_level b on(a.kdlevel=b.kdlevel)
				where a.id_alur=?
				order by a.nourut
			",[
				$id_alur
			]);
			
			$status1 = '<option value="">Pilih Data</option>';
			foreach($rows as $row){
				$cek = '';
				if($row->status==$status){
					$cek = 'selected';
				}
				$status1 .= '<option value="'.$row->status.'" '.$cek.'>'.$row->nmstatus.' - '.$row->nmlevel.'</option>';
			}
			
			$data['dropdown'] = $status1;
			
		}
		else{
			$data['error'] = true;
			$data['message'] = 'Data header tidak ditemukan!';
		}
		
		return response()->json($data);
	}
	
	public function simpan(Request $request)
	{
		DB::connection()->getPdo()->beginTransaction();

		try{
			$lanjut = false;
			$error = '';

			$rows = DB::select("
				select	count(*) as jml
				from d_trans a
				where a.id=? and a.id_alur=? and a.status=?
			",[
				$request->input('inp-id'),
				$request->input('id_alur'),
				$request->input('status')
			]);
			
			if(count($rows)>0){
						
				$insert = DB::insert("
					insert into d_trans_histori(id_trans,id_alur,status,ket,id_user,created_at,updated_at)
					select	id,id_alur,status,ket,id_user,created_at,updated_at
					from d_trans
					where id=?
				",[
					$request->input('inp-id')
				]);
				
				if($insert){
					
					$update = DB::update("
						update d_trans
						set status=?,
							id_user=?,
							updated_at=sysdate
						where id=?
					",[
						$request->input('status1'),
						session('id_user'),
						$request->input('inp-id')
					]);
					
					if($update){
						$lanjut = true;
					}
					else{
						$error = 'Status gagal diupdate!';
					}
					
				}
				else{
					$error = 'Data histori gagal disimpan!';
				}
				
			}
			else{
				$error = 'Data tidak ditemukan!';
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

			$rows = DB::select("
				select	count(rowid) as jml
				from d_trans
				where id=?
			",[
				$request->input('id')
			]);
			
			if($rows[0]->jml==1){
				
				$delete = DB::delete("
					delete from d_trans_histori
					where id_trans=?
				",[
					$request->input('id')
				]);
				
				$delete = DB::delete("
					delete from d_trans_dok
					where id_trans=?
				",[
					$request->input('id')
				]);
				
				$delete = DB::delete("
					delete from d_trans_akun
					where id_trans=?
				",[
					$request->input('id')
				]);
				
				$delete = DB::delete("
					delete from d_trans
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
				
			}
			else{
				$error = 'Data tidak dapat dihapus karena sudah diproses!';
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