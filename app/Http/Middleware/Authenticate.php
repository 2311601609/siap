<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\Guard;
use Closure;
use DB;

class Authenticate
{
    protected $auth;

    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }
    
    public function handle($request, Closure $next)
    {
        $base_url = \config('app.url');

        try{

            if( isset($_COOKIE['siap_token']) ){

                if($_COOKIE['siap_token']!==null){

                    $JWT = new \App\Libraries\jwtphp\JWT;
                    $token = $_COOKIE['siap_token'];
                    $key = \config('jwt.key');

                    if($JWT->decode($token, $key, array('HS256'))){
                        
                        $json = $JWT->decode($token, $key);
                        $data = json_decode($json,true);
                        $current_time = time();
                        $token_time = (int)$data['exp'];
                        $token_issued = $data['issued'];

                        if($current_time<=$token_time){

                            if(!session('authenticated') || $token_issued!==session('token_issued')){

                                $rows = DB::select("
                                    select  a.id,
                                            a.pass,
                                            a.nama,
                                            a.nik,
                                            a.aktif,
                                            a.foto,
                                            b.kdlevel,
                                            c.nmlevel,
                                            d.kdunit,
                                            e.nmunit
                                    from t_user a
                                    left outer join t_user_level b on(a.id=b.id_user)
                                    left outer join t_level c on(b.kdlevel=c.kdlevel)
                                    left outer join t_user_unit d on(a.id=d.id_user)
                                    left outer join t_unit e on(d.kdunit=e.kdunit)
                                    where a.username=? and b.status='1' and d.status='1'
                                ",[
                                    $data['username']
                                ]);
                                
                                if(count($rows)>0){
                                                
                                    if($rows[0]->aktif=='1' || $rows[0]->aktif=='2'){

                                        session([
                                            'authenticated' => true,
                                            'id_user' => $rows[0]->id,
                                            'username' => $data['username'],
                                            'nama' => $rows[0]->nama,
                                            'nik' => $rows[0]->nik,
                                            'foto' => $rows[0]->foto,
                                            'kdlevel' => $rows[0]->kdlevel,
                                            'nmlevel' => $rows[0]->nmlevel,
                                            'kdunit' => $rows[0]->kdunit,
                                            'nmunit' => $rows[0]->nmunit,
                                            'status' => $rows[0]->aktif,
                                            'app_versi' => '',
                                            'app_nama' => '',
                                            'app_ket' => '',
                                            'tahun' => $data['tahun']
                                        ]);
                                        
                                        return $next($request);

                                    }
                                    else{
                                        if ($request->ajax()){
                                            return response()->json(['success' => false, 'code' => '06', 'message'=>'kesalahan lainnya'], 401);
                                        }
                                        else
                                        {
                                            return redirect()->guest($base_url.'auth/logout?error=06');
                                        }
                                    }

                                }
                                else{
                                    if ($request->ajax()){
                                        return response()->json(['success' => false, 'code' => '05', 'message'=>'kesalahan lainnya'], 401);
                                    }
                                    else
                                    {
                                        return redirect()->guest($base_url.'auth/logout?error=05');
                                    }
                                }

                            }
                            else{
                                return $next($request);
                            }
                            
                        }
                        else{
                            if ($request->ajax()){
                                return response()->json(['success' => false, 'code' => '04', 'message'=>'kesalahan lainnya'], 401);
                            }
                            else
                            {
                                return redirect()->guest($base_url.'auth/logout?error=04');
                            }
                        }

                    }
                    else{
                        if ($request->ajax()){
                            return response()->json(['success' => false, 'code' => '03', 'message'=>'kesalahan lainnya'], 401);
                        }
                        else
                        {
                            return redirect()->guest($base_url.'auth/logout?error=03');
                        }
                    }
                    
                }
                else{
                    if ($request->ajax()){
                        return response()->json(['success' => false, 'code' => '02', 'message'=>'kesalahan lainnya'], 401);
                    }
                    else
                    {
                        return redirect()->guest($base_url.'auth/logout?error=02');
                    }
                }
                
            }
            else{

                if ($request->ajax()){
                    return response()->json(['success' => false, 'code' => '01', 'message'=>'Token tidak valid'], 401);
                }
                else
                {
                    return redirect()->guest($base_url.'auth/logout?error=01');
                }
            }

        }
        catch(\Exception $e){

            if ($request->ajax()){
                return response()->json(['success' => false, 'code' => '09', 'message'=>'kesalahan lainnya'], 401);
            }
            else
            {
                return redirect()->guest($base_url.'auth/logout?error=09');
            }

        }
        
    }
}
