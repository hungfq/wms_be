<?php

$api->group([
    'prefix' => 'auth',
    'namespace' => 'Modules\Auth\Controllers'
], function ($api) {

    $api->post('/login', [
        'as' => '',
        'uses' => 'AuthController@loginWithUserPass',
    ]);

    $api->group([
        'middleware' => ['auth']
    ], function ($api) {
        $api->get('/profile', [
            'as' => '',
            'uses' => 'AuthController@getProfile',
        ]);

        $api->post('/profile', [
            'as' => '',
            'uses' => 'AuthController@updateProfile',
        ]);

        $api->post('/info', [
            'as' => '',
            'uses' => 'AuthController@setInfo',
        ]);

        $api->get('/permissions', [
            'as' => '',
            'uses' => 'AuthController@getPermissions',
        ]);

        $api->get('/logout', [
            'as' => '',
            'uses' => 'AuthController@logout',
        ]);
    });
});
