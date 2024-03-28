<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PengeluaranBayarController extends Controller {

	public function index(Request $request)
	{
		$aColumns = array('id','nourut','nmunit','nama','nmtrans','total','bayar','nocek');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  a.id,
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
					where b.menu=4 and a.thang='".session('tahun')."' and c.is_final=1
					order by a.id desc
					";
		
		/*
		 * Paging
		 */ 
		$sLimit = " ";
		if((isset($_GET['iDisplayStart']))&&(isset($_GET['iDisplayLength']))){
			$iDisplayStart=$_GET['iDisplayStart']+1;
			$iDisplayLength=$_GET['iDisplayLength'];
			$sSearch=$_GET['sSearch'];
			if ((isset( $iDisplayStart )) &&  ($iDisplayLength != '-1' )) 
			{
				$iDisplayEnd=$iDisplayStart+$iDisplayLength-1;
				$sLimit = " WHERE NO BETWEEN '$iDisplayStart' AND '$iDisplayEnd'";
			}
		}
		
		/*
		 * Ordering
		 */
		$sOrder = " ";
		if((isset($_GET['iSortCol_0']))&&(isset($_GET['sSortDir_0']))){
			$iSortCol_0=$_GET['iSortCol_0'];
			$iSortDir_0=$_GET['sSortDir_0'];
			if ( isset($iSortCol_0  ) )
			{		
				//modified ordering
				for($i=0;$i<count($aColumns);$i++){
					if($iSortCol_0==$i){
						if($iSortDir_0=='asc'){
							$sOrder = " ORDER BY ".$aColumns[$i]." DESC ";
						}
						else{
							$sOrder = " ORDER BY ".$aColumns[$i]." ASC ";
						}
					}
				}
			}
		}
		
		//modified filtering
		$sWhere="";
		if(isset($_GET['sSearch'])){
			$sSearch=$_GET['sSearch'];
			if((isset($sSearch))&&($sSearch!='')){
				$sWhere=" where lower(pks) like lower('".$sSearch."%') or lower(pks) like lower('%".$sSearch."%') or
								lower(nourut) like lower('".$sSearch."%') or lower(nourut) like lower('%".$sSearch."%') or nilai=".$sSearch." ";
			}
		}
		
		/* Data set length after filtering */
		$iFilteredTotal = 0;
		$rows = DB::select("
			SELECT COUNT(*) as JUMLAH FROM (".$sTable.") qry
		");
		$result = (array)$rows[0];
		if($result){
			$iFilteredTotal = $result['jumlah'];
		}
		
		/* Total data set length */
		$iTotal = 0;
		$rows = DB::select("
			SELECT COUNT(".$sIndexColumn.") as JUMLAH FROM (".$sTable.") qry
		");
		$result = (array)$rows[0];
		if($result){
			$iTotal = $result['jumlah'];
		}

		/*
		 * Format Output
		 */
		$sEcho="";
		if(isset($_GET['sEcho'])){
			$sEcho=$_GET['sEcho'];
		}
		$output = array(
			"sEcho" => intval($sEcho),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iFilteredTotal,
			"aaData" => array()
		);
		
		$str=str_replace(" , ", " ", implode(", ", $aColumns));
		
		$sQuery = "SELECT * FROM ( SELECT ROWNUM AS NO,".$str." FROM ( SELECT * FROM (".$sTable.") ".$sOrder.") ".$sWhere." ) a ".$sLimit." ";
		
		$rows = DB::select($sQuery);
		
		foreach( $rows as $row )
		{
			$aksi='<center>
						<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
						<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
							<a id="'.$row->id.'" class="dropdown-item proses" href="javascript:;">Bayar</a>
						</div>
					</center>';
			
			$output['aaData'][] = array(
				$row->no,
				$row->nourut,
				$row->nmunit,
				$row->nama,
				$row->nmtrans,
				'<div style="text-align:right;">'.number_format($row->total).'</div>',
				$row->bayar,
				$aksi
			);
		}
		
		return response()->json($output);
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
					m.kdakun as bayar
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
				where a.grup=1 and a.kddk='K'
			) m on(a.id=m.id_trans)
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
				where lvl=6 and substr(kdakun,1,3)='111' and substr(kdakun,1,4)<>'1112'
				order by kdakun
			");
			
			$pajak = '<option value="">Pilih Data</option>';
			foreach($rows as $row){
				$selected = '';
				if($row->kdakun==$detil->bayar){
					$selected = 'selected';
				}
				$pajak .= '<option value="'.$row->kdakun.'" '.$selected.'>'.$row->nmakun.'</option>';
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
		DB::beginTransaction();
		
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
			$request->input('inp-id'),
		]);
		
		$delete = DB::delete("
			delete from d_trans_akun
			where id_trans=? and grup='3'
		",[
			$request->input('inp-id')
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
			$request->input('inp-id'),
			$request->input('bayar'),
			$request->input('inp-id')
		]);
		
		if($insert){
			DB::commit();
			return 'success';
		}
		else{
			return 'Proses gagal disimpan!';
		}		
	}
	
	public function hapus(Request $request)
	{
		DB::beginTransaction();
		
		$arr_id = explode("-", $request->input('id'));
		
		$delete = DB::delete("
			delete from d_trans_akun
			where id_trans=? and grup=?
		",[
			$arr_id[0],
			$arr_id[1]
		]);
		
		if($delete==true) {
			DB::commit();
			return 'success';
		}
		else {
			return 'Proses hapus gagal. Hubungi Administrator.';
		}
	}
}