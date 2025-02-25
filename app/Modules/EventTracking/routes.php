<?php

$api->group([
    'prefix' => 'event-trackings',
    'namespace' => '\App\Modules\EventTracking\Controllers',
], function ($api) {

    $api->get('/', [
        'uses' => 'EventTrackingController@viewTmp',
    ]);

});
