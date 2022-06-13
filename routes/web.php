<?php

use App\Http\Controllers\AdobeController;
use App\Http\Controllers\TransferwiseTest;
use App\Services\AdobeService;
use Illuminate\Support\Facades\Route;

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
    Route::post('get-child-fields', [TransferwiseTest::class, 'childRequirements'])->name('getChildFormFields');
    Route::post('create-recipient', [TransferwiseTest::class, 'createRecipient'])->name('createRecipient');
    Route::get('delete/{accountID}', [TransferwiseTest::class, 'deleteMember'])->name('deleteMember');
});

/* ============================================================================================================== */

Route::prefix('adobe')->as('adobe.')->group(function () {
    Route::get('/', [AdobeController::class, 'index'])->name('index');
    Route::get('code-state', [AdobeController::class, 'setCredentials'])->name('setCredentials');
    Route::post('create-agreement', [AdobeController::class, 'createAgreement'])->name('createAgreement');
    Route::get('view-agreement/{id}/status/{status}', [AdobeController::class, 'viewAgreement'])->name('viewAgreement');
    Route::post('/form-fields', [AdobeController::class, 'getTemplateFields'])->name('getTemplateFields');
});
