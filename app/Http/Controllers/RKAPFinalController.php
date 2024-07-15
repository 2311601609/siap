<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests;
use DB;
use Session;
use DataTables;
use App\Libraries\PublicFunction;

class RKAPFinalController extends Controller
{
	protected $table;

	/**
	 * description 
	 */
	public function __construct()
	{
		$this->table = 'd_pagu';
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
		$arr_where = array();
		if(isset($_GET['kdsdana'])){
			if($_GET['kdsdana']!==null && $_GET['kdsdana']!==''){
				$arr_where[] = " a.kdsdana='".$_GET['kdsdana']."'";
			}
		}

		if(isset($_GET['id_proyek'])){
			if($_GET['id_proyek']!==null && $_GET['id_proyek']!==''){
				$arr_where[] = " a.id_proyek='".$_GET['id_proyek']."'";
			}
		}

		if(isset($_GET['kdunit'])){
			if($_GET['kdunit']!==null && $_GET['kdunit']!==''){
				$arr_where[] = " a.kdunit='".$_GET['kdunit']."'";
			}
		}

		if(isset($_GET['kdakun'])){
			if($_GET['kdakun']!==null && $_GET['kdakun']!==''){
				$arr_where[] = " a.kdakun='".$_GET['kdakun']."'";
			}
		}

		$where = "";
		if(count($arr_where)>0){
			$where = "where ".implode(" and ", $arr_where);
		}

		$sql = "
			select  a.*,
					b.realisasi,
					a.pagu-nvl(b.realisasi,0) as sisa
			from(

				select  a.id,
						a.thang,
						a.kdunit,
						b.nmunit,
						a.id_proyek,
						c.nmproyek,
						a.kdsdana,
						d.nmsdana,
						a.id_program,
						e.uraian as nmprogram,
						a.id_giat,
						f.uraian as nmgiat,
						a.kdakun,
						g.nmakun,
						a.nilai as pagu
				from d_pagu a
				left join t_unit b on(a.kdunit=b.kdunit)
				left join t_proyek c on(a.id_proyek=c.id)
				left join t_sdana d on(a.kdsdana=d.kdsdana)
				left join t_program e on(a.id_program=e.id)
				left join t_kegiatan f on(a.id_giat=f.id)
				left join t_akun g on(a.kdakun=g.kdakun)
				where a.thang='".session('tahun')."' and a.kdunit like '".session('kdunit')."%'
				
			) a
			left join(
				
				select  b.kdunit,
						b.thang,
						b.id_proyek,
						b.kdsdana,
						b.id_program,
						b.id_giat,
						a.kdakun,
						sum(a.nilai) as realisasi
				from d_trans_akun a
				left join d_trans b on(a.id_trans=b.id)
				where b.thang='".session('tahun')."' and b.kdunit like '".session('kdunit')."%'
				group by b.kdunit,
						b.thang,
						b.id_proyek,
						b.kdsdana,
						b.id_program,
						b.id_giat,
						a.kdakun

			) b on(a.kdunit=b.kdunit and a.thang=b.thang and a.id_proyek=b.id_proyek and a.kdsdana=b.kdsdana and a.id_program=b.id_program and a.id_giat=b.id_giat and a.kdakun=b.kdakun)
			".$where."
			order by a.kdunit
		";

		$query = DB::table(DB::raw("($sql) a"))
				->selectRaw('a.*');

		$datatables = DataTables::of($query)
					->addIndexColumn()
					->editColumn('pagu', function($row){
						return number_format($row->pagu, 0, ',', '.');
					})
					->editColumn('realisasi', function($row){
						return number_format($row->realisasi, 0, ',', '.');
					})
					->editColumn('sisa', function($row){
						return number_format($row->sisa, 0, ',', '.');
					})
					->rawColumns(['pagu','realisasi','sisa'])
					->make(true);

		return $datatables;
	}

}
