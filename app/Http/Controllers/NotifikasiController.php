<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NotifikasiController extends Controller {

	public function index(Request $request)
	{
		$umk_jatuh_tempo = \config('app.umk_jatuh_tempo');
		$umk_reminder = \config('app.umk_reminder');

		$output['success'] = false;
		$output['message'] = 'Data tidak ditemukan.';
		$output['data'] = [];

		if(session('kdlevel')=='11'){
			
			$rows = DB::select("
				select  a.id,
						'UMK' as jenis,
						lpad(a.nourut,5,'0') as nourut,
						a.uraian,
						a.nilai_bersih as nilai,
						to_char(a.tgl_jatuh_tempo,'dd/mm/yyyy') as tgl_jatuh_tempo,
						a.jml_hari
				from(

					select  a.id,
							a.nourut,
							a.uraian,
							a.nilai_bersih,
							a.updated_at as tgl_bayar,
							a.updated_at+".$umk_jatuh_tempo." as tgl_jatuh_tempo,
							floor((a.updated_at+".$umk_jatuh_tempo.")-sysdate) as jml_hari
					from d_trans a
					left join t_alur_status b on(a.id_alur=b.id_alur and a.status=b.status)
					where a.thang='".session('tahun')."' and a.kdtran=18 and b.is_final='1'
					
				) a
				where a.jml_hari between 0 and ".$umk_reminder."
			");

			if(count($rows)>0){

				$output['success'] = true;
				$output['message'] = 'Data ditemukan.';
				$output['data'] = $rows;

			}
		
		}

		return response()->json($output);

	}
	
}