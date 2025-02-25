<?php

$api->group([
    'prefix' => 'api',
    'namespace' => '\App\Modules\Api\Controllers',
], function ($api) {

    $api->group(['prefix' => '/dashboard/whs/{whsId}'], function ($api) {

        $api->get('/po', [
            'uses' => 'ApiDashboardController@getDashboardTmp',
        ]);

        $api->get('/wave-pick', [
            'uses' => 'ApiDashboardController@getDashboardTmp',
        ]);

        $api->get('/order', [
            'uses' => 'ApiDashboardController@getDashboardTmp',
        ]);

        $api->get('/location-capacity', [
            'uses' => 'ApiDashboardController@getDashboardTmp',
        ]);

        $api->get('/replenishment', [
            'uses' => 'ApiDashboardController@getDashboardTmp',
        ]);

    });
});
