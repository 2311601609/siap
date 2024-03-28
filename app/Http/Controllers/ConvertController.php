<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ConvertController extends Controller
{
    //
    // FUNGSI TERBILANG OLEH : MALASNGODING.COM
	// WEBSITE : WWW.MALASNGODING.COM
	// AUTHOR : https://www.malasngoding.com/author/admin

    public static function terbilang($x){
        $_this = new self;
        $x = abs($x);
        $angka = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        $temp = "";
        if ($x <12) {
            $temp = " ". $angka[$x];
        } else if ($x <20) {
            $temp = $_this->terbilang($x - 10). " belas";
        } else if ($x <100) {
            $temp = $_this->terbilang($x/10)." puluh". $_this->terbilang($x % 10);
        } else if ($x <200) {
            $temp = " seratus" . $_this->terbilang($x - 100);
        } else if ($x <1000) {
            $temp = $_this->terbilang($x/100) . " ratus" . $_this->terbilang($x % 100);
        } else if ($x <2000) {
            $temp = " seribu" . $_this->terbilang($x - 1000);
        } else if ($x <1000000) {
            $temp = $_this->terbilang($x/1000) . " ribu" . $_this->terbilang($x % 1000);
        } else if ($x <1000000000) {
            $temp = $_this->terbilang($x/1000000) . " juta" . $_this->terbilang($x % 1000000);
        } else if ($x <1000000000000) {
            $temp = $_this->terbilang($x/1000000000) . " milyar" . $_this->terbilang(fmod($x,1000000000));
        } else if ($x <1000000000000000) {
            $temp = $_this->terbilang($x/1000000000000) . " trilyun" . $_this->terbilang(fmod($x,1000000000000));
        }     
        return $temp;
    }
}
