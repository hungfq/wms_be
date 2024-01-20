<?php

$api->group([
    'prefix' => '/inbound',
    'namespace' => '\App\Modules\Inbound\Controllers'
], function ($api) {

    $api->group(['prefix' => '/po',], function ($api) {
        $api->group(['prefix' => '/dropd',], function ($api) {
            $api->get('/all-messages', [
                'uses' => 'LanguageController@getAll',
            ]);
        });
    });


});
