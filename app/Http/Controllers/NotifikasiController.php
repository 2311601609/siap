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

		$arr_query = [];
		if(session('kdlevel')=='11'){

			$arr_query[] = "
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
			";

		}

		$panjang = strlen(session('kdunit'));

		$arr_query[] = "
			select  a.id,
					'BUK' as jenis,
					lpad(a.nourut,5,'0') as nourut,
					a.uraian,
					a.nilai,
					a.tgjtempo as tgl_jatuh_tempo,
					0 as jml_hari
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
		";

		$arr_query[] = "
			select  a.id,
					'Kas Kecil' as jenis,
					lpad(a.nourut,5,'0') as nourut,
					a.uraian,
					a.nilai,
					a.tgjtempo as tgl_jatuh_tempo,
					0 as jml_hari
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
				where b.menu=6 and a.thang='".session('tahun')."' and c.kdlevel='".session('kdlevel')."'
			) a
			where a.akses=1 and nvl(a.is_final,'0')<>'1'
		";

		if(count($arr_query)>0){

			$rows = DB::select(implode(" union all ", $arr_query));

			if(count($rows)>0){

				$output['success'] = true;
				$output['message'] = 'Data ditemukan.';
				$output['data'] = $rows;

			}

		}

		return response()->json($output);

	}
	
}