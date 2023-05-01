<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CustomerTicketController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/dashboard', function () {
    return view('welcome');
});

// Route::get('/', function () {
//     return view('user.index');
// })->name('dashboard');
Route::get('/', [HomeController::class, 'index'])->name('dashboard');

// Route::middleware('auth')->group(function () {

    Route::prefix('ticket')->name('ticket.')->controller(TicketController::class)->name('ticket.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('register/{id}', 'order')->name("order");
        Route::get('order/', 'create')->name("create");

        Route::post('buy', 'store')->name('store');
        Route::get('buy', 'store1')->name('store1');
    }
    );

    Route::prefix('admin')->name('admin.')->controller(AdminController::class)->name('admin.')->group(
        function () {
            Route::get('', 'index')->name('index');
            Route::get('/detail/{id}', 'show')->name('detail');
        }
    );
    Route::prefix('payment')->controller(PaymentController::class)->group(
        function () {
            Route::post('/getVirtualAccount', 'getVirtualAccount')->name("getVirtualAccount");
            // Route::post('/notification/handling', 'notifHandling')->name("notifHandling");
            Route::post('/inquiry-process', 'inquiryProcess')->name("inquiryProcess");
            Route::post('/payment-notif', 'notifHandling')->name("notifHandling");
        }
    );
    Route::get('/event', 'App\Http\Controllers\EventController@index')->name('event.index');

    Route::get('/payment', 'App\Http\Controllers\PaymentController@index')->name('payment.index');
    Route::post('/payment/ping', 'App\Http\Controllers\PaymentController@ping')->name('payment.ping');
    Route::get('/scanner', 'App\Http\Controllers\ScanController@index')->name('scanner.index');
    Route::get('/qr/{id}', 'App\Http\Controllers\ScanController@generateQR')->name('qrgenerate');
    Route::post('/payment/ping', 'App\Http\Controllers\PaymentController@ping')->name('payment.ping');
    // });


Route::prefix('ticket')->name('ticket.')->controller(TicketController::class)->name('ticket.')->group(function () {
    Route::get('', 'index')->name('index');
    Route::get('register/{id}', 'order')->name("order");
    Route::get('order/', 'create')->name("create");
    Route::post('store', 'store')->name('store');
}
);


Route::prefix('user')->group(function () {
    Route::get('/order', [App\Http\Controllers\TicketController::class, 'index_user'])->name('user.order');
    Route::get('/order/{id}', [App\Http\Controllers\TicketController::class, 'detail_transaki'])->name('detail.trx');
    Route::post('/regis', [App\Http\Controllers\TicketController::class, 'regis'])->name('regis');
});
Route::get('/support', [CustomerTicketController::class, 'index'])->name('support.index');
Route::post('/tickets/create', [CustomerTicketController::class, 'createTicket'])->name('ticket.create');

Route::prefix('admin')->name('admin.')->controller(AdminController::class)->name('admin.')->group(
    function () {
        Route::get('', 'index')->name('index');
        Route::get('/detail/{id}', 'show')->name('detail');
    }
);
Route::get('/genkey', 'App\Http\Controllers\PaymentController@genkey')->name('genkey');
Route::get('/add-data-manual', 'App\Http\Controllers\PaymentController@addManualData')->name('addManualData');
Route::post('/postadddatamanual', 'App\Http\Controllers\PaymentController@postadddatamanual')->name('postadddatamanual');

Route::get('/event', 'App\Http\Controllers\EventController@index')->name('event.index');

Route::get('/payment', 'App\Http\Controllers\PaymentController@index')->name('payment.index');
Route::post('/payment/ping', 'App\Http\Controllers\PaymentController@ping')->name('payment.ping');
Route::get('/scanner', 'App\Http\Controllers\ScanController@index')->name('scanner.index');
Route::get('/qr/{id}', 'App\Http\Controllers\ScanController@generateQR')->name('qrgenerate');
Route::get('/faq', 'App\Http\Controllers\FaqController@index')->name('faq.index');
Route::get('/galeri/{id}', 'App\Http\Controllers\GaleriController@index')->name('galeri.index');
Route::get('/home', 'App\Http\Controllers\UserController@index')->name('user.index');



// Route::get('/admin', function () {
//     return view('admin.tiket.index');
// })->name('admindash');

require __DIR__ . '/auth.php';
