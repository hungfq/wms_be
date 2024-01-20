<?php

$api->group([
    'prefix' => '/documents',
    'namespace' => '\App\Modules\Documents\Controllers'
], function ($api) {

    $api->get('/', [
        'uses' => 'DocumentController@index',
    ]);

    $api->get('/{docId:[0-9]+}', [
        'uses' => 'DocumentController@show',
    ]);

    $api->get('/{docId:[0-9]+}/download', [
        'uses' => 'DocumentController@download',
    ]);

    $api->post('/', [
        'uses' => 'DocumentController@store',
    ]);

    $api->delete('/', [
        'uses' => 'DocumentController@delete',
    ]);

    $api->get('/zip', [
        'uses' => 'DocumentController@downloadZip',
    ]);
});
