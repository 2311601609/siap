<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests;
use DB;
use Session;
use DataTables;
use App\Libraries\PublicFunction;

class RKAPPerubahanController extends Controller
{
	protected $table;

	/**
	 * description 
	 */
	public function __construct()
	{
		$this->table = 'd_rkap_akun';
	}

	/**
	 * description 
	 */
	public function index()
	{
		if(session('kdlevel')=='11'){
			return 1;
		}
	}

	/**
	 * description 
	 */
	public function data()
	{
		$id_rkap = 0;
		if(isset($_GET['id_rkap'])){
			if($_GET['id_rkap']!==null && $_GET['id_rkap']!==''){
				$id_rkap = $_GET['id_rkap'];
			}
		}

		$sql = "
			select  a.kdsdana||' - '||b.nmsdana as nmsdana,
					a.kdakun||' - '||c.nmakun as nmakun,
					a.id_proyek||' - '||d.nmproyek as nmproyek,
					a.id_program||' - '||e.uraian as nmprogram,
					a.id_giat||' - '||f.uraian as nmgiat,
					a.nilai1,
					a.nilai2,
					a.status
			from(

				select  nvl(a.kdsdana,b.kdsdana) as kdsdana,
						nvl(a.kdakun,b.kdakun) as kdakun,
						nvl(a.id_proyek,b.id_proyek) as id_proyek,
						nvl(a.id_program,b.id_program) as id_program,
						nvl(a.id_giat,b.id_giat) as id_giat,
						nvl(a.nilai,0) as nilai1,
						nvl(b.nilai,0) as nilai2,
						case
							when a.nilai>0 and b.nilai>0 and a.nilai=b.nilai
								then 'Tidak ada perubahan'
							when a.nilai>0 and b.nilai>0 and a.nilai<>b.nilai
								then 'Ada perubahan nilai'
							else
								'Penambahan/ pengurangan data'
						end as status
				from(

					select  a.*,
							b.kdunit,
                            b.thang
					from d_rkap_akun a
					left join d_rkap b on(a.id_rkap=b.id)
					where a.id_rkap=".$id_rkap."
					
				) a

				full join

				(
					select  a.*,
							b.kdunit,
                            b.thang
					from d_rkap_akun a
					left join d_rkap b on(a.id_rkap=b.id)
					where a.id_rkap in(

						select  max(id) as id
						from d_rkap
						where id<>".$id_rkap." and status in(3,4)
						
					)
					
				) b on(a.kdunit=b.kdunit and a.thang=b.thang and a.kdsdana=b.kdsdana and a.kdakun=b.kdakun and a.id_proyek=b.id_proyek and a.id_program=b.id_program and a.id_giat=b.id_giat)
				
			) a
			left join t_sdana b on(a.kdsdana=b.kdsdana)
			left join t_akun c on(a.kdakun=c.kdakun)
			left join t_proyek d on(a.id_proyek=d.id)
			left join t_program e on(a.id_program=e.id)
			left join t_kegiatan f on(a.id_giat=f.id)
			order by a.kdsdana,a.id_program,a.id_giat,a.id_proyek,a.kdakun asc, a.nilai1,a.nilai2 desc
		";

		$query = DB::table(DB::raw("($sql) a"))
				->selectRaw('a.*');

		$datatables = DataTables::of($query)
					->addIndexColumn()
					->editColumn('nilai1', function($row){
						return number_format($row->nilai1, 0, ',', '.');
					})
					->editColumn('nilai2', function($row){
						return number_format($row->nilai2, 0, ',', '.');
					})
					->rawColumns(['nilai1','nilai2'])
					->make(true);

		return $datatables;
	}

}
