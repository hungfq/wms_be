<?php

namespace App\Modules\Outbound\Actions;

use App\Entities\BinLocation;
use App\Entities\Carton;
use App\Entities\Item;
use App\Entities\Location;
use App\Entities\OdrCarton;
use App\Entities\OrderDtl;
use App\Entities\OrderHdr;
use App\Entities\Pallet;
use App\Entities\Warehouse;
use App\Entities\WvDtl;
use App\Entities\WvHdr;
use App\Exceptions\UserException;
use App\Libraries\Config;
use App\Libraries\Data;
use App\Libraries\Helpers;
use App\Libraries\Language;
use App\Modules\Outbound\DTO\WavePickPickingDTO;

class WavePickPickingAction
{
    const MULTIPLE_ORDERS = 1;
    const FIRST_BIN_LOCATION = 'L1';

    public WavePickPickingDTO $dto;
    public $input;
    public $user;
    public $wvHdr;
    public $wvDtl;
    public $item;
    public $odrDtls;
    public $cartons;
    public $pallet;
    public $location;
    public $odrCartons = [];
    public $pickingResults = [];
    public $pltIds = [];

    public $currentCarton;
    public $currentPickQty;

    public $algorithm;
    public $actPickedQty;
    public $events;
    protected $multiPickingQty;
    protected $firstBinLoc;

    /**
     * @param WavePickPickingDTO
     */
    public function handle($dto)
    {
        $this->dto = $dto;
        $this->user = Data::getCurrentUser();
        $this->algorithm = 'FIFO';

        $this->validateDataInput();

        foreach ($this->dto->location_picks as $location_pick) {
            $this->input = $location_pick;

            $this->validateLocationAndPallet()
                ->validatePickQty()
                ->getOrderDetails()
                ->getCartons();

            if ($this->odrDtls->count() > self::MULTIPLE_ORDERS) {
                $this->makePickingCartonNonSerialMultipleOrder()
                    ->createOdrCartonsAndUpdateOrderItemNonSerialMultipleOrder()
                    ->updateWvDtl()
                    ->createWvDtlLocations();
            } else {
                $this->makePickingCartonNonSerial()
                    ->createOdrCartonsAndUpdateOrderItemNonSerial()
                    ->updateWvDtl()
                    ->createWvDtlLocations();
            }
        }

        $this->clearPltRfidAndLocationIfEmpty()
            ->eventTracking();
    }

    public function validateDataInput()
    {
        $warehouse = Warehouse::query()->find($this->dto->whs_id);

        if (!$warehouse) {
            throw new UserException(Language::translate('Warehouse not found'));
        }

        $this->wvHdr = WvHdr::query()->where([
            'whs_id' => $this->dto->whs_id,
            'id' => $this->dto->wv_hdr_id,
        ])->first();

        if (!$this->wvHdr) {
            throw new UserException(Language::translate('Wave Pick Hdr does not exist'));
        }

        $pickers = $this->wvHdr->pickers;
        if ($pickers->count()) {
            $existsPicker = $this->wvHdr->pickers()
                ->where('picker_id', Data::getCurrentUser()->id)
                ->exists();

            if (!$existsPicker) {
                throw new UserException(Language::translate('This WavePick has not been assigned to you yet'));
            }
        }

        $this->wvDtl = $this->wvHdr->wvDtls()->where('id', $this->dto->wv_dtl_id)->first();

        if (!$this->wvDtl) {
            throw new UserException(Language::translate('Wave Pick Detail does not exist'));
        }

        if (!in_array($this->wvDtl->wv_dtl_sts, [WvDtl::STS_NEW, WvDtl::STS_PICKING])) {
            throw new UserException(
                Language::translate('Wave Pick Detail Status must be {0} or {1}',
                    Language::translate(Config::getStatusName('WV_DTL_STATUS', WvDtl::STS_NEW)),
                    Language::translate(Config::getStatusName('WV_DTL_STATUS', WvDtl::STS_PICKING))
                )
            );
        }

        $this->item = $this->wvDtl->item;

        return $this;
    }

    protected function validateLocationAndPallet()
    {
        $locCode = data_get($this->input, 'loc_code');

        $this->location = Location::where([
            'loc_code' => $locCode,
            'whs_id' => $this->dto->whs_id
        ])
            ->first();

        if (!$this->location) {
            throw new UserException(Language::translate('Location {0} does not exist', $locCode));
        }

        if ($this->location->loc_sts == Location::LOCATION_STATUS_LOCKED) {
            throw new UserException(Language::translate(
                "Actual Location {0} is locked. So, you can't pick up at this location",
                $locCode
            ));
        }

        if ($this->location->loc_sts != Location::LOCATION_STATUS_ACTIVE) {
            $sts = Language::translate(Config::getStatusName('LOCATION_STATUS', Location::LOCATION_STATUS_ACTIVE));
            throw new UserException(Language::translate('Location Status must be {0}', $sts));
        }

        if (!$this->location->zone_id) {
            throw new UserException(Language::translate('Location is not assign to zone'));
        }

        $this->pallet = null;
        $pltNum = data_get($this->input, 'plt_num');

        if ($this->location->goods_type == Location::GOODS_TYPE_WHOLESALE && !isset($pltNum)) {
            throw new UserException(Language::translate('Wholesale Location must be scan pallet'));
        }

        if ($pltNum) {
            $this->pallet = Pallet::where(['rfid' => $pltNum, 'whs_id' => $this->dto->whs_id])->first();

            if (!$this->pallet) {
                throw new UserException(Language::translate('Pallet {0} does not exist', $pltNum));
            }

            if ($this->pallet->plt_sts != Pallet::STS_ACTIVE) {
                $sts = Language::translate(Config::getStatusName('PALLET_STATUS', Pallet::STS_ACTIVE));
                throw new UserException(Language::translate('Pallet Status must be {0}', $sts));
            }

            if ($this->location->loc_id != $this->pallet->loc_id) {
                throw new UserException(Language::translate(
                    'Pallet {0} is not located at {1}',
                    $pltNum,
                    $locCode
                ));
            }
        }

        return $this;
    }

    public function getOrderDetails()
    {
        $this->wvHdr->load([
            'odrHdrs' => function ($q) {
                $q->whereIn('odr_sts', [OrderHdr::STS_PICKING, OrderHdr::STS_OUT_SORTING]);
                $q->orderBy('ship_by_dt', 'ASC');
                $q->orderBy('id', 'ASC');
            },
            'odrHdrs.orderDtls' => function ($q) {
                $q->where([
                    'item_id' => $this->wvDtl->item_id,
                    'bin_loc_id' => $this->wvDtl->bin_loc_id,
                    'lot' => $this->wvDtl->lot,
                ])
                    ->whereIn('odr_dtl_sts', [OrderDtl::STS_NEW, OrderDtl::STS_PICKING, OrderHdr::STS_OUT_SORTING]);

                if ($this->wvDtl->lot != Config::ANY) {
                    $q->whereRaw('picked_qty < alloc_qty');
                } else {
                    $q->whereRaw('picked_qty < piece_qty');
                }
            }
        ]);

        $this->odrDtls = $this->wvHdr->odrHdrs->pluck('orderDtls')->flatten()->values();

        if (!$this->odrDtls->count()) {
            throw new UserException(Language::translate('Order Detail does not exist!'));
        }

        return $this;
    }

    public function getCartons()
    {
        $pickingQty = data_get($this->input, 'qty');
        $remainQty = $this->wvDtl->piece_qty - $this->wvDtl->picked_qty;

        if ((int)$pickingQty > (int)$remainQty) {
            throw new UserException(Language::translate(
                'Total qty pick must be less or equal than pick remain QTY {0}',
                $remainQty
            ));
        }

        $this->wvDtl->load([
            'cartons' => function ($q) use ($pickingQty) {
                $q->filterByWvDtl($this->wvDtl)
                    ->orderBy('ctn_ttl', 'ASC')
                    ->orderBy('piece_remain', 'ASC')
                    ->where('loc_id', $this->location->loc_id);

                if ($this->pallet) {
                    $q->where('plt_id', $this->pallet->plt_id);
                }
            }
        ]);

        $this->cartons = $this->wvDtl->cartons;

        $pickAbleQty = $this->wvDtl->cartons->reduce(function ($total, $ctn) {
            return $total += ($ctn->piece_init * $ctn->ctn_ttl + $ctn->piece_remain);
        }, 0);

        if ($pickingQty > $pickAbleQty) {
            throw new UserException(Language::translate(
                'Total picked qty must be less or equal than pick remain qty in inventory'
            ));
        }

        if (!$this->cartons->count()) {
            throw new UserException(Language::translate(
                'The Pickable QTY of the suggested location has changed. Please reload.',
            ));
        }

        return $this;
    }

    public function makePickingCartonNonSerialMultipleOrder()
    {
        if ($this->item->serial == Item::SERIAL) {
            return $this;
        }

        $this->multiPickingQty = (int)data_get($this->input, 'qty');
        $this->actPickedQty = [];
        $this->odrDtls->each(function ($odrDtl) {
            $this->actPickedQty[$odrDtl->id] = 0;
        });

        $this->pickingResults = [];

        $this->currentCarton = $this->cartons->shift();

        $this->odrDtls->each(function ($odrDtl) {
            if ($this->multiPickingQty <= 0) {
                return;
            }
            if ($odrDtl->piece_qty == $odrDtl->picked_qty) {
                return;
            }

            $this->currentPickQty = $odrDtl->picked_qty;

            if (data_get($this->currentCarton, 'ctn_sts') != Carton::STS_ACTIVE) {
                $carton = $this->cartons->shift();
                $this->currentCarton = $carton;
            } else {
                $carton = $this->currentCarton;
            }

            $this->pickCarton($carton, $odrDtl);
        });

        if ($this->multiPickingQty > 0) {
            throw new UserException(Language::translate('The location is not enough goods. Please check pick qty!'));
        }

        return $this;
    }

    protected function pickCarton($carton, $odrDtl)
    {
        // HOT FIX RE-CHECK
        if (!$carton) {
            return;
        }

        if ($this->multiPickingQty <= 0) {
            return;
        }

        if ($odrDtl->piece_qty == $odrDtl->picked_qty) {
            return;
        }

        $pieceInCarton = ($carton->piece_init * $carton->ctn_ttl) + $carton->piece_remain;

        $odrDtlPieceQty = $odrDtl->piece_qty - $this->currentPickQty;
        $pickQty = min($odrDtlPieceQty, $this->multiPickingQty);

        if ($pieceInCarton > $pickQty) {
            $remainQty = (int)$pieceInCarton - (int)$pickQty;
            $cartonCalc = Helpers::calculateCartonQty($remainQty, $carton->piece_init);
            $carton->ctn_ttl = data_get($cartonCalc, 'ctn_ttl');
            $carton->piece_remain = data_get($cartonCalc, 'piece_remain');
            $carton->save();

            $pickedCarton = $carton->replicate();
            $pickedCartonCalc = Helpers::calculateCartonQty($pickQty, $carton->piece_init);
            $pickedCarton->ctn_ttl = data_get($pickedCartonCalc, 'ctn_ttl');
            $pickedCarton->piece_remain = data_get($pickedCartonCalc, 'piece_remain');
            $pickedCarton->ctn_sts = Carton::STS_PICKED;
            $pickedCarton->save();

            $this->pickingResults[$odrDtl->id . '-' . $pickedCarton->ctn_id] = [
                'carton' => $pickedCarton,
                'ctn_id' => $pickedCarton->ctn_id,
                'loc_id' => $pickedCarton->loc_id,
                'plt_id' => $pickedCarton->plt_id,
                'current_qty' => (int)$pickQty,
                'picked_qty' => (int)$pickQty,
                'remain_qty' => 0,
            ];


            $this->actPickedQty[$odrDtl->id] += $odrDtlPieceQty;
            $this->multiPickingQty -= $pickQty;
            return;
        } else if ($pieceInCarton == $pickQty) {
            $carton->update([
                'ctn_sts' => Carton::STS_PICKED
            ]);

            $this->pickingResults[$odrDtl->id . '-' . $carton->ctn_id] = [
                'carton' => $carton,
                'ctn_id' => $carton->ctn_id,
                'loc_id' => $carton->loc_id,
                'plt_id' => $carton->plt_id,
                'current_qty' => (int)$pieceInCarton,
                'picked_qty' => (int)$pickQty,
                'remain_qty' => 0,
            ];

            $this->actPickedQty[$odrDtl->id] += $pickQty;
            $this->multiPickingQty -= $pickQty;
            return;
        }

        $carton->update([
            'ctn_sts' => Carton::STS_PICKED
        ]);

        $this->currentPickQty += $pieceInCarton;

        $this->pickingResults[$odrDtl->id . '-' . $carton->ctn_id] = [
            'carton' => $carton,
            'ctn_id' => $carton->ctn_id,
            'loc_id' => $carton->loc_id,
            'plt_id' => $carton->plt_id,
            'current_qty' => (int)$pieceInCarton,
            'picked_qty' => (int)$pieceInCarton,
            'remain_qty' => (int)0,
        ];

        $this->actPickedQty[$odrDtl->id] += $pieceInCarton;
        $this->multiPickingQty -= $pieceInCarton;

        $this->currentCarton = $this->cartons->shift();

        $this->pickCarton($this->currentCarton, $odrDtl);
    }

    public function makePickingCartonNonSerial()
    {
        if ($this->item->serial == Item::SERIAL) {
            return $this;
        }

        $this->actPickedQty = 0;
        $this->pickingResults = [];

        $pickingQty = data_get($this->input, 'qty');

        foreach ($this->cartons as $index => $carton) {
            if ($pickingQty <= 0) {
                break;
            }

            $index = $this->cartons->search($carton);
            $this->cartons->forget($index);

            $pieceInCarton = ($carton->piece_init * $carton->ctn_ttl) + $carton->piece_remain;

            if ($pieceInCarton > $pickingQty) {
                $this->pickingResults[] = [
                    'carton' => $carton,
                    'ctn_id' => $carton->ctn_id,
                    'loc_id' => $carton->loc_id,
                    'plt_id' => $carton->plt_id,
                    'current_qty' => (int)$pieceInCarton,
                    'picked_qty' => (int)$pickingQty,
                    'remain_qty' => (int)$pieceInCarton - $pickingQty,
                ];

                $pickingQty = 0;
            } else {
                $this->pickingResults[] = [
                    'carton' => $carton,
                    'ctn_id' => $carton->ctn_id,
                    'loc_id' => $carton->loc_id,
                    'plt_id' => $carton->plt_id,
                    'current_qty' => (int)$pieceInCarton,
                    'picked_qty' => (int)$pieceInCarton,
                    'remain_qty' => 0
                ];

                $pickingQty -= $pieceInCarton;
            }
        }

        foreach ($this->pickingResults as $index => $pickingResult) {
            $carton = data_get($pickingResult, 'carton');
            if (isset($carton['in_loc_qty'])) {
                unset($carton['in_loc_qty']);
            }

            $pickedQty = data_get($pickingResult, 'picked_qty');
            $remainQty = data_get($pickingResult, 'remain_qty');
            $this->actPickedQty += $pickedQty;

            if ($remainQty == 0) {
                $carton->update([
                    'ctn_sts' => Carton::STS_PICKED,
                ]);
            }

            if ($remainQty > 0) {
                $newCarton = $carton->replicate();
                $newCartonCalc = Helpers::calculateCartonQty($remainQty, $newCarton->piece_init);
                $newCarton->fill([
                    'ctn_ttl' => data_get($newCartonCalc, 'ctn_ttl'),
                    'piece_remain' => data_get($newCartonCalc, 'piece_remain'),
                ])->save();

                $cartonCalc = Helpers::calculateCartonQty($pickedQty, $carton->piece_init);
                $carton->update([
                    'ctn_ttl' => data_get($cartonCalc, 'ctn_ttl'),
                    'piece_remain' => data_get($cartonCalc, 'piece_remain'),
                    'ctn_sts' => Carton::STS_PICKED,
                ]);
            }

            $this->pltIds[] = data_get($carton, 'plt_id');
        }

        return $this;
    }

    protected function createOdrCartonsAndUpdateOrderItemNonSerialMultipleOrder()
    {
        if ($this->item->serial == Item::SERIAL) {
            return $this;
        }

        $this->odrCartons = [];
        $passedOdrDtlIds = [];
        $currentTime = date('Y-m-d H:i:s');
        $currentUser = Data::getCurrentUser();
        $invtItemLot = [];


        foreach ($this->pickingResults as $key => $pickingResult) {
            $dtlId = explode('-', $key)[0];
            $pickedQty = data_get($pickingResult, 'picked_qty');
            $carton = data_get($pickingResult, 'carton');

            if ($pickedQty <= 0) {
                continue;
            }

            $odrDtl = $this->odrDtls->where('id', $dtlId)->first();

            if ($pickedQty <= 0) {
                break;
            }

            if (in_array($odrDtl->id, $passedOdrDtlIds)) {
                continue;
            }

            $pick_remain = $odrDtl->alloc_qty - $odrDtl->picked_qty;

            if ($pick_remain <= 0) {
                continue;
            }

            $item = $odrDtl->item;
            $evtQty = $pickedQty;

            if ($pick_remain > $pickedQty) {
                $odrDtl->picked_qty += $pickedQty;
                $pickedQty = 0;
            } else {
                $odrDtl->picked_qty += $pick_remain;
                $evtQty = $pick_remain;
                $pickedQty -= $pick_remain;
            }

            if (!isset($invtItemLot[$item->item_id . '-' . $odrDtl->lot . '-' . $odrDtl->bin_loc_id . '-' . $odrDtl->id])) {
                $invtItemLot[$item->item_id . '-' . $odrDtl->lot . '-' . $odrDtl->bin_loc_id . '-' . $odrDtl->id] = 0;
            }

            $invtItemLot[$item->item_id . '-' . $odrDtl->lot . '-' . $odrDtl->bin_loc_id . '-' . $odrDtl->id] += $evtQty;

            $this->odrCartons[] = [
                'whs_id' => $odrDtl->whs_id,
                'cus_id' => $odrDtl->cus_id,
                'odr_hdr_id' => $odrDtl->odr_id,
                'odr_dtl_id' => $odrDtl->id,
                'bin_loc_id' => $odrDtl->bin_loc_id,
                'wv_hdr_id' => $this->wvDtl->wv_hdr_id,
                'wv_dtl_id' => $this->wvDtl->id,
                'loc_id' => $carton->loc_id,
                'plt_id' => $carton->plt_id,
                'ctn_id' => $carton->ctn_id,
                'picked_qty' => $evtQty,
                'created_at' => $currentTime,
                'created_by' => $currentUser->id,
                'updated_at' => $currentTime,
                'updated_by' => $currentUser->id
            ];

//            $odrHdr = $odrDtl->order;

            $odrDtl->wv_id = $this->wvDtl->wv_hdr_id;

            if ($odrDtl->picked_qty >= $odrDtl->alloc_qty) {
                $odrDtl->odr_dtl_sts = OrderDtl::STS_PICKED;
                $passedOdrDtlIds[] = $odrDtl->id;
            } else {
                $odrDtl->odr_dtl_sts = OrderDtl::STS_PICKING;
            }
        }

//        $this->events[] = [
//            'cus_id' => data_get($odrHdr, 'cus_id'),
//            'owner' => data_get($odrHdr, 'odr_num'),
//            'transaction' => data_get($odrHdr, 'cus_odr_num'),
//            'event_code' => EventTracking::GUN_ORDER_PICKING,
//            'info' => '{0}, Batch {1} have been picked from location {2}',
//            'info_params' => [
//                $this->item->sku,
//                $this->wvDtl->lot,
//                $this->location->loc_code
//            ],
//        ];

        foreach ($this->odrCartons as $odrCtn) {
            OdrCarton::query()->create($odrCtn);
        }

        $this->odrDtls->each(function ($odrDtl) use ($invtItemLot) {
            if ($odrDtl->isDirty()) {
                $invt = $odrDtl->getInventory;

                if (isset($invtItemLot[$odrDtl->item_id . '-' . $odrDtl->lot . '-' . $odrDtl->bin_loc_id . '-' . $odrDtl->id])) {
                    $invtQty = $invtItemLot[$odrDtl->item_id . '-' . $odrDtl->lot . '-' . $odrDtl->bin_loc_id . '-' . $odrDtl->id];

                    if ($this->actPickedQty[$odrDtl->id] > ($invt->avail_qty + $invt->alloc_qty)) {
                        throw new UserException(Language::translate(
                            'Total picked qty must be less or equal than pick remain qty in inventory'
                        ));
                    }

                    $invt->alloc_qty -= $invtQty;
                    $invt->picked_qty += $invtQty;

                    //Set damage out sort
                    if ($invt->alloc_qty < 0) {
                        $alloc = abs($invt->alloc_qty);
                        $invt->alloc_qty = 0;
                        $invt->avail_qty -= $alloc;
                    }

                    $invt->save();
                }

                $odrDtl->save();

                $odrHdr = $odrDtl->order;
                $countPickedOdrDtl = $odrHdr->orderDtls()
                    ->whereNotIn('odr_dtl_sts', [Config::getStatusCode('ORDER_DTL_STATUS', 'Cancelled')])
                    ->whereIn('odr_dtl_sts', [OrderDtl::STS_PICKED, OrderDtl::STS_OUT_SORTING, OrderDtl::STS_OUT_SORTED])
                    ->count();
                $countOdrDtl = $odrHdr->orderDtls()->whereNotIn('odr_dtl_sts', [Config::getStatusCode('ORDER_DTL_STATUS', 'Cancelled')])->count();
                if ($countPickedOdrDtl == $countOdrDtl) {
                    $odrHdr->update([
                        'odr_sts' => OrderDtl::STS_PICKED
                    ]);

//                    $this->events[] = [
//                        'cus_id' => $odrHdr->cus_id,
//                        'owner' => $odrHdr->odr_num,
//                        'transaction' => $odrHdr->cus_odr_num,
//                        'event_code' => EventTracking::GUN_ORDER_PICKED,
//                        'info' => 'Order {0} picked by {1}',
//                        'info_params' => [$odrHdr->odr_num, $this->wvHdr->wv_hdr_num],
//                    ];
                }
            }
        });

        return $this;
    }

    protected function createOdrCartonsAndUpdateOrderItemNonSerial()
    {
        if ($this->item->serial == Item::SERIAL) {
            return $this;
        }

        $this->odrCartons = [];
        $passedOdrDtlIds = [];
        $currentTime = date('Y-m-d H:i:s');
        $currentUser = Data::getCurrentUser();
        $invtItemLot = [];

        foreach ($this->pickingResults as $pickingResult) {
            $pickedQty = data_get($pickingResult, 'picked_qty');
            $carton = data_get($pickingResult, 'carton');

            if ($pickedQty <= 0) {
                continue;
            }

            foreach ($this->odrDtls as $odrDtl) {
                if ($pickedQty <= 0) {
                    break;
                }

                if (in_array($odrDtl->id, $passedOdrDtlIds)) {
                    continue;
                }

                $pick_remain = $odrDtl->alloc_qty - $odrDtl->picked_qty;

                if ($pick_remain <= 0) {
                    continue;
                }

                $item = $odrDtl->item;
                $evtQty = $pickedQty;

                if ($pick_remain > $pickedQty) {
                    $odrDtl->picked_qty += $pickedQty;
                    $pickedQty = 0;
                } else {
                    $odrDtl->picked_qty += $pick_remain;
                    $evtQty = $pick_remain;
                    $pickedQty -= $pick_remain;
                }

                if (!isset($invtItemLot[$item->item_id . '-' . $odrDtl->lot . '-' . $odrDtl->bin_loc_id . '-' . $odrDtl->id])) {
                    $invtItemLot[$item->item_id . '-' . $odrDtl->lot . '-' . $odrDtl->bin_loc_id . '-' . $odrDtl->id] = 0;
                }

                $invtItemLot[$item->item_id . '-' . $odrDtl->lot . '-' . $odrDtl->bin_loc_id . '-' . $odrDtl->id] += $evtQty;

                $this->odrCartons[] = [
                    'whs_id' => $odrDtl->whs_id,
                    'cus_id' => $odrDtl->cus_id,
                    'odr_hdr_id' => $odrDtl->odr_id,
                    'odr_dtl_id' => $odrDtl->id,
                    'bin_loc_id' => $odrDtl->bin_loc_id,
                    'wv_hdr_id' => $this->wvDtl->wv_hdr_id,
                    'wv_dtl_id' => $this->wvDtl->id,
                    'loc_id' => $carton->loc_id,
                    'plt_id' => $carton->plt_id,
                    'ctn_id' => $carton->ctn_id,
                    'picked_qty' => $evtQty,
                    'created_at' => $currentTime,
                    'created_by' => $currentUser->id,
                    'updated_at' => $currentTime,
                    'updated_by' => $currentUser->id
                ];

//                $odrHdr = $odrDtl->order;

//                $this->events[] = [
//                    'cus_id' => $odrHdr->cus_id,
//                    'owner' => $odrHdr->odr_num,
//                    'transaction' => $odrHdr->cus_odr_num,
//                    'event_code' => EventTracking::GUN_ORDER_PICKING,
//                    'info' => '{0} {1}, Batch {2} have been picked from location {3}',
//                    'info_params' => [$evtQty, $item->sku, $odrDtl->lot, $this->location->loc_code],
//                ];

                $odrDtl->wv_id = $this->wvDtl->wv_hdr_id;

                if ($odrDtl->picked_qty >= $odrDtl->alloc_qty) {
                    $odrDtl->odr_dtl_sts = OrderDtl::STS_PICKED;
                    $passedOdrDtlIds[] = $odrDtl->id;
                } else {
                    $odrDtl->odr_dtl_sts = OrderDtl::STS_PICKING;
                }
            }
        }

        foreach ($this->odrCartons as $odrCtn) {
            OdrCarton::query()->create($odrCtn);
        }

//        $this->events[] = [
//            'cus_id' => data_get($odrHdr, 'cus_id'),
//            'owner' => data_get($odrHdr, 'odr_num'),
//            'transaction' => data_get($odrHdr, 'cus_odr_num'),
//            'event_code' => EventTracking::GUN_ORDER_PICKING,
//            'info' => '{0}, Batch {1} have been picked from location {2}',
//            'info_params' => [
//                $this->item->sku,
//                $this->wvDtl->lot,
//                $this->location->loc_code
//            ],
//        ];

        $this->odrDtls->each(function ($odrDtl) use ($invtItemLot) {
            if ($odrDtl->isDirty()) {
                $invt = $odrDtl->getInventory;

                if (isset($invtItemLot[$odrDtl->item_id . '-' . $odrDtl->lot . '-' . $odrDtl->bin_loc_id . '-' . $odrDtl->id])) {
                    $invtQty = $invtItemLot[$odrDtl->item_id . '-' . $odrDtl->lot . '-' . $odrDtl->bin_loc_id . '-' . $odrDtl->id];

                    if ($this->actPickedQty > ($invt->avail_qty + $invt->alloc_qty)) {
                        throw new UserException(Language::translate(
                            'Total picked qty must be less or equal than pick remain qty in inventory'
                        ));
                    }

                    $invt->alloc_qty -= $invtQty;
                    $invt->picked_qty += $invtQty;

                    //Set damage out sort
                    if ($invt->alloc_qty < 0) {
                        $alloc = abs($invt->alloc_qty);
                        $invt->alloc_qty = 0;
                        $invt->avail_qty -= $alloc;
                    }

                    $invt->save();
                }

                $odrDtl->save();

                $odrHdr = $odrDtl->order;
                $countPickedOdrDtl = $odrHdr->orderDtls()
                    ->whereNotIn('odr_dtl_sts', [Config::getStatusCode('ORDER_DTL_STATUS', 'Cancelled')])
                    ->whereIn('odr_dtl_sts', [OrderDtl::STS_PICKED, OrderDtl::STS_OUT_SORTING, OrderDtl::STS_OUT_SORTED])
                    ->count();
                $countOdrDtl = $odrHdr->orderDtls()->whereNotIn('odr_dtl_sts', [Config::getStatusCode('ORDER_DTL_STATUS', 'Cancelled')])->count();
                if ($countPickedOdrDtl == $countOdrDtl) {
                    $odrHdr->update([
                        'odr_sts' => OrderDtl::STS_PICKED
                    ]);

//                    $this->events[] = [
//                        'cus_id' => $odrHdr->cus_id,
//                        'owner' => $odrHdr->odr_num,
//                        'transaction' => $odrHdr->cus_odr_num,
//                        'event_code' => EventTracking::GUN_ORDER_PICKED,
//                        'info' => 'Order {0} picked by {1}',
//                        'info_params' => [$odrHdr->odr_num, $this->wvHdr->wv_hdr_num],
//                    ];
                }
            }
        });

        return $this;
    }

    protected function updateWvDtl()
    {
        $qtyPicking = data_get($this->input, 'qty');

        if ($this->wvDtl->lot != Config::ANY) {
            $pickedSts = WvDtl::STS_PICKED;
            $currentUserId = $this->user->id;

            $this->wvDtl->picked_qty += $qtyPicking;
            $this->wvDtl->algorithm = $this->algorithm;
            $this->wvDtl->picker_id = $this->wvDtl->picker_id ?? $currentUserId;

            $this->wvDtl->wv_dtl_sts = ($this->wvDtl->wv_dtl_sts == WvDtl::STS_NEW) ? WvDtl::STS_PICKING : $this->wvDtl->wv_dtl_sts;

            if ($this->wvDtl->picked_qty == $this->wvDtl->piece_qty) {
                $this->wvDtl->wv_dtl_sts = $pickedSts;
            }

//            $this->events[] = [
//                'cus_id' => $this->wvDtl->cus_id,
//                'event_code' => EventTracking::GUN_WAVE_PICK_PICKING,
//                'owner' => $this->wvHdr->wv_hdr_num,
//                'transaction' => $this->wvHdr->wv_hdr_num,
//                'info' => '{0} {1}, Batch {2} have been picked for {3}',
//                'info_params' => [
//                    $qtyPicking,
//                    $this->wvDtl->item->sku,
//                    $this->wvDtl->lot,
//                    $this->wvHdr->wv_hdr_num
//                ],
//            ];
        } else {
            $this->wvDtl->piece_qty -= $qtyPicking;

            if ($this->wvDtl->piece_qty <= 0) {
                $this->wvDtl->deleted = DELETED;
                $this->wvDtl->deleted_at = date('Y-m-d H:i:s');
            }
        }

        $this->wvDtl->save();

        $countPickedWvDtl = $this->wvHdr->wvDtls()->where('wv_dtl_sts', WvDtl::STS_PICKED)->count();
        $countWvDtl = $this->wvHdr->wvDtls()->whereNotIn('wv_dtl_sts', [Config::getStatusCode('WV_DTL_STATUS', 'Cancelled')])->count();

        if ($countPickedWvDtl == $countWvDtl) {
            $this->wvHdr->update([
                'wv_hdr_sts' => WvHdr::STS_PICKED
            ]);

//            $this->events[] = [
//                'cus_id' => $this->wvDtl->cus_id,
//                'event_code' => EventTracking::GUN_WAVE_PICK_PICKED,
//                'owner' => $this->wvHdr->wv_hdr_num,
//                'transaction' => $this->wvHdr->wv_hdr_num,
//                'info' => 'Wave Pick {0} have been picked',
//                'info_params' => [
//                    $this->wvHdr->wv_hdr_num
//                ],
//            ];

            //BillableService::process(ChargeCode::FROM_WAVE_PICK, [
            //    'wv_hdr_id' => $this->wvHdr->id,
            //    'cus_id' => 1,
            //]);
        } else {
            if (data_get($this->wvHdr, 'wv_hdr_sts') != WvHdr::STS_PICKING) {
                $this->wvHdr->update([
                    'wv_hdr_sts' => WvHdr::STS_PICKING
                ]);
            }
        }

        return $this;
    }

    protected function createWvDtlLocations()
    {
        $pickAbleQty = data_get($this->input, 'pickable_qty');
        $ttlQty = data_get($this->input, 'ttl_qty');
        $qtyPicking = $ttlQty ?: data_get($this->input, 'qty');

        if (!$pickAbleQty) {
            return $this;
        }

        $param = [
            'whs_id' => data_get($this->wvDtl, 'whs_id'),
            'cus_id' => data_get($this->wvDtl, 'cus_id'),
            'wv_hdr_id' => data_get($this->wvHdr, 'id'),
            'item_id' => data_get($this->wvDtl, 'item_id'),
            'loc_id' => data_get($this->location, 'loc_id'),
            'plt_id' => data_get($this->pallet, 'plt_id'),
            'plt_num' => data_get($this->pallet, 'rfid'),
            'pickable_qty' => $pickAbleQty,
            'picked_qty' => $qtyPicking,
        ];

        $this->wvDtl->wvDtlLocs()->create($param);

        return $this;
    }

    public function clearPltRfidAndLocationIfEmpty()
    {
        $pltIds = collect($this->pltIds)->unique()->filter()->toArray();

        $pallets = Pallet::whereIn('plt_id', $pltIds)
            ->where('rfid', 'NOT LIKE', Pallet::PREFIX_VIR . "%")
            ->whereDoesntHave('cartons', function ($q) {
                $q->whereIn('ctn_sts', [Carton::STS_ACTIVE, Carton::STS_RECEIVING]);
                $q->where('ctn_ttl', '>', 0);
            })
            ->get();

        $pallets->each(function ($pallet) {
            $pallet->update([
                'loc_id' => null,
                'loc_code' => null,
                'loc_name' => null,
                'plt_sts' => Pallet::STS_PICKED,
            ]);
        });

        return $this;
    }

    private function eventTracking()
    {
//        foreach ($this->events as $evtCode => $event) {
//            event(new EventTracking($event));
//        }

        return $this;
    }

    protected function validatePickQty()
    {
        $pickQty = (int)data_get($this->input, 'qty');
        $wvDtl = $this->wvDtl;
        $ttlQtyCanPick = $wvDtl->piece_qty - $wvDtl->picked_qty;
        if ($pickQty > $ttlQtyCanPick) {
            throw new UserException(Language::translate('Pick Qty cannot be greater than {0}', $ttlQtyCanPick));
        }

        $this->firstBinLoc = BinLocation::query()
            ->where('code', 'like', "%" . self::FIRST_BIN_LOCATION . "%")
            ->first();

        return $this;
    }
}
