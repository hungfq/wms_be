<?php

$api->group([
    'prefix' => '/inbound',
    'namespace' => '\App\Modules\Inbound\Controllers'
], function ($api) {

    $api->group(['prefix' => '/po'], function ($api) {
        $api->get('/', [
            'uses' => 'PoController@index',
        ]);

        $api->post('/', [
//            'as' => 'po.create',
            'uses' => 'PoController@store',
        ]);

        $api->group(['prefix' => '/{poHdrId:[0-9]+}'], function ($api) {
            $api->get('/', [
                'uses' => 'PoController@show',
            ]);

            $api->put('/', [
//            'as' => 'po.update',
                'uses' => 'PoController@update',
            ]);
        });
    });

    $api->group(['prefix' => '/autocomplete'], function ($api) {
        $api->get('/po-num', [
            'uses' => 'AutocompleteController@poNum',
        ]);
    });

});
