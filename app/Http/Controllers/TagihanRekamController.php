<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DataTables;
use App\Libraries\PublicFunction;

class TagihanRekamController extends Controller {

	/**
	 * description 
	 */
	public function __construct()
	{
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
		$panjang = strlen(session('kdunit'));
		
		$arrLevel = ['03','05','08','11'];
	
		$arr_where = array();
		if(in_array(session('kdlevel'), $arrLevel)){
			$arr_where[] = " substr(a.kdunit,1,".$panjang.")='".session('kdunit')."' ";
		}

		$where = "";
		if(count($arr_where)>0){
			$where = " and ".implode(" and ", $arr_where);
		}

		$sql = "
			select  a.id,
					lpad(a.nourut,5,'0') as nourut,
					d.nmunit as unit,
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
			left outer join t_trans h on(a.kdtran=h.id)
			where b.menu=1 and a.thang='".session('tahun')."' ".$where."
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

						$ubah = '';
						$hapus = '';
						if(session('kdlevel')=='12'){
							
							if($row->is_ubah==1){

								$ubah = '<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>';
								$hapus = '<a id="'.$row->id.'" class="dropdown-item hapus" href="javascript:;">Hapus Data</a>';
										
							}
							
						}
						elseif(session('kdlevel')=='07' || session('kdlevel')=='04'){
							
							if($row->is_final!=='1'){
								
								$ubah = '<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>';
								
							}
							
						}
						elseif(session('kdlevel')=='00'){
							
							$ubah = '<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>';
							
						}
						
						return '<center>
											<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
											<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
											'.$ubah.'	
											'.$hapus.'	
											<a class="dropdown-item" target="_blank" href="tagihan/rekam/cetak?id='.$row->id.'">Cetak Tagihan</a>
											</div>
										</center>';


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
					a.id_alur,
					a.kdtran,
					a.id_proyek,
					a.id_penerima as id_pelanggan,
					a.nodok as nopks,
					to_char(a.tgdok,'yyyy-mm-dd') as tgpks,
					to_char(a.tgdok1,'yyyy-mm-dd') as tgjtempo,
					to_char(a.tgrekam,'yyyy-mm-dd') as tgrekam,
					a.uraian,
					floor(nvl(b.nilai,0)) as nilai,
					a.nilai_bersih as total,
					a.nilai,
					b.kdakun as debet,
					c.kdakun as kredit,
					a.ttd1,
					a.ttd2,
					a.ttd3,
					a.ttd4,
					(nvl(b.nilai,0)-(floor(nvl(b.nilai,0))))*100 as nilai_des,
					a.id_kontrak_dtl,
					d.id_kontrak
			from d_trans a
			left outer join(
				select	id_trans,
						kdakun,
						nilai
				from d_trans_akun
				where kddk='D' and grup=1
			) b on(a.id=b.id_trans)
			left outer join(
				select	id_trans,
						kdakun,
						nilai
				from d_trans_akun
				where kddk='K' and grup=1
			) c on(a.id=c.id_trans)
			left join d_kontrak_dtl d on(a.id_kontrak_dtl=d.id)
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
			
			$data['akun'] = '';
			$data['x'] = 0;
			$rows = DB::select("
				select	a.kdakun,
						floor(nvl(a.nilai,0)) as nilai,
						b.kddk,
						b.nilai as nilai1
				from d_trans_akun a
				left join t_akun_pajak b on(a.kdakun=b.kdakun)
				where a.id_trans=? and a.grup=0
			",[
				$id
			]);
			
			if(count($rows)>0){				
				$data['akun'] = $rows;
				$data['x'] = count($rows);
			}
			
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

			$total = (float)str_replace(',', '', $request->input('total'));
			$nilai = (float)str_replace(',', '', $request->input('nilai'));
			
			$nourut = (int)$request->input('nourut');
			
			if($total>0){
				
				$id_proyek = '';
				if($request->input('id_proyek')!==''){
					$arr_proyek = explode("-", $request->input('id_proyek'));
					$id_proyek = $arr_proyek[0];
				}
				
				if($request->input('inp-rekambaru')=='1'){
					
					$rows = DB::select("
						select	count(*) as jml
						from d_trans a
						left join t_alur b on(a.id_alur=b.id)
						where a.thang=? and b.menu=1 and a.nourut=?
					",[
						session('tahun'),
						$nourut
					]);
					
					if($rows[0]->jml==0){
						
						$id_trans = DB::table('d_trans')->insertGetId([
							'kdsdana' => $request->input('kdsdana'),
							'kdunit' => $request->input('kdunit'),
							'thang' => session('tahun'),
							'id_alur' => $request->input('id_alur'),
							'nourut' => $nourut,
							'kdtran' => $request->input('kdtran'),
							'id_proyek' => $id_proyek,
							'id_penerima' => $request->input('id_pelanggan'),
							'nodok' => $request->input('nopks'),
							'tgdok' => $request->input('tgpks'),
							'tgdok1' => $request->input('tgjtempo'),
							'tgrekam' => DB::raw("to_date('".$request->input('tgrekam')."','yyyy-mm-dd')"),
							'uraian' => $request->input('uraian'),
							'ttd1' => $request->input('ttd1'),
							'ttd2' => $request->input('ttd2'),
							'ttd3' => $request->input('ttd3'),
							'ttd4' => $request->input('ttd4'),
							'nilai_bersih' => $total,
							'nilai' => $nilai,
							'status' => 1,
							'id_user' => session('id_user'),
							'id_kontrak_dtl' => $request->input('id_kontrak_dtl')
						]);
						
						if($id_trans){
							
							$arr_insert[] = "select	".$id_trans." as id_trans,
													'".$request->input('debet')."' as kdakun,
													'D' as kddk,
													".$total." as nilai,
													1 as grup
											from dual";
											
							$arr_insert[] = "select	".$id_trans." as id_trans,
													'".$request->input('kredit')."' as kdakun,
													'K' as kddk,
													".$nilai." as nilai,
													1 as grup
											from dual
											";
							
							$lanjut = true;
							$arr_pajak = $request->input('rincian');
							if(is_array($arr_pajak)){
								if(count($arr_pajak)>0){
									
									$arr_keys = array_keys($arr_pajak);
									
									for($i=0;$i<count($arr_keys);$i++){
										
										$pajak = (float)(str_replace(',', '', $arr_pajak[$arr_keys[$i]]["'nilai'"]));
										
										if($pajak>0){
										
											$arr_akun = explode("|", $arr_pajak[$arr_keys[$i]]["'kdakun'"]);
											$kdakun = $arr_akun[0];
											$kddk = $arr_akun[1];
										
											$arr_insert[] = "select	".$id_trans." as id_trans,
																	'".$kdakun."' as kdakun,
																	'".$kddk."' as kddk,
																	".$pajak." as nilai,
																	0 as grup
															from dual";
															
										}
										
									}
									
								}
							}
								
							$insert = DB::insert("
								insert into d_trans_akun(id_trans,kdakun,kddk,nilai,grup)
								".implode(" union all ", $arr_insert)."
							");
							
							if($insert){
								
								$rows_balance = DB::select("
									select	count(*) as jml
									from(
										select	sum(decode(a.kddk,'D',a.nilai,0)) as debet,
												sum(decode(a.kddk,'K',a.nilai,0)) as kredit
										from(
											".implode(" union all ", $arr_insert)."
										) a
									) a
									where a.debet=a.kredit
								");

								if($rows_balance[0]->jml==1){
									$lanjut = true;
								}
								else{
									$error = 'Jurnal tidak balance, silahkan cek perhitungan total.';
								}

							}
							else{
								$error = 'Simpan detil gagal!';
							}
							
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
					
					$id_trans = $request->input('inp-id');

					$update = DB::table('d_trans')
					->where('id', $id_trans)
					->update([
						'nodok' => $request->input('nopks'),
						'tgdok' => $request->input('tgpks'),
						'tgdok1' => $request->input('tgjtempo'),
						'tgrekam' => DB::raw("to_date('".$request->input('tgrekam')."','yyyy-mm-dd')"),
						'uraian' => $request->input('uraian'),
						'ttd1' => $request->input('ttd1'),
						'ttd2' => $request->input('ttd2'),
						'ttd3' => $request->input('ttd3'),
						'ttd4' => $request->input('ttd4'),
						'nilai_bersih' => $total,
						'nilai' => $nilai,
						'status' => 1,
						'id_user' => session('id_user'),
						'updated_at' => DB::raw('sysdate')
					]);
					
					if($update){

						$arr_insert[] = "select	".$id_trans." as id_trans,
												'".$request->input('debet')."' as kdakun,
												'D' as kddk,
												".$total." as nilai,
												1 as grup
										from dual";
										
						$arr_insert[] = "select	".$id_trans." as id_trans,
												'".$request->input('kredit')."' as kdakun,
												'K' as kddk,
												".$nilai." as nilai,
												1 as grup
										from dual
										";
						
						$lanjut = true;
						$arr_pajak = $request->input('rincian');
						if(is_array($arr_pajak)){
							if(count($arr_pajak)>0){
								
								$arr_keys = array_keys($arr_pajak);
								
								for($i=0;$i<count($arr_keys);$i++){
									
									$pajak = (float)(str_replace(',', '', $arr_pajak[$arr_keys[$i]]["'nilai'"]));
									
									if($pajak>0){
									
										$arr_akun = explode("|", $arr_pajak[$arr_keys[$i]]["'kdakun'"]);
										$kdakun = $arr_akun[0];
										$kddk = $arr_akun[1];
									
										$arr_insert[] = "select	".$id_trans." as id_trans,
																'".$kdakun."' as kdakun,
																'".$kddk."' as kddk,
																".$pajak." as nilai,
																0 as grup
														from dual";
														
									}
									
								}
								
							}
						}
						
						$delete = DB::delete("
							delete from d_trans_akun
							where id_trans=?
						",[
							$id_trans
						]);
							
						$insert = DB::insert("
							insert into d_trans_akun(id_trans,kdakun,kddk,nilai,grup)
							".implode(" union all ", $arr_insert)."
						");
						
						if($insert){
							
							$rows_balance = DB::select("
								select	count(*) as jml
								from(
									select	sum(decode(a.kddk,'D',a.nilai,0)) as debet,
											sum(decode(a.kddk,'K',a.nilai,0)) as kredit
									from(
										".implode(" union all ", $arr_insert)."
									) a
								) a
								where a.debet=a.kredit
							");

							if($rows_balance[0]->jml==1){
								$lanjut = true;
							}
							else{
								$error = 'Jurnal tidak balance, silahkan cek perhitungan total.';
							}

						}
						else{
							$error = 'Simpan pajak gagal!';
						}

					}
					else{
						$error = 'Data header gagal diubah!';
					}
					
				}
				
			}
			else{
				$error = 'Hitung dulu total transaksi ini!';
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

	public function hitungTotal(Request $request)
	{
		$param = '';
		if(isset($_GET['param'])){
			if($_GET['param']!==''){
				$param = $_GET['param'];
			}
		}
		
		$nilai = (float)(str_replace(',', '', $request->input('nilai')));
		$pajak = 0;
		$arr_pajak = $request->input('rincian');
		if(is_array($arr_pajak)){
			if(count($arr_pajak)>0){
			
				$arr_keys = array_keys($arr_pajak);
				
				for($i=0;$i<count($arr_keys);$i++){
					$pajak1 = (float)(str_replace(',', '', $arr_pajak[$arr_keys[$i]]["'nilai'"]));
					$arr_akun = explode("|", $arr_pajak[$arr_keys[$i]]["'kdakun'"]);
					
					if(isset($arr_akun[1])){
						
						$kddk = $arr_akun[1];
						if($pajak1>0){
							
							$pajak += $pajak1;
							
						}
						
					}
				}
			}
		}
		
		return number_format($nilai+$pajak,2);
	}

	public function cetak(Request $request)
	{
		if(isset($_GET['id'])){
			
			$id = $_GET['id'];

			$rows = DB::select("
				select  a.id,
						a.id_kontrak_dtl,
						b.id_kontrak,
						e.nmproyek,
						c.ket as ket_kontrak,
						d.nama,
						lpad(a.nourut,5,'0') as nourut,
						a.thang,
						a.nodok,
						to_char(a.tgdok,'d FMMonth yyyy') as tgdok,
						to_char(b.tgjtempo,'d FMMonth yyyy') as tgjtempo,
						a.uraian,
						b.tahun,
						b.bulan,
						nvl(f.nilai,0) as nilai,
						nvl(g.nilai,0) as pajak,
						nvl(f.nilai,0)+nvl(g.nilai,0) as total
				from d_trans a
				left join d_kontrak_dtl b on(a.id_kontrak_dtl=b.id)
				left join d_kontrak c on(b.id_kontrak=c.id)
				left join t_penerima d on(c.id_penerima=d.id)
				left join t_proyek e on(a.id_proyek=e.id)
				left join(
					
					select  id_trans,
							sum(nilai) as nilai
					from d_trans_akun
					where id_trans=? and grup='1' and kddk='K'
					group by id_trans

				) f on(a.id=f.id_trans)
				left join(
					
					select  id_trans,
							sum(nilai) as nilai
					from d_trans_akun
					where id_trans=? and grup='0'
					group by id_trans

				) g on(a.id=g.id_trans)
				where a.id=?
			",[
				$id,
				$id,
				$id
			]);

			if(count($rows)>0){

				$tagihan = $rows[0];

				return view('tagihan.index', [
					'tagihan' => $tagihan
				]);

			}
			else{
				return 'Data tidak ditemukan.';
			}

		}
		else{
			return 'Parameter tidak valid.';
		}
	}
	
}