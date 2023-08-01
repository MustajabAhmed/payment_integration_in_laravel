<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Payment\PaymentController;
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

Route::get('/', function () {
    return view('welcome');
});



Route::get('/create-paypal-subscription/{slug}/{payprice}/{duration}', [PaymentController::class, 'CreatePaypalSub']);

Route::get('/paypal/subscription/success/{slug}/{payprice}/{duration}', [PaymentController::class, 'success']);
Route::get('/paypal/subscription/cancel', [PaymentController::class, 'cancel']);
