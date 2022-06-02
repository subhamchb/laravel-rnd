<?php

use App\Http\Controllers\AdobeController;
use App\Http\Controllers\TransferwiseTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Subhamchbt\OAuth2\AdobeSign;
use Subhamchbt\Sign\AdobeSign as Sign;

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

Route::view('/', 'index')->name('index');

Route::prefix('wise')->as('wise.')->group(function () {
    Route::get('/', [TransferwiseTest::class, 'index'])->name('index');
    Route::post('get-form-fields', [TransferwiseTest::class, 'getFormFields'])->name('getFormFields');
    Route::post('create-recipient', [TransferwiseTest::class, 'createRecipient'])->name('createRecipient');
    Route::get('delete/{accountID}', [TransferwiseTest::class, 'deleteMember'])->name('deleteMember');
});

/* ============================================================================================================== */

Route::prefix('adobe')->as('adobe.')->group(function () {
    Route::get('/', [AdobeController::class, 'index'])->name('index');
    Route::get('code-state', [AdobeController::class, 'setCredentials'])->name('setCredentials');
    Route::post('create-agreement', [AdobeController::class, 'createAgreement'])->name('createAgreement');
    Route::get('view-agreement/{id}/status/{status}', [AdobeController::class, 'viewAgreement'])->name('viewAgreement');
});
