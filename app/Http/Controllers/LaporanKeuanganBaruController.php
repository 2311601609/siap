<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Libraries\PublicFunction;
use App\Exports\LaporanLabaRugiExport;
use App\Exports\LaporanPosisiKeuanganExport;
use Maatwebsite\Excel\Facades\Excel;

class LaporanKeuanganBaruController extends Controller {
	
	private function queryLabaRugi($periode)
	{
		$rows = DB::select("
			select	to_char(last_day(to_date('".session('tahun')."-".$periode."-01','yyyy-mm-dd')),'dd') as tgl_pelaporan,
					upper(nmbulan) as nmbulan
			from t_bulan
			where bulan='".$periode."'
		");

		$bulan = "N/A";
		$tgl_pelaporan = "N/A";
		if(count($rows)>0){
			$bulan = $rows[0]->nmbulan;
			$tgl_pelaporan = $rows[0]->tgl_pelaporan.' '.$bulan.' '.session('tahun');
		}

		$param = array(
			'tgl_pelaporan' => $tgl_pelaporan,
			'thang' => session('tahun')
		);

		$rows1 = DB::select("
			select  a.*,
					nvl(b.nilai,0) as nilai,
					nvl(c.nilai,0) as nilai_sd
			from(
				
				select  a.kdakun,
						a.nmakun
				from t_akun a
				where a.kdlap='LR' and a.kdlapdtl='01'
				
			) a
			left join(
				
				select  substr(a.kdakun,1,2)||'0000' as kdakun,
						sum(a.kredit)-sum(a.debet) as nilai
				from d_buku_besar a
				where a.thang='".session('tahun')."' and a.periode='".$periode."'
				group by substr(a.kdakun,1,2)||'0000'

			) b on(a.kdakun=b.kdakun)
			left join(
				
				select  substr(a.kdakun,1,2)||'0000' as kdakun,
						sum(a.kredit)-sum(a.debet) as nilai
				from d_buku_besar a
				where a.thang='".session('tahun')."' and a.periode<='".$periode."'
				group by substr(a.kdakun,1,2)||'0000'

			) c on(a.kdakun=c.kdakun)
			order by a.kdakun
		");

		$rows2 = DB::select("
			select  a.*,
					nvl(b.nilai,0) as nilai,
					nvl(c.nilai,0) as nilai_sd
			from(
				
				select  a.kdakun,
						a.nmakun
				from t_akun a
				where a.kdlap='LR' and a.kdlapdtl='02'
				
			) a
			left join(
				
				select  substr(a.kdakun,1,4)||'00' as kdakun,
						sum(a.debet)-sum(a.kredit) as nilai
				from d_buku_besar a
				where a.thang='".session('tahun')."' and a.periode='".$periode."'
				group by substr(a.kdakun,1,4)||'00'

			) b on(a.kdakun=b.kdakun)
			left join(
				
				select  substr(a.kdakun,1,4)||'00' as kdakun,
						sum(a.debet)-sum(a.kredit) as nilai
				from d_buku_besar a
				where a.thang='".session('tahun')."' and a.periode<='".$periode."'
				group by substr(a.kdakun,1,4)||'00'

			) c on(a.kdakun=c.kdakun)
			order by a.kdakun
		");

		$rows3 = DB::select("
			select  a.*,
					nvl(b.nilai,0) as nilai,
					nvl(c.nilai,0) as nilai_sd
			from(
				
				select  a.kdakun,
						a.nmakun
				from t_akun a
				where a.kdlap='LR' and a.kdlapdtl='03'
				
			) a
			left join(
				
				select  substr(a.kdakun,1,4)||'00' as kdakun,
						sum(a.kredit)-sum(a.debet) as nilai
				from d_buku_besar a
				where a.thang='".session('tahun')."' and a.periode='".$periode."'
				group by substr(a.kdakun,1,4)||'00'

			) b on(a.kdakun=b.kdakun)
			left join(
				
				select  substr(a.kdakun,1,4)||'00' as kdakun,
						sum(a.kredit)-sum(a.debet) as nilai
				from d_buku_besar a
				where a.thang='".session('tahun')."' and a.periode<='".$periode."'
				group by substr(a.kdakun,1,4)||'00'

			) c on(a.kdakun=c.kdakun)
			order by a.kdakun
		");

		$rows4 = DB::select("
			select  a.*,
					nvl(b.nilai,0) as nilai,
					nvl(c.nilai,0) as nilai_sd
			from(
				
				select  a.kdakun,
						a.nmakun
				from t_akun a
				where a.kdlap='LR' and a.kdlapdtl='04'
				
			) a
			left join(
				
				select  substr(a.kdakun,1,4)||'00' as kdakun,
						sum(a.debet)-sum(a.kredit) as nilai
				from d_buku_besar a
				where a.thang='".session('tahun')."' and a.periode='".$periode."'
				group by substr(a.kdakun,1,4)||'00'

			) b on(a.kdakun=b.kdakun)
			left join(
				
				select  substr(a.kdakun,1,4)||'00' as kdakun,
						sum(a.debet)-sum(a.kredit) as nilai
				from d_buku_besar a
				where a.thang='".session('tahun')."' and a.periode<='".$periode."'
				group by substr(a.kdakun,1,4)||'00'

			) c on(a.kdakun=c.kdakun)
			order by a.kdakun
		");

		$rows5 = DB::select("
			select  a.*,
					nvl(b.nilai,0) as nilai,
					nvl(c.nilai,0) as nilai_sd
			from(
				
				select  a.kdakun,
						a.nmakun
				from t_akun a
				where a.kdlap='LR' and a.kdlapdtl='05'
				
			) a
			left join(
				
				select  substr(a.kdakun,1,4)||'00' as kdakun,
						sum(a.debet)-sum(a.kredit) as nilai
				from d_buku_besar a
				where a.thang='".session('tahun')."' and a.periode='".$periode."'
				group by substr(a.kdakun,1,4)||'00'

			) b on(a.kdakun=b.kdakun)
			left join(
				
				select  substr(a.kdakun,1,4)||'00' as kdakun,
						sum(a.debet)-sum(a.kredit) as nilai
				from d_buku_besar a
				where a.thang='".session('tahun')."' and a.periode<='".$periode."'
				group by substr(a.kdakun,1,4)||'00'

			) c on(a.kdakun=c.kdakun)
			order by a.kdakun
		");

		$output = [
			'params' => $param,
			'rows1' => $rows1,
			'rows2' => $rows2,
			'rows3' => $rows3,
			'rows4' => $rows4,
			'rows5' => $rows5,
		];

		return $output;

	}

	private function queryPosisiKeuangan($periode)
	{
		$rows = DB::select("
			select	to_char(last_day(to_date('".session('tahun')."-".$periode."-01','yyyy-mm-dd')),'dd') as tgl_pelaporan,
					upper(nmbulan) as nmbulan
			from t_bulan
			where bulan='".$periode."'
		");

		$bulan = "N/A";
		$tgl_pelaporan = "N/A";
		if(count($rows)>0){
			$bulan = $rows[0]->nmbulan;
			$tgl_pelaporan = $rows[0]->tgl_pelaporan.' '.$bulan.' '.session('tahun');
		}

		$param = array(
			'tgl_pelaporan' => $tgl_pelaporan,
			'thang' => session('tahun')
		);

		$rows1 = DB::select("
			select  a.*,
					nvl(b.nilai,0) as nilai,
					nvl(c.nilai,0) as nilai_sd
			from(
				
				select  a.kdakun,
						a.nmakun
				from t_akun a
				where a.kdlap='NR' and a.kdlapdtl='01'
				
			) a
			left join(
				
				select  substr(a.kdakun,1,4)||'00' as kdakun,
						sum(a.debet)-sum(a.kredit) as nilai
				from d_buku_besar a
				where a.thang='".session('tahun')."' and a.periode='".$periode."'
				group by substr(a.kdakun,1,4)||'00'

			) b on(a.kdakun=b.kdakun)
			left join(
				
				select  substr(a.kdakun,1,2)||'0000' as kdakun,
						sum(a.debet)-sum(a.kredit) as nilai
				from d_buku_besar a
				where a.thang='".session('tahun')."' and a.periode<='".$periode."'
				group by substr(a.kdakun,1,2)||'0000'

			) c on(a.kdakun=c.kdakun)
			order by a.kdakun
		");

		$rows2 = DB::select("
			select  a.*,
					nvl(b.nilai,0) as nilai,
					nvl(c.nilai,0) as nilai_sd
			from(
				
				select  a.kdakun,
						a.nmakun
				from t_akun a
				where a.kdlap='NR' and a.kdlapdtl='02'
				
			) a
			left join(
				
				select  substr(a.kdakun,1,4)||'00' as kdakun,
						sum(a.debet)-sum(a.kredit) as nilai
				from d_buku_besar a
				where a.thang='".session('tahun')."' and a.periode='".$periode."'
				group by substr(a.kdakun,1,4)||'00'

			) b on(a.kdakun=b.kdakun)
			left join(
				
				select  substr(a.kdakun,1,4)||'00' as kdakun,
						sum(a.debet)-sum(a.kredit) as nilai
				from d_buku_besar a
				where a.thang='".session('tahun')."' and a.periode<='".$periode."'
				group by substr(a.kdakun,1,4)||'00'

			) c on(a.kdakun=c.kdakun)
			order by a.kdakun
		");

		$rows3 = DB::select("
			select  a.*,
					nvl(b.nilai,0) as nilai,
					nvl(c.nilai,0) as nilai_sd
			from(
				
				select  a.kdakun,
						a.nmakun
				from t_akun a
				where a.kdlap='NR' and a.kdlapdtl='03'
				
			) a
			left join(
				
				select  substr(a.kdakun,1,4)||'00' as kdakun,
						sum(a.kredit)-sum(a.debet) as nilai
				from d_buku_besar a
				where a.thang='".session('tahun')."' and a.periode='".$periode."'
				group by substr(a.kdakun,1,4)||'00'

			) b on(a.kdakun=b.kdakun)
			left join(
				
				select  substr(a.kdakun,1,4)||'00' as kdakun,
						sum(a.kredit)-sum(a.debet) as nilai
				from d_buku_besar a
				where a.thang='".session('tahun')."' and a.periode<='".$periode."'
				group by substr(a.kdakun,1,4)||'00'

			) c on(a.kdakun=c.kdakun)
			order by a.kdakun
		");

		$rows4 = DB::select("
			select  a.*,
					nvl(b.nilai,0) as nilai,
					nvl(c.nilai,0) as nilai_sd
			from(
				
				select  a.kdakun,
						a.nmakun
				from t_akun a
				where a.kdlap='NR' and a.kdlapdtl='04'
				
			) a
			left join(
				
				select  substr(a.kdakun,1,4)||'00' as kdakun,
						sum(a.kredit)-sum(a.debet) as nilai
				from d_buku_besar a
				where a.thang='".session('tahun')."' and a.periode='".$periode."'
				group by substr(a.kdakun,1,4)||'00'

			) b on(a.kdakun=b.kdakun)
			left join(
				
				select  substr(a.kdakun,1,4)||'00' as kdakun,
						sum(a.kredit)-sum(a.debet) as nilai
				from d_buku_besar a
				where a.thang='".session('tahun')."' and a.periode<='".$periode."'
				group by substr(a.kdakun,1,4)||'00'

			) c on(a.kdakun=c.kdakun)
			order by a.kdakun
		");

		$rows5 = DB::select("
			select  a.*,
					nvl(b.nilai,0) as nilai,
					nvl(c.nilai,0) as nilai_sd
			from(
				
				select  a.kdakun,
						a.nmakun
				from t_akun a
				where a.kdlap='NR' and a.kdlapdtl='05'
				
			) a
			left join(
				
				select  substr(a.kdakun,1,4)||'00' as kdakun,
						sum(a.kredit)-sum(a.debet) as nilai
				from d_buku_besar a
				where a.thang='".session('tahun')."' and a.periode='".$periode."'
				group by substr(a.kdakun,1,4)||'00'

			) b on(a.kdakun=b.kdakun)
			left join(
				
				select  substr(a.kdakun,1,4)||'00' as kdakun,
						sum(a.kredit)-sum(a.debet) as nilai
				from d_buku_besar a
				where a.thang='".session('tahun')."' and a.periode<='".$periode."'
				group by substr(a.kdakun,1,4)||'00'

			) c on(a.kdakun=c.kdakun)
			order by a.kdakun
		");

		$output = [
			'params' => $param,
			'rows1' => $rows1,
			'rows2' => $rows2,
			'rows3' => $rows3,
			'rows4' => $rows4,
			'rows5' => $rows5,
		];

		return $output;

	}

	public function labaRugi()
	{
		if (request()->has('periode')) {

			$periode = $_GET['periode'];

			if($periode!==''){

				$output = $this->queryLabaRugi($periode);

				return view('excel.laporan-laba-rugi', $output);

			}

		}
	}

	public function labaRugiExcel()
	{
		if (request()->has('periode')) {

			$periode = $_GET['periode'];

			if($periode!==''){

				$output = $this->queryLabaRugi($periode);

				return Excel::download(new LaporanLabaRugiExport($output), 'laporan-laba-rugi-'.session('tahun').'-'.$periode.'.xlsx');

			}

		}
	}

	public function posisiKeuangan()
	{
		if (request()->has('periode')) {

			$periode = $_GET['periode'];

			if($periode!==''){

				$output = $this->queryPosisiKeuangan($periode);

				return view('excel.laporan-posisi-keuangan', $output);

			}

		}
	}

	public function posisiKeuanganExcel()
	{
		if (request()->has('periode')) {

			$periode = $_GET['periode'];

			if($periode!==''){

				$output = $this->queryPosisiKeuangan($periode);

				return Excel::download(new LaporanPosisiKeuanganExport($output), 'laporan-posisi-keuangan-'.session('tahun').'-'.$periode.'.xlsx');

			}

		}
	}
	
}