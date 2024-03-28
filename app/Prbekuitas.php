<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Prbekuitas extends Model
{
    //
    /**
	 * description 
	 */
	public static function ekuitasAwal($kdakun, Array $arrParam)
	{
		if(isset($arrParam['tahun'])) {
			$thang = htmlentities($arrParam['tahun']);
		} 	
		
		$rows = DB::select("
			SELECT thang,
				   TO_CHAR (tgsawal, 'yyyy-mm-dd') tgsawal,
				   kdlap,
				   kdakun,
				   kddk,
				   nilai AS saldo
			  FROM d_sawal
			 WHERE kdakun IS NOT NULL AND thang = ? AND SUBSTR (kdakun, 1, 1) = 3 AND kddk = 'K'
		", [$thang]);

		return $rows[0];
	}

	/**
	 * description 
	 */
	public static function labaBersih($tahun)
	{
		
	}

	/**
	 * description 
	 */
	public static function kewajibanImbalan($tahun)
	{
		
	}

	/**
	 * description 
	 */
	public static function kewajibanPAD($tahun)
	{
		
	}

	/**
	 * description 
	 */
	public static function danaSosial($tahun)
	{
		
	}
	
	/**
	 * description 
	 */
	public static function saldoLaba($tahun)
	{
		
	}
}
