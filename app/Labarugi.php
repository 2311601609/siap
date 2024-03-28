<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Labarugi extends Model
{
    //

    /**
	 * description 
	 */
	public static function emptyRows()
	{
		return DB::select("
			SELECT '' AS kdakun, '' AS nmakun, 0 AS nilai FROM DUAL
		");
	}
	
	/**
	 * description 
	 */
	public static function getPendapatan($tahun, $periode)
	{
		$query = DB::select("
			select	a.*,
					b.*
			from(
				select  nvl(sum(a.kredit-a.debet),0) as nilai
				from d_buku_besar a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.thang=? and a.periode<=? and b.kdlap='LR' and substr(a.kdakun,1,1)='4'
			) a,
			(
				select  nvl(sum(a.kredit-a.debet),0) as nilai1
				from d_buku_besar a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.thang=? and a.periode=? and b.kdlap='LR' and substr(a.kdakun,1,1)='4'
			) b
		", [$tahun,$periode,$tahun,$periode]);

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 

		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getBebanPokokPendapatan($tahun, $periode)
	{
		$query = DB::select("
			select	a.*,
					b.*
			from(
				select  nvl(sum(a.debet-a.kredit),0) as nilai
				from d_buku_besar a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.thang=? and a.periode<=? and b.kdlap='LR' and substr(a.kdakun,1,2)='51'
			) a,
			(
				select  nvl(sum(a.debet-a.kredit),0) as nilai1
				from d_buku_besar a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.thang=? and a.periode=? and b.kdlap='LR' and substr(a.kdakun,1,2)='51'
			) b
		", [$tahun,$periode,$tahun,$periode]);

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 

		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getBebanUsaha($tahun, $periode)
	{
		$query = DB::select("
			select	a.*,
					b.*
			from(
				select  nvl(sum(a.debet-a.kredit),0) as nilai
				from d_buku_besar a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.thang=? and a.periode<=? and b.kdlap='LR' and substr(a.kdakun,1,2)='52'
			) a,
			(
				select  nvl(sum(a.debet-a.kredit),0) as nilai1
				from d_buku_besar a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.thang=? and a.periode=? and b.kdlap='LR' and substr(a.kdakun,1,2)='52'
			) b
		", [$tahun,$periode,$tahun,$periode]);
		
		if(count($query) == 0) {
			$query = self::emptyRows();
		} 

		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getBebanPemasaran($tahun, $periode)
	{
		$query = DB::select("
			select	a.*,
					b.*
			from(
				select  nvl(sum(a.debet-a.kredit),0) as nilai
				from d_buku_besar a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.thang=? and a.periode<=? and b.kdlap='LR' and substr(a.kdakun,1,3)='521'
			) a,
			(
				select  nvl(sum(a.debet-a.kredit),0) as nilai1
				from d_buku_besar a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.thang=? and a.periode=? and b.kdlap='LR' and substr(a.kdakun,1,3)='521'
			) b
		", [$tahun,$periode,$tahun,$periode]);

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 

		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getBebanAdministrasiUmum($tahun, $periode)
	{
		$query = DB::select("
			select	a.*,
					b.*
			from(
				select  nvl(sum(a.debet-a.kredit),0) as nilai
				from d_buku_besar a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.thang=? and a.periode<=? and b.kdlap='LR' and substr(a.kdakun,1,3)='522'
			) a,
			(
				select  nvl(sum(a.debet-a.kredit),0) as nilai1
				from d_buku_besar a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.thang=? and a.periode=? and b.kdlap='LR' and substr(a.kdakun,1,3)='522'
			) b
		", [$tahun,$periode,$tahun,$periode]);

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 

		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getLabaKotor($tahun, $periode)
	{
		$labaKotor = (int) self::getPendapatan($tahun, $periode)->nilai - self::getBebanPokokPendapatan($tahun, $periode)->nilai;

		$query = DB::select("
			SELECT ".htmlentities($labaKotor)." AS nilai
			FROM DUAL
		");

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 

		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getLabaUsaha($tahun, $periode)
	{
		$labaUsaha = (int) self::getLabaKotor($tahun, $periode)->nilai - (int) self::getBebanUsaha($tahun, $periode)->nilai;

		$query = DB::select("
			SELECT ".htmlentities($labaUsaha)." AS nilai
			FROM DUAL
		");

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 
		
		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getPendapatanLainLain($tahun, $periode)
	{
		$query = DB::select("
			select	a.*,
					b.*
			from(
				select  nvl(sum(a.kredit-a.debet),0) as nilai
				from d_buku_besar a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.thang=? and a.periode<=? and b.kdlap='LR' and substr(a.kdakun,1,2)='61'
			) a,
			(
				select  nvl(sum(a.kredit-a.debet),0) as nilai1
				from d_buku_besar a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.thang=? and a.periode=? and b.kdlap='LR' and substr(a.kdakun,1,2)='61'
			) b
		", [$tahun,$periode,$tahun,$periode]);

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 

		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getBebanLainLain($tahun, $periode)
	{
		$query = DB::select("
			select	a.*,
					b.*
			from(
				select  nvl(sum(a.debet-a.kredit),0) as nilai
				from d_buku_besar a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.thang=? and a.periode<=? and b.kdlap='LR' and substr(a.kdakun,1,2)='62'
			) a,
			(
				select  nvl(sum(a.debet-a.kredit),0) as nilai1
				from d_buku_besar a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.thang=? and a.periode=? and b.kdlap='LR' and substr(a.kdakun,1,2)='62'
			) b
		", [$tahun,$periode,$tahun,$periode]);

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 

		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getPendapatanBebanLainLain($tahun, $periode)
	{
		$pendapatanBebanLainLain = (int) self::getPendapatanLainLain($tahun, $periode)->nilai - (int) self::getBebanLainLain($tahun, $periode)->nilai;
	
		$query = DB::select("
			SELECT ".htmlentities($pendapatanBebanLainLain)." AS nilai
			FROM DUAL
		");

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 
		
		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getManfaatBebanPajakPenghasilan($tahun, $periode)
	{
		$manfaatBebanPajakPenghasilan = 0;
	
		$query = DB::select("
			SELECT ".htmlentities($manfaatBebanPajakPenghasilan)." AS nilai
			FROM DUAL
		");

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 
		
		return $query[0];
	}

	/**
	 * description 
	 */
	
	public static function getPajakKini($tahun, $periode)
	{
		$pajakKini = 0;
	
		$query = DB::select("
			SELECT ".htmlentities($pajakKini)." AS nilai
			FROM DUAL
		");

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 
		
		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getPajakBadan($tahun, $periode)
	{
		$pajakBadan = 0;
	
		$query = DB::select("
			SELECT ".htmlentities($pajakBadan)." AS nilai
			FROM DUAL
		");

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 
		
		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getLabaAnakPerusahaan($tahun, $periode)
	{
		$labaAnakPerusahaan = (int) self::getPajakKini($tahun, $periode)->nilai + (int) self::getPajakBadan($tahun, $periode)->nilai ;
	
		$query = DB::select("
			SELECT ".htmlentities($labaAnakPerusahaan)." AS nilai
			FROM DUAL
		");

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 
		
		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getLabaBersih($tahun, $periode)
	{
		$labaBersih = 0;
	
		$query = DB::select("
			SELECT ".htmlentities($labaBersih)." AS nilai
			FROM DUAL
		");

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 
		
		return $query[0];
	}

}
