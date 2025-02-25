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


        $api->get('/search-item', [
            'uses' => 'OrderInventoryController@searchItem',
        ]);

    });


});
