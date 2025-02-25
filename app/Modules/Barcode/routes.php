<?php

$api->group([
    'prefix' => 'barcode',
    'namespace' => '\App\Modules\Barcode\Controllers',

], function ($api) {

//    $api->group(['prefix' => 'scan'], function ($api) {
//        $api->post('/', [
//            'as' => 'barcode.Goods-receipt.receive-carton',
//            'uses' => 'BarcodeInboundController@scanCarton',
//        ]);
//
//        $api->delete('/pallet/{pltId:[0-9]+}', [
//            'uses' => 'BarcodeInboundController@removePalletScanned',
//        ]);
//    });
//
//    $api->group(['prefix' => 'put-away'], function ($api) {
//
//        $api->get('/suggest-location', [
//            'as' => 'barcode.Goods-receipt.receive-carton',
//            'uses' => 'BarcodeInboundController@putAwaySuggestLocation',
//        ]);
//
//        $api->post('/pallet', [
//            'as' => 'barcode.Goods-receipt.receive-carton',
//            'uses' => 'BarcodeInboundController@putAwayByPallet',
//        ]);
//
//        $api->get('/history', [
//            'uses' => 'BarcodeInboundController@viewPutAwayHistory',
//        ]);
//
//        $api->delete('/pallet/{pltId:[0-9]+}', [
//            'uses' => 'BarcodeInboundController@removePalletLocation',
//        ]);
//    });

    $api->group(['prefix' => 'warehouses/{whsId:[0-9]+}'], function ($api) {

        $api->group(['prefix' => 'receive'], function ($api) {

            $api->post('/', [
                'as' => '',
                'uses' => 'BarcodeInboundController@receiveCarton',
            ]);

//        $api->get('/suggest-location', [
//            'as' => 'barcode.Goods-receipt.receive-carton',
//            'uses' => 'BarcodeInboundController@receiveSuggestLocation',
//        ]);
//
//        $api->get('/location', [
//            'uses' => 'BarcodeInboundController@viewReceivedLocation',
//        ]);
//
//        $api->delete('/location/{locId:[0-9]+}', [
//            'uses' => 'BarcodeInboundController@removeReceivedLocation',
//        ]);
        });
    });

});
