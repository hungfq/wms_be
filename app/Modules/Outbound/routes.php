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

        $api->post('/out-sort', [
            'uses' => 'OrderController@outSortMultiple',
        ]);

        $api->post('/schedule-to-ship', [
            'uses' => 'OrderController@scheduleToShip',
        ]);

        $api->post('/ship', [
            'uses' => 'OrderController@ship',
        ]);

        $api->post('/revert', [
            'uses' => 'OrderController@revert',
        ]);

        $api->post('/cancel', [
            'uses' => 'OrderController@cancel',
        ]);

        $api->group(['prefix' => '/{odrHdrId:[0-9]+}'], function ($api) {
            $api->get('/', [
                'uses' => 'OrderController@show',
            ]);

            $api->put('/', [
                'uses' => 'OrderController@update',
            ]);

            $api->put('/remark', [
                'uses' => 'OrderController@updateRemark',
            ]);
        });

        $api->group(['prefix' => '/auto'], function ($api) {
            $api->get('/search-item', [
                'uses' => 'OrderInventoryController@searchItem',
            ]);
        });


    });

    $api->group(['prefix' => '/whs/{whsId}/wavepick'], function ($api) {

        $api->get('/', [
            'uses' => 'WavePickController@view',
        ]);

        $api->post('/', [
            'uses' => 'WavePickController@store',
        ]);

        $api->group(['prefix' => '/{wvHdrId:[0-9]+}'], function ($api) {
            $api->get('/', [
                'uses' => 'WavePickController@show',
            ]);

            $api->post('/cancel', [
                'uses' => 'WavePickController@cancel',
            ]);

            $api->get('/dtl/{wvDtlId:[0-9]+}/suggest-location', [
                'uses' => 'WavePickController@suggestLocation',
            ]);

            $api->post('/dtl/{wvDtlId:[0-9]+}', [
                'uses' => 'WavePickController@pick',
            ]);
        });

    });
});
