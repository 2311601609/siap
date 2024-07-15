<?php

namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mail;

class AppController extends Controller {

	public function index()
	{
			
		$html_out='';
		$angular = 'var app = angular.module("spa", ["ui.router","chieffancypants.loadingBar"]);
					app.config(function($stateProvider, $urlRouterProvider){
					$urlRouterProvider.otherwise("/");
					$stateProvider';
			
		// Create menu....
		$menus = DB::select("
			SELECT * FROM t_menu WHERE aktif='1' AND kdlevel LIKE '%+".session('kdlevel')."+%' AND parent_id=0  ORDER BY nourut
		");
		
		foreach($menus as $menu) {

			if($menu->is_parent=='0'){
				//jika tidak, tidak perlu buat sub menu
				
				//apakah buka tab baru?
				if($menu->new_tab=='1'){
					$html_out .= '<li class="nav-item">
										<a href="'.$menu->url.'" target="_blank">
											<i class="'.$menu->icon.'"></i>
											<span data-i18n="" class="menu-title">'.$menu->nmmenu.'</span>
										</a>
									</li>';
				}
				else{					
					if($menu->url==''){
						$html_out .= '<li class="nav-item">
										<a ui-sref="/">
											<i class="'.$menu->icon.'"></i>
											<span data-i18n="" class="menu-title">'.$menu->nmmenu.'</span>
										</a>
										</li>';
						$angular .= '.state("/", {
										url: "/",
										templateUrl: "partials/'.$menu->nmfile.'"
									})';
					}
					else{
						$html_out .= '<li class="nav-item">
										<a ui-sref="'.$menu->url.'">
											<i class="'.$menu->icon.'"></i>
											<span data-i18n="" class="menu-title">'.$menu->nmmenu.'</span>
										</a>
										</li>';
						$angular .= '.state("'.$menu->url.'", {
										url: "/'.$menu->url.'",
										templateUrl: "partials/'.$menu->nmfile.'"
									})';
					}
				}
				
			}
			else{

				if($menu->nourut==0){

					$angular .= '.state("'.$menu->url.'", {
						url: "/'.$menu->url.'",
						templateUrl: "partials/'.$menu->nmfile.'"
					})';

				}
				else{

					//jika ya, perlu buat sub menu dengan parameter parent_id ybs
					$html_out .= '<li class="has-sub nav-item">
									<a href="javascript:void(0);">
										<i class="'.$menu->icon.'"></i>
										<span data-i18n="" class="menu-title">'.$menu->nmmenu.'</span>
									</a>
									<ul class="menu-content">';

					$sub_menus = DB::select("
						SELECT * FROM t_menu WHERE aktif='1' AND kdlevel LIKE '%+".session('kdlevel')."+%' AND parent_id='".$menu->id."' ORDER BY nourut
					");

					//bentuk sub menu
					foreach($sub_menus as $sub_menu){
						
						//apakah tab baru?
						if($sub_menu->new_tab=='1'){
							$html_out .= '<li>
											<a id="submenu-'.$sub_menu->id.'" class="submenu-li" href="'.$sub_menu->url.'" class="waves-effect waves-block" target="_blank">'.$sub_menu->nmmenu.'</a>
										</li>';
						}
						else{
							$html_out .= '<li>
											<a ui-sref="'.$sub_menu->url.'" class="menu-item">'.$sub_menu->nmmenu.'</a>
											</li>';
							$angular .= '.state("'.$sub_menu->url.'", {
											url: "/'.$sub_menu->url.'",
											templateUrl: "partials/'.$sub_menu->nmfile.'"
										})';
						}
						
					}

					$html_out .= 	'</ul>
								</li>';

				}

			}
			
		}
		
		$angular .=		'.state("profile", {
							url: "/profile",
							templateUrl: "partials/profile.html"
						})
						;
					});';
		
		header("x-frame-options:SAMEORIGIN");
		
		return view('app',
			[
				'menu' => $html_out,
				'angular' => $angular,
				'info_nmkantor' => session('nmunit'),
				'info_nmlevel' => session('nmlevel'),
				'info_foto' => session('foto'),
				'info_username' => session('username'),
				'info_nama' => session('nama'),
				'info_email' => session('email'),
				'info_tahun' => session('tahun'),
				'app_nama' => session('app_nama'),
				'app_versi' => session('app_versi'),
				'app_ket' => session('app_ket'),
				'notifikasi' => ''//$notif
			]
		);

	}
	
	public function token()
	{
		return csrf_token();
	}
	
	public function test_mail($email)
	{
		$data = array(
			'email' => $email
		);
		
		$kirim = Mail::send('emails.test', $data, function ($message) use ($data) {
			
			$message->from('sikumbang.lemhanas@gmail.com', 'Sikumbang');
			$message->to($data['email'], 'Test')->subject('Test Email');
		
		});
		
		if($kirim==1){
			return 'success';
		}
		else{
			return $kirim;
		}
	}
}