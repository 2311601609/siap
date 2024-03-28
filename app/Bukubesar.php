<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bukubesar extends Model
{
    public static function getSaldoAwal($tahun, $kdakun)
	{
		$rows = \DB::select("
			select  a.nilai
			from d_sawal a
			where a.thang=? and a.kdakun='111110'
		", [$periode, $tahun, $periode, $kdakun]);

		return $rows;
	}
	
	//
    /**
	 * description 
	 */
	public static function getAllData($tahun, $periode, $kdakun)
	{
		$rows = \DB::select("
			  SELECT a.kdakun,
					 d.nmakun,
					 to_char(last_day(b.tgdok),'dd')||' '||e.nmbulan as bulan,
					 TO_CHAR (b.tgdok, 'dd-mm-yyyy') AS tanggal,
					 b.nodok||' | '||c.nmunit AS no_voucher,
					 substr(b.kdunit,1,4) as kd_pc,
					 f.nama||' | '||b.uraian AS remark,
					 DECODE (a.kddk, 'D', a.nilai, 0) AS debet,
					 DECODE (a.kddk, 'K', a.nilai, 0) AS kredit,
					 SUM (decode(a.kddk,'D',a.nilai,'K',-a.nilai)) OVER (ORDER BY b.tgdok,a.id) AS saldo
				FROM d_trans_akun a
					 LEFT JOIN d_trans b
						ON (a.id_trans = b.id)
					 LEFT JOIN t_unit c
						ON (SUBSTR (b.kdunit, 1, 4) = c.kdunit)
					 LEFT JOIN t_akun d
						ON (a.kdakun = d.kdakun)
					 LEFT JOIN t_penerima f
						ON (b.id_penerima=f.id),
					 (
						select	nmbulan
						from t_bulan
						where bulan=?
					 ) e
			   WHERE     b.thang = ?
					 AND TO_CHAR (b.tgdok, 'mm') <= ?
					 AND a.kdakun = ?
			ORDER BY b.tgdok,a.id
		", [$periode, $tahun, $periode, $kdakun]);

		return $rows;
	}
	
    /**
	 * description 
	 */
	public static function getSumData($tahun, $periode, $kdakun)
	{
		$rows = \DB::select("
			  SELECT kdakun,
					 SUM (debet) AS debet,
					 SUM (kredit) AS kredit,
					 SUM (saldo) AS saldo
				FROM (SELECT a.kdakun,
							 d.nmakun,
							 TO_CHAR (b.tgdok, 'dd-mm-yyyy') AS tanggal,
							 b.nodok || ' ' || c.nmunit AS no_voucher,
							 'Z' AS kd_pc,
							 b.uraian AS remark,
							 DECODE (a.kddk, 'D', a.nilai, 0) AS debet,
							 DECODE (a.kddk, 'K', a.nilai, 0) AS kredit,
							 SUM (a.nilai) OVER (ORDER BY b.tgdok) AS saldo
						FROM d_trans_akun a
							 LEFT JOIN d_trans b
								ON (a.id_trans = b.id)
							 LEFT JOIN t_unit c
								ON (SUBSTR (b.kdunit, 1, 4) = c.kdunit)
							 LEFT JOIN t_akun d
								ON (a.kdakun = d.kdakun)
					   WHERE     b.thang = ?
							 AND TO_CHAR (b.tgdok, 'mm') <= ?
							 AND a.kdakun = ?)
			GROUP BY kdakun
		", [$tahun, $periode, $kdakun]);

		return $rows[0];
	}
	
}
