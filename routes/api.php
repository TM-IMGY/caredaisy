<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// 連携API
Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/users', 'Api\UserController@index');
    Route::get('/service_plan_pdf', 'Api\ServicePlanController@pdf')->name('api.service_plan_pdf');
});

// 伝送請求API
Route::prefix('invoice')->group(function(){
    Route::post('auth', 'Api\Invoice\AuthController@issue');
    Route::group(['middleware' => ['client']], function () {
        Route::get('/facility/list', 'Api\Invoice\InvoiceController@getFacilities');
        Route::get('/v1/facility/list', 'Api\Invoice\InvoiceController@getFacilitiesV1');
        Route::get('/list', 'Api\Invoice\InvoiceController@getInvoices');
        Route::get('/v1/list', 'Api\Invoice\InvoiceController@getInvoicesV1');
        Route::post('/update', 'Api\Invoice\InvoiceController@updateInvoices');
        Route::post('/document/update', 'Api\Invoice\InvoiceController@updateDocuments');
        Route::post('/attachment/update', 'Api\Invoice\InvoiceController@updateAttachments');
        Route::post('/watchdog', 'Api\WatchdogController@index');
        Route::get('/slack_notification', 'Api\SlackNotificationController@notification');
    });
});

// Hospitac
Route::post('token', 'Api\Hospitac\AuthController@issue');
Route::group(['middleware' => ['client']], function () {
    Route::post('hospitacFileUpload', 'Api\Hospitac\HospitacController@fileUpload');
});
