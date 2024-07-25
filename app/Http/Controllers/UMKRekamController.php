<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DataTables;
use App\Libraries\PublicFunction;

class UMKRekamController extends Controller {

	public function index(Request $request)
	{
		if(session('kdlevel')=='11'){
			return 1;
		}
	}
	
	public function data(Request $request)
	{
		$panjang = strlen(session('kdunit'));
		
		$arrLevel = ['03','05','08','11'];
		
		$and = "";
		if(in_array(session('kdlevel'), $arrLevel)){
			$and = " and substr(a.kdunit,1,".$panjang.")='".session('kdunit')."'";
		}

		$sql = "
			select  a.id,
					lpad(a.nourut,5,'0') as nourut,
					d.nmunit,
					e.nama,
					h.nmtrans,
					a.nodok as pks,
					to_char(a.tgdok1,'dd-mm-yyyy') as tgjtempo,
					a.uraian,
					nvl(a.nilai,0) as nilai,
					c.nmstatus as status,
					c.is_ubah,
					c.is_final
			from d_trans a
			left outer join t_alur b on(a.id_alur=b.id)
			left outer join t_alur_status c on(a.id_alur=c.id_alur and a.status=c.status)
			left outer join t_unit d on(a.kdunit=d.kdunit)
			left outer join t_penerima e on(a.id_penerima=e.id)
			left outer join t_level g on(c.kdlevel=g.kdlevel)
			left outer join t_trans h on(a.kdtran=h.id)
			where b.menu=3 and a.thang='".session('tahun')."' ".$and."
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

						$ruh = '';
						if(session('kdlevel')=='11'){
							
							if($row->is_ubah==1){
								$ruh = '<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>
										<a id="'.$row->id.'" class="dropdown-item hapus" href="javascript:;">Hapus Data</a>';
							}
							
						}
						elseif(session('kdlevel')=='04' || session('kdlevel')=='07'){
							
							if($row->is_final!=='1'){
								$ruh = '<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>';
							}
							
						}
						elseif(session('kdlevel')=='00'){
							
							$ruh = '<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>';
							
						}
						
						$aksi='<center>
									<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
									<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
										'.$ruh.'
										<a class="dropdown-item" href="bukti/uang-muka/'.$row->id.'" target="_blank">Cetak Bukti</a>
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
					lpad(a.nourut,5,'0') as nourut,
					a.kdunit,
					a.kdsdana,
					a.id_proyek,
					a.id_alur,
					a.kdtran,
					a.id_penerima as id_pelanggan,
					a.nodok as nopks,
					to_char(a.tgdok,'yyyy-mm-dd') as tgpks,
					to_char(a.tgdok1,'yyyy-mm-dd') as tgjtempo,
					a.uraian,
					nvl(a.nilai,0) as nilai,
					nvl(a.debet,'') as debet,
					nvl(a.kredit,'') as kredit,
					ttd1,
					ttd2,
					ttd3,
					ttd4
			from d_trans a
			where a.id=?
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$detil = $rows[0];
			
			$rows = DB::select("
				select  a.*
				from t_akun a,
				(
					select  kdakun,
							panjang
					from t_trans_akun
					where id_trans=? and kddk='D'
				) b
				where a.lvl=6 and substr(a.kdakun,1,b.panjang)=substr(b.kdakun,1,b.panjang)
				order by a.kdakun asc
			",[
				$detil->kdtran
			]);
			
			$dropdown = '<option value="" style="display:none;">Pilih Data</option>';
			foreach($rows as $row){
				$selected = '';
				if($row->kdakun==$detil->debet){
					$selected = 'selected';
				}
				$dropdown .= '<option value="'.$row->kdakun.'" '.$selected.'> '.$row->nmakun.'</option>';
			}
			
			$data['dropdown_d'] = $dropdown;
			
			$rows = DB::select("
				select  a.*
				from t_akun a,
				(
					select  kdakun,
							panjang
					from t_trans_akun
					where id_trans=? and kddk='K'
				) b
				where a.lvl=6 and substr(a.kdakun,1,b.panjang)=substr(b.kdakun,1,b.panjang)
				order by a.kdakun asc
			",[
				$detil->kdtran
			]);
			
			$dropdown = '<option value="" style="display:none;">Pilih Data</option>';
			foreach($rows as $row){
				$selected = '';
				if($row->kdakun==$detil->kredit){
					$selected = 'selected';
				}
				$dropdown .= '<option value="'.$row->kdakun.'" '.$selected.'> '.$row->nmakun.'</option>';
			}
			
			$data['dropdown_k'] = $dropdown;
			
			$data['error'] = false;
			$data['message'] = $detil;
			
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
				select	*
				from t_alur
				where id=?
			",[
				$request->input('id_alur')
			]);
			
			$nourut = (int)$request->input('nourut');
			
			if(count($rows)>0){
				
				$nilai = (int)str_replace(',', '', $request->input('nilai'));
				
				if($nilai>=$rows[0]->batas1 && $nilai<=$rows[0]->batas2){
					
					if($request->input('inp-rekambaru')=='1'){
						
						$rows = DB::select("
							select	count(*) as jml
							from d_trans a
							left join t_alur b on(a.id_alur=b.id)
							where a.thang=? and b.menu=3 and a.nourut=?
						",[
							session('tahun'),
							$nourut
						]);
						
						if($rows[0]->jml==0){
							
							$id_trans = DB::table('d_trans')->insertGetId([
								'kdunit' => session('kdunit'),
								'thang' => session('tahun'),
								'id_alur' => $request->input('id_alur'),
								'kdsdana' => $request->input('kdsdana'),
								'id_proyek' => $request->input('id_proyek'),
								'nourut' => $nourut,
								'kdtran' => $request->input('kdtran'),
								'id_penerima' => $request->input('id_pelanggan'),
								'nodok' => $request->input('nopks'),
								'tgdok' => $request->input('tgpks'),
								'tgdok1' => $request->input('tgjtempo'),
								'uraian' => $request->input('uraian'),
								'ttd1' => $request->input('ttd1'),
								'ttd2' => $request->input('ttd2'),
								'ttd3' => $request->input('ttd3'),
								'ttd4' => $request->input('ttd4'),
								'debet' => $request->input('debet'),
								'nilai' => str_replace(',', '', $request->input('nilai')),
								'nilai_bersih' => str_replace(',', '', $request->input('nilai')),
								'status' => 1,
								'id_user' => session('id_user')
							]);
							
							if($id_trans){
								
								$lanjut = true;
								
							}
							else{
								$error = 'Data gagal disimpan!';
							}
							
						}
						else{
							$error = 'Duplikasi nomor transaksi!';
						}
						
					}
					else{
						$update = DB::update("
							update d_trans
							set kdtran=?,
								id_penerima=?,
								nodok=?,
								tgdok=?,
								tgdok1=?,
								uraian=?,
								ttd1=?,
								ttd2=?,
								ttd3=?,
								ttd4=?,
								nilai=?,
								nilai_bersih=?,
								id_user=?,
								updated_at=sysdate,
								kdsdana=?,
								id_proyek=?,
								debet=?
							where id=?
						",[
							$request->input('kdtran'),
							$request->input('id_pelanggan'),
							$request->input('nopks'),
							$request->input('tgpks'),
							$request->input('tgjtempo'),
							$request->input('uraian'),
							$request->input('ttd1'),
							$request->input('ttd2'),
							$request->input('ttd3'),
							$request->input('ttd4'),
							str_replace(',', '', $request->input('nilai')),
							str_replace(',', '', $request->input('nilai')),
							session('id_user'),
							$request->input('kdsdana'),
							$request->input('id_proyek'),
							$request->input('debet'),
							$request->input('inp-id')
						]);
						
						if($update){
							
							$lanjut = true;
							
						}
						else{
							$error = 'Data gagal diubah!';
						}
					}
					
				}
				else{
					$error = 'Nilai transaksi tidak valid!';
				}
				
			}
			else{
				$error = 'Kode proses tidak ditemukan!';
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
				where id=? and status=1
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