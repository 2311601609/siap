<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PenerimaanPajakController extends Controller {

	public function index(Request $request)
	{
		$aColumns = array('id','nourut','nmunit','nama','nmtrans','nilai','pajak','total');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  	a.id,
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
							nvl(a.nilai,0)+nvl(a.ppn,0)+nvl(a.pph21,0)+nvl(a.pph22,0)+nvl(a.pph23,0)+nvl(a.pph25,0) as total
					from d_trans a
					left outer join t_alur b on(a.id_alur=b.id)
					left outer join t_alur_status c on(a.id_alur=c.id_alur and a.status=c.status)
					left outer join t_unit d on(a.kdunit=d.kdunit)
					left outer join t_penerima e on(a.id_penerima=e.id)
					left outer join t_level g on(c.kdlevel=g.kdlevel)
					left outer join t_trans h on(a.kdtran=h.id)
					where b.menu=2 and a.thang='".session('tahun')."'
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
							<a id="'.$row->id.'" class="dropdown-item proses" href="javascript:;">Ubah Pajak</a>
						</div>
					</center>';
			
			$output['aaData'][] = array(
				$row->no,
				$row->nourut,
				$row->nmunit,
				$row->nama,
				$row->nmtrans,
				'<div style="text-align:right;">'.number_format($row->nilai).'</div>',
				'<div style="text-align:right;">'.number_format($row->pajak).'</div>',
				'<div style="text-align:right;">'.number_format($row->total).'</div>',
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
					c.nmunit,
					d.nama as nmpelanggan,
					e.nmtrans,
					a.nodok as nopks,
					to_char(a.tgdok,'yyyy-mm-dd') as tgpks,
					to_char(a.tgdok1,'yyyy-mm-dd') as tgjtempo,
					a.uraian,
					nvl(j.nilai,0) as nilai,
					nvl(a.ppn,0) as ppn,
					nvl(a.pph21,0) as pph21,
					nvl(a.pph22,0) as pph22,
					nvl(a.pph23,0) as pph23,
					nvl(a.pph25,0) as pph25,
					nvl(h.nilai,0) as total,
					h.kdakun as kdakun_d,
					nvl(h.nmakun,0) as debet,
					j.kdakun as kdakun_k,
					nvl(j.nmakun,0) as kredit,
					a.kredit as kdakun,
					a.id_alur,
					a.status,
					(nvl(j.nilai,0)-(floor(nvl(j.nilai,0))))*100 as nilai_des
			from d_trans a
			left outer join t_alur b on(a.id_alur=b.id)
			left outer join t_unit c on(a.kdunit=c.kdunit)
			left outer join t_penerima d on(a.id_penerima=d.id)
			left outer join t_trans e on(a.kdtran=e.id)
			left outer join t_akun f on(a.debet=f.kdakun)
			left outer join t_akun i on(a.kredit=i.kdakun)
			left outer join(
				select  a.id_trans,
						a.kdakun,
						b.nmakun,
						a.nilai
				from d_trans_akun a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.grup=1 and a.kddk='D'
			) h on(a.id=h.id_trans)
			left outer join(
				select  a.id_trans,
						a.kdakun,
						b.nmakun,
						a.nilai
				from d_trans_akun a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.grup=1 and a.kddk='K'
			) j on(a.id=j.id_trans)
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
		$total = str_replace(',', '', $request->input('total'));
		
		if($total>0){
		
			DB::beginTransaction();
			
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
				
				$id_trans = $request->input('inp-id');
				
				$arr_insert[] = "select	".$id_trans." as id_trans,
										'".$request->input('kdakun_d')."' as kdakun,
										'D' as kddk,
										".str_replace(',', '', $request->input('total'))." as nilai,
										1 as grup
								 from dual";
								 
				$arr_insert[] = "select	".$id_trans." as id_trans,
										'".$request->input('kdakun_k')."' as kdakun,
										'K' as kddk,
										".str_replace(',', '', $request->input('nilai'))." as nilai,
										1 as grup
								 from dual
								 ";
				
				$lanjut = true;
				$arr_pajak = $request->input('rincian');
				$id_trans = $request->input('inp-id');
				if(is_array($arr_pajak)){
					if(count($arr_pajak)>0){
						
						$arr_keys = array_keys($arr_pajak);
						
						for($i=0;$i<count($arr_keys);$i++){
							
							if($arr_pajak[$arr_keys[$i]]["'nilai'"]>0){
							
								$arr_akun = explode("|", $arr_pajak[$arr_keys[$i]]["'kdakun'"]);
								$kdakun = $arr_akun[0];
								$kddk = $arr_akun[1];
								
								$arr_insert[] = "select	".$id_trans." as id_trans,
														'".$kdakun."' as kdakun,
														'".$kddk."' as kddk,
														".str_replace(',', '', $arr_pajak[$arr_keys[$i]]["'nilai'"])." as nilai,
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
					DB::commit();
					return 'success';
				}
				else{
					return 'Simpan pajak gagal!';
				}
				
			}
			else{
				return 'Data gagal diupdate!';
			}
			
		}
		else{
			return 'Hitung dulu total transaksi ini!';
		}
	}
	
}