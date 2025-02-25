<?php

$api->group([
    'prefix' => 'notification',
    'namespace' => 'App\Modules\Notification\Controllers'
], function ($api) {

    $api->get('/', [
        'as' => '',
        'uses' => 'NotificationController@view',
    ]);

    $api->put('/{id:[0-9]+}', [
        'as' => '',
        'uses' => 'NotificationController@read',
    ]);

    $api->delete('/{id:[0-9]+}', [
        'as' => '',
        'uses' => 'NotificationController@delete',
    ]);

});
