<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DataTables;
use App\Libraries\PublicFunction;
use Storage;

class PengeluaranBayarController extends Controller {

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
					b.nmalur||'<br>'||g.nmlevel||'<br>'||c.nmstatus as status,
					nvl(a.ppn,0)+nvl(a.pph21,0)+nvl(a.pph22,0)+nvl(a.pph23,0)+nvl(a.pph25,0) as pajak,
					nvl(a.nilai,0)-nvl(a.ppn,0)-nvl(a.pph21,0)-nvl(a.pph22,0)-nvl(a.pph23,0)-nvl(a.pph25,0) as total,
					nvl(a.nocek,'') as nocek,
					decode(a.nocek,null,'Belum','Sudah') as bayar
			from d_trans a
			left outer join t_alur b on(a.id_alur=b.id)
			left outer join t_alur_status c on(a.id_alur=c.id_alur and a.status=c.status)
			left outer join t_unit d on(a.kdunit=d.kdunit)
			left outer join t_penerima e on(a.id_penerima=e.id)
			left outer join t_level g on(c.kdlevel=g.kdlevel)
			left outer join t_trans h on(a.kdtran=h.id)
			where b.menu=4 and a.thang='".session('tahun')."' and c.is_bayar1=1
			order by a.id desc
		";

		$query = DB::table(DB::raw("($sql) a"))
				->selectRaw('a.*');

		$datatables = DataTables::of($query)
					->addIndexColumn()
					->editColumn('total', function($row){
						return number_format($row->total, 0, ',', '.');
					})
					->addColumn('aksi', function($row){

						$aksi='<center>
									<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
									<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
										<a id="'.$row->id.'" class="dropdown-item proses" href="javascript:;">Bayar</a>
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
					b.nmalur,
					a.nobuku,
					c.nmunit,
					d.nama as nmpelanggan,
					e.nmtrans,
					j.uraian as nmtrans_dtl,
					a.nodok as nopks,
					to_char(a.tgdok,'yyyy-mm-dd') as tgpks,
					to_char(a.tgdok1,'yyyy-mm-dd') as tgjtempo,
					a.uraian,
					a.nilai_bersih as nilai,
					l.nilai as pajak,
					a.nilai as total,
					nvl(k.nmakun,0) as debet,
					nvl(i.nmakun,0) as kredit,
					k.kdakun,
					a.id_alur,
					a.status,
					a.nobuku,
					a.nocek,
					to_char(a.tgcek,'yyyy-mm-dd') as tgcek,
					m.kdakun as bayar,
					n.nmfile,
					decode(o.id_trans,null,0,1) as utang_bayar
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
						b.nmakun
				from d_trans_akun a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.grup=1 and a.kddk='D'
			) k on(a.id=k.id_trans)
			left outer join(
				select	a.id_trans,
						sum(decode(b.kddk,'D',a.nilai,-a.nilai)) as nilai
				from d_trans_akun a
				left join t_akun_pajak b on(a.kdakun=b.kdakun)
				where a.grup=0
				group by a.id_trans
			) l on(a.id=l.id_trans)
			left outer join(
				select  a.id_trans,
						a.kdakun,
						b.nmakun
				from d_trans_akun a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.grup=3 and a.kddk='K'
			) m on(a.id=m.id_trans)
			left outer join(
				select	*
				from d_trans_dok
				where id_dok_dtl=53
			) n on(a.id=n.id_trans)
			left outer join(
				select  a.id_trans
				from d_trans_akun a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.grup=1 and a.kddk='K' and a.kdakun='210301'
			) o on(a.id=o.id_trans)
			where a.id=?
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$id_alur = $rows[0]->id_alur;
			$detil = $rows[0];
			$detil->token = csrf_token();
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
			$nmfile = '';
			foreach($rows as $row){
				$lampiran .= '<li><a href="pengeluaran-lampiran/download?nmfile='.$row->nmfile.'" target="_blank" title="Download Lampiran">'.$row->uraian.'</li>';
				$nmfile = $row->nmfile;
			}
			$lampiran .= '</ul>';
			
			$data['lampiran'] = $lampiran;
			$data['nmfile'] = $nmfile;
			
			$rows = DB::select("
				select  *
				from t_akun
				where substr(kdakun,1,4)='1101' and lvl=6
				order by kdakun
			");

			if($detil->utang_bayar==1){

				$pajak = '';

				foreach($rows as $row){
					$selected = '';
					if($row->kdakun==$detil->bayar){
						$selected = 'selected';
					}
					$pajak .= '<option value="'.$row->kdakun.'" '.$selected.'>'.$row->nmakun.'</option>';
				}

			}
			else{
				$pajak = '<option value="000000">NIHIL</option>';
			}
			
			$data['pajak'] = $pajak;
			
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
			$id_trans = $request->input('inp-id');
			$nmfile = $request->input('nmfile_baru');
		
			$update = DB::update("
				update d_trans
				set nobuku=?,
					nocek=?,
					tgcek=to_date(?,'yyyy-mm-dd'),
					updated_at=sysdate
				where id=?
			",[
				$request->input('nobuku'),
				$request->input('nocek'),
				$request->input('tgcek'),
				$id_trans,
			]);

			$next = true;

			if($request->input('bayar')!=='000000'){

				$delete = DB::delete("
					delete from d_trans_akun
					where id_trans=? and grup='3'
				",[
					$id_trans
				]);
				
				$insert = DB::insert("
					insert into d_trans_akun(id_trans,kdakun,nilai,kddk,grup)
					select  id_trans,
							kdakun,
							nilai,
							'D' as kddk,
							'3' as grup
					from d_trans_akun
					where id_trans=? and kddk='K' and grup='1'

					union all

					select  id_trans,
							? as kdakun,
							nilai,
							'K' as kddk,
							'3' as grup
					from d_trans_akun
					where id_trans=? and kddk='K' and grup='1'
				",[
					$id_trans,
					$request->input('bayar'),
					$id_trans
				]);

				if(!$insert){
					$next = false;
					$error = 'Proses simpan data pembayaran gagal.';
				}

			}

			if($next){
				
				if($nmfile!==null && $nmfile!==''){

					$delete = DB::table('d_trans_dok')
					->where('id_trans', $id_trans)
					->where('id_dok_dtl', 53)
					->delete();

					$insert = DB::table('d_trans_dok')->insert([
						'id_trans' => $id_trans,
						'id_dok_dtl' => 53,
						'nmfile' => $nmfile,
						'ket' => 'Dokumen bukti transfer'
					]);

					if($insert){
						$lanjut = true;
					}
					else{
						$error = 'Upload file gagal disimpan.';
					}

				}
				else{
					$error = 'File bukti pembayaran belum diupload.';
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
	
	public function hapus(Request $request)
	{
		DB::connection()->getPdo()->beginTransaction();
		try{
			$lanjut = false;
			$error = '';
		
			$arr_id = explode("-", $request->input('id'));
			
			$delete = DB::delete("
				delete from d_trans_akun
				where id_trans=? and grup=?
			",[
				$arr_id[0],
				$arr_id[1]
			]);
			
			if($delete==true) {
				$lanjut = true;
			}
			else {
				$error = 'Proses hapus gagal. Hubungi Administrator.';
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
							$pathFTP = 'buk/';
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
}