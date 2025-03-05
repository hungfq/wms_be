<?php

$api->group([
    'prefix' => '/whs-config/{whsId:[0-9]+}',
    'namespace' => '\App\Modules\WhsConfig\Controllers'
], function ($api) {

    $api->group(['prefix' => '/locations'], function ($api) {

        $api->get('/', [
            'uses' => 'LocationController@view',
        ]);

        $api->post('/', [
            'uses' => 'LocationController@store',
        ]);

        $api->delete('/', [
            'uses' => 'LocationController@delete',
        ]);

        $api->group(['prefix' => '/{locId:[0-9]+}'], function ($api) {
            $api->get('/', [
                'uses' => 'LocationController@show',
            ]);

            $api->put('/', [
                'uses' => 'LocationController@update',
            ]);
        });

    });
});
