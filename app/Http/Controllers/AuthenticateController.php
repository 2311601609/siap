<?php

namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Libraries\PublicFunction;

class AuthenticateController extends Controller {

	public function index(Request $request)
	{
		$app_name = \config('app.name');
		$app_version = \config('app.version');
		$app_desc = \config('app.desc');

		return view('login',
			array(
				'app_versi' => $app_version,
				'app_nama' => $app_name,
				'app_ket' => $app_desc,
				'tahun' => ''
			)
		);
	}
	
	public function login(Request $request)
	{
		try{
			$username = htmlspecialchars($request->input('username'));
			$password = htmlspecialchars($request->input('password'));
			$tahun = htmlspecialchars($request->input('tahun'));
			
			if(	$username!==null && $password!==null && $tahun!==null &&
				$username!=='' && $password!=='' && $tahun!==''){
				
				$rows = DB::select("
					select	id,
							username,
							pass,
							email,
							aktif
					from t_user
					where username=?
				",[
					$username
				]);
				
				if(count($rows)>0){
				
					if($rows[0]->pass==md5($password)){
					
						if($rows[0]->aktif=='1' || $rows[0]->aktif=='2'){

							if($rows[0]->email!==''){

								$email = $rows[0]->email;

								$lifetime = \config('jwt.lifetime'); //60 detik * 30 menit * 1 jam = setengah jam
								$issued = time();
								$exp = time()+$lifetime;
								
								$header = '{
											"typ":"JWT",
											"alg":"HS256"
										}';

								$payload = '{
											"iss":"'.\config('app.name').'",
											"exp":'.$exp.',
											"issued":'.$issued.',
											"username":"'.$username.'",
											"tahun":"'.$tahun.'"
											}';

								$key = \config('jwt.key');

								$JWT = new \App\Libraries\jwtphp\JWT;

								$token = $JWT->encode($header, $payload, $key);

								$kirimOTP = PublicFunction::kirimOTP($email);

								if($kirimOTP['success']){

									$otp = $kirimOTP['otp'];
									$lifetime = $kirimOTP['lifetime'];
									$exp = time() + $lifetime;

									session(array(
										'otp' => $otp,
										'otp_exp' => $exp
									));

									$panjang = strlen($email);

									$email = 'xxxxx'.substr($email,5,$panjang);

									return response()->json(['success' => true, 'message' => 'Proses login berhasil.', 'token' => $token, 'email' => $email]);

								}
								else{
									return response()->json(['success' => false, 'message' => $kirimOTP['message']]);	
								}

							}
							else{
								return response()->json(['success' => false, 'message' => 'Email user tidak valid.']);	
							}
							
						}
						else{
							return response()->json(['success' => false, 'message' => 'User tidak aktif.']);
						}
						
					}
					else{
						return response()->json(['success' => false, 'message' => 'Password salah.']);
					}
				
				}
				else{
					return response()->json(['success' => false, 'message' => 'Username tidak terdaftar.']);
				}
				
			}
			else{
				return response()->json(['success' => false, 'message' => 'Parameter tidak valid.']);
			}

		}
		catch(\Exception $e){
			if(PublicFunction::errorLog($request, substr($e->getMessage(),0,255))){
				return response()->json(['success' => false, 'message' => 'Kesalahan lainnya, hubungi Administrator.']);
			}
			else{
				return response()->json(['success' => false, 'message' => 'Kesalahan lainnya, hubungi Administrator. Log error gagal disimpan.']);
			}
		}
	}

	public function otp(Request $request)
	{
		try{
			$otp = htmlspecialchars($request->input('otp'));
			$token_user = htmlspecialchars($request->input('token_user'));
			
			if(	$otp!==null && $token_user!==null &&
				$otp!=='' && $token_user!==''){

				$otp_status = \config('otp.status');

				if($otp_status){

					if($otp==session('otp')){

						$current = time();
						
						if($current<=session('otp_exp')){
	
							setcookie('siap_token', $token_user, null, "/"); // 86400 = 1 day
	
							return response()->json(['success' => true, 'message' => 'Kode OTP valid, Anda akan diredirect ke halaman utama.']);	
	
						}
						else{
							return response()->json(['success' => false, 'message' => 'Kode OTP expired, refresh halaman browser Anda.']);	
						}
	
					}
					else{
						return response()->json(['success' => false, 'message' => 'Kode OTP tidak valid, cek kembali kode OTP yang terkirim ke email Anda.']);
					}

				}
				else{

					setcookie('siap_token', $token_user, null, "/"); // 86400 = 1 day
	
					return response()->json(['success' => true, 'message' => 'Kode OTP valid, Anda akan diredirect ke halaman utama.']);	

				}
				
			}
			else{
				return response()->json(['success' => false, 'message' => 'Parameter tidak valid.']);
			}

		}
		catch(\Exception $e){
			if(PublicFunction::errorLog($request, substr($e->getMessage(),0,255))){
				return response()->json(['success' => false, 'message' => 'Kesalahan lainnya, hubungi Administrator.']);
			}
			else{
				return response()->json(['success' => false, 'message' => 'Kesalahan lainnya, hubungi Administrator. Log error gagal disimpan.']);
			}
		}
	}
	
	public function create_token(Request $request)
	{
		try {
			$data=(array)json_decode($request->getContent());
		
			//cek apakah http post body kosong
			if(count($data)>0){
				
				$username= $data['username'];
				$password = $data['password'];
				$tahun = $data['tahun'];
				
				$rows = DB::select("
					select *
					from t_api_key
					where username=?
				",[$username]);
				
				if(isset($rows[0]) && $rows[0]->password){
				
					if($rows[0]->password==md5($password)){
					
						if($rows[0]->aktif=='1'){
							
							$id = $rows[0]->id;
							
							$lifetime=60*60*24; //60 detik * 60 menit * 24 jam = 1 hari
							$issued=time();
							$exp=time()+$lifetime;
							
							$header = '{
										"typ":"JWT",
										"alg":"HS256"
									   }';
							
							$arr_url=$request->fullUrl();
							$arr_url=explode("/", $arr_url);
							$ip_server=$arr_url[2];
							
							$payload = '{
										 "iss":"'.$id.'",
										 "exp":'.$exp.',
										 "issued":'.$issued.',
										 "user":"'.$rows[0]->username.'",
										 "server":"'.$ip_server.'",
										 "kdsatker":"'.$rows[0]->kdsatker.'",
										 "tahun":"'.$tahun.'"
										}';

							$key = 'cinta123!';

							$JWT = new \App\Libraries\jwtphp\JWT;

							$token = $JWT->encode($header, $payload, $key);
							return response()->json(['error' => false, 'message' => $token], 200);
							
						}
						else{
							return response()->json(['error' => true, 'message' => 'User tidak aktif!'], 401);
						}
						
					}
					else{
						return response()->json(['error' => true, 'message' => 'Password salah!'], 401);
					}
				
				}
				else{
					return response()->json(['error' => true, 'message' => 'Username tidak terdaftar!'], 401);
				}
				
			}
			else{
				return response()->json(['error' => true, 'message' => 'Body content kosong, silahkah lihat dokumentasi API!'], 400);
			}
		}
		catch(\Exception $e) {
			return response(json_encode(array('error' => true, 'message' => 'Kesalahan lainnya!')), 500);
		}
		
	}
	
	public function cek_level()
	{
		return session('kdlevel');
	}
	
	public function logout()
	{
		\Session::flush();

		\Cookie::queue(\Cookie::forget('siap_token'));

		$error = '';
		if(isset($_GET['error'])){
			if($_GET['error']!==null && $_GET['error']!==''){
				$error = '?error='.$_GET['error'];
			}
		}

		return redirect()->guest('/auth'.$error);
	}

	public function hapus_sesi_upload(Request $request)
	{
		session(['sesi_upload' => null]);

		return 'Sesi upload berhasil dihapus!';
	}
}