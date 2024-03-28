<?php namespace App\Libraries;

use Session;
use DB;

/*
 * Copyright(c)2011 Miguel Angel Nubla Ruiz (miguelangel.nubla@gmail.com). All rights reserved
 */

class PublicFunction
{
    
    public function __construct()
    {  
    }

    public static function errorLog($request, $error)
    {
        $username = session('username');
        $url = $request->fullUrl();
        $body = $request->getContent();
        $ip = $request->ip();

        $insert = DB::table('temp_log_error')->insert(
            array(
                'username' => $username,
                'url' => $url,
                'body' => $body,
                'error' => $error,
                'ip_address' => $ip
            )
        );

        if($insert){
            return true;
        }

    }
	
}