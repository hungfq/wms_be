<?php

namespace App\Modules\Outbound\Actions;

use App\Entities\EventLog;
use App\Entities\Inventory;
use App\Entities\OrderDtl;
use App\Entities\OrderHdr;
use App\Exceptions\UserException;
use App\Libraries\Config;
use App\Libraries\Language;
use App\Modules\Outbound\DTO\OrderAllocateDTO;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderAllocateAction
{
    public $orderNotEnoughInventory = [];
    public OrderAllocateDTO $dto;
    public $odrHdr;
    public $odrDtls;
    public $odrDtlRetails;
    public $dtlCounts = [];
    public $naItemIds = [];
    public $haveLotItemIds = [];
    public $events = [];
    public $odrDtlAny = [];

    protected function _resetData()
    {
        $this->odrHdr = null;
        $this->odrDtls = null;
        $this->odrDtlRetails = [];
        $this->dtlCounts = [];
        $this->naItemIds = [];
        $this->haveLotItemIds = [];
        $this->events = [];
        $this->odrDtlAny = [];
    }

    /**
     * @param OrderAllocateDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        foreach ($this->dto->odr_hdr_ids as $odr_hdr_id) {
            $this->performTransaction($odr_hdr_id, function () use ($odr_hdr_id) {
                DB::transaction(function () use ($odr_hdr_id) {
                    $this->_resetData();
                    $this->validateDataInput($odr_hdr_id)
                        ->handleOrderDetailFullOrPartial()
                        ->createOrUpdateOrderDetails()
                        ->eventTracking();
                });
            });
        }

        if (count($this->orderNotEnoughInventory) > 0) {
            throw new UserException(Language::translate(
                'Order(s) {0} does not have enough inventory',
                implode(', ', $this->orderNotEnoughInventory)
            ));
        }
    }

    protected function performTransaction($dataId, $callback)
    {
        $lockKey = "transaction_lock_allocate_{$dataId}";

        $mutex = Cache::lock($lockKey);

        if ($mutex->get()) {
            try {
                $callback();
            } finally {
                $mutex->forceRelease();
            }
        } else {
            throw new UserException(Language::translate('This Order is being allocate by another user!'));
        }
    }

    public function validateDataInput($odrHdrId)
    {
        $this->odrHdr = OrderHdr::query()
            ->where([
                'whs_id' => $this->dto->whs_id,
                'id' => $odrHdrId
            ])
            ->first();

        if (!$this->odrHdr) {
            throw new UserException(Language::translate('Order does not exist'));
        }

        if ($this->odrHdr->odr_sts != OrderHdr::STS_NEW) {
            throw new UserException(Language::translate('Only New Orders can be allocated!'));
        }

        $this->odrDtls = $this->getDetailItemByHdrId();
        $itemIds = Arr::pluck($this->odrDtls, 'item_id', 'item_id');
        $binLocIds = $this->odrDtls->pluck('bin_loc_id')->filter()->toArray();

        foreach ($this->odrDtls as $odrDtl) {
            if ($odrDtl->piece_qty == 0) {
                throw new UserException(Language::translate('Can not allocate order when CTNS/QTY of model Code is 0. Please check!'));
            }

            if ($odrDtl->lot == Config::ANY) {
                $this->naItemIds[$odrDtl->item_id] = $odrDtl->item_id;
            } else {
                $this->haveLotItemIds[$odrDtl->item_id] = $odrDtl->item_id;
            }
        }

        $invts = $this->getAvailableQtyByItem(array_merge($itemIds, $this->naItemIds), $binLocIds);
        $itemSkus = Arr::pluck($this->odrDtls, 'item.sku', 'sku');

        if (!$invts->count()) {
            throw new UserException(Language::translate('Order: {0} - Skus: {1} does not have available quantity in inventory.', data_get($this->odrHdr, 'odr_num'), implode(', ', $itemSkus)));
        }

        return $this;
    }

    public function handleOrderDetailFullOrPartial()
    {
        $odrDtls = $this->odrHdr->orderDtls;
        foreach ($odrDtls as $odrDtl) {
            if (!$this->isLotAny($odrDtl->lot)) {
                continue;
            }

            $itemPackSize = data_get($odrDtl, 'item.pack_size');

            if (empty($this->odrDtlAny[$odrDtl->id])) {
                $this->odrDtlAny[$odrDtl->id] = [
                    'org_ctn' => $odrDtl->ctn_ttl,
                    'org_qty' => $odrDtl->piece_qty,
                ];
            }

            if ($odrDtl->is_retail == OrderDtl::ALLOCATE_WHOLESALE) {
                $pieceQty = $odrDtl->piece_qty;

                while ($pieceQty > 0) {
                    $inventory = Inventory::query()
                        ->where([
                            'inventory.whs_id' => $this->dto->whs_id,
                            'inventory.cus_id' => $odrDtl->cus_id,
                            'inventory.item_id' => $odrDtl->item_id,
                            'inventory.bin_loc_id' => $odrDtl->bin_loc_id,
                            ['inventory.lot', '<>', Config::RETAIL],
                            ['inventory.avail_qty', '>', 0],
                            ['inventory.ttl', '>', 0]
                        ])
                        ->orderBy('created_at', "ASC")
                        ->first();

                    if (!$inventory) {
                        $pieceQty = 0;
                        continue;
                    }

                    $availQty = $inventory->avail_qty;

                    if ($availQty >= $pieceQty) {
                        $qty = $odrDtl->piece_qty;
                    } else {
                        $qty = $inventory->avail_qty;
                    }

                    $dataEven = $this->checkInventoryCartonEven($odrDtl, $qty);

                    if ($dataEven['is_created'] && $dataEven['qty']) {
                        $odrDtl->piece_qty -= $dataEven['qty'];
                        $odrDtl->ctn_ttl -= $dataEven['ctn_ttl'];
                        $odrDtl->save();
                        $pieceQty -= $dataEven['qty'];
                    }
                }
            } elseif ($itemPackSize == 1) {
                $oddQty = $odrDtl->piece_qty;

                $inventory = Inventory::query()
                    ->where([
                        'inventory.whs_id' => $this->dto->whs_id,
                        'inventory.cus_id' => $odrDtl->cus_id,
                        'inventory.item_id' => $odrDtl->item_id,
                        'inventory.bin_loc_id' => $odrDtl->bin_loc_id,
                        'inventory.lot' => Config::RETAIL,
                        ['inventory.avail_qty', '>', 0],
                        ['inventory.ttl', '>', 0]
                    ])
                    ->first();

                if ($inventory) {
                    if ($inventory->avail_qty >= $oddQty) {
                        // Update Inventory
                        $inventory->alloc_qty += $oddQty;
                        $inventory->avail_qty -= $oddQty;
                        $inventory->save();

                        // Create New Order Retail
                        $odrDtlOdd = $odrDtl->replicate();
                        $odrDtlOdd->lot = Config::RETAIL;
                        $odrDtlOdd->ctn_ttl = $oddQty;
                        $odrDtlOdd->piece_qty = $oddQty;
                        $odrDtlOdd->alloc_qty = $oddQty;
                        $odrDtlOdd->parent_id = $odrDtl->odr_dtl_id;
                        $odrDtlOdd->is_user_created = 0;
                        $odrDtlOdd->save();

                        // Tracking
//                        $this->events[] = [
//                            'cus_id' => $this->odrHdr->cus_id,
//                            'event_code' => EventTracking::ORDER_ALLOCATE,
//                            'owner' => $this->odrHdr->odr_num,
//                            'transaction' => $this->odrHdr->cus_odr_num,
//                            'info' => '{0} {1}, Batch {2}, Bin Loc {3} allocated',
//                            'info_params' => [
//                                $oddQty,
//                                data_get($odrDtl, 'item.sku'),
//                                Config::RETAIL,
//                                data_get($odrDtl, 'binLocation.name')
//                            ]
//                        ];

                        $this->odrDtlRetails[] = $odrDtlOdd;

                        $odrDtl->piece_qty = 0;
                        $odrDtl->ctn_ttl = 0;
                        $odrDtl->save();
                    } else {
                        $remainQty = $inventory->avail_qty;

                        // Update Inventory
                        $inventory->alloc_qty += $remainQty;
                        $inventory->avail_qty -= $remainQty;
                        $inventory->save();

                        // Create New Order Retail
                        $odrDtlOdd = $odrDtl->replicate();
                        $odrDtlOdd->lot = Config::RETAIL;
                        $odrDtlOdd->ctn_ttl = $remainQty;
                        $odrDtlOdd->piece_qty = $remainQty;
                        $odrDtlOdd->alloc_qty = $remainQty;
                        $odrDtlOdd->parent_id = $odrDtl->odr_dtl_id;
                        $odrDtlOdd->is_user_created = 0;
                        $odrDtlOdd->save();

                        // Tracking
//                        $this->events[] = [
//                            'cus_id' => $this->odrHdr->cus_id,
//                            'event_code' => EventTracking::ORDER_ALLOCATE,
//                            'owner' => $this->odrHdr->odr_num,
//                            'transaction' => $this->odrHdr->cus_odr_num,
//                            'info' => '{0} {1}, Batch {2}, Bin Loc {3} allocated',
//                            'info_params' => [
//                                $oddQty,
//                                data_get($odrDtl, 'item.sku'),
//                                Config::RETAIL,
//                                data_get($odrDtl, 'binLocation.name')
//                            ]
//                        ];

                        $this->odrDtlRetails[] = $odrDtlOdd;

                        $odrDtl->piece_qty -= $remainQty;
                        $odrDtl->ctn_ttl -= $remainQty;
                        $odrDtl->save();
                    }
                }

            } elseif ($odrDtl->is_retail == OrderDtl::ALLOCATE_RETAIL) {
                $oddQty = $odrDtl->piece_qty;

                $inventory = Inventory::query()
                    ->where([
                        'inventory.whs_id' => $this->dto->whs_id,
                        'inventory.cus_id' => $odrDtl->cus_id,
                        'inventory.item_id' => $odrDtl->item_id,
                        'inventory.bin_loc_id' => $odrDtl->bin_loc_id,
                        'inventory.lot' => Config::RETAIL,
                        ['inventory.avail_qty', '>', 0],
                        ['inventory.ttl', '>', 0]
                    ])
                    ->first();

                if (!$inventory) {
                    continue;
                }

                $availQty = $inventory->avail_qty;

                if ($availQty >= $oddQty) {
                    $oddQty = $odrDtl->piece_qty;

                    $dataOdd = $this->checkInventoryCartonOdd($odrDtl, $oddQty);

                    if ($dataOdd['is_created'] && $dataOdd['qty']) {
                        $odrDtl->piece_qty -= $dataOdd['qty'];
                        $odrDtl->ctn_ttl -= $dataOdd['ctn_ttl'];
                        $odrDtl->save();
                    } else {
                        continue;
                    }
                } else {
                    $oddQty = $inventory->avail_qty;
                    $dataOdd = $this->checkInventoryCartonOdd($odrDtl, $oddQty);

                    if ($dataOdd['is_created'] && $dataOdd['qty']) {
                        $odrDtl->piece_qty -= $dataOdd['qty'];
                        $odrDtl->ctn_ttl -= $dataOdd['ctn_ttl'];
                        $odrDtl->save();
                    } else {
                        continue;
                    }
                }
            } else {
                $evenCtn = intdiv($odrDtl->piece_qty, $itemPackSize);
                $oddQty = $odrDtl->piece_qty - ($evenCtn * $itemPackSize);

                $dataOdd = $this->checkInventoryCartonOdd($odrDtl, $oddQty);

                if ($dataOdd['is_created'] && $dataOdd['qty']) {
                    $odrDtl->piece_qty -= $dataOdd['qty'];
                    $odrDtl->ctn_ttl -= $dataOdd['ctn_ttl'];
                    $odrDtl->save();
                } else {
                    continue;
                }
            }
        }

        return $this;
    }

    public function checkInventoryCartonOdd($odrDtl, $oddQty)
    {
        $inventory = Inventory::query()
            ->where([
                'inventory.whs_id' => $this->dto->whs_id,
                'inventory.cus_id' => $odrDtl->cus_id,
                'inventory.item_id' => $odrDtl->item_id,
                'inventory.bin_loc_id' => $odrDtl->bin_loc_id,
                'inventory.lot' => Config::RETAIL,
                ['inventory.avail_qty', '>', 0],
                ['inventory.ttl', '>', 0]
            ])
            ->first();

        if (!$inventory || !$oddQty) {
            return [
                'is_created' => false,
                'qty' => 0,
                'ctn_ttl' => 0,
            ];
        }

        if ($inventory->avail_qty < $oddQty) {
            return [
                'is_created' => false,
                'qty' => 0,
                'ctn_ttl' => 0,
            ];
        }

        $retailCtn = ceil($oddQty / data_get($odrDtl, 'item.pack_size'));

        // Update Inventory
        $inventory->alloc_qty += $oddQty;
        $inventory->avail_qty -= $oddQty;
        $inventory->save();

        // Create New Order Retail
        $odrDtlOdd = $odrDtl->replicate();
        $odrDtlOdd->lot = Config::RETAIL;
        $odrDtlOdd->ctn_ttl = $retailCtn;
        $odrDtlOdd->piece_qty = $oddQty;
        $odrDtlOdd->alloc_qty = $oddQty;
        $odrDtlOdd->parent_id = $odrDtl->odr_dtl_id;
        $odrDtlOdd->is_user_created = 0;
        $odrDtlOdd->save();

        // Tracking
//        $this->events[] = [
//            'cus_id' => $this->odrHdr->cus_id,
//            'event_code' => EventTracking::ORDER_ALLOCATE,
//            'owner' => $this->odrHdr->odr_num,
//            'transaction' => $this->odrHdr->cus_odr_num,
//            'info' => '{0} {1}, Batch {2}, Bin Loc {3} allocated',
//            'info_params' => [
//                $oddQty,
//                data_get($odrDtl, 'item.sku'),
//                Config::RETAIL,
//                data_get($odrDtl, 'binLocation.name')
//            ]
//        ];

        $this->odrDtlRetails[] = $odrDtlOdd;

        return [
            'is_created' => true,
            'qty' => $oddQty,
            'ctn_ttl' => $retailCtn,
        ];
    }

    public function checkInventoryCartonEven($odrDtl, $qty)
    {
        $inventory = Inventory::query()
            ->where([
                'inventory.whs_id' => $this->dto->whs_id,
                'inventory.cus_id' => $odrDtl->cus_id,
                'inventory.item_id' => $odrDtl->item_id,
                'inventory.bin_loc_id' => $odrDtl->bin_loc_id,
                ['inventory.lot', '<>', Config::RETAIL],
                ['inventory.avail_qty', '>', 0],
                ['inventory.ttl', '>', 0]
            ])
            ->orderBy('created_at', "ASC")
            ->first();

        if (!$inventory || !$qty) {
            return [
                'is_created' => false,
                'qty' => 0,
                'ctn_ttl' => 0,
            ];
        }

        $evenCtn = ceil($qty / data_get($odrDtl, 'item.pack_size'));

        // Update Inventory
        $inventory->alloc_qty += $qty;
        $inventory->avail_qty -= $qty;
        $inventory->save();

        // Create New Order Detail
        $odrDtlNew = $odrDtl->replicate();
        $odrDtlNew->lot = $inventory->lot;
        $odrDtlNew->ctn_ttl = $evenCtn;
        $odrDtlNew->piece_qty = $qty;
        $odrDtlNew->alloc_qty = $qty;
        $odrDtlNew->parent_id = $odrDtl->odr_dtl_id;
        $odrDtlNew->is_user_created = 0;
        $odrDtlNew->save();

        // Tracking
//        $this->events[] = [
//            'cus_id' => $this->odrHdr->cus_id,
//            'event_code' => EventTracking::ORDER_ALLOCATE,
//            'owner' => $this->odrHdr->odr_num,
//            'transaction' => $this->odrHdr->cus_odr_num,
//            'info' => '{0} {1}, Batch {2}, Bin Loc {3} allocated',
//            'info_params' => [
//                $qty,
//                data_get($odrDtl, 'item.sku'),
//                $inventory->lot,
//                data_get($odrDtl, 'binLocation.name')
//            ]
//        ];

        return [
            'is_created' => true,
            'qty' => $qty,
            'ctn_ttl' => $evenCtn,
        ];
    }

    public function getDetailItemByHdrId()
    {
        return $this->odrHdr->orderDtls()
            ->select([
                'odr_dtl.odr_id',
                'odr_dtl.id AS odr_dtl_id',
                'odr_dtl.whs_id',
                'odr_dtl.cus_id',
                'odr_dtl.item_id',
                'odr_dtl.lot',
                'odr_dtl.is_retail',
                'odr_dtl.ctn_ttl',
                'odr_dtl.piece_qty',
                'odr_dtl.alloc_qty',
                'odr_dtl.picked_qty',
                'odr_dtl.packed_qty',
                'odr_dtl.cancelled_qty',
                'odr_dtl.put_back_qty',
                'odr_dtl.bin_loc_id',
                'odr_dtl.price',
                'items.sku',
                'items.pack_size',
            ])
            ->join('items', 'items.item_id', '=', 'odr_dtl.item_id')
            ->get();
    }

    public function getAvailableQtyByItem($itemIds, $options = [])
    {
        $binLocIds = data_get($options, 'bin_loc_ids') ?? [];

        $query = Inventory::query()
            ->where('inventory.whs_id', $this->dto->whs_id)
            ->whereIn('inventory.item_id', $itemIds)
            ->where('inventory.avail_qty', '>', 0);

        if ($binLocIds) {
            $query->whereIn('bin_loc_id', $binLocIds);
        }

        return $query->orderBy('created_at', "ASC")->get();
    }

    public function createOrUpdateOrderDetails()
    {
        $lots = array_filter(Arr::pluck($this->odrDtls, 'lot', 'lot'));
        $isAny = false;

        if (!empty($lots[Config::ANY])) {
            unset($lots[Config::ANY]);
            $isAny = true;
        }

        if (!empty($lots)) {
            $this->updateOdrDtlAndInvt($this->haveLotItemIds, $lots);
        }

        if ($isAny) {
            $this->updateOdrDtlAndInvt($this->naItemIds);
        }

        $this->odrHdr->update([
            'sku_ttl' => count($this->dtlCounts),
            'odr_sts' => OrderHdr::STS_ALLOCATED,
            'allocate_at' => date('Y-m-d H:i:s'),
        ]);

        $this->events[] = [
            'whs_id' => $this->dto->whs_id,
            'event_code' => EventLog::ORDER_ALLOCATE,
            'owner' => data_get($this->odrHdr, 'odr_num'),
            'info' => '{0} allocated',
            'info_params' => [data_get($this->odrHdr, 'odr_num')],
        ];

        return $this;
    }

    public function updateOdrDtlAndInvt($itemIds, $lots = [])
    {
        $odrDtls = $this->odrHdr->orderDtls;
        $binLocIds = $odrDtls->pluck('bin_loc_id')->filter()->toArray();

        if (empty($lots)) {
            $invItems = $this->getByItemWithLotNA($itemIds, ['bin_loc_ids' => $binLocIds]);
            $lots[Config::ANY] = Config::ANY;
        } else {
            $invItems = $this->getByItemLot($itemIds, $lots, ['bin_loc_ids' => $binLocIds]);
        }

        foreach ($odrDtls as $k => $odrDtl) {
            if (!isset($lots[$odrDtl->lot])) {
                continue;
            }

            unset($odrDtls[$k]);
            $allQty = $reqQty = $odrDtl->piece_qty;

            foreach ($invItems as $invItem) {
                if (($invItem->item_id != $odrDtl->item_id) || ($invItem->bin_loc_id != $odrDtl->bin_loc_id)) {
                    continue;
                }

                if ($odrDtl->piece_qty <= 0) {
                    continue;
                }

                $item = $odrDtl->item;
                $this->dtlCounts[$invItem->item_id . '-' . $invItem->bin_loc_id] = $invItem->item_id . '-' . $invItem->bin_loc_id;
                $lot = $this->isLotAny($odrDtl->lot) ? $invItem->lot : $odrDtl->lot;

                if (!$this->isLotAny($odrDtl->lot) && $odrDtl->lot != $invItem->lot) {
                    continue;
                }

                if ($invItem->avail_qty < $allQty) {
                    $availQty = $invItem->avail_qty;
                    $this->createNewOrUpdateOdrDtl($invItem, $odrDtl, $availQty);
                    $allQty = $this->updateInvSum($invItem, $allQty);

//                    $this->events[] = [
//                        'cus_id' => $this->odrHdr->cus_id,
//                        'event_code' => EventTracking::ORDER_ALLOCATE,
//                        'owner' => $this->odrHdr->odr_num,
//                        'transaction' => $this->odrHdr->cus_odr_num,
//                        'info' => '{0} {1}, Batch {2}, Bin Loc {3} allocated',
//                        'info_params' => [
//                            $availQty,
//                            $item->sku,
//                            $lot,
//                            data_get($odrDtl, 'binLocation.name')
//                        ]
//                    ];
                } else {
                    $this->updateInvSum($invItem, $allQty);
                    $this->createNewOrUpdateOdrDtl($invItem, $odrDtl, $allQty);

//                    $this->events[] = [
//                        'cus_id' => $this->odrHdr->cus_id,
//                        'event_code' => EventTracking::ORDER_ALLOCATE,
//                        'owner' => $this->odrHdr->odr_num,
//                        'transaction' => $this->odrHdr->cus_odr_num,
//                        'info' => '{0} {1}, Batch {2}, Bin Loc {3} allocated',
//                        'info_params' => [
//                            $allQty,
//                            $item->sku,
//                            $lot,
//                            data_get($odrDtl, 'binLocation.name')
//                        ]
//                    ];
                    $allQty = 0;
                    break;
                }
            }

            if ($allQty > 0) {
                $ivtRetail = Inventory::where([
                    'inventory.whs_id' => $this->dto->whs_id,
                    'item_id' => $odrDtl->item_id,
                    'lot' => Config::RETAIL,
                    'bin_loc_id' => $odrDtl->bin_loc_id,
                    ['inventory.avail_qty', '>', 0],
                    ['inventory.ttl', '>', 0]
                ])->first();

                if (!$ivtRetail || $ivtRetail->avail_qty < $allQty || $odrDtl->lot != Config::ANY) {
                    throw new UserException(Language::translate(
                        'Model: {0} - Bin: {1} - Lot: {2} does not have enough inventory',
                        data_get($odrDtl, 'item.sku'),
                        data_get($odrDtl, 'binLocation.name'),
                        $odrDtl->lot,
                    ));
                }

                $odrDtlRetail = collect($this->odrDtlRetails)
                    ->where('item_id', $odrDtl->item_id)
                    ->where('bin_loc_id', $odrDtl->bin_loc_id)
                    ->first();

                $itemPackSize = data_get($odrDtl->item, 'pack_size');

                if (!$odrDtlRetail) {
                    $ctnTtl = ceil($allQty / $itemPackSize);

                    $odrDtlRetail = $odrDtl->replicate();
                    $odrDtlRetail->lot = Config::RETAIL;
                    $odrDtlRetail->is_retail = 1;
                    $odrDtlRetail->ctn_ttl = $ctnTtl;
                    $odrDtlRetail->piece_qty = $allQty;
                    $odrDtlRetail->alloc_qty = $allQty;
                    $odrDtlRetail->parent_id = $odrDtl->odr_dtl_id;
                    $odrDtlRetail->is_user_created = 0;
                    $odrDtlRetail->save();

                    $infoParams = [
                        $allQty,
                        data_get($odrDtlRetail, 'item.sku'),
                        Config::RETAIL,
                        data_get($odrDtlRetail, 'binLocation.name')
                    ];
                } else {

                    $oddQty = $odrDtlRetail->piece_qty + $allQty;
                    $ctnTtl = ceil($oddQty / $itemPackSize);
                    $odrDtlRetail->ctn_ttl = $ctnTtl;
                    $odrDtlRetail->piece_qty += $allQty;
                    $odrDtlRetail->alloc_qty += $allQty;
                    $odrDtlRetail->save();

                    $infoParams = [
                        $allQty,
                        data_get($odrDtlRetail, 'item.sku'),
                        Config::RETAIL,
                        data_get($odrDtlRetail, 'binLocation.name')
                    ];
                }

//                $this->events[] = [
//                    'cus_id' => $this->odrHdr->cus_id,
//                    'event_code' => EventTracking::ORDER_ALLOCATE,
//                    'owner' => $this->odrHdr->odr_num,
//                    'transaction' => $this->odrHdr->cus_odr_num,
//                    'info' => '{0} {1}, Batch {2}, Bin Loc {3} allocated',
//                    'info_params' => $infoParams
//                ];

                $ivtRetail->avail_qty -= $allQty;
                $ivtRetail->alloc_qty += $allQty;
                $ivtRetail->save();
            }

            if ($this->isLotAny($odrDtl->lot)) {
                $odrDtlRetail = collect($this->odrDtlRetails)
                    ->where('item_id', $odrDtl->item_id)
                    ->where('bin_loc_id', $odrDtl->bin_loc_id)
                    ->first();

                if ($odrDtlRetail) {
                    $detailOrigin = collect($this->odrDtls)
                        ->where('item_id', $odrDtl->item_id)
                        ->where('bin_loc_id', $odrDtl->bin_loc_id)
                        ->first();

                    $odrDtl->ctn_ttl = $detailOrigin->ctn_ttl;
                    $odrDtl->piece_qty = $detailOrigin->piece_qty;
                    $odrDtl->save();
                }

                if (isset($this->odrDtlAny[$odrDtl->id])) {
                    $detail = $this->odrDtlAny[$odrDtl->id];

                    $odrDtl->ctn_ttl = data_get($detail, 'org_ctn');
                    $odrDtl->piece_qty = data_get($detail, 'org_qty');
                    $odrDtl->save();
                }

                $odrDtl->delete();
            }

        }
    }

    public function getByItemWithLotNA($itemIds, $options = [])
    {
        $binLocIds = data_get($options, 'bin_loc_ids') ?? [];

        $query = Inventory::query()
            ->where('inventory.whs_id', $this->dto->whs_id)
            ->whereIn('inventory.item_id', $itemIds)
            ->where('inventory.avail_qty', '>', 0)
            ->where('inventory.ttl', '>', 0);

        $query->whereNotIn('inventory.lot', [Config::RETAIL])
            ->orderByRaw("(CASE WHEN inventory.lot = 'NA' THEN 1 ELSE inventory.created_at END)");

        if ($binLocIds) {
            $query->whereIn('bin_loc_id', $binLocIds);
        }

        return $query->get();
    }

    public function getByItemLot($itemIds, $lots, $options = [])
    {
        $binLocIds = data_get($options, 'bin_loc_ids') ?? [];

        $query = Inventory::query()
            ->where('inventory.whs_id', $this->dto->whs_id)
            ->whereIn('inventory.item_id', $itemIds)
            ->whereIn('inventory.lot', $lots)
            ->where('inventory.avail_qty', '>', 0)
            ->where('inventory.ttl', '>', 0);

        if ($binLocIds) {
            $query->whereIn('bin_loc_id', $binLocIds);
        }

        return $query->orderBy('created_at', "ASC")->get();
    }

    private function createNewOrUpdateOdrDtl($invSum, $odrDtl, $allQty)
    {
        $lot = $odrDtl->lot;
        $itemPSize = data_get($odrDtl, 'item.pack_size');

        $ctnTtl = $itemPSize ? ceil($allQty / $itemPSize) : 0;

        if ($this->isLotAny($lot)) {
            OrderDtl::create([
                'whs_id' => $odrDtl->whs_id,
                'cus_id' => $odrDtl->cus_id,
                'odr_id' => $odrDtl->odr_id,
                'item_id' => $odrDtl->item_id,
                'bin_loc_id' => $odrDtl->bin_loc_id,
                'price' => $odrDtl->price,
                'is_retail' => $odrDtl->is_retail,
                'lot' => $invSum->lot,
                'ctn_ttl' => (int)$ctnTtl,
                'piece_qty' => (int)$allQty,
                'alloc_qty' => (int)$allQty,
                'odr_dtl_sts' => OrderDtl::STS_NEW,
                'parent_id' => $odrDtl->id,
                'is_user_created' => 0,
                'extra_detail' => data_get($odrDtl, 'extra_detail'),
            ]);
        } else {
            $odrDtl->update([
                'ctn_ttl' => (int)$ctnTtl,
                'piece_qty' => (int)$allQty,
                'alloc_qty' => (int)$allQty
            ]);
        }
    }

    private function updateInvSum(&$invSum, $allQty)
    {
        if ($invSum->avail_qty < $allQty) {
            $allQty = $allQty - $invSum->avail_qty;
            $alloc_qty = $invSum->alloc_qty + $invSum->avail_qty;
            $avail_qty = 0;
        } else {
            $alloc_qty = $invSum->alloc_qty + $allQty;
            $avail_qty = $invSum->avail_qty - $allQty;
            $allQty = 0;
        }

        $invSum->alloc_qty = $alloc_qty;
        $invSum->avail_qty = $avail_qty;
        $invSum->save();

        return $allQty;
    }

    private function isLotAny($lot)
    {
        $lot = strtoupper($lot);
        if ($lot === Config::ANY) {
            return true;
        }

        return false;
    }

    public function eventTracking()
    {
        foreach ($this->events as $evt) {
            EventLog::query()->create($evt);
        }

        return $this;
    }
}
