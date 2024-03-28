<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LaporanController extends Controller {
	
	public function keuangan($param)
	{
		$where = "";
		if($param=='01'){
			$where = " and a.kdlap='NR'";
		}
		elseif($param=='02'){
			$where = " and a.kdlap='LR'";
		}
		
		//akun 1 digit
		$rows = DB::select("
			select  a.kdakun,
					a.kddk,
					a.kdlap,
					a.nmakun,
					decode(a.kddk,'D',nvl(b.debet,0)-nvl(b.kredit,0),nvl(b.kredit,0)-nvl(b.debet,0)) as nilai
			from(
				select  a.*
				from t_akun a
				where substr(a.kdakun,1,1)<>'0' and substr(a.kdakun,2,1)='0'
			) a
			left join(
				select  substr(a.kdakun,1,1) as kdakun,
						sum(a.debet) as debet,
						sum(a.kredit) as kredit
				from d_buku_besar a
				group by substr(a.kdakun,1,1)
			) b on(substr(a.kdakun,1,1)=b.kdakun)
			order by a.kdakun
		");
		
		$html = '<table>';
		foreach($rows as $row){
			$html .= '<tr>
						<td>'.$row->nmakun.'</td>
						<td>'.$row->nilai.'</td>
					</tr>';
		}
		$html .= '</table>';
		
		return $html;
		
	}
	
}