<?php

/** @var  $api noinspection Php **/

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {

    $api->group([
        'prefix' => 'v1',
        'middleware' => ['logApi'],
        'namespace' => 'App'
    ], function ($api) {
        $api->get('/test', function () {
            return ['data' => 'API OK!'];
        });

        $api->get('/test-socket', [
            'as' => '',
            'uses' => 'Http\Controllers\TestDataController@testSocket',
        ]);

        $api->post('/test-api', [
            'as' => '',
            'uses' => 'Http\Controllers\TestDataController@testApi',
        ]);

        $api->post('/test-mail', [
            'as' => '',
            'uses' => 'Http\Controllers\TestDataController@testMail',
        ]);

        // authentication
        require_once base_path("app/Modules/Auth/routes.php");

//        $api->post('/login', [
//            'as' => '',
//            'uses' => 'Modules\Auth\Controllers\AuthController@loginWithGoogleAccessToken',
//        ]);
//
//        $api->get('/template', [
//            'as' => '',
//            'uses' => 'Modules\Template\Controllers\TemplateController@download',
//        ]);
    });

    $api->group([
        'prefix' => 'v1',
        'middleware' => ['auth', 'logApi']
    ], function ($api) {
        $api->get('/', function ($api) {
            return ['data' => 'Token OK!'];
        });

        $modules = [
            'Notification',
            'Documents',
            'MasterData',
            'Inbound',
            'Api',
            'EventTracking',
            'Barcode',
            'Inventory',
            'Outbound',
            'WhsConfig',
        ];

        foreach ($modules as $module) {
            require_once base_path("app/Modules/{$module}/routes.php");
        }
    });
});
