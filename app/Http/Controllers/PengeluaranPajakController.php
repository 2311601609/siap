<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DataTables;
use App\Libraries\PublicFunction;

class PengeluaranPajakController extends Controller {

	public function index(Request $request)
	{
		$sql = "
			select  a.id,
					lpad(a.nourut,5,'0') as nourut,
					d.nmunit,
					e.nama,
					h.nmtrans,
					a.nodok as pks,
					to_char(a.tgdok1,'dd-mm-yyyy') as tgjtempo,
					a.uraian,
					nvl(a.nilai_bersih,0) as nilai,
					b.nmalur||'<br>'||g.nmlevel||'<br>'||c.nmstatus as status,
					nvl(a.nilai,0)-nvl(a.nilai_bersih,0) as pajak,
					nvl(a.nilai,0) as total
			from d_trans a
			left outer join t_alur b on(a.id_alur=b.id)
			left outer join t_alur_status c on(a.id_alur=c.id_alur and a.status=c.status)
			left outer join t_unit d on(a.kdunit=d.kdunit)
			left outer join t_penerima e on(a.id_penerima=e.id)
			left outer join t_level g on(c.kdlevel=g.kdlevel)
			left outer join t_trans h on(a.kdtran=h.id)
			where b.menu=4 and a.thang='".session('tahun')."'
			order by a.id desc
		";

		$query = DB::table(DB::raw("($sql) a"))
				->selectRaw('a.*');

		$datatables = DataTables::of($query)
					->addIndexColumn()
					->editColumn('nilai', function($row){
						return number_format($row->nilai, 0, ',', '.');
					})
					->editColumn('pajak', function($row){
						return number_format($row->pajak, 0, ',', '.');
					})
					->editColumn('total', function($row){
						return number_format($row->total, 0, ',', '.');
					})
					->addColumn('aksi', function($row){

						$aksi='<center>
									<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
									<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
										<a id="'.$row->id.'" class="dropdown-item proses" href="javascript:;">Ubah Pajak</a>
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
					a.kdtran,
					b.nmalur,
					c.nmunit,
					d.nama as nmpelanggan,
					e.nmtrans,
					j.uraian as nmtrans_dtl,
					a.nodok as nopks,
					to_char(a.tgdok,'yyyy-mm-dd') as tgpks,
					to_char(a.tgdok1,'yyyy-mm-dd') as tgjtempo,
					a.uraian,
					nvl(a.nilai_bersih,0) as nilai,
					a.nilai-nvl(a.nilai_bersih,0) as pajak,
					a.nilai as total,
					k.kdakun as kdakun_d,
					nvl(k.nmakun,0) as debet,
					l.kdakun as kdakun_k,
					l.kdakun as kredit,
					k.kdakun,
					a.id_alur,
					a.status,
					m.nilai as umk
			from d_trans a
			left outer join t_alur b on(a.id_alur=b.id)
			left outer join t_unit c on(a.kdunit=c.kdunit)
			left outer join t_penerima d on(a.id_penerima=d.id)
			left outer join t_trans e on(a.kdtran=e.id)
			left outer join t_akun f on(a.debet=f.kdakun)
			left outer join t_akun i on(a.kredit=i.kdakun)
			left outer join t_trans_dtl j on(a.kdtran_dtl=j.id)
			left outer join(
				select  a.id_trans,
						a.kdakun,
						b.nmakun,
						a.nilai
				from d_trans_akun a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.grup=1 and a.kddk='D'
			) k on(a.id=k.id_trans)
			left outer join(
				select  a.id_trans,
						a.kdakun,
						b.nmakun,
						a.nilai
				from d_trans_akun a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.grup=1 and a.kddk='K'
			) l on(a.id=l.id_trans)
			left join(

				select	a.id,
						a.nodok as nopks,
						to_char(a.tgdok,'dd-mm-yyyy') as tgpks,
						a.id_penerima as id_pelanggan,
						a.uraian,
						nvl(c.nilai,0) as nilai,
						c.kdakun
				from d_trans a
				left outer join d_trans_akun c on(a.id=c.id_trans)
				where c.kddk='D' and a.kdtran=18

			) m on(a.parent_id=m.id)
			where a.id=?
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$id_alur = $rows[0]->id_alur;
			$detil = $rows[0];
			
			$data['error'] = false;
			$data['message'] = $detil;
			
			$rows = DB::select("
				select	a.id,
						b.uraian,
						a.nmfile
				from d_trans_dok a
				left outer join t_dok_dtl b on(a.id_dok_dtl=b.id)
				where a.id_trans=?
			",[
				$id
			]);
			
			$lampiran = '<ul>';
			foreach($rows as $row){
				$lampiran .= '<li><a href="penerimaan/rekam/download/'.$row->id.'" target="_blank" title="Download Lampiran">'.$row->uraian.'</li>';
			}
			$lampiran .= '</ul>';
			
			$data['lampiran'] = $lampiran;
			
			$rows = DB::select("
				select  *
				from t_akun
				where substr(kdakun,1,2)='72' and lvl=6
			");
			
			$pajak = '<option value="">Pilih Data</option>';
			foreach($rows as $row){
				$selected = '';
				if($row->kdakun==$detil->kdakun){
					$selected = 'selected';
				}
				$pajak .= '<option value="'.$row->kdakun.'" '.$selected.'>'.$row->nmakun.'</option>';
			}
			
			$data['pajak'] = $pajak;
			
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

			$nilai = str_replace(',', '', $request->input('nilai'));
			$total = str_replace(',', '', $request->input('total'));
			$id_trans = $request->input('inp-id');
			$kredit_akun = $request->input('kredit');
			$umk = $request->input('umk');
			
			if($total>0){
				
				$rows = DB::select("
					select	*
					from t_alur
					where id=?
				",[
					$request->input('id_alur')
				]);
				
				if(count($rows)>0){

					if($total>=$rows[0]->batas1 && $total<=$rows[0]->batas2){

						$rows = DB::select("
							select	*
							from t_trans
							where id=?
						",[
							$request->input('kdtran')
						]);
						
						if(count($rows)>0){

							$update = DB::update("
								update d_trans
								set nilai=?,
									nilai_bersih=?,
									id_user=?,
									updated_at=sysdate
								where id=?
							",[
								str_replace(',', '', $request->input('total')),
								str_replace(',', '', $request->input('nilai')),
								session('id_user'),
								$request->input('inp-id')
							]);
							
							if($update){
								
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
	
}