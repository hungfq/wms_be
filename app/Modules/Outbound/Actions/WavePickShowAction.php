<?php

namespace App\Modules\Outbound\Actions;

use App\Entities\WvHdr;
use App\Exceptions\UserException;
use App\Libraries\Language;

class WavePickShowAction
{
    public function handle($whsId, $wvHdrId, $request)
    {
        $wvHdr = WvHdr::query()
            ->where([
                'id' => $wvHdrId,
                'whs_id' => $whsId
            ])
            ->first();

        if (!$wvHdr) {
            throw new UserException(Language::translate('Wave Pick does not exist!'));
        }

        $wvHdr->load([
            'odrHdrs',
            'wvDtls' => function ($wvDtl) use ($request) {
                $wvDtl->select('wv_dtls.*')
                    ->join('items', function ($item) {
                        $item->on('items.item_id', 'wv_dtls.item_id')
                            ->where('items.deleted', 0);
                    });

                if ($sku = $request->input('sku')) {
                    $wvDtl->where('sku', 'LIKE', "%{$sku}%");
                }
            },
            'wvDtls.item.uom',
            'wvDtls.picker',
            'wvDtls.customer.algorithm',
            'wvDtls.binLocation',
            'createdBy',
            'updatedBy',
        ]);

        return $wvHdr;
    }
}