<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DataTables;
use App\Libraries\PublicFunction;

class PenerimaanRekamController extends Controller {

	public function index(Request $request)
	{
		if(session('kdlevel')=='10' || session('kdlevel')=='12'){
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
					e.nama,
					h.nmtrans,
					a.nodok||'<br>'||to_char(a.tgdok,'dd-mm-yyyy') as pks,
					to_char(a.tgdok1,'dd-mm-yyyy') as tgjtempo,
					a.uraian,
					a.nilai,
					c.nmstatus as status,
					i.lampiran,
					c.is_ubah,
					c.is_final
			from d_trans a
			left outer join t_alur b on(a.id_alur=b.id)
			left outer join t_alur_status c on(a.id_alur=c.id_alur and a.status=c.status)
			left outer join t_penerima e on(a.id_penerima=e.id)
			left outer join t_level g on(c.kdlevel=g.kdlevel)
			left outer join t_trans h on(a.kdtran=h.id)
			left outer join(
				select  a.id_trans,
						rtrim(xmlagg(xmlelement(e, a.id||'|'||b.uraian, ',')).extract('//text()').getclobval(), ',') as lampiran
				from d_trans_dok a
				left outer join t_dok_dtl b on(a.id_dok_dtl=b.id)
				group by a.id_trans
			) i on(a.id=i.id_trans)
			where b.menu=2 and a.thang='".session('tahun')."' ".$and."
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
						if(session('kdlevel')=='10' || session('kdlevel')=='12'){
							
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
						
						return '<center>
									<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
									<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
										'.$ruh.'
										<a class="dropdown-item" href="bukti/uang-masuk/'.$row->id.'" target="_blank">Cetak Bukti</a>
										<a class="dropdown-item" href="bukti/kuitansi/'.$row->id.'" target="_blank">Cetak Kuitansi</a>
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
					a.kdunit,
					a.kdsdana,
					a.id_alur,
					a.kdtran,
					nvl(a.id_proyek,'') as id_proyek,
					a.id_penerima as id_pelanggan,
					a.nodok as nopks,
					to_char(a.tgdok,'yyyy-mm-dd') as tgpks,
					a.nocek,
					to_char(a.tgcek,'yyyy-mm-dd') as tgcek,
					to_char(a.tgdok1,'yyyy-mm-dd') as tgjtempo,
					a.uraian,
					b.kdakun as debet,
					c.kdakun as kredit,
					floor(nvl(c.nilai,0)) as nilai,
					(nvl(c.nilai,0)-(floor(nvl(c.nilai,0))))*100 as nilai_des,
					nvl(b.nilai,0) as total,
					nvl(a.parent_id,0) as parent_id,
					lpad(a.nourut,5,'0') as nourut,
					a.ttd1,
					a.ttd2,
					a.ttd3,
					a.ttd4,
					a.id_program||'|'||d.uraian as id_program,
					a.id_giat||'|'||e.uraian as id_giat
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
			left join t_program d on(a.id_program=d.id)
			left join t_kegiatan e on(a.id_giat=e.id)
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
			
			$rows = DB::select("
				select	*
				from t_proyek
				order by nmproyek asc
			");
			
			$dropdown = '<option value="" style="display:none;">Pilih Data</option>';
			foreach($rows as $row){
				$dropdown .= '<option value="'.$row->id.'-'.$row->id_penerima.'"> '.$row->nmproyek.' : Rp.'.number_format($row->nilai).',-</option>';
			}
			
			$data['dropdown_p'] = $dropdown;
			
			$dropdown = '';
			
			if($detil->parent_id!==''){
				$rows = DB::select("
					select	a.id,
							a.nodok as nopks,
							to_char(a.tgdok,'dd-mm-yyyy') as tgpks,
							a.id_penerima as id_pelanggan,
							a.uraian,
							nvl(c.nilai,0) as nilai,
							c.kdakun,
							a.id_proyek
					from d_trans a
					left outer join d_trans_akun c on(a.id=c.id_trans)
					where a.id=? and c.kddk='D'
				",[
					$detil->parent_id
				]);
				
				$dropdown = '<option value="" style="display:none;">Pilih Data</option>';
				foreach($rows as $row){
					$dropdown .= '<option value="'.$row->id.'|'.$row->id_pelanggan.'|'.$row->uraian.'|'.$row->nilai.'|'.$row->kdakun.'|'.$row->id_proyek.'-'.$row->id_pelanggan.'" selected> PKS : '.$row->nopks.', '.$row->tgpks.', Rp. '.number_format($row->nilai).',-</option>';
				}
			}
			
			$data['tagihan'] = $dropdown;
			
			$data['akun'] = '';
			$data['x'] = 0;
			$rows = DB::select("
				select	a.kdakun,
						floor(nvl(a.nilai,0)) as nilai,
						(nvl(a.nilai,0)-(floor(nvl(a.nilai,0))))*100 as nilai_des,
						c.kddk,
						c.nilai as nilai1
				from d_trans_akun a
				left join d_trans b on(a.id_trans=b.id)
                left join t_trans_pajak c on(b.kdtran=c.id_trans and a.kdakun=c.kdakun and a.kddk=c.kddk)
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
							
							if($param==1){ //tagihan
								
								if($kddk=='D'){
									$pajak += $pajak1;
								}
								else{
									$pajak -= $pajak1;
								}
								
							}
							elseif($param==2){ //penerimaan
								
								if($kddk=='D'){
									$pajak -= $pajak1;
								}
								else{
									$pajak += $pajak1;
								}
								
							}
							
						}
						
					}
				}
			}
		}
		
		return number_format($nilai+$pajak,2);
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

			$program = htmlspecialchars($request->input('id_program'));
			$kegiatan = htmlspecialchars($request->input('id_giat'));

			$arr_program = explode("|", $program);
			$arr_kegiatan = explode("|", $kegiatan);

			$id_program = $arr_program[0];
			$id_giat = $arr_kegiatan[0];
			
			if($total>0){
			
				$id_proyek = '';
				if($request->input('param-id_proyek')!==''){
					$arr_proyek = explode("-", $request->input('param-id_proyek'));
					$id_proyek = $arr_proyek[0];
				}
				
				if($request->input('debet')!=='' && $request->input('kredit')!==''){
					
					if($request->input('inp-rekambaru')=='1'){
					
						$rows = DB::select("
							select	count(*) as jml
							from d_trans a
							left join t_alur b on(a.id_alur=b.id)
							where a.thang=? and b.menu=2 and a.nourut=?
						",[
							session('tahun'),
							$nourut
						]);
						
						if($rows[0]->jml==0){
							
							$arr_parent = explode("|", $request->input('parent_id'));
							$parent_id = $arr_parent[0];
							
							$id_trans = DB::table('d_trans')->insertGetId([
								'thang' => session('tahun'),
								'nourut' => $nourut,
								'kdunit' => $request->input('param-kdunit'),
								'id_alur' => $request->input('id_alur'),
								'kdtran' => $request->input('kdtran'),
								'kdsdana' => $request->input('param-kdsdana'),
								'id_proyek' => $id_proyek,
								'id_penerima' => $request->input('param-id_pelanggan'),
								'nodok' => $request->input('nopks'),
								'tgdok' => $request->input('tgcek'),
								'nocek' => $request->input('nocek'),
								'tgcek' => $request->input('tgcek'),
								'tgdok1' => $request->input('tgcek'),
								'uraian' => $request->input('uraian'),
								'ttd1' => $request->input('ttd1'),
								'ttd2' => $request->input('ttd2'),
								'ttd3' => $request->input('ttd3'),
								'ttd4' => $request->input('ttd4'),
								'id_program' => $id_program,
								'id_giat' => $id_giat,
								'nilai' => $total,
								'nilai_bersih' => $nilai,
								'parent_id' => $parent_id,
								'status' => 1,
								'id_user' => session('id_user')
							]);
							
							if($id_trans){
								
								$arr_insert[] = "select	".$id_trans." as id_trans,
														'".$request->input('debet')."' as kdakun,
														'D' as kddk,
														".$total." as nilai,
														1 as grup
												from dual";
												
								$arr_insert[] = "select	".$id_trans." as id_trans,
														'".$request->input('param-kredit')."' as kdakun,
														'K' as kddk,
														".$nilai." as nilai,
														1 as grup
												from dual
												";

								$arr_pajak = $request->input('rincian');
								if(is_array($arr_pajak)){
									if(count($arr_pajak)>0){
										
										$arr_keys = array_keys($arr_pajak);
										
										for($i=0;$i<count($arr_keys);$i++){
											
											$pajak = (float)str_replace(',', '', $arr_pajak[$arr_keys[$i]]["'nilai'"]);
											
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
						$update = DB::update("
							update d_trans
							set kdsdana=?,
								kdtran=?,
								id_proyek=?,
								id_penerima=?,
								nodok=?,
								tgdok=?,
								nocek=?,
								tgcek=?,
								tgdok1=?,
								uraian=?,
								nilai=?,
								nilai_bersih=?,
								ttd1=?,
								ttd2=?,
								ttd3=?,
								ttd4=?,
								id_program=?,
								id_giat=?,
								id_user=?,
								updated_at=sysdate
							where id=?
						",[
							$request->input('param-kdsdana'),
							$request->input('kdtran'),
							$id_proyek,
							$request->input('param-id_pelanggan'),
							$request->input('nopks'),
							$request->input('tgcek'),
							$request->input('nocek'),
							$request->input('tgcek'),
							$request->input('tgjtempo'),
							$request->input('uraian'),
							$total,
							$nilai,
							$request->input('ttd1'),
							$request->input('ttd2'),
							$request->input('ttd3'),
							$request->input('ttd4'),
							$id_program,
							$id_giat,
							session('id_user'),
							$request->input('inp-id')
						]);
						
						if($update){
							
							$id_trans = $request->input('inp-id');
							
							$arr_insert[] = "select	".$id_trans." as id_trans,
													'".$request->input('debet')."' as kdakun,
													'D' as kddk,
													".$total." as nilai,
													1 as grup
											from dual";
											
							$arr_insert[] = "select	".$id_trans." as id_trans,
													'".$request->input('param-kredit')."' as kdakun,
													'K' as kddk,
													".$nilai." as nilai,
													1 as grup
											from dual
											";

							$arr_pajak = $request->input('rincian');
							if(is_array($arr_pajak)){
								if(count($arr_pajak)>0){
									
									$arr_keys = array_keys($arr_pajak);
									
									for($i=0;$i<count($arr_keys);$i++){
										
										$pajak = (float)str_replace(',', '', $arr_pajak[$arr_keys[$i]]["'nilai'"]);
										
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
								$error = 'Simpan detil gagal!';
							}
							
						}
						else{
							$error = 'Data gagal diubah!';
						}
					}
								
				}
				else{
					$error = 'Kode debet dan kredit tidak dapat dikosongkan!';
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
				
				if($delete) {
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
	
	public function hapusDok(Request $request)
	{
		DB::beginTransaction();
			
		$delete = DB::delete("
			delete from d_trans_dok
			where id=?
		",[
			$request->input('id')
		]);
		
		if($delete==true) {
			DB::commit();
			return 'success';
		}
		else {
			return 'Proses hapus gagal. Hubungi Administrator.';
		}
	}
	
	public function tagihan($id)
	{
		$data['dropdown'] = '';
		$data['error'] = true;
		
		$rows = DB::select("
			select	*
			from t_trans
			where id=?
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$is_parent = $rows[0]->is_parent;
			$parent_id = $rows[0]->parent_id;
			
			if($is_parent==1){
				
				$rows = DB::select("
					select	a.id,
							lpad(a.nourut,5,'0') as nourut,
							a.nodok as nopks,
							to_char(a.tgdok,'dd-mm-yyyy') as tgpks,
							a.id_penerima as id_pelanggan,
							a.uraian,
							nvl(b.nilai,0) as nilai,
							b.kdakun,
							a.id_proyek,
							a.kdsdana,
							a.kdunit
					from d_trans a
					left join(
						select	id_trans,
								kdakun,
								nilai
						from d_trans_akun
						where kddk='D' and grup='1'
					) b on(a.id=b.id_trans)
					left join t_alur_status c on(a.id_alur=c.id_alur and a.status=c.status)
					where a.kdtran=? and c.is_final='1'
					order by a.id desc
				",[
					$parent_id
				]);
				
				$dropdown = '<option value="" style="display:none;">Pilih Data</option>';
				foreach($rows as $row){
					$dropdown .= '<option value="'.$row->id.'|'.$row->id_pelanggan.'|'.$row->uraian.'|'.$row->nilai.'|'.$row->kdakun.'|'.$row->id_proyek.'-'.$row->id_pelanggan.'-'.$row->kdsdana.'-'.$row->kdunit.'"> Invoice : '.$row->nourut.' | '.$row->uraian.' | Rp. '.number_format($row->nilai).',-</option>';
				}
				
				$data['dropdown'] = $dropdown;
				$data['error'] = false;
				
			}
			
		}
		
		return response()->json($data);
	}

	public function upload(Request $request)
	{
		try{
			$id_dok = $request->input('id_dok');

			$targetFolder = 'data/lampiran/'; // Relative to the root
		
			$rows = DB::select("
				select	*
				from t_dok_dtl
				where id=?
			",[
				$id_dok
			]);
			
			if(count($rows)>0){

				$ukuran = (int)$rows[0]->ukuran;
				$arr_tipe = explode(",", $rows[0]->tipe);

				if (!empty($_FILES)) {
				
					$file = $request->file('file');
					$fileName = $_FILES['file']['name'];
					$tempFile = $_FILES['file']['tmp_name'];
					$fileTypes = $arr_tipe;
					$fileParts = pathinfo($_FILES['file']['name']);
					$fileExt = $fileParts['extension'];
					$fileSize = $_FILES['file']['size'];				
					$error = $_FILES['file']['error'];
					
					if($error === UPLOAD_ERR_OK){
	
						//cek tipe file
						if (in_array($fileExt, $fileTypes)) {
							
							//cek ukuran file
							if($fileSize>0 && $fileSize<=($ukuran*1000000)){
	
								$now = new \DateTime();
								$tglupload = $now->format('YmdHis');
								
								$file_name_baru = md5($tglupload).'.'.$fileParts['extension'];

								$kirim = \Storage::disk('public')->put($targetFolder.$file_name_baru, fopen($file, 'r+'));
								
								if($kirim){
									
									session(array('upload_lampiran'=>$file_name_baru));
									return '1';
									
								}
								else{
									return 'File gagal diupload!';
								}
								
							}
							else{
								return 'File melebihi batas ukuran maksimal!';
							}
	
						}
						else{
							return 'Tipe file tidak sesuai.';
						}
	
					}
					else{
						return 'Kesalahan lainnya : '.$error;
					}
	
				}
				else{
					return 'Isi file kosong.';
				}

			}
			else{
				return 'Setting dokumen tidak ditemukan!';
			}

		}
		catch(\Exception $e){
			return 'Kesalahan lainnya : '.substr($e->getMessage(),0,200);
		}
	}
	
	public function uploadOld(Request $request, $id_dok)
	{
		$targetFolder = 'data/lampiran/'; // Relative to the root
		
		$rows = DB::select("
			select	*
			from t_dok_dtl
			where id=?
		",[
			$id_dok
		]);
		
		if(count($rows)>0){
			
			$ukuran = (int)$rows[0]->ukuran;
			$arr_tipe = explode(",", $rows[0]->tipe);
			
			if(!empty($_FILES)) {
				$file_name = $_FILES['file']['name'];
				$tempFile = $_FILES['file']['tmp_name'];
				$targetFile = $targetFolder.$file_name;
				$fileTypes = $arr_tipe; // File extensions
				$fileParts = pathinfo($_FILES['file']['name']);
				$fileSize = $_FILES['file']['size'];
				//type file sesuai..??	
				if(in_array($fileParts['extension'],$fileTypes)) {
					
					//isi kosong..??
					if($fileSize>0){
						
						if($fileSize<=$ukuran*1000000){
							
							$now = new \DateTime();
							$tglupload = $now->format('YmdHis');
							
							$file_name_baru = md5($tglupload).'.'.$fileParts['extension'];
							move_uploaded_file($tempFile,$targetFolder.$file_name_baru);
							
							if(file_exists($targetFolder.$file_name_baru)){
								
								session(array('upload_lampiran'=>$file_name_baru));
								return '1';
								
							}
							else{
								return 'File gagal diupload!';
							}
							
						}
						else{
							return 'File melebihi batas ukuran maksimal!';
						}
						
					}
					else{
						return 'Isi file kosong, periksa data anda.';
					}
				}
				else{
					return 'Tipe file tidak sesuai.';
				}
			}
			else{
				return 'Tidak ada file yang diupload.';
			}
			
		}
		else{
			return 'Setting dokumen tidak ditemukan!';
		}
	}
	
	public function uploadSimpan(Request $request)
	{
		DB::beginTransaction();
		
		if(session('upload_lampiran')!=='' && session('upload_lampiran')!==null){
			
			$delete = DB::delete("
				delete from d_trans_dok
				where id_trans=? and id_dok_dtl=?
			",[
				$request->input('id_trans'),
				$request->input('id_dok'),
			]);
			
			$insert = DB::insert("
				insert into d_trans_dok(id_trans,id_dok_dtl,nmfile)
				values(?,?,?)
			",[
				$request->input('id_trans'),
				$request->input('id_dok'),
				session('upload_lampiran')
			]);
			
			if($insert) {
				DB::commit();
				session(array('upload_lampiran'=>null));
				return 'success';
			}
			else {
				return 'Proses hapus gagal. Hubungi Administrator.';
			}
			
		}
		else{
			return 'Lampiran belum diupload!';
		}
	}
	
	public function download(Request $request, $id)
	{
		$rows = DB::select("
			select	*
			from d_trans_dok
			where id=?
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$nmfile = $rows[0]->nmfile;
				
			$targetPath = 'data/lampiran/';

			if(\Storage::disk('public')->exists($targetPath.$nmfile)) {
				
				return \Storage::disk('public')->download($targetPath.$nmfile);

			}
			else{
				return 'File lampiran tidak ditemukan di server.';
			}
			
		}
		else{
			return 'Dokumen tidak ditemukan!';
		}
	}

	public function downloadOld(Request $request, $id)
	{
		$rows = DB::select("
			select	*
			from d_trans_dok
			where id=?
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$log = 'data/lampiran/'.$rows[0]->nmfile;
			
			header('Content-Description:Lampiran Transaksi');
			header('Content-Disposition:attachment;filename=' . basename($log));
			header('Content-Transfer-Encoding:binary');
			header('Expires:0');
			header('Cahce-Control:must-revalidate');
			header('Pragma:public');
			header('Content-Length:'.filesize($log));
			readfile($log);
			
		}
		else{
			return 'Dokumen tidak ditemukan!';
		}
	}
	
}