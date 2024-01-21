<?php

$api->group([
    'prefix' => '/master-data',
    'namespace' => '\App\Modules\MasterData\Controllers'
], function ($api) {

    $api->group(['prefix' => '/languages'], function ($api) {
        $api->get('/all-messages', [
            'uses' => 'LanguageController@getAll',
        ]);
    });

    $api->group(['prefix' => '/warehouses'], function ($api) {
        $api->get('/all-warehouse', [
            'uses' => 'WarehouseController@getAll',
        ]);
    });

    $api->group(['prefix' => '/configs'], function ($api) {
        $api->get('/apply', [
            'uses' => 'ConfigController@getApplyConfigs'
        ]);
    });

    $api->group(['prefix' => '/menu'], function ($api) {
        $api->get('/', [
            'uses' => 'MenuController@index'
        ]);
    });

    $api->group(['prefix' => '/table-config'], function ($api) {
        $api->get('/', [
            'uses' => 'TableConfigController@index'
        ]);
        $api->post('/', [
            'uses' => 'TableConfigController@upsert'
        ]);
    });

    $api->group(['prefix' => '/system-setting'], function ($api) {
        $api->get('/whs/{whsId:[0-9]+}', [
            'as' => '',
            'uses' => 'SystemSettingController@getSettingsOfWhs'
        ]);
        $api->post('/whs/{whsId:[0-9]+}', [
            'as' => '',
            'uses' => 'SystemSettingController@upsertSettingsOfWhs'
        ]);
    });

    $api->group(['prefix' => '/dropdown'], function ($api) {
        $api->get('/container-types', [
            'uses' => 'DropdownController@containerTypes',
        ]);
        $api->get('/po-types', [
            'uses' => 'DropdownController@poTypes',
        ]);
        $api->get('/bin-locations', [
            'uses' => 'DropdownController@binLocs',
        ]);
        $api->get('/customers', [
            'uses' => 'DropdownController@customers',
        ]);
        $api->get('/user-by-whs', [
            'uses' => 'DropdownController@userByWhs',
        ]);
    });

    $api->group(['prefix' => '/autocomplete'], function ($api) {
        $api->get('/items', [
            'uses' => 'AutocompleteController@items',
        ]);
    });

    $api->group(['prefix' => '/status'], function ($api) {
        $api->get('/{sts_type}', [
            'uses' => 'StatusController@getAll',
        ]);
    });

});
