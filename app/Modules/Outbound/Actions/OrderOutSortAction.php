<?php

namespace App\Modules\Outbound\Actions;

use App\Entities\Carton;
use App\Entities\OrderDtl;
use App\Entities\OrderHdr;
use App\Entities\Pallet;
use App\Modules\Outbound\DTO\OrderOutSortDTO;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderOutSortAction
{
    public OrderOutSortDTO $dto;
    public $odrHdr;
    public $whsId;
    public $odrDtl;
    public $events;
    public $pltIds;

    /**
     * @param OrderOutSortDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        foreach ($this->dto->odr_hdr_ids as $odrHdrId) {
            $this->_resetData();

            $this->odrHdr = OrderHdr::query()
                ->where('odr_sts', OrderHdr::STS_PICKED)
                ->find($odrHdrId);

            if (!$this->odrHdr) {
                continue;
            }

            DB::transaction(function () {
                $odrDtls = $this->odrHdr->orderDtls()
                    ->whereIn('odr_dtl_sts', [OrderDtl::STS_PICKED, OrderDtl::STS_OUT_SORTING, OrderDtl::STS_PICKING])
                    ->get();

                foreach ($odrDtls as $odrDtl) {
                    $this->odrDtl = $odrDtl;

                    $this->confirmIsNotScanSerial()
                        ->updateOrder();
                }
                $this->restoreOrDeletePalletOutSort()
                    ->eventTracking();
            });
        }
    }

    private function _resetData()
    {
        $this->odrHdr = null;
        $this->odrDtl = null;
        $this->events = [];
        $this->pltIds = [];

        return $this;
    }


    public function confirmIsNotScanSerial()
    {
        $inputQty = $this->odrDtl->picked_qty;

        $orderCartons = $this->odrDtl->orderCartons()
            ->whereRaw('picked_qty > out_sort_qty')
            ->get();

        $odrCartonIds = [];
        foreach ($orderCartons as $orderCarton) {
            if ($inputQty <= 0) {
                continue;
            }

            $pickedQty = $orderCarton->picked_qty;
            $outSortQty = $orderCarton->out_sort_qty;
            $remainQty = $pickedQty - $outSortQty;

            $qty = $remainQty - $inputQty;

            if ($qty == 0) {
                $orderCarton->out_sort_qty += $remainQty;
                $orderCarton->save();

                $param = [
                    'whs_id' => $this->odrHdr->whs_id,
                    'cus_id' => $this->odrHdr->cus_id,
                    'odr_hdr_id' => $this->odrHdr->id,
                    'odr_dtl_id' => $this->odrDtl->id,
                    'odr_carton_id' => $orderCarton->id,
                    'item_id' => $this->odrDtl->item_id,
                    'qty' => $inputQty,
                    'confirm_by' => Auth::id(),
                ];

                if (count($odrCartonIds)) {
                    $odrCartonIds[] += $orderCarton->id;
                    $param['odr_carton_ids'] = json_encode($odrCartonIds);
                    $param['qty'] = $this->odrDtl->picked_qty;

                    $this->odrDtl->orderOutSorts()->create($param);
                } else {
                    $orderCarton->orderOutSorts()->create($param);
                }

                $carton = $orderCarton->carton;
                $carton->ctn_sts = Carton::STS_OUT_SORTED;
                $carton->save();

                $inputQty = 0;
                $this->pltIds[] += $orderCarton->plt_id;
                continue;
            } elseif ($qty > 0) {
                $orderCarton->out_sort_qty += $inputQty;
                $orderCarton->save();

                $param = [
                    'whs_id' => $this->odrHdr->whs_id,
                    'cus_id' => $this->odrHdr->cus_id,
                    'odr_hdr_id' => $this->odrHdr->id,
                    'odr_dtl_id' => $this->odrDtl->id,
                    'odr_carton_id' => $orderCarton->id,
                    'item_id' => $this->odrDtl->item_id,
                    'qty' => $inputQty,
                    'confirm_by' => Auth::id(),
                ];

                if ($odrCartonIds) {
                    $odrCartonIds[] += $orderCarton->id;
                    $param['odr_carton_ids'] = json_encode($odrCartonIds);
                    $param['qty'] = $this->odrDtl->picked_qty;

                    $this->odrDtl->orderOutSorts()->create($param);
                } else {
                    $orderCarton->orderOutSorts()->create($param);
                }

                $inputQty = 0;
                $this->pltIds[] += $orderCarton->plt_id;
                continue;
            } elseif ($qty < 0) {
                $orderCarton->out_sort_qty = $orderCarton->picked_qty;
                $orderCarton->save();

                $odrCartonIds[] = $orderCarton->id;

                $carton = $orderCarton->carton;
                $carton->ctn_sts = Carton::STS_OUT_SORTED;
                $carton->save();

                $inputQty = $inputQty - $remainQty;
                $this->pltIds[] += $orderCarton->plt_id;
                continue;
            }
        }

        return $this;
    }

    public function updateOrder()
    {
        $pieceQty = data_get($this->odrDtl, 'piece_qty');
        $pickedQty = data_get($this->odrDtl, 'picked_qty');
        $qtyOutSort = $this->odrDtl->orderOutSorts()->sum('qty');

        if ($pieceQty == $pickedQty) {
            $this->odrHdr->odr_sts = OrderHdr::STS_OUT_SORTING;
            $this->odrDtl->odr_dtl_sts = OrderDtl::STS_OUT_SORTING;
        } else {
            $this->odrHdr->odr_sts = OrderHdr::STS_PICKING;
            $this->odrDtl->odr_dtl_sts = OrderDtl::STS_PICKING;
        }

        if ($pieceQty == $qtyOutSort) {
            $this->odrDtl->odr_dtl_sts = OrderDtl::STS_OUT_SORTED;
        }

        $this->odrDtl->save();

        $odrNotSorted = $this->odrHdr->orderDtls()->whereNotIn('odr_dtl_sts', [OrderDtl::STS_OUT_SORTED, OrderDtl::STS_CANCEL])->exists();

        if (!$odrNotSorted) {
            $this->odrHdr->odr_sts = OrderHdr::STS_OUT_SORTED;

//            $this->events[] = [
//                'cus_id' => $this->odrHdr->cus_id,
//                'owner' => $this->odrHdr->odr_num,
//                'transaction' => $this->odrHdr->cus_odr_num,
//                'event_code' => EventTracking::GUN_ORDER_OUT_SORTED,
//                'info' => 'Order {0} has been out sorted',
//                'info_params' => [
//                    $this->odrHdr->odr_num
//                ],
//            ];
        }

        $this->odrHdr->save();

        return $this;
    }

    public function restoreOrDeletePalletOutSort()
    {
        $pltIds = collect($this->pltIds)->unique()->filter()->values()->toArray();

        if (!$pltIds) {
            return $this;
        }

        foreach ($pltIds as $pltId) {

            $pallet = Pallet::withTrashed()
                ->where([
                    'pallet.whs_id' => $this->odrHdr->whs_id,
                    'pallet.plt_id' => $pltId
                ])
                ->first();

            $carton = $pallet->load([
                'cartons' => function ($q) {
                    $q->where([
                        'ctn_sts' => Carton::STS_ACTIVE,
                        'deleted' => 0
                    ]);
                }
            ])->cartons->first();

            if ($carton) {
                $pallet->plt_sts = Pallet::STS_ACTIVE;
                $pallet->save();

                $pallet->restore();
            } else {
                $pallet->update([
                    'rfid' => null,
                    'plt_sts' => Pallet::STS_PICKED,
                ]);
            }
        }

        return $this;
    }

    public function eventTracking()
    {
//        foreach ($this->events as $event) {
//            event(new EventTracking($event));
//        }
    }
}
