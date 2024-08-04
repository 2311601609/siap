<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests;
use DB;
use Session;
use DataTables;
use App\Libraries\PublicFunction;

class TagihanUploadDetilController extends Controller
{
	protected $table;

	/**
	 * description 
	 */
	public function __construct()
	{
		$this->table = 'd_upload_tagihana_dtl';
	}

	/**
	 * description 
	 */
	public function index()
	{
		if(session('kdlevel')=='12'){
			return 1;
		}
	}

	/**
	 * description 
	 */
	public function data()
	{
		$id_upload_tagihan = 0;
		if(isset($_GET['id_upload_tagihan'])){
			if($_GET['id_upload_tagihan']!==null && $_GET['id_upload_tagihan']!==''){
				$id_upload_tagihan = $_GET['id_upload_tagihan'];
			}
		}

		$sql = "
			select  a.*
			from d_upload_tagihan_dtl a
			where a.id_upload_tagihan=".$id_upload_tagihan."
		";

		$query = DB::table(DB::raw("($sql) a"))
				->selectRaw('a.*');

		$datatables = DataTables::of($query)
					->addIndexColumn()
					->make(true);

		return $datatables;
	}

}
