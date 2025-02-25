<?php

$api->group([
    'prefix' => 'inventory',
    'namespace' => '\App\Modules\Inventory\Controllers',

], function ($api) {
    $api->group(['prefix' => 'warehouses/{whsId:[0-9]+}'], function ($api) {

        $api->get('/inventory', [
            'as' => '',
            'uses' => 'InventoryController@viewInventory',
        ]);

        $api->get('/inventory-by-location', [
            'as' => '',
            'uses' => 'InventoryController@viewInventoryByLocation',
        ]);

    });
});
