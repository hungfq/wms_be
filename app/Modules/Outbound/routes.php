<?php

$api->group([
    'prefix' => '/outbound',
    'namespace' => '\App\Modules\Outbound\Controllers'
], function ($api) {

    $api->group(['prefix' => '/whs/{whsId}/orders'], function ($api) {
        $api->get('/', [
            'uses' => 'OrderController@view',
        ]);

        $api->post('/', [
            'uses' => 'OrderController@store',
        ]);

        $api->post('/allocates', [
            'uses' => 'OrderController@allocateMultiple',
        ]);

        $api->group(['prefix' => '/{odrHdrId:[0-9]+}'], function ($api) {
            $api->get('/', [
                'uses' => 'OrderController@show',
            ]);

            $api->put('/', [
                'uses' => 'OrderController@update',
            ]);
        });

        $api->group(['prefix' => '/auto'], function ($api) {
            $api->get('/search-item', [
                'uses' => 'OrderInventoryController@searchItem',
            ]);
        });


    });

    $api->group(['prefix' => '/whs/{whsId}/wavepick'], function ($api) {

        $api->post('/', [
            'uses' => 'WavePickController@store',
        ]);

    });
});
