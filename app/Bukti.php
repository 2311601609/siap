<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Bukti extends Model
{
    //
    /**
	 * description 
	 */
	public static function queryUangMukaKerja($tahun, $id)
	{
		$rows = DB::select("
            SELECT a.id,
                   a.kdunit,
                   a.thang,
                   a.id_alur,
                   a.id_penerima,
                   a.nodok,
                   TO_CHAR (a.tgdok, 'dd-mm-yyyy') tgdok,
                   a.uraian,
                   a.notrans,
                   a.kdtran,
                   b.nmalur,
                   b.menu,
                   b.batas1,
                   b.batas2,
                   c.id AS id_trans_akun,
                   c.kdakun,
                   a.nilai,
                   c.kddk,
                   d.nmakun
              FROM d_trans a
                   LEFT JOIN t_alur b
                      ON (a.id_alur = b.id)
                   LEFT JOIN d_trans_akun c
                      ON (a.id = c.id_trans)
                   LEFT JOIN t_akun d
                      ON (c.kdakun = d.kdakun)
             WHERE a.id = ?
		",
		[$id]);

		return $rows[0];
	}

    /**
     * description 
     */
    public static function queryUangKeluar()
    {
        $rows = DB::select("
            
        ",
        []);
    }

    /**
     * description 
     */
    public static function queryUangMasuk()
    {
        $rows = DB::select("
            
        ",
        []);
    }

    /**
     * description 
     */
    public static function getSeniorManagerKeuangan()
    {
        $rows = DB::select("
            SELECT u.nik,
                   u.nama,
                   v.kdlevel,
                   l.nmlevel
              FROM t_user u
                   LEFT JOIN t_user_level v
                      ON (u.id = v.id_user)
                   LEFT JOIN t_level l
                      ON (v.kdlevel = l.kdlevel)
             WHERE u.aktif = 1 AND v.kdlevel = '04'
        ");
        
        if(count($rows) != 0){
            return $rows[0];
        } else {
            $rows = DB::select("
                SELECT '' AS nik, '' AS nama, '' AS kdlevel, '' AS nmlevel
                  FROM dual 
            ");

            return $rows[0];
        }
    }

    /**
     * description 
     */
    public static function getPenerimaUangMuka($idTrans )
    {
        $rows = DB::select("
            SELECT nama,
                   npwp,
                   kdbank,
                   norek
              FROM d_trans a
                   LEFT JOIN t_alur b
                      ON (a.id_alur = b.id)
                   LEFT JOIN t_penerima c
                      ON (a.id_penerima = c.id)
             WHERE b.menu = 3 AND a.id = ?
        ", [$idTrans]);

        if(count($rows) != 0) {
            return $rows[0];
        } else {
            $rows = DB::select("
                SELECT '' AS nama, '' AS npwp, '' AS kdbank, '' AS norek
                  FROM dual
            ");

            return $rows[0];
        }
    }

    /**
     * description 
     */
    public static function getDivisiUmumSDM()
    {
        $rows = DB::select("
            SELECT u.nik,
                   u.nama,
                   v.kdlevel,
                   l.nmlevel
              FROM t_user u
                   LEFT JOIN t_user_level v
                      ON (u.id = v.id_user)
                   LEFT JOIN t_level l
                      ON (v.kdlevel = l.kdlevel)
             WHERE u.aktif = 1 AND v.kdlevel = '04'
        ");

        if(count($rows) != 0){
            return $rows[0];
        } else {
            $rows = DB::select("
                SELECT '' AS nik, '' AS nama, '' AS kdlevel, '' AS nmlevel
                  FROM dual 
            ");

            return $rows[0];
        }
    }
}
