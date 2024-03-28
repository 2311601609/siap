<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PembukuanPostingController extends Controller {

	public function index(Request $request)
	{
		$aColumns = array('id','thang','nmbulan','jumlah','username','created_at');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  	a.id,
							a.thang,
							c.nmbulan,
							a.jumlah,
							b.username,
							to_char(a.created_at,'dd-mm-yyyy hh24:mi:ss') as created_at
					from d_posting a
					left outer join t_user b on(a.id_user=b.id)
					left outer join t_bulan c on(a.periode=c.bulan)
					order by a.id desc";
		
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
				$sWhere=" where lower(nmbulan) like lower('".$sSearch."%') or lower(nmbulan) like lower('%".$sSearch."%') or
								lower(thang) like lower('".$sSearch."%') or lower(thang) like lower('%".$sSearch."%') ";
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
			$aksi='';
			if(session('kdlevel')=='00' || session('kdlevel')=='05'){
				$aksi='<center>
							<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
							<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
								<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>
								<a id="'.$row->id.'" class="dropdown-item hapus" href="javascript:;">Hapus Data</a>
							</div>
						</center>';
			}
			
			$output['aaData'][] = array(
				$row->no,
				$row->thang,
				$row->nmbulan,
				'<div style="text-align:right;">'.number_format($row->jumlah).'</div>',
				$row->username,
				$row->created_at
			);
		}
		
		return response()->json($output);
	}
	
	public function simpan(Request $request)
	{
		$periode = $request->input('periode');
		
		if($periode!==''){
			
			$where = '';
			
			$query = "
				select  a.thang,
						a.periode,
						a.kdakun,
						sum(decode(a.kddk,'D',a.nilai,0)) as debet,
						sum(decode(a.kddk,'K',a.nilai,0)) as kredit,
						".session('id_user')." as id_user
				from(
					/* saldo awal */
					select  to_char(a.tgsawal,'YYYY') as thang,
							to_char(a.tgsawal,'MM') as periode,
							a.kddk,
							a.kdakun,
							sum(a.nilai) as nilai
					from d_sawal a
					where a.thang=?
					group by to_char(a.tgsawal,'YYYY'),
							to_char(a.tgsawal,'MM'),
							a.kdakun,
							a.kddk
					
					union all
					
					/* transaksi berjalan termasuk pajak dan penyesuaian */
					select  to_char(a.tgdok,'yyyy') as thang,
							to_char(a.tgdok,'mm') as periode,
							a.kddk,
							a.kdakun,
							sum(a.nilai) as nilai
					from(
						select  a.kdakun,
								a.kddk,
								a.nilai,
								case
									when c.menu=1 /* tagihan */
										then b.tgrekam
									when c.menu in(2,3) /* penerimaan dan umk */
										then b.tgcek
									when c.menu=4 and a.grup in('0','1','2') /* buk rekam */
										then b.tgrekam
									when c.menu=4 and a.grup='3' /* buk bayar */
										then b.tgcek
									else /* kas kecil dan penyesuaian */
										nvl(b.tgdok,b.tgrekam)
								end as tgdok
						from d_trans_akun a
						left join d_trans b on(a.id_trans=b.id)
						left join t_alur c on(b.id_alur=c.id)
						where b.thang=?
					) a
					group by to_char(a.tgdok,'yyyy'),
							 to_char(a.tgdok,'mm'),
							 a.kddk,
							 a.kdakun
					
				) a
				where a.periode<=?
				group by a.thang,a.periode,a.kdakun
			";
			
			DB::beginTransaction();
			
			$rows = DB::select("
				select	count(*) as jml
				from(".$query.") a
			",[
				session('tahun'),
				session('tahun'),
				$periode
			]);
			
			if($rows[0]->jml>0){
				
				$insert = DB::insert("
					insert into d_posting(thang,periode,jumlah,id_user)
					values(?,?,?,?)
				",[
					session('tahun'),
					$periode,
					$rows[0]->jml,
					session('id_user')
				]);
				
				if($insert){
					
					$delete = DB::delete("
						delete from d_buku_besar
						where thang='".session('tahun')."' and periode<='".$periode."'
					");
					
					$insert = DB::insert("
						insert into d_buku_besar(thang,periode,kdakun,debet,kredit,id_user)
						".$query."
					",[
						session('tahun'),
						session('tahun'),
						$periode
					]);
					
					if($insert){
						DB::commit();
						return 'success';
					}
					else{
						return 'Insert buku besar gagal disimpan!';
					}
					
				}
				else{
					return 'Data log posting gagal disimpan!';
				}
				
			}
			else{
				return 'Data transaksi tidak ditemukan!';
			}			
			
		}
		else{
			return 'Periode tidak dapat dikosongkan!';
		}
	}
	
	public function buku_besar()
	{
		if(isset($_GET['kdakun'])){
			
			if($_GET['kdakun']!==''){
				
				$kdakun = $_GET['kdakun'];
				
				$where = "where a.bulan<='12'";
				if(isset($_GET['periode'])){
					if($_GET['periode']!==''){
						$where = "where a.bulan<='".$_GET['periode']."'";
					}
				}
				
				$rows = DB::select("
					select  a.bulan,
							a.nmbulan,
							nvl(b.debet,0) as debet,
							nvl(b.kredit,0) as kredit,
							to_char(b.created_at,'dd-mm-yyyy hh24:mi:ss') as created_at
					from t_bulan a
					left outer join d_buku_besar b on(a.bulan=b.periode and b.kdakun=?)
					".$where."
					order by a.bulan asc
				",[
					$kdakun
				]);
				
				if(count($rows)>0){
					
					$data = '';
					$total = 0;
					$i = 1;
					foreach($rows as $row){
						
						$total += $row->debet;
						$total -= $row->kredit;
						
						$data .= '<tr>
									<td>'.$i++.'</td>
									<td>'.$row->nmbulan.'</td>
									<td style="text-align:right;">'.number_format($row->debet).'</td>
									<td style="text-align:right;">'.number_format($row->kredit).'</td>
									<td style="text-align:right;">'.number_format($total).'</td>
									<td>'.$row->created_at.'</td>
								  </tr>';
						
					}
					
					return $data;
					
				}
				else{
					return 'Data tidak ditemukan!';
				}
				
			}
			else{
				return 'Kode akun tidak dapat dikosongkan!';
			}
			
		}
		else{
			return 'Kode akun tidak dapat dikosongkan!';
		}
	}
	
}