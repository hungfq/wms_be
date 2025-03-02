<?php

$api->group([
    'prefix' => '/inbound',
    'namespace' => '\App\Modules\Inbound\Controllers'
], function ($api) {

    $api->group(['prefix' => '/whs/{whsId}/po'], function ($api) {
        $api->get('/', [
            'uses' => 'PoController@index',
        ]);

        $api->post('/', [
//            'as' => 'po.create',
            'uses' => 'PoController@store',
        ]);

        $api->put('/cancel', [
            'uses' => 'PoController@cancel',
        ]);

        $api->group(['prefix' => '/{poHdrId:[0-9]+}'], function ($api) {
            $api->get('/', [
                'uses' => 'PoController@show',
            ]);

            $api->put('/', [
//            'as' => 'po.update',
                'uses' => 'PoController@update',
            ]);

            $api->put('/complete', [
                'uses' => 'PoController@complete',
            ]);
        });
    });

    $api->group(['prefix' => '/whs/{whsId}/goods-receipt'], function ($api) {
        $api->get('/', [
            'uses' => 'GrController@index',
        ]);

        $api->group(['prefix' => '/{grHdrId}'], function ($api) {
            $api->get('/', [
                'uses' => 'GrController@show',
            ]);

            $api->put('/complete', [
                'uses' => 'GrController@complete',
            ]);

            $api->get('/gr-logs', [
                'uses' => 'GrController@viewGrLogs',
            ]);
        });
    });

    $api->group(['prefix' => '/autocomplete'], function ($api) {
        $api->get('/po-num', [
            'uses' => 'AutocompleteController@poNum',
        ]);

        $api->get('/vendors', [
            'uses' => 'AutocompleteController@vendor',
        ]);
    });

});
