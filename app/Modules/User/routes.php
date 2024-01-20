<?php

$api->group([
    'prefix' => 'user',
    'namespace' => 'App\Modules\User\Controllers'
], function ($api) {

    $api->get('/', [
        'as' => '',
        'uses' => 'UserController@view',
    ]);

    $api->get('/{id:[0-9]+}', [
        'as' => '',
        'uses' => 'UserController@show',
    ]);

    $api->post('/', [
        'as' => '',
        'uses' => 'UserController@store',
    ]);

    $api->put('/{id:[0-9]+}', [
        'as' => '',
        'uses' => 'UserController@update',
    ]);

    $api->delete('/{id:[0-9]+}', [
        'as' => '',
        'uses' => 'UserController@delete',
    ]);

    $api->post('/import', [
        'as' => '',
        'uses' => 'UserController@import',
    ]);

    $api->get('/stats', [
        'as' => '',
        'uses' => 'UserController@getStats',
    ]);

});
