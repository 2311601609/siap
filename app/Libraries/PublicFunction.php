<?php namespace App\Libraries;

use Session;
use DB;
use Illuminate\Support\Facades\Mail;

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

    public static function kirimOTP($email)
    {
        $otp_status = \config('otp.status');
        $otp_digit = \config('otp.digit');
        $otp_lifetime = \config('otp.lifetime');
        $output = array(
            'success' => false,
            'message' => 'N/A',
            'otp' => '',
            'lifetime' => 0
        );

        if($otp_status){

            // Tentukan panjang digit yang diinginkan
            $length = $otp_digit; // Bisa diganti dengan nilai lain

            // Hitung batas minimum dan maksimum sesuai panjang digit
            $min = 10 ** ($length - 1);       // Contoh: untuk 5 digit -> 10000
            $max = (10 ** $length) - 1;        // Contoh: untuk 5 digit -> 99999

            // Hasilkan angka acak dalam rentang yang sesuai dan tambahkan padding jika perlu
            $otp = str_pad(random_int($min, $max), $length, '0', STR_PAD_LEFT);

            try {
                $lifetime = $otp_lifetime;
                $emailTo = $email;
                $emailFrom = \config('mail.from.address');
                $emailName = \config('mail.from.name');
                $emailSubject = 'Kode OTP Anda';
    
                Mail::send('mails.otp', ['otp' => $otp, 'lifetime' => $lifetime], function ($mail) use ($emailTo, $emailSubject, $emailFrom, $emailName) {
                    $mail->to($emailTo)
                         ->subject($emailSubject)
                         ->from($emailFrom, $emailName);
                });
        
                // Jika tidak ada exception, pengiriman berhasil
                $output = array(
                    'success' => true,
                    'message' => 'Proses pengiriman OTP berhasil',
                    'otp' => $otp,
                    'lifetime' => $lifetime
                );
        
            } catch (\Exception $e) {

                $output = array(
                    'success' => false,
                    'message' => 'Proses pengiriman OTP gagal : '.substr($e->getMessage(), 0, 100),
                    'otp' => '',
                    'lifetime' => 0
                );

            }

        }
        else{
            $output = array(
                'success' => true,
                'message' => 'Status OTP tidak aktif',
                'otp' => '',
                'lifetime' => 0
            );
        }

        return $output;

    }
	
}