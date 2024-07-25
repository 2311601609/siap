<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DataTables;
use App\Libraries\PublicFunction;

class PengeluaranProsesController extends Controller {

	public function index(Request $request)
	{
		$panjang = strlen(session('kdunit'));
		
		$sql = "
			select    a.*
			from(
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
						decode(c.is_unit,null,
							1,
							decode(substr(a.kdunit,1,".$panjang."),'".session('kdunit')."',
								1,
								0
							)
						) as akses,
						c.is_final
				from d_trans a
				left outer join t_alur b on(a.id_alur=b.id)
				left outer join t_alur_status c on(a.id_alur=c.id_alur and a.status=c.status)
				left outer join t_unit d on(a.kdunit=d.kdunit)
				left outer join t_penerima e on(a.id_penerima=e.id)
				left outer join t_level g on(c.kdlevel=g.kdlevel)
				left outer join t_trans h on(a.kdtran=h.id)
				where b.menu=4 and a.thang='".session('tahun')."' and c.kdlevel='".session('kdlevel')."'
			) a
			where a.akses=1 and nvl(a.is_final,'0')<>'1'
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

						$aksi = '';
						if($row->is_final!=='1'){
							$aksi='<center>
									<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
									<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
										<a id="'.$row->id.'" class="dropdown-item proses" href="javascript:;">Proses Data</a>
										<a class="dropdown-item" href="bukti/uang-keluar/'.$row->id.'" target="_blank">Cetak Bukti</a>
										<a class="dropdown-item" href="bukti/tanda-terima/'.$row->id.'" target="_blank">Cetak Tanda Terima</a>
									</div>
								</center>';
						}

						return $aksi;
 
					})
					->rawColumns(['lampiran','aksi'])
					->make(true);

		return $datatables;
	}
	
	public function monitoring(Request $request)
	{
		$panjang = strlen(session('kdunit'));
		
		$arrLevel = ['03','05','08','11'];
		
		$and = "";
		if(in_array(session('kdlevel'), $arrLevel)){
			$and = " and substr(a.kdunit,1,".$panjang.")='".session('kdunit')."'";
		}
		
		$and1 = "";
		if(isset($_GET['status'])){
			if($_GET['status']!==null && $_GET['status']!==''){
				$and1 = " and a.status=".$_GET['status'];
			}
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
					nvl(a.nilai,0)-nvl(a.ppn,0)-nvl(a.pph21,0)-nvl(a.pph22,0)-nvl(a.pph23,0)-nvl(a.pph25,0) as nilai,
					g.nmlevel||'/ '||c.nmstatus as status
			from d_trans a
			left outer join t_alur b on(a.id_alur=b.id)
			left outer join t_alur_status c on(a.id_alur=c.id_alur and a.status=c.status)
			left outer join t_unit d on(a.kdunit=d.kdunit)
			left outer join t_penerima e on(a.id_penerima=e.id)
			left outer join t_level g on(c.kdlevel=g.kdlevel)
			left outer join t_trans h on(a.kdtran=h.id)
			where b.menu=4 and a.thang='".session('tahun')."' ".$and." ".$and1."
			order by a.nourut desc
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
									<a id="'.$row->id.'" class="dropdown-item proses" href="javascript:;">Lihat Data</a>
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
					b.nmalur,
					a.id_output,
					a.kdunit,
					a.thang,
					c.nmunit,
					a.id_proyek,
					l.nmproyek,
					m.nmsdana,
					d.nama as nmpelanggan,
					k.nmtrans,
					a.nodok as nopks,
					to_char(a.tgdok,'yyyy-mm-dd') as tgpks,
					to_char(a.tgdok1,'yyyy-mm-dd') as tgjtempo,
					a.uraian,
					nvl(a.nilai_bersih,0) as nilai,
					nvl(j.nilai,0) as pajak,
					nvl(a.nilai,0) as total,
					nvl(n.nmakun,0) as debet,
					a.id_alur,
					a.status,
					n.kdakun,
					o.uraian as nmtrans_dtl,
					to_char(a.tgrekam,'yyyy-mm-dd') as tgrekam
			from d_trans a
			left outer join t_alur b on(a.id_alur=b.id)
			left outer join t_unit c on(a.kdunit=c.kdunit)
			left outer join t_penerima d on(a.id_penerima=d.id)
			left outer join t_trans k on(a.kdtran=k.id)
			left outer join t_akun f on(a.debet=f.kdakun)
			left outer join t_akun i on(a.kredit=i.kdakun)
			left outer join t_proyek l on(a.id_proyek=l.id)
			left outer join t_sdana m on(a.kdsdana=m.kdsdana)
			left outer join(
				select	a.id_trans,
						sum(decode(b.kddk,'D',a.nilai,-a.nilai)) as nilai
				from d_trans_akun a
				left join t_akun_pajak b on(a.kdakun=b.kdakun)
				where a.grup=0
				group by a.id_trans
			) j on(a.id=j.id_trans)
			left outer join(
				select  a.id_trans,
						a.kdakun,
						b.nmakun
				from d_trans_akun a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.grup=1 and a.kddk='D'
			) n on(a.id=n.id_trans)
			left join t_trans_dtl o on(a.kdtran_dtl=o.id) 
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
				select  *
				from t_alur_status_dtl
				where id_alur_status=(
					select id
					from t_alur_status
					where id_alur=? and status=?
				)
				order by nourut asc
			",[
				$id_alur,
				$status
			]);
			
			$status = '<option value="">Pilih Data</option>';
			foreach($rows as $row){
				$status .= '<option value="'.$row->id_alur_status_lanjut.'">'.$row->dropdown.'</option>';
			}
			
			$data['dropdown'] = $status;
			
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
				select  a.thang,
						a.kdunit,
						a.id_proyek,
						a.kdakun,
						nvl(b.pagu,0) as pagu,
						nvl(c.realisasi,0) as realisasi,
						a.nilai,
						nvl(b.pagu,0)-nvl(c.realisasi,0)-a.nilai as sisa
				from(
					
					select  b.thang,
							b.kdunit,
							b.id_proyek,
							a.kdakun,
							sum(a.nilai) as nilai
					from d_trans_akun a
					left join d_trans b on(a.id_trans=b.id)
					where a.id_trans=? and a.kddk='D' and a.grup='1'
					group by b.thang,b.kdunit,b.id_proyek,a.kdakun
					
				) a
				left join(

					select  thang,
							kdunit,
							id_proyek,
							kdakun,
							sum(nilai) as pagu
					from d_pagu
					group by thang,kdunit,id_proyek,kdakun

				) b on(a.thang=b.thang and a.kdunit=b.kdunit and a.id_proyek=b.id_proyek and a.kdakun=b.kdakun)
				left join(

					select  b.thang,
							b.kdunit,
							b.id_proyek,
							a.kdakun,
							sum(a.nilai) as realisasi
					from d_trans_akun a
					left join d_trans b on(a.id_trans=b.id)
					where a.id_trans<>? and a.kddk='D' and a.grup='1'
					group by b.thang,b.kdunit,b.id_proyek,a.kdakun

				) c on(a.thang=c.thang and a.kdunit=c.kdunit and a.id_proyek=c.id_proyek and a.kdakun=c.kdakun)
				order by a.kdakun asc
			",[
				$id,
				$id
			]);
			
			$tabel = '<table class="table table-bordered">
						<thead>
							<tr>
								<th>Akun/ COA</th>
								<th>Pagu/ Anggaran</th>
								<th>Realisasi Sebelumnya</th>
								<th>Realisasi Ini</th>
								<th>Sisa Pagu/ Anggaran</th>
							</tr>
						</thead>
						<tbody>';
			foreach($rows as $row){
				$tabel .= '
					<tr>
						<td style="text-align:center;">'.$row->kdakun.'</td>
						<td style="text-align:right;">'.number_format($row->pagu,2).'</td>
						<td style="text-align:right;">'.number_format($row->realisasi,2).'</td>
						<td style="text-align:right;">'.number_format($row->nilai,2).'</td>
						<td style="text-align:right;">'.number_format($row->sisa,2).'</td>
					</tr>
				';
			}
			$tabel .= '</tbody></table>';
			
			$data['tabel_pagu'] = $tabel;
			
			$rows = DB::select("
				select	a.kdakun,
						b.nmakun,
						a.nilai,
						a.kddk
				from d_trans_akun a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.id_trans=?
				order by a.kddk,a.kdakun
			",[
				$id
			]);
			
			$akun = '';
			foreach($rows as $row){
				
				if($row->kddk=='D'){
					$debet = number_format($row->nilai,2);
					$kredit = '';
				}
				else{
					$kredit = number_format($row->nilai,2);
					$debet = '';
				}
				
				$akun .= '<tr>
							<td>'.$row->kdakun.'</td>
							<td>'.$row->nmakun.'</td>
							<td style="text-align:right;">'.$debet.'</td>
							<td style="text-align:right;">'.$kredit.'</td>
						  </tr>';
			}
			
			$data['akun'] = $akun;
			
			$rows = DB::select("
				select  to_char(a.updated_at,'dd-mm-yyyy hh24:mi:ss') as tanggal,
						b.nmstatus,
						c.nmlevel,
						d.nama,
						a.ket
				from(
					select  a.id_alur,
							a.status,
							a.id_user,
							a.ket,
							a.updated_at
					from d_trans a
					where a.id=?

					union all

					select  a.id_alur,
							a.status,
							a.id_user,
							a.ket,
							a.updated_at
					from d_trans_histori a
					where a.id_trans=?
				) a
				left join t_alur_status b on(a.id_alur=b.id_alur and a.status=b.status)
				left join t_level c on(b.kdlevel=c.kdlevel)
				left join t_user d on(a.id_user=d.id)
				order by a.updated_at
			",[
				$id,
				$id
			]);
			
			$catatan = '';
			foreach($rows as $row){
				$catatan .= '<tr>
								<td>'.$row->tanggal.'</td>
								<td>'.$row->nmstatus.'</td>
								<td>'.$row->nmlevel.'</td>
								<td>'.$row->ket.'</td>
							</tr>';
			}
			
			$data['catatan'] = $catatan;
			
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
				from d_trans
				where id=? and id_alur=? and status=?
			",[
				$request->input('inp-id'),
				$request->input('id_alur'),
				$request->input('status')
			]);
			
			if($rows[0]->jml==1){
				
				$rows1 = DB::select("
					select  sum(decode(a.kddk,'D',a.nilai,0)) as debet,
							sum(decode(a.kddk,'K',a.nilai,0)) as kredit
					from d_trans_akun a
					where a.id_trans=?
				",[
					$request->input('inp-id')
				]);
				
				if($rows1[0]->debet>0){
					
					if($rows1[0]->debet==$rows1[0]->kredit){
						
						$rows = DB::select("
							select	*
							from t_alur_status
							where id=?
						",[
							$request->input('status1')
						]);
						
						if(count($rows)>0){
							
							$next = true;
							$error = $rows[0]->ket;
							
							if($rows[0]->is_bayar=='1'){
								
								$rows_bayar = DB::select("
									select nvl(nocek,'') as nocek
									from d_trans
									where id=?
								",[
									$request->input('inp-id'),
								]);
								
								if($rows_bayar[0]->nocek==''){
									$next = false;
								}
								
							}
							
							if($next){
								
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
										set id_alur=?,
											status=?,
											id_user=?,
											updated_at=sysdate,
											ket=?
										where id=?
									",[
										$rows[0]->id_alur,
										$rows[0]->status,
										session('id_user'),
										$request->input('ket'),
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
							
						}
						else{
							$error = 'Status tidak ditemukan!';
						}
						
					}
					else{
						$error = 'Jurnal transaksi tidak balance : Debet ('.number_format($rows1[0]->debet).') | Kredit ('.number_format($rows1[0]->kredit).')';
					}
					
				}
				else{
					$error = 'Nilai tidak valid.';
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
	
}