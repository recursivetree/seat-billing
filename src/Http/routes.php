<?php

Route::group([
    'namespace' => 'Denngarr\Seat\Billing\Http\Controllers',
    'prefix' => 'billing',
    'middleware' => ['web', 'auth']
], function () {
    Route::get('/', [
        'as' => 'billing.view',
        'uses' => 'BillingController@getLiveBillingView',
        'middleware' => 'can:billing.view'
    ]);

    Route::get('/alliance/{alliance_id}', [
        'as' => 'billing.allianceview',
        'uses' => 'BillingController@getLiveBillingView',
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

    Route::get('/getindbilling/{id}', [
        'as' => 'billing.getindbilling',
        'uses' => 'BillingController@getUserBilling',
        'middleware' => 'can:billing.view'
    ]);

    Route::get('/pastbilling/{year}/{month}', [
        'as' => 'billing.pastbilling',
        'uses' => 'BillingController@previousBillingCycle',
        'middleware' => 'can:billing.view'
    ]);

    Route::get('/getindpastbilling/{id}/{year}/{month}', [
        'as' => 'billing.getindbilling',
        'uses' => 'BillingController@getPastUserBilling',
        'middleware' => 'can:billing.view'
    ]);
});
