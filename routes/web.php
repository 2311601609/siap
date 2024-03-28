<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticateController;
use App\Http\Controllers\DropdownController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AnggaranPaguUnitController;
use App\Http\Controllers\AnggaranPaguProyekController;
use App\Http\Controllers\AnggaranRencanaController;
use App\Http\Controllers\AnggaranTargetController;
use App\Http\Controllers\TagihanRekamController;
use App\Http\Controllers\TagihanProsesController;
use App\Http\Controllers\TagihanPajakController;
use App\Http\Controllers\PenerimaanRekamController;
use App\Http\Controllers\PenerimaanProsesController;
use App\Http\Controllers\PenerimaanPajakController;
use App\Http\Controllers\KasKecilRekamController;
use App\Http\Controllers\KasKecilProsesController;
use App\Http\Controllers\UMKRekamController;
use App\Http\Controllers\UMKProsesController;
use App\Http\Controllers\PengeluaranRekamController;
use App\Http\Controllers\PengeluaranProsesController;
use App\Http\Controllers\PengeluaranPajakController;
use App\Http\Controllers\PengeluaranBayarController;
use App\Http\Controllers\KoreksiTransaksiController;
use App\Http\Controllers\PembukuanSaldoAwalController;
use App\Http\Controllers\PembukuanJurnalController;
use App\Http\Controllers\PembukuanJurnalPController;
use App\Http\Controllers\PembukuanPostingController;
use App\Http\Controllers\BukuBesarController;
use App\Http\Controllers\BuktiTransaksiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaporanKeuanganController;
use App\Http\Controllers\LaporanRealisasiController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\RefUserController;
use App\Http\Controllers\RefUnitController;
use App\Http\Controllers\RefAkunController;
use App\Http\Controllers\RefTransaksiController;
use App\Http\Controllers\RefPenerimaController;
use App\Http\Controllers\RefAlurController;
use App\Http\Controllers\RefProyekController;
use App\Http\Controllers\RefPejabatController;
use App\Http\Controllers\RefRekeningController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//otentikasi/login
Route::group(['prefix' => 'auth'], function () {

	Route::get('', [AuthenticateController::class, 'index']);
	Route::get('tahun', [DropdownController::class, 'tahun']);
    Route::get('logout', [AuthenticateController::class, 'logout']);
	Route::post('', [AuthenticateController::class, 'login']);

});

// /* group auth routes */
Route::middleware(['auth'])->group(function () {

    Route::get('', [AppController::class, 'index']);
    Route::get('/', [AppController::class, 'index']);
    Route::get('/token', [AppController::class, 'token']);
	Route::get('cek/level', [AuthenticateController::class, 'cek_level']);
	Route::get('hapus/sesi/upload', [AuthenticateController::class, 'hapus_sesi_upload']);

    //Beranda
	Route::group(['prefix' => 'home'], function(){
		
		Route::get('total', [HomeController::class, 'total']);
		Route::get('data', [HomeController::class, 'data']);
		
	});

    //Profile
	Route::group(['prefix' => 'profile'], function(){
		
		Route::get('', [ProfileController::class, 'index']);
		Route::post('', [ProfileController::class, 'ubah']);
		Route::post('/upload', [ProfileController::class, 'upload']);
		
	});

    //anggaran
	Route::group(['prefix' => 'anggaran'], function () {
		
		Route::group(['prefix' => 'pagu'], function () {
			
			Route::group(['prefix' => 'unit'], function () {
			
				Route::get('', [AnggaranPaguUnitController::class, 'index']);
				Route::get('/pilih/{param}', [AnggaranPaguUnitController::class, 'pilih'])->middleware('role:00');
				Route::get('/sisa', [AnggaranPaguUnitController::class, 'sisaPagu']);
				Route::get('/revisike', [AnggaranPaguUnitController::class, 'revisike']);
				Route::post('', [AnggaranPaguUnitController::class, 'simpan'])->middleware('role:00');
				Route::post('/hapus', [AnggaranPaguUnitController::class, 'hapus'])->middleware('role:00');
				Route::post('/revisi', [AnggaranPaguUnitController::class, 'simpanRevisi'])->middleware('role:00');
				
			});
			
		});
		
		Route::group(['prefix' => 'target'], function () {
			
			Route::get('', [AnggaranTargetController::class, 'index']);
			Route::get('/pilih/{param}', [AnggaranTargetController::class, 'pilih'])->middleware('role:00');
			Route::post('', [AnggaranTargetController::class, 'simpan'])->middleware('role:00');
			Route::post('/hapus', [AnggaranTargetController::class, 'hapus'])->middleware('role:00');
			
		});
		
		Route::group(['prefix' => 'proyek'], function () {
			
			Route::get('', [AnggaranProyekController::class, 'index']);
			Route::get('/pilih/{param}', [AnggaranProyekController::class, 'pilih'])->middleware('role:00');
			Route::post('', [AnggaranProyekController::class, 'simpan'])->middleware('role:00');
			Route::post('/hapus', [AnggaranProyekController::class, 'hapus'])->middleware('role:00');
			
		});
		
		Route::group(['prefix' => 'rencana'], function () {
			
			Route::get('', [AnggaranRencanaController::class, 'index']);
			Route::get('/pilih/{param}', [AnggaranRencanaController::class, 'pilih'])->middleware('role:00');
			Route::post('', [AnggaranRencanaController::class, 'simpan'])->middleware('role:00');
			Route::post('/hapus', [AnggaranRencanaController::class, 'hapus'])->middleware('role:00');
			
		});
		
	});

	//tagihan
	Route::group(['prefix' => 'tagihan'], function () {
		
		Route::group(['prefix' => 'monitoring'], function () {
			
			Route::get('', [TagihanProsesController::class, 'monitoring']);
			
		});
		
		Route::group(['prefix' => 'proses'], function () {
			
			Route::get('', [TagihanProsesController::class, 'index']);
			Route::get('/pilih/{param}', [TagihanProsesController::class, 'pilih']);
			Route::post('', [TagihanProsesController::class, 'simpan']);
			
		});
		
		Route::group(['prefix' => 'pajak'], function () {
			
			Route::get('', [TagihanPajakController::class, 'index']);
			Route::get('/pilih/{param}', [TagihanPajakController::class, 'pilih']);
			Route::post('', [TagihanPajakController::class, 'simpan']);
			Route::post('/hapus', [TagihanPajakController::class, 'hapus']);
			
		});
		
		Route::group(['prefix' => 'rekam'], function () {
			
			Route::get('', [TagihanRekamController::class, 'index']);
			Route::get('/pilih/{param}', [TagihanRekamController::class, 'pilih'])->middleware('role:00.04.07.12');
			Route::get('/nomor', [TagihanRekamController::class, 'nomor'])->middleware('role:04.07.12');
			Route::get('/detil/{param}', [TagihanRekamController::class, 'detil']);
			Route::get('/download/{param}', [TagihanRekamController::class, 'download']);
			Route::post('', [TagihanRekamController::class, 'simpan'])->middleware('role:00.04.07.12');
			Route::post('/hapus', [TagihanRekamController::class, 'hapus'])->middleware('role:12');
			Route::post('/upload', [TagihanRekamController::class, 'upload'])->middleware('role:12');
			
		});
		
	});

	//penerimaan
	Route::group(['prefix' => 'penerimaan'], function () {
		
		Route::group(['prefix' => 'monitoring'], function () {
			
			Route::get('', [PenerimaanProsesController::class, 'monitoring']);
			
		});
		
		Route::group(['prefix' => 'proses'], function () {
			
			Route::get('', [PenerimaanProsesController::class, 'index']);
			Route::get('/pilih/{param}', [PenerimaanProsesController::class, 'pilih']);
			Route::post('', [PenerimaanProsesController::class, 'simpan']);
			
		});
		
		Route::group(['prefix' => 'pajak'], function () {
			
			Route::get('', [PenerimaanPajakController::class, 'index']);
			Route::get('/pilih/{param}', [PenerimaanPajakController::class, 'pilih']);
			Route::post('', [PenerimaanPajakController::class, 'simpan']);
			Route::post('/hapus', [PenerimaanPajakController::class, 'hapus']);
			
		});
		
		Route::group(['prefix' => 'rekam'], function () {
			
			Route::get('', [PenerimaanRekamController::class, 'index']);
			Route::get('/pilih/{param}', [PenerimaanRekamController::class, 'pilih'])->middleware('role:00.04.07.10.12');
			Route::get('/nomor', [PenerimaanRekamController::class, 'nomor'])->middleware('role:04.07.10.12');
			Route::get('/tagihan/{param}', [PenerimaanRekamController::class, 'tagihan']);
			Route::get('/detil/{param}', [PenerimaanRekamController::class, 'detil']);
			Route::get('/download/{param}', [PenerimaanRekamController::class, 'download']);
			Route::get('/upload/{param}', [PenerimaanRekamController::class, 'dok'])->middleware('role:00.04.07.10.12');
			Route::post('', [PenerimaanRekamController::class, 'simpan'])->middleware('role:00.04.07.10.12');
			Route::post('/hitung-total', [PenerimaanRekamController::class, 'hitungTotal']);
			Route::post('/hapus', [PenerimaanRekamController::class, 'hapus'])->middleware('role:10.12');
			Route::post('/upload', [PenerimaanRekamController::class, 'upload'])->middleware('role:00.04.07.10.11.12');
			Route::post('/upload-simpan', [PenerimaanRekamController::class, 'uploadSimpan'])->middleware('role:00.04.07.10.11.12');
			Route::post('/hapus-dok', [PenerimaanRekamController::class, 'hapusDok'])->middleware('role:00.04.07.10.11.12');
			
		});
		
	});

	//kas kecil
	Route::group(['prefix' => 'kas-kecil'], function () {
		
		Route::group(['prefix' => 'monitoring'], function () {
			
			Route::get('', [KasKecilProsesController::class, 'monitoring']);
			
		});
		
		Route::group(['prefix' => 'proses'], function () {
			
			Route::get('', [KasKecilProsesController::class, 'index']);
			Route::get('/pilih/{param}', [KasKecilProsesController::class, 'pilih']);
			Route::post('', [KasKecilProsesController::class, 'simpan']);
			
		});
		
		Route::group(['prefix' => 'rekam'], function () {
			
			Route::get('', [KasKecilRekamController::class, 'index']);
			Route::get('/pilih/{param}', [KasKecilRekamController::class, 'pilih'])->middleware('role:00.04.07.10');
			Route::get('/nomor', [KasKecilRekamController::class, 'nomor'])->middleware('role:04.07.10');
			Route::get('/detil/{param}', [KasKecilRekamController::class, 'detil']);
			Route::get('/download/{param}', [KasKecilRekamController::class, 'download']);
			Route::post('', [KasKecilRekamController::class, 'simpan'])->middleware('role:00.04.07.10');
			Route::post('/hapus', [KasKecilRekamController::class, 'hapus'])->middleware('role:10');
			Route::post('/upload', [KasKecilRekamController::class, 'upload'])->middleware('role:04.07.10');
			
		});
		
	});

	//umk
	Route::group(['prefix' => 'umk'], function () {
		
		Route::group(['prefix' => 'monitoring'], function () {
			
			Route::get('', [UMKProsesController::class, 'monitoring']);
			
		});
		
		Route::group(['prefix' => 'proses'], function () {
			
			Route::get('', [UMKProsesController::class, 'index']);
			Route::get('/pilih/{param}', [UMKProsesController::class, 'pilih']);
			Route::post('', [UMKProsesController::class, 'simpan']);
			
		});
		
		Route::group(['prefix' => 'rekam'], function () {
			
			Route::get('', [UMKRekamController::class, 'index']);
			Route::get('/pilih/{param}', [UMKRekamController::class, 'pilih'])->middleware('role:00.04.07.11');
			Route::get('/nomor', [UMKRekamController::class, 'nomor'])->middleware('role:04.07.11');
			Route::get('/detil/{param}', [UMKRekamController::class, 'detil']);
			Route::get('/download/{param}', [UMKRekamController::class, 'download']);
			Route::post('', [UMKRekamController::class, 'simpan'])->middleware('role:00.04.07.11');
			Route::post('/hapus', [UMKRekamController::class, 'hapus'])->middleware('role:11');
			Route::post('/upload', [UMKRekamController::class, 'upload'])->middleware('role:04.07.11');
			
		});
		
	});

	//pengeluaran
	Route::group(['prefix' => 'pengeluaran'], function () {
		
		Route::group(['prefix' => 'monitoring'], function () {
			
			Route::get('', [PengeluaranProsesController::class, 'monitoring']);
			
		});
		
		Route::group(['prefix' => 'proses'], function () {
			
			Route::get('', [PengeluaranProsesController::class, 'index']);
			Route::get('/pilih/{param}', [PengeluaranProsesController::class, 'pilih']);
			Route::post('', [PengeluaranProsesController::class, 'simpan']);
			
		});
		
		Route::group(['prefix' => 'pajak'], function () {
			
			Route::get('', [PengeluaranPajakController::class, 'index']);
			Route::get('/pilih/{param}', [PengeluaranPajakController::class, 'pilih']);
			Route::post('', [PengeluaranPajakController::class, 'simpan']);
			Route::post('/hapus', [PengeluaranPajakController::class, 'hapus']);
			
		});
		
		Route::group(['prefix' => 'bayar'], function () {
			
			Route::get('', [PengeluaranBayarController::class, 'index']);
			Route::get('/pilih/{param}', [PengeluaranBayarController::class, 'pilih']);
			Route::post('', [PengeluaranBayarController::class, 'simpan']);
			Route::post('/hapus', [PengeluaranBayarController::class, 'hapus']);
			
		});
		
		Route::group(['prefix' => 'rekam'], function () {
			
			Route::get('', [PengeluaranRekamController::class, 'index']);
			Route::get('/pilih/{param}', [PengeluaranRekamController::class, 'pilih'])->middleware('role:00.04.07.11');
			Route::get('/nomor', [PengeluaranRekamController::class, 'nomor'])->middleware('role:04.07.11');
			Route::get('/tagihan/{param}', [PengeluaranRekamController::class, 'tagihan']);
			Route::get('/detil/{param}', [PengeluaranRekamController::class, 'detil']);
			Route::get('/download/{param}', [PengeluaranRekamController::class, 'download']);
			Route::post('/hitung-total', [PengeluaranRekamController::class, 'hitungTotal']);
			Route::get('/upload/{param}', [PengeluaranRekamController::class, 'dok'])->middleware('role:00.04.07.11');
			Route::post('', [PengeluaranRekamController::class, 'simpan'])->middleware('role:00.04.07.11');
			Route::post('/beta', [PengeluaranRekamController::class, 'simpanBeta'])->middleware('role:00.04.07.11');
			Route::post('/hapus', [PengeluaranRekamController::class, 'hapus'])->middleware('role:11');
			Route::post('/upload/{param}', [PengeluaranRekamController::class, 'upload'])->middleware('role:00.04.07.11');
			Route::post('/upload-simpan', [PengeluaranRekamController::class, 'uploadSimpan'])->middleware('role:00.04.07.11');
			Route::post('/hapus-dok', [PengeluaranRekamController::class, 'hapusDok'])->middleware('role:00.04.07.11');
			
		});
		
	});

	//koreksi transaksi
	Route::group(['prefix' => 'koreksi-transaksi'], function () {
		
		Route::get('', [KoreksiTransaksiController::class, 'index']);
		Route::get('/pilih/{param}', [KoreksiTransaksiController::class, 'pilih']);
		Route::post('', [KoreksiTransaksiController::class, 'simpan']);
		Route::post('/hapus', [KoreksiTransaksiController::class, 'hapus']);
		
	});

	//pembukuan
	Route::group(['prefix' => 'pembukuan'], function () {
		
		Route::group(['prefix' => 'saldo-awal'], function () {
		
			Route::get('', [PembukuanSaldoAwalController::class, 'index']);
			Route::get('/total', [PembukuanSaldoAwalController::class, 'total']);
			Route::get('/pilih/{param}', [PembukuanSaldoAwalController::class, 'pilih'])->middleware('role:00.04');
			Route::post('', [PembukuanSaldoAwalController::class, 'simpan'])->middleware('role:00.04');
			Route::post('/hapus', [PembukuanSaldoAwalController::class, 'hapus'])->middleware('role:00.04');
			
		});
		
		Route::group(['prefix' => 'jurnal'], function () {
		
			Route::get('/{param1}/{param2}', [PembukuanJurnalController::class, 'index']);
			Route::get('/{param1}/{param2}/excel', [PembukuanJurnalController::class, 'neracaExcel']);
			
		});
		
		Route::group(['prefix' => 'jurnal-penyesuaian'], function () {
			
			Route::get('', [PembukuanJurnalPController::class, 'index']);
			Route::get('/pilih/{param}', [PembukuanJurnalPController::class, 'pilih'])->middleware('role:00.04');
			Route::get('/detil/{param}', [PembukuanJurnalPController::class, 'detil']);
			Route::post('', [PembukuanJurnalPController::class, 'simpan'])->middleware('role:00.04');
			Route::post('/hapus', [PembukuanJurnalPController::class, 'hapus'])->middleware('role:00.04');
			
		});
		
		Route::group(['prefix' => 'neraca-penyesuaian'], function () {
		
			Route::get('', [PembukuanJurnalController::class, 'neracaPenyesuaian']);
			
		});
		
		Route::group(['prefix' => 'neraca-lajur'], function () {
		
			Route::get('/{param}', [PembukuanJurnalController::class, 'neracaLajur']);
			Route::get('/{param}/excel', [PembukuanJurnalController::class, 'neracaLajurExcel']);
			
		});
		
		Route::group(['prefix' => 'posting'], function () {
		
			Route::get('', [PembukuanPostingController::class, 'index']);
			Route::get('/buku-besar', [PembukuanPostingController::class, 'buku_besar']);
			Route::post('', [PembukuanPostingController::class, 'simpan'])->middleware('role:00.04');
			
		});
		
	});

	//GL
	Route::group(['prefix' => 'gl'], function () {
		
		Route::get('/excel', [BukuBesarController::class, 'excel']);
		Route::get('/excel-all', [BukuBesarController::class, 'excelAll']);
		Route::get('/excel-baru', [BukuBesarController::class, 'excelBaru']);
		Route::get('/pdf', [BukuBesarController::class, 'pdf']);
		
	});

	//route for Bukti
	Route::group(['prefix' => 'bukti'], function() {
	
		Route::get('/uang-muka/{param}', [BuktiTransaksiController::class, 'uangMukaKerja']);
		Route::get('/uang-masuk/{param}', [BuktiTransaksiController::class, 'uangMasuk']);
		Route::get('/uang-keluar/{param}', [BuktiTransaksiController::class, 'uangKeluar']);
		Route::get('/tanda-terima/{param}', [BuktiTransaksiController::class, 'tandaTerima']);
		Route::get('/kuitansi/{param}', [BuktiTransaksiController::class, 'kuitansi']);
		Route::get('/kas-kecil/{param}', [BuktiTransaksiController::class, 'kasKecil']);
		
	});

	//route for Reporting
	Route::group(['prefix' => 'laporan'], function() {

		Route::get('/keuangan/{param}', [LaporanController::class, 'keuangan']);
		Route::get('/laba-rugi', [LaporanKeuanganController::class, 'incomeStatement']);
		Route::get('/prb-ekuitas', [LaporanKeuanganController::class, 'changeOnEquity']);
		Route::get('/neraca', [LaporanKeuanganController::class, 'balanceSheet']);
		Route::get('/arus-kas', [LaporanKeuanganController::class, 'cashFlow']);
		Route::get('/rkey', [LaporanKeuanganController::class, 'rKey']);
		
	});

	//route for realisasi
	Route::group(['prefix' =>'realisasi'], function() {

		//realisasi pendapatan
		Route::get('/pendapatan-umum', [LaporanRealisasiController::class, 'pendapatan']);
		Route::get('/pendapatan-pengembangan', [LaporanRealisasiController::class, 'pendapatanPengembangan']);
		Route::get('/pendapatan-pengelolaan', [LaporanRealisasiController::class, 'pendapatanPengelolaan']);

		//realisasi beban
		Route::get('/beban-umum', [LaporanRealisasiController::class, 'beban']);
		Route::get('/beban-penjualan', [LaporanRealisasiController::class, 'bebanPokokPenjualan']);
		Route::get('/beban-usaha', [LaporanRealisasiController::class, 'bebanUsaha']);

		//realisasi investasi
		Route::get('/investasi', [LaporanRealisasiController::class, 'investasi']);
		
	});

	//route for monitoring
	Route::group(['prefix' => 'monitoring'], function () {
		
		Route::group(['prefix' => 'realisasi'], function () {
		
			Route::get('/pendapatan/{param}', [MonitoringController::class, 'realPendapatan']);
			Route::get('/belanja/{param}', [MonitoringController::class, 'realBelanja']);
			
		});
		
		Route::group(['prefix' => 'saldokas'], function () {
			
			Route::get('/{param}', [MonitoringController::class, 'saldoKas']);
			
		});
		
	});

	//route for Referensi
	Route::group(['prefix' => 'ref'], function(){
		
		Route::group(['prefix' => 'user'], function(){
			
			Route::get('', [RefUserController::class, 'index'])->middleware('role:00');
			Route::get('/pilih/{param}', [RefUserController::class, 'pilih'])->middleware('role:00');
			Route::post('', [RefUserController::class, 'simpan'])->middleware('role:00');
			Route::post('/hapus', [RefUserController::class, 'hapus'])->middleware('role:00');
			Route::post('/reset', [RefUserController::class, 'reset'])->middleware('role:00');
			
		});
		
		Route::group(['prefix' => 'unit'], function(){
			
			Route::get('', [RefUnitController::class, 'index']);
			Route::get('/pilih/{param}', [RefUnitController::class, 'pilih'])->middleware('role:00');
			Route::post('', [RefUnitController::class, 'simpan'])->middleware('role:00');
			Route::post('/hapus', [RefUnitController::class, 'hapus'])->middleware('role:00');
			
		});
		
		Route::group(['prefix' => 'akun'], function(){
			
			Route::get('', [RefAkunController::class, 'index']);
			Route::get('/pilih/{param}', [RefAkunController::class, 'pilih'])->middleware('role:00');
			Route::post('', [RefAkunController::class, 'simpan'])->middleware('role:00');
			Route::post('/hapus', [RefAkunController::class, 'hapus'])->middleware('role:00');
			
		});
		
		Route::group(['prefix' => 'transaksi'], function(){
			
			Route::get('', [RefTransaksiController::class, 'index']);
			Route::get('/pilih/{param}', [RefTransaksiController::class, 'pilih'])->middleware('role:00');
			Route::get('/detil/{param}', [RefTransaksiController::class, 'detil']);
			Route::get('/detil1/{param}', [RefTransaksiController::class, 'detil1'])->middleware('role:00.01');
			Route::post('', [RefTransaksiController::class, 'simpan'])->middleware('role:00');
			Route::post('/hapus', [RefTransaksiController::class, 'hapus'])->middleware('role:00');
			
		});
		
		Route::group(['prefix' => 'penerima'], function(){
			
			Route::get('', [RefPenerimaController::class, 'index']);
			Route::get('/pilih/{param}', [RefPenerimaController::class, 'pilih'])->middleware('role:00.01');
			Route::post('', [RefPenerimaController::class, 'simpan'])->middleware('role:00.01');
			Route::post('/hapus', [RefPenerimaController::class, 'hapus'])->middleware('role:00.01');
			
		});
		
		Route::group(['prefix' => 'alur'], function(){
			
			Route::get('', [RefAlurController::class, 'index']);
			Route::get('/pilih/{param}', [RefAlurController::class, 'pilih'])->middleware('role:00');
			Route::post('', [RefAlurController::class, 'simpan'])->middleware('role:00');
			Route::post('/hapus', [RefAlurController::class, 'hapus'])->middleware('role:00');
			
		});
		
		Route::group(['prefix' => 'proyek'], function(){
			
			Route::get('', [RefProyekController::class, 'index']);
			Route::get('/pilih/{param}', [RefProyekController::class, 'pilih'])->middleware('role:00.01');
			Route::post('', [RefProyekController::class, 'simpan'])->middleware('role:00.01');
			Route::post('/hapus', [RefProyekController::class, 'hapus'])->middleware('role:00.01');
			
		});
		
		Route::group(['prefix' => 'pejabat'], function(){
			
			Route::get('', [RefPejabatController::class, 'index']);
			Route::get('/pilih/{param}', [RefPejabatController::class, 'pilih'])->middleware('role:00.01');
			Route::post('', [RefPejabatController::class, 'simpan'])->middleware('role:00.01');
			Route::post('/hapus', [RefPejabatController::class, 'hapus'])->middleware('role:00.01');
			
		});
		
		Route::group(['prefix' => 'rekening'], function(){
			
			Route::get('', [RefRekeningController::class, 'index']);
			Route::get('/pilih/{param}', [RefRekeningController::class, 'pilih'])->middleware('role:00.01');
			Route::post('', [RefRekeningController::class, 'simpan'])->middleware('role:00.01');
			Route::post('/hapus', [RefRekeningController::class, 'hapus'])->middleware('role:00.01');
			
		});
		
	});

	//route for Dropdown
	Route::group(['prefix' => 'dropdown'], function(){
		
		Route::get('/output', [DropdownController::class, 'output']);
		Route::get('/kegiatan', [DropdownController::class, 'kegiatan']);
		Route::get('/dok/{param}', [DropdownController::class, 'dok']);
		Route::get('/dokDetil/{param}', [DropdownController::class, 'dokTransaksi']);
		Route::get('/tagihan', [DropdownController::class, 'tagihan']);
		Route::get('/transaksi', [DropdownController::class, 'transaksi']);
		Route::get('/transaksi/{param}', [DropdownController::class, 'transaksiByParam']);
		Route::get('/unit', [DropdownController::class, 'unit_all']);
		Route::get('/unit/{param}', [DropdownController::class, 'unit']);
		Route::get('/level', [DropdownController::class, 'level']);
		Route::get('/level-pejabat', [DropdownController::class, 'levelPejabat']);
		Route::get('/jenis-pagu', [DropdownController::class, 'jenis_pagu']);
		Route::get('/alur', [DropdownController::class, 'alur']);
		Route::get('/alur-tagihan', [DropdownController::class, 'alurTagihan']);
		Route::get('/alur-penerimaan', [DropdownController::class, 'alurPenerimaan']);
		Route::get('/alur-kas-kecil', [DropdownController::class, 'alurKasKecil']);
		Route::get('/alur-umk', [DropdownController::class, 'alurUmk']);
		Route::get('/alur-pengeluaran', [DropdownController::class, 'alurPengeluaran']);
		Route::get('/penerima', [DropdownController::class, 'penerima']);
		Route::get('/bank', [DropdownController::class, 'bank']);
		Route::get('/lap', [DropdownController::class, 'lap']);
		Route::get('/akun/html/level1', [DropdownController::class, 'akun_html_level1']);
		Route::get('/akun/json', [DropdownController::class, 'akun_json']);
		Route::get('/akun-pajak/json', [DropdownController::class, 'akun_pajak_json']);
		Route::get('/akun/html/all', [DropdownController::class, 'akun_html_all']);
		Route::get('/akun/html/all1', [DropdownController::class, 'akun_html_all_lvl']);
		Route::get('/akun/debet/{param}', [DropdownController::class, 'akun_debet']);
		Route::get('/akun/debet/{param}/json', [DropdownController::class, 'akun_debet_json']);
		Route::get('/akun/kredit/{param}', [DropdownController::class, 'akun_kredit']);
		Route::get('/akun/belanja', [DropdownController::class, 'akun_belanja']);
		Route::get('/periode', [DropdownController::class, 'periode']);
		Route::get('/triwulan', [DropdownController::class, 'triwulan']);
		Route::get('/tahun', [DropdownController::class, 'tahun']);
		Route::get('/proyek', [DropdownController::class, 'proyek']);
		Route::get('/nourut/{param}', [DropdownController::class, 'nourut']);
		Route::get('/jenis-lap', [DropdownController::class, 'jenisLap']);
		Route::get('/sdana', [DropdownController::class, 'sdana']);
		Route::get('/ttd/{param}', [DropdownController::class, 'ttd']);
		Route::get('/trans-dtl', [DropdownController::class, 'transDtl']);
		Route::get('/saldo-kas-kecil', [DropdownController::class, 'saldoKasKecil']);
		Route::get('/tanggal', [DropdownController::class, 'tanggal']);
		Route::get('/status', [DropdownController::class, 'status']);
		
	});

});