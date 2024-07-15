<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DataTables;
use App\Libraries\PublicFunction;

class TagihanPajakController extends Controller {

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
					nvl(a.nilai,0) as nilai,
					abs(nvl(a.nilai,0)-nvl(a.nilai_bersih,0)) as pajak,
					nvl(a.nilai_bersih,0) as total
			from d_trans a
			left outer join t_alur b on(a.id_alur=b.id)
			left outer join t_alur_status c on(a.id_alur=c.id_alur and a.status=c.status)
			left outer join t_unit d on(a.kdunit=d.kdunit)
			left outer join t_penerima e on(a.id_penerima=e.id)
			left outer join t_level g on(c.kdlevel=g.kdlevel)
			left outer join t_trans h on(a.kdtran=h.id)
			where b.menu=1 and a.thang='".session('tahun')."' and c.is_pajak1='1'
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

						return '<center>
									<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
									<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
										<a id="'.$row->id.'" class="dropdown-item proses" href="javascript:;">Ubah Pajak</a>
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
					b.nmalur,
					c.nmunit,
					d.nama as nmpelanggan,
					e.nmtrans,
					a.nodok as nopks,
					to_char(a.tgdok,'yyyy-mm-dd') as tgpks,
					to_char(a.tgdok1,'yyyy-mm-dd') as tgjtempo,
					a.uraian,
					nvl(f.nmakun,0) as debet,
					nvl(i.nmakun,0) as kredit,
					a.kredit as kdakun,
					a.id_alur,
					a.status,
					f.kdakun as kdakun_d,
					i.kdakun as kdakun_k,
					nvl(f.nilai,0) as total,
					nvl(i.nilai,0) as nilai
			from d_trans a
			left outer join t_alur b on(a.id_alur=b.id)
			left outer join t_unit c on(a.kdunit=c.kdunit)
			left outer join t_penerima d on(a.id_penerima=d.id)
			left outer join t_trans e on(a.kdtran=e.id)
			left outer join(
				select  a.id_trans,
						a.kdakun,
						b.nmakun,
						a.nilai
				from d_trans_akun a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.grup=1 and a.kddk='D'
			) f on(a.id=f.id_trans)
			left outer join(
				select  a.id_trans,
						a.kdakun,
						b.nmakun,
						a.nilai
				from d_trans_akun a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.grup=1 and a.kddk='K'
			) i on(a.id=i.id_trans)
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
				select	a.kdakun,
						a.nilai,
						b.kddk,
						b.nilai as nilai1,
						(nvl(a.nilai,0)-(floor(nvl(a.nilai,0))))*100 as nilai_des
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

			$id_trans = str_replace(',', '', $request->input('inp-id'));
			$nilai = str_replace(',', '', $request->input('nilai'));
			$total = str_replace(',', '', $request->input('total'));
		
			if($total>0){
				
				$update = DB::update("
					update d_trans
					set nilai=?,
						nilai_bersih=?,
						id_user=?,
						updated_at=sysdate
					where id=?
				",[
					str_replace(',', '', $request->input('nilai')),
					str_replace(',', '', $request->input('total')),
					session('id_user'),
					$request->input('inp-id')
				]);
				
				if($update){
					
					$arr_insert[] = "select	".$id_trans." as id_trans,
											'".$request->input('kdakun_d')."' as kdakun,
											'D' as kddk,
											".$total." as nilai,
											1 as grup
									from dual";
									
					$arr_insert[] = "select	".$id_trans." as id_trans,
											'".$request->input('kdakun_k')."' as kdakun,
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
						$lanjut = true;
					}
					else{
						$error = 'Simpan pajak gagal!';
					}
					
				}
				else{
					$error = 'Data gagal diupdate!';
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
	
}