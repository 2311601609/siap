<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DataTables;
use App\Libraries\PublicFunction;

class PengeluaranRekamController extends Controller {

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
					i.lampiran,
					c.is_ubah,
					c.is_final
			from d_trans a
			left outer join t_alur b on(a.id_alur=b.id)
			left outer join t_alur_status c on(a.id_alur=c.id_alur and a.status=c.status)
			left outer join t_unit d on(a.kdunit=d.kdunit)
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
			where b.menu=4 and a.thang='".session('tahun')."' ".$and."
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
							
							$ruh = '<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>
									<a id="'.$row->id.'" class="dropdown-item hapus" href="javascript:;">Hapus Data</a>';
							
						}
						
						$aksi='<center>
									<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
									<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
										'.$ruh.'
										<a id="'.$row->id.'" class="dropdown-item"
											href="#/pengeluaran/lampiran?
											id_trans='.$row->id.'&
											nourut='.$row->nourut.'&
											back=pengeluaran/rekam">
											Lampiran
										</a>
										<a class="dropdown-item" href="bukti/uang-keluar/'.$row->id.'" target="_blank">Cetak Bukti</a>
										<a class="dropdown-item" href="bukti/tanda-terima/'.$row->id.'" target="_blank">Cetak Tanda Terima</a>
									</div>
								</center>';

						return $aksi;

					})
					->rawColumns(['lampiran','aksi'])
					->make(true);

		return $datatables;

	}
	
	public function pilih(Request $request, $id)
	{
		$rows = DB::select("
			select  a.id,
					lpad(a.nourut,5,'0') as nourut,
					a.kdsdana,
					a.kdunit,
					a.id_alur,
					a.id_output,
					a.kdtran,
					a.kdtran_dtl,
					nvl(a.id_proyek,'') as id_proyek,
					a.id_penerima as id_pelanggan,
					a.nodok as nopks,
					to_char(a.tgdok,'yyyy-mm-dd') as tgpks,
					to_char(a.tgdok1,'yyyy-mm-dd') as tgjtempo,
					a.uraian,
					nvl(a.nilai_bersih,0) as nilai,
					a.nilai as total,
					b.kdakun as debet,
					c.kdakun as kredit,
					a.parent_id,
					a.ttd1,
					a.ttd2,
					a.ttd3,
					a.ttd4,
					a.nilai-a.nilai_bersih as pajak,
					to_char(a.tgrekam,'yyyy-mm-dd') as tgrekam,
					a.id_program||'|'||d.uraian as id_program,
					a.id_giat||'|'||e.uraian as id_giat,
					a.id_kontrak_dtl,
					f.id_kontrak
			from d_trans a
			left join(
				select	*
				from d_trans_akun
				where kddk='D' and grup=1
			) b on(a.id=b.id_trans)
			left join(
				select	*
				from d_trans_akun
				where kddk='K' and grup=1
			) c on(a.id=c.id_trans)
			left join t_program d on(a.id_program=d.id)
			left join t_kegiatan e on(a.id_giat=e.id)
			left join d_kontrak_dtl f on(a.id_kontrak_dtl=f.id)
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
			
			$dropdown = '';
			
			if($detil->parent_id!==''){
				$rows = DB::select("
					select	a.id,
							a.nodok as nopks,
							to_char(a.tgdok,'dd-mm-yyyy') as tgpks,
							a.id_penerima as id_pelanggan,
							a.uraian,
							nvl(c.nilai,0) as nilai,
							c.kdakun
					from d_trans a
					left outer join d_trans_akun c on(a.id=c.id_trans)
					where a.id=? and c.kddk='D'
				",[
					$detil->parent_id
				]);
				
				$dropdown = '<option value="" style="display:none;">Pilih Data</option>';
				foreach($rows as $row){
					$dropdown .= '<option value="'.$row->id.'|'.$row->id_pelanggan.'|-|'.$row->nilai.'|'.$row->kdakun.'" selected> PKS : '.$row->nopks.', '.$row->tgpks.', Rp. '.number_format($row->nilai).',-</option>';
				}
			}
			
			$data['tagihan'] = $dropdown;
			
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
			
			$data['akun'] = '';
			$data['x'] = 0;
			$rows = DB::select("
				select  a.kdakun,
                        a.nilai,
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
			
			$data['akun1'] = '';
			$data['x1'] = 0;
			$rows = DB::select("
				select	a.kdakun,
						a.nilai,
						a.kddk,
						a.nilai as nilai1
				from d_trans_akun a
				where a.id_trans=? and a.kddk='D' and a.grup='1'
			",[
				$id
			]);
			
			if(count($rows)>0){
				$data['akun1'] = $rows;
				$data['x1'] = count($rows);
			}
			
			$rows = DB::select("
				select  nvl(a.nilai,0) as pagu,
						nvl(b.nilai,0) as realisasi,
						nvl(a.nilai,0)-nvl(b.nilai,0) as sisa
				from(
					select  sum(nilai) as nilai
					from d_pagu
					where kdunit=? and thang=? and kdsdana=? and id_proyek=? and kdakun=?
				) a,
				(
					select  sum(nilai) as nilai
					from d_trans
					where kdunit=? and thang=? and kdsdana=? and id_proyek=? and debet=? and id<>?
				) b
			",[
				substr(session('kdunit'),0,4),
				session('tahun'),
				$detil->kdsdana,
				$detil->id_proyek,
				$detil->debet,
				substr(session('kdunit'),0,4),
				session('tahun'),
				$detil->kdsdana,
				$detil->id_proyek,
				$detil->debet,
				$id
			]);
			
			$pagu = 0;
			$realisasi = 0;
			$sisa = 0;
			if(count($rows)>0){
				$pagu = $rows[0]->pagu;
				$realisasi = $rows[0]->realisasi;
				$sisa = $rows[0]->sisa;
			}
			
			$data['pagu'] = $pagu;
			$data['realisasi'] = $realisasi;
			$data['sisa'] = $sisa;
			
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

			$program = htmlspecialchars($request->input('id_program'));
			$kegiatan = htmlspecialchars($request->input('id_giat'));

			$arr_program = explode("|", $program);
			$arr_kegiatan = explode("|", $kegiatan);

			$id_program = $arr_program[0];
			$id_giat = $arr_kegiatan[0];

			$total = str_replace(',', '', $request->input('total'));
			
			$nourut = (int)$request->input('nourut');
			
			if($total>0){
				
				$rows = DB::select("
					select	*
					from t_alur
					where id=?
				",[
					$request->input('id_alur')
				]);
				
				if(count($rows)>0){
					
					$nilai = (int)str_replace(',', '', $request->input('nilai'));
					
					if($total>=$rows[0]->batas1 && $total<=$rows[0]->batas2){
						
						$rows = DB::select("
							select	*
							from t_trans
							where id=?
						",[
							$request->input('kdtran')
						]);
						
						if(count($rows)>0){
							
							$next = true;
							
							if(($request->input('kdtran')==15 && $request->input('parent_id')=='') || ($request->input('kdtran')==16 && $request->input('parent_id')=='')){
								$next = false;
								$error = 'SPJ UMK harus dipilih untuk jenis pembayaran ini!';
							}
							
							if($next){
								
								$id_proyek = '';
								if($request->input('id_proyek')!==''){
									$arr_proyek = explode("-", $request->input('id_proyek'));
									$id_proyek = $arr_proyek[0];
								}
								
								if($request->input('kredit')!==''){
									
									$kredit_akun = $request->input('kredit');
									
									if($request->input('inp-rekambaru')=='1'){
										
										$rows = DB::select("
											select	count(*) as jml
											from d_trans a
											left join t_alur b on(a.id_alur=b.id)
											where a.thang=? and b.menu=4 and a.nourut=?
										",[
											session('tahun'),
											$nourut
										]);
										
										if($rows[0]->jml==0){
											
											$arr_parent = explode("|", $request->input('parent_id'));
											$parent_id = $arr_parent[0];
											
											$id_trans = DB::table('d_trans')->insertGetId([
												'kdunit' => session('kdunit'),
												'thang' => session('tahun'),
												'nourut' => $nourut,
												'id_proyek' => $id_proyek,
												'kdsdana' => $request->input('kdsdana'),
												'id_alur' => $request->input('id_alur'),
												'kdtran' => $request->input('kdtran'),
												'kdtran_dtl' => $request->input('kdtran_dtl'),
												'id_penerima' => $request->input('id_pelanggan'),
												'nodok' => $request->input('nopks'),
												'tgdok' => $request->input('tgpks'),
												'tgrekam' => DB::raw("to_date('".$request->input('tgrekam')."','yyyy-mm-dd')"),
												'tgdok1' => $request->input('tgjtempo'),
												'uraian' => $request->input('uraian'),
												'ttd1' => $request->input('ttd1'),
												'ttd2' => $request->input('ttd2'),
												'ttd3' => $request->input('ttd3'),
												'ttd4' => $request->input('ttd4'),
												'id_program' => $id_program,
												'id_giat' => $id_giat,
												'nilai' => str_replace(',', '', $request->input('total')),
												'nilai_bersih' => str_replace(',', '', $request->input('nilai')),
												'parent_id' => $parent_id,
												'status' => 1,
												'id_user' => session('id_user'),
												'id_kontrak_dtl' => $request->input('id_kontrak_dtl')
											]);
											
											if($id_trans){
												
												if($request->input('kdtran')!=='15' && $request->input('kdtran')!=='16'){ //LS
													
													$arr_buk = $request->input('rincian1');
													
													/* generate akun beban */
													if(is_array($arr_buk)){
														if(count($arr_buk)>0){
															
															$arr_keys = array_keys($arr_buk);
															
															for($j=0;$j<count($arr_keys);$j++){
																
																$kdakun = $arr_buk[$arr_keys[$j]]["'kdakun'"];
																$nilai = (float)(str_replace(',', '', $arr_buk[$arr_keys[$j]]["'nilai'"]));
																
																if($kdakun!=='' && $nilai>0){
																
																	$arr_insert[] = "select	".$id_trans." as id_trans,
																							'".$kdakun."' as kdakun,
																							'D' as kddk,
																							".$nilai." as nilai,
																							1 as grup
																					from dual";
																					
																}
																
															}
															
														}
													}
													
													/* generate akun kredit */
													if(count($arr_insert)>0){
														
														$arr_insert[] = "select	".$id_trans." as id_trans,
																				'".$kredit_akun."' as kdakun,
																				'K' as kddk,
																				".str_replace(',', '', $request->input('total'))." as nilai,
																				1 as grup
																		from dual
																		";
														
													}
													
													/* generate akun pajak */
													$lanjut = true;
													$arr_pajak = $request->input('rincian');
													if(is_array($arr_pajak)){
														if(count($arr_pajak)>0){
															
															$arr_keys = array_keys($arr_pajak);
															
															for($i=0;$i<count($arr_keys);$i++){
																
																if($arr_pajak[$arr_keys[$i]]["'nilai'"]>0){
																
																	$arr_akun = explode("|", $arr_pajak[$arr_keys[$i]]["'kdakun'"]);
																	$kdakun = $arr_akun[0];
																	$kddk = $arr_akun[1];
																	$nilai = (float)(str_replace(',', '', $arr_pajak[$arr_keys[$i]]["'nilai'"]));
																
																	$arr_insert[] = "select	".$id_trans." as id_trans,
																							'".$kdakun."' as kdakun,
																							'".$kddk."' as kddk,
																							".$nilai." as nilai,
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
														$lanjut = true;
													}
													else{
														$error = 'Simpan detil gagal!';
													}
													
												}
												else{ //SPJ UMK
													
													$arr_parent = explode("|", $request->input('parent_id'));
													$parent_id = $arr_parent[0];
													$umk = $arr_parent[3];
													
													$arr_buk = $request->input('rincian1');
													
													/* generate akun beban */
													if(is_array($arr_buk)){
														if(count($arr_buk)>0){
															
															$arr_keys = array_keys($arr_buk);
															
															for($j=0;$j<count($arr_keys);$j++){
																
																$kdakun = $arr_buk[$arr_keys[$j]]["'kdakun'"];
																$nilai = (float)(str_replace(',', '', $arr_buk[$arr_keys[$j]]["'nilai'"]));
																
																if($kdakun!=='' && $nilai>0){
																
																	$arr_insert[] = "select	".$id_trans." as id_trans,
																							'".$kdakun."' as kdakun,
																							'D' as kddk,
																							".$nilai." as nilai,
																							1 as grup
																					from dual";
																					
																}
																
															}
															
														}
													}
													
													/* generate akun pajak */
													$lanjut = true;
													$arr_pajak = $request->input('rincian');
													if(is_array($arr_pajak)){
														if(count($arr_pajak)>0){
															
															$arr_keys = array_keys($arr_pajak);
															
															for($i=0;$i<count($arr_keys);$i++){
																
																if($arr_pajak[$arr_keys[$i]]["'nilai'"]>0){
																
																	$arr_akun = explode("|", $arr_pajak[$arr_keys[$i]]["'kdakun'"]);
																	$kdakun = $arr_akun[0];
																	$kddk = $arr_akun[1];
																	$nilai = (float)(str_replace(',', '', $arr_pajak[$arr_keys[$i]]["'nilai'"]));
																
																	$arr_insert[] = "select	".$id_trans." as id_trans,
																							'".$kdakun."' as kdakun,
																							'".$kddk."' as kddk,
																							".$nilai." as nilai,
																							0 as grup
																					from dual";
																					
																}
																
															}
															
														}
													}
													
													$total = str_replace(',', '', $request->input('total'));
													
													if($total>$umk){ // SPJ > UMK
														
														$selisih = str_replace(',', '', $request->input('total'))-$umk;
														
														$arr_insert[] = "select	".$id_trans." as id_trans,
																				'114120' as kdakun,
																				'K' as kddk,
																				".$umk." as nilai,
																				2 as grup
																		from dual
																		
																		union all
														
																		select	".$id_trans." as id_trans,
																				'".$kredit_akun."' as kdakun,
																				'K' as kddk,
																				".$selisih." as nilai,
																				1 as grup
																		from dual
																		";
														
													}
													elseif($total<$umk){ // SPJ < UMK
														
														$selisih = $umk-str_replace(',', '', $request->input('total'));
														
														$arr_insert[] = "select	".$id_trans." as id_trans,
																				'114120' as kdakun,
																				'K' as kddk,
																				".$umk." as nilai,
																				2 as grup
																		from dual
																		
																		union all
														
																		select	".$id_trans." as id_trans,
																				'111300' as kdakun,
																				'D' as kddk,
																				".$selisih." as nilai,
																				3 as grup
																		from dual
																		";
														
													}
													else{ // SPJ = UMK
														
														$arr_insert[] = "select	".$id_trans." as id_trans,
																				'114120' as kdakun,
																				'K' as kddk,
																				".$umk." as nilai,
																				2 as grup
																		from dual
																		";
														
													}
													
													$insert = DB::insert("
														insert into d_trans_akun(id_trans,kdakun,kddk,nilai,grup)
														".implode(" union all ", $arr_insert)."
													");
													
													if($insert){
														$lanjut = true;
													}
													else{
														$error = 'Simpan detil gagal!';
													}
													
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
											set id_proyek=?,
												kdsdana=?,
												kdtran=?,
												kdtran_dtl=?,
												id_penerima=?,
												nodok=?,
												tgdok=?,
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
												updated_at=sysdate,
												tgrekam=to_date(?,'yyyy-mm-dd'),
												id_kontrak_dtl=?
											where id=?
										",[
											$id_proyek,
											$request->input('kdsdana'),
											$request->input('kdtran'),
											$request->input('kdtran_dtl'),
											$request->input('id_pelanggan'),
											$request->input('nopks'),
											$request->input('tgpks'),
											$request->input('tgjtempo'),
											$request->input('uraian'),
											str_replace(',', '', $request->input('total')),
											str_replace(',', '', $request->input('nilai')),
											$request->input('ttd1'),
											$request->input('ttd2'),
											$request->input('ttd3'),
											$request->input('ttd4'),
											$id_program,
											$id_giat,
											session('id_user'),
											$request->input('tgrekam'),
											$request->input('id_kontrak_dtl'),
											$request->input('inp-id')
										]);
										
										if($update){
											
											$id_trans = $request->input('inp-id');
											
											$delete = DB::delete("
												delete from d_trans_akun
												where id_trans=?
											",[
												$id_trans
											]);
											
											if($request->input('kdtran')!=='15' && $request->input('kdtran')!=='16'){ //LS
													
												$arr_buk = $request->input('rincian1');
												
												/* generate akun beban */
												if(is_array($arr_buk)){
													if(count($arr_buk)>0){
														
														$arr_keys = array_keys($arr_buk);
														
														for($j=0;$j<count($arr_keys);$j++){
															
															$kdakun = $arr_buk[$arr_keys[$j]]["'kdakun'"];
															$nilai = (float)(str_replace(',', '', $arr_buk[$arr_keys[$j]]["'nilai'"]));
															
															if($kdakun!=='' && $nilai>0){
															
																$arr_insert[] = "select	".$id_trans." as id_trans,
																						'".$kdakun."' as kdakun,
																						'D' as kddk,
																						".$nilai." as nilai,
																						1 as grup
																				from dual";
																				
															}
															
														}
														
													}
												}
												
												/* generate akun kredit */
												if(count($arr_insert)>0){
													
													$arr_insert[] = "select	".$id_trans." as id_trans,
																			'".$kredit_akun."' as kdakun,
																			'K' as kddk,
																			".str_replace(',', '', $request->input('total'))." as nilai,
																			1 as grup
																	from dual
																	";
													
												}
												
												/* generate akun pajak */
												$lanjut = true;
												$arr_pajak = $request->input('rincian');
												if(is_array($arr_pajak)){
													if(count($arr_pajak)>0){
														
														$arr_keys = array_keys($arr_pajak);
														
														for($i=0;$i<count($arr_keys);$i++){
															
															if($arr_pajak[$arr_keys[$i]]["'nilai'"]>0){
															
																$arr_akun = explode("|", $arr_pajak[$arr_keys[$i]]["'kdakun'"]);
																$kdakun = $arr_akun[0];
																$kddk = $arr_akun[1];
																$nilai = (float)(str_replace(',', '', $arr_pajak[$arr_keys[$i]]["'nilai'"]));
															
																$arr_insert[] = "select	".$id_trans." as id_trans,
																						'".$kdakun."' as kdakun,
																						'".$kddk."' as kddk,
																						".$nilai." as nilai,
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
													$lanjut = true;
												}
												else{
													$error = 'Simpan detil gagal!';
												}
												
											}
											else{ //SPJ UMK
												
												$arr_parent = explode("|", $request->input('parent_id'));
												$parent_id = $arr_parent[0];
												$umk = $arr_parent[3];
												$arr_buk = $request->input('rincian1');
												
												/* generate akun beban */
												if(is_array($arr_buk)){
													if(count($arr_buk)>0){
														
														$arr_keys = array_keys($arr_buk);
														
														for($j=0;$j<count($arr_keys);$j++){
															
															$kdakun = $arr_buk[$arr_keys[$j]]["'kdakun'"];
															$nilai = (float)(str_replace(',', '', $arr_buk[$arr_keys[$j]]["'nilai'"]));
															
															if($kdakun!=='' && $nilai>0){
															
																$arr_insert[] = "select	".$id_trans." as id_trans,
																						'".$kdakun."' as kdakun,
																						'D' as kddk,
																						".$nilai." as nilai,
																						1 as grup
																				from dual";
																				
															}
															
														}
														
													}
												}
												
												/* generate akun pajak */
												$lanjut = true;
												$arr_pajak = $request->input('rincian');
												if(is_array($arr_pajak)){
													if(count($arr_pajak)>0){
														
														$arr_keys = array_keys($arr_pajak);
														
														for($i=0;$i<count($arr_keys);$i++){
															
															if($arr_pajak[$arr_keys[$i]]["'nilai'"]>0){
															
																$arr_akun = explode("|", $arr_pajak[$arr_keys[$i]]["'kdakun'"]);
																$kdakun = $arr_akun[0];
																$kddk = $arr_akun[1];
																$nilai = (float)(str_replace(',', '', $arr_pajak[$arr_keys[$i]]["'nilai'"]));
															
																$arr_insert[] = "select	".$id_trans." as id_trans,
																						'".$kdakun."' as kdakun,
																						'".$kddk."' as kddk,
																						".$nilai." as nilai,
																						0 as grup
																				from dual";
																				
															}
															
														}
														
													}
												}
												
												$total = str_replace(',', '', $request->input('total'));
												
												if($total>$umk){ // SPJ > UMK
													
													$selisih = str_replace(',', '', $request->input('total'))-$umk;
													
													$arr_insert[] = "select	".$id_trans." as id_trans,
																			'114120' as kdakun,
																			'K' as kddk,
																			".$umk." as nilai,
																			2 as grup
																	from dual
																	
																	union all
													
																	select	".$id_trans." as id_trans,
																			'".$kredit_akun."' as kdakun,
																			'K' as kddk,
																			".$selisih." as nilai,
																			1 as grup
																	from dual
																	";
													
												}
												elseif($total<$umk){ // SPJ < UMK
													
													$selisih = $umk-str_replace(',', '', $request->input('total'));
													
													$arr_insert[] = "select	".$id_trans." as id_trans,
																			'114120' as kdakun,
																			'K' as kddk,
																			".$umk." as nilai,
																			2 as grup
																	from dual
																	
																	union all
													
																	select	".$id_trans." as id_trans,
																			'111300' as kdakun,
																			'D' as kddk,
																			".$selisih." as nilai,
																			3 as grup
																	from dual
																	";
													
												}
												else{ // SPJ = UMK
													
													$arr_insert[] = "select	".$id_trans." as id_trans,
																			'114120' as kdakun,
																			'K' as kddk,
																			".$umk." as nilai,
																			2 as grup
																	from dual
																	";
													
												}
												
												$insert = DB::insert("
													insert into d_trans_akun(id_trans,kdakun,kddk,nilai,grup)
													".implode(" union all ", $arr_insert)."
												");
												
												if($insert){
													$lanjut = true;
												}
												else{
													$error = 'Simpan detil gagal!';
												}
												
											}
											
										}
										else{
											$error = 'Data gagal diubah!';
										}
									}
									
								}
								else{
									$error = 'Kolom kredit tidak boleh dikosongkan!';
								}
									
							}
							
						}
						else{
							$error = 'Jenis transaksi tidak ditemukan!';
						}
						
					}
					else{
						$error = 'Nilai transaksi tidak valid!';
					}
					
				}
				else{
					$error = 'Kode proses tidak ditemukan!';
				}
				
			}
			else{
				$error = 'Hitung dulu total transaksi ini!';
			}

			if($lanjut){

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
					DB::connection()->getPdo()->commit();
					return 'success';
				}
				else{
					DB::connection()->getPdo()->rollBack();
					return 'Jurnal tidak balance, silahkan cek kembali perhitungan anda.';
				}
				
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
				
				if($delete==true) {
					DB::commit();
					$error = 'success';
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
		$panjang = strlen(session('kdunit'));
		
		$arrLevel = ['03','05','08','11'];
		
		$and = "";
		if(in_array(session('kdlevel'), $arrLevel)){
			$and = " and substr(a.kdunit,1,".$panjang.")='".session('kdunit')."'";
		}
		
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
			
			if($parent_id>0){
			
				$rows = DB::select("
					select	a.id,
							lpad(a.nourut,5,'0') as nourut,
							a.nodok as nopks,
							to_char(a.tgdok,'dd-mm-yyyy') as tgpks,
							a.id_penerima as id_pelanggan,
							a.uraian,
							nvl(a.nilai,0) as nilai,
							a.debet as kdakun
					from d_trans a
					left join t_alur_status b on(a.id_alur=b.id_alur and a.status=b.status)
					left join d_trans c on(a.id=c.parent_id)
					where a.thang=? and b.is_final=1 and c.id is null and a.kdtran=? ".$and."
					order by a.id desc
				",[
					session('tahun'),
					$parent_id
				]);
				
				$dropdown = '<option value="" style="display:none;">Pilih Data</option>';
				foreach($rows as $row){
					$dropdown .= '<option value="'.$row->id.'|'.$row->id_pelanggan.'|-|'.$row->nilai.'|'.$row->kdakun.'"> No.Urut UMK : '.$row->nourut.', '.$row->tgpks.', Rp. '.number_format($row->nilai).',-</option>';
				}
				
				$data['dropdown'] = $dropdown;
				$data['error'] = false;
				
			}
			else{
				$data['dropdown'] = '';
				$data['error'] = true;
			}
			
		}
		
		return response()->json($data);
	}
	
	public function upload(Request $request, $id_dok)
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
	
	public function hitungTotal(Request $request)
	{
		$nilai = 0;
		//$nilai = (float)(str_replace(',', '', $request->input('nilai')).'.'.str_replace(',', '', $request->input('nilai_des')));
		$arr_buk = $request->input('rincian1');
		if(is_array($arr_buk)){
			if(count($arr_buk)>0){
			
				$arr_keys = array_keys($arr_buk);
				
				for($j=0;$j<count($arr_keys);$j++){
					$nilai1 = (float)(str_replace(',', '', $arr_buk[$arr_keys[$j]]["'nilai'"]));
					$nilai += $nilai1;
				}
			}
		}
		
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
							if($kddk=='D'){
								$pajak += $pajak1;
							}
							else{
								$pajak -= $pajak1;
							}
						}
						
					}
				}
			}
		}
		
		if(isset($_GET['param'])){
			if($_GET['param']!==''){
				
				if($_GET['param']==1){
					
					$nilai = str_replace(',', '', $request->input('nilai'));
					$total = $nilai+$pajak;
					
				}
				
			}
		}
		
		return response()->json([
			'nilai' => number_format($nilai,2),
			'pajak' => number_format($pajak,2),
			'total' => number_format($nilai+$pajak,2)
		]);
	}
	
}