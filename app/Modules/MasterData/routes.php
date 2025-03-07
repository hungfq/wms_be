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
        $api->get('/item-categories', [
            'uses' => 'DropdownController@itemCategory',
        ]);
        $api->get('/uoms', [
            'uses' => 'DropdownController@uom',
        ]);
        $api->get('/zone', [
            'uses' => 'DropdownController@zone',
        ]);
        $api->get('/loc-types', [
            'uses' => 'DropdownController@locType',
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
        $api->get('/{tpId:[0-9]+}', [
            'uses' => 'ThirdPartyController@show',
        ]);
        $api->put('/{tpId}', [
            'uses' => 'ThirdPartyController@update',
        ]);
        $api->get('/{tpId:[0-9]+}/wallet', [
            'uses' => 'ThirdPartyController@viewWallet',
        ]);
        $api->put('/{tpId:[0-9]+}/wallet', [
            'uses' => 'ThirdPartyController@updateWallet',
        ]);
    });

    $api->group(['prefix' => '/items'], function ($api) {
        $api->get('/', [
            'uses' => 'ItemController@view',
        ]);
        $api->post('/', [
            'uses' => 'ItemController@store',
        ]);
        $api->get('/{itemId:[0-9]+}', [
            'uses' => 'ItemController@show',
        ]);
        $api->put('/{itemId:[0-9]+}', [
            'uses' => 'ItemController@update',
        ]);
    });

    $api->group(['prefix' => '/uom'], function ($api) {
        $api->get('/', [
            'uses' => 'UomController@view',
        ]);
        $api->post('/', [
            'uses' => 'UomController@store',
        ]);
        $api->get('/{id:[0-9]+}', [
            'uses' => 'UomController@show',
        ]);
        $api->put('/{id:[0-9]+}', [
            'uses' => 'UomController@update',
        ]);
    });

    $api->group(['prefix' => '/roles'], function ($api) {
        $api->get('/', [
            'uses' => 'RoleController@view',
        ]);
    });

    $api->group(['prefix' => '/users'], function ($api) {
        $api->get('/', [
            'uses' => 'UserController@view',
        ]);
        $api->post('/', [
            'uses' => 'UserController@store',
        ]);
        $api->get('/{userId:[0-9]+}', [
            'uses' => 'UserController@show',
        ]);
        $api->put('/{userId:[0-9]+}', [
            'uses' => 'UserController@update',
        ]);
    });

    $api->group(['prefix' => '/warehouses/{whsId:[0-9]+}/replenishment-config'], function ($api) {
        $api->get('/', [
            'uses' => 'ReplenishmentConfigController@view',
        ]);
        $api->post('/', [
            'uses' => 'ReplenishmentConfigController@store',
        ]);
        $api->get('/{reId:[0-9]+}', [
            'uses' => 'ReplenishmentConfigController@show',
        ]);
        $api->put('/{reId:[0-9]+}', [
            'uses' => 'ReplenishmentConfigController@update',
        ]);
        $api->delete('/{reId:[0-9]+}', [
            'uses' => 'ReplenishmentConfigController@delete',
        ]);
    });

});
