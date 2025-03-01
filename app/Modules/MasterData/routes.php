<?php

$api->group([
    'prefix' => '/master-data',
    'namespace' => '\App\Modules\MasterData\Controllers'
], function ($api) {

    $api->group(['prefix' => '/languages'], function ($api) {
        $api->get('/all-messages', [
            'uses' => 'LanguageController@getAll',
        ]);
        $api->get('/language-types', [
            'uses' => 'LanguageController@languageType',
        ]);
        $api->get('/', [
            'uses' => 'LanguageController@view',
        ]);
        $api->post('/', [
            'uses' => 'LanguageController@store',
        ]);
        $api->put('/{lgId:[0-9]+}', [
            'uses' => 'LanguageController@update',
        ]);
        $api->delete('/', [
            'uses' => 'LanguageController@delete',
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
        $api->put('/', [
            'uses' => 'MenuController@update'
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
        $api->get('/country', [
            'uses' => 'DropdownController@country',
        ]);
        $api->get('/state', [
            'uses' => 'DropdownController@state',
        ]);
        $api->get('/order-types', [
            'uses' => 'DropdownController@orderTypes',
        ]);
        $api->get('/departments', [
            'uses' => 'DropdownController@department',
        ]);
    });

    $api->group(['prefix' => '/autocomplete'], function ($api) {
        $api->get('/items', [
            'uses' => 'AutocompleteController@items',
        ]);
        $api->get('/locations', [
            'uses' => 'AutocompleteController@location',
        ]);
        $api->get('/third-parties', [
            'uses' => 'AutocompleteController@thirdParty',
        ]);
    });

    $api->group(['prefix' => '/status'], function ($api) {
        $api->get('/{sts_type}', [
            'uses' => 'StatusController@getAll',
        ]);
    });

    $api->group(['prefix' => '/third-party'], function ($api) {
        $api->get('/', [
            'uses' => 'ThirdPartyController@view',
        ]);
        $api->post('/', [
            'uses' => 'ThirdPartyController@store',
        ]);
        $api->get('/{tpId}', [
            'uses' => 'ThirdPartyController@show',
        ]);
        $api->put('/{tpId}', [
            'uses' => 'ThirdPartyController@update',
        ]);
    });

});
