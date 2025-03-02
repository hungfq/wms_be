<?php

namespace App\Modules\Outbound\Actions;

use App\Entities\OrderHdr;
use App\Exceptions\UserException;
use App\Libraries\Language;
use App\Modules\Outbound\DTO\OrderPrintDeliveryNoteDTO;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class OrderPrintDeliveryNoteAction
{
    public OrderPrintDeliveryNoteDTO $dto;
    public $odrHdr;
    public $orderDtls;
    public $template = 'OutBound.DeliveryNoteTemplate';
    public $html;

    /**
     * @param OrderPrintDeliveryNoteDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $this->checkData()
            ->loadReportTemplate()
            ->exportPdf();
    }

    protected function checkData()
    {
        $this->odrHdr = OrderHdr::query()
            ->with([
                'wvHdr',
                'orderDtls.item.uom',
            ])
            ->where([
                'id' => $this->dto->odr_hdr_id,
                'whs_id' => $this->dto->whs_id,
            ])
            ->first();

        if (!$this->odrHdr) {
            throw new UserException(Language::translate('Order not found'));
        }

        $this->orderDtls = $this->odrHdr->orderDtls()
            ->with('item.uom')
            ->select([
                '*',
                DB::raw('SUM(piece_qty) as ttl_qty'),
            ])
            ->groupBy('item_id', 'price')
            ->havingRaw('ttl_qty > 0')
            ->get();

        return $this;
    }

    protected function loadReportTemplate()
    {
        if ($this->dto->with_price) {
//            $this->template = 'OutBound.DeliveryNoteTemplateWithPrice';
        }

        if ($shippedDate = data_get($this->odrHdr, 'shipped_dt')) {
            $shippedDate = Carbon::parse($shippedDate);
            $date = sprintf("Ngày %s tháng %s năm %d", $shippedDate->format('d'), $shippedDate->format('m'),
                $shippedDate->year);
        } elseif ($createWvDate = data_get($this->odrHdr, 'wvHdr.created_at')) {
            $createWvDate = Carbon::parse($createWvDate);
            $date = sprintf("Ngày %s tháng %s năm %d", $createWvDate->format('d'), $createWvDate->format('m'),
                $createWvDate->year);
        } else {
            $now = Carbon::now();
            $date = sprintf("Ngày %s tháng %s năm %d", $now->format('d'), $now->format('m'), $now->year);
        }
        $ship_address = data_get($this->odrHdr, 'ship_to_add', '');

        $this->html = (string)view($this->template, [
            'odrHdr' => $this->odrHdr,
            'orderDtls' => $this->orderDtls,
            'date' => $date,
            'ship_address' => $ship_address,
            'num_of_copy' => 1,
        ]);

        return $this;
    }

    protected function exportPdf()
    {
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A5',
        ]);
        $mpdf->WriteHTML($this->html);

        $filename = data_get($this->odrHdr, 'odr_num', 'DeliveryNote');
        $mpdf->Output("$filename.pdf", "D");
    }
}