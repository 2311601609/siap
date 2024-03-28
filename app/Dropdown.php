<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Dropdown extends Model
{
    //
	public static function getTahun()
	{
		return DB::select("
			  SELECT tahun, aktif
			    FROM T_TAHUN
			ORDER BY tahun, aktif
		");
	}

}
