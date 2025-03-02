<?php

$api->group([
    'prefix' => 'api',
    'namespace' => '\App\Modules\Api\Controllers',
], function ($api) {

    $api->group(['prefix' => '/dashboard/whs/{whsId}'], function ($api) {

        $api->get('/po', [
            'uses' => 'ApiDashboardController@getStatisticPo',
        ]);

        $api->get('/wave-pick', [
            'uses' => 'ApiDashboardController@getStatisticWavePick',
        ]);

        $api->get('/order', [
            'uses' => 'ApiDashboardController@getStatisticOrder',
        ]);

        $api->get('/location-capacity', [
            'uses' => 'ApiDashboardController@getStatisticLocationCapacity',
        ]);

        $api->get('/replenishment', [
            'uses' => 'ApiDashboardController@getDashboardTmp',
        ]);

    });
});
