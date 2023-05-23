<?php

Route::group([
    'namespace' => 'Denngarr\Seat\Billing\Http\Controllers',
    'prefix' => 'billing',
    'middleware' => ['web', 'auth']
], function () {
    Route::get('/', [
        'as' => 'billing.view',
        'uses' => 'BillingController@showCurrentBill',
        'middleware' => 'can:billing.view'
    ]);

    Route::get('/settings', [
        'as' => 'billing.settings',
        'uses' => 'BillingController@getBillingSettings',
        'middleware' => 'can:billing.settings'
    ]);

    Route::post('/settings', [
        'as' => 'billing.savesettings',
        'uses' => 'BillingController@saveBillingSettings',
        'middleware' => 'can:billing.settings'
    ]);

    Route::get('/past/{year}/{month}', [
        'as' => 'billing.pastbilling',
        'uses' => 'BillingController@showBill',
        'middleware' => 'can:billing.view'
    ]);

    Route::get('/character/{id}/{year}/{month}', [
        'as' => 'billing.getindbilling',
        'uses' => 'BillingController@getCharacterBill',
        'middleware' => 'can:billing.view'
    ]);

    Route::get('/userBill/', [
        'as' => 'billing.userBill',
        'uses' => 'BillingController@getUserBill',
    ]);
});
