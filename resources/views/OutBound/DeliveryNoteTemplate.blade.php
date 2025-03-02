<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Phiếu Xuất Kho</title>
    <style>
        td, th {
            min-width: 100px;
            height: 22px;
        }

        page {
            background: white;
            display: block;
            margin: 0 auto;
            margin-bottom: 0.5cm;
            box-shadow: 0 0 0.5cm rgba(0, 0, 0, 0.5);
        }

        page[size="A5"] {
            width: 14.8cm;
            height: 21cm;
        }

        page[size="A5"][layout="landscape"] {
            width: 21cm;
            height: 14.8cm;
        }

        @media print {
            body, page {
                margin: 0;
                box-shadow: none;
            }
        }

        body {
            font-family: "Arial", sans-serif;
            font-size: 14px;
        }

        .td-underline {
            border-bottom: 1px solid #282828;
        }

        .tb-border table, .tb-border td {
            border: 1px solid #262626;
        }

        .tb-border th {
            border: 1px solid #262626;
            background-color: #d6f5d6;
            vertical-align: middle;
        }

        @page {
            margin: 0.8cm;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
@for($pageIndex = 1; $pageIndex <= $num_of_copy; $pageIndex++)
    <page size="A5">
        <table style="border-collapse: collapse; width: 100%;">
            <tr>
                <td width="60%">
                    <b>CỬA HÀNG VTNN MINH HOÀNG</b><br/>
                    Ấp Tân Phước, Xã Tân Hiệp B, Huyện Tân Hệp,<br/>
                    Tỉnh Kiên Giang
                </td>
                <td width="40%" style="text-align: center">
                    <b>Mẫu số: 02 - VT</b><br/>
                    <i>(Ban hành theo Thông tư số: 200/2014/TT-BTC ngày 22/12/2014 của Bộ Tài Chính)</i>
                </td>
            </tr>
        </table>
        <table style="border-collapse: collapse; width: 100%; margin-top: 5px">
            <tr>
                <td width="30%"></td>
                <td width="40%" style="text-align: center">
                    <h2>PHIẾU XUẤT KHO</h2>
                    <i>{{ $date }}</i><br/>
                    {{ "Số phiếu: "  . data_get($odrHdr, 'odr_num') }}<br/>
                </td>
                <td width="30%" style="vertical-align: top; text-align: center;">
                    {{--                    <barcode code="{{ data_get($odrHdr, 'odr_num') }}" type="C128B"--}}
                    {{--                             class="barcode" size="0.8" height="1.3" pr="0.5"/>--}}
                    {{--                    <span> {{ data_get($odrHdr, 'odr_num') }} </span>--}}
                </td>
            </tr>
        </table>

        <table style="border-collapse: collapse; width: 100%; margin-top: 10px; vertical-align: top">
            <tr>
                <td width="20%">{{ "Khách hàng: " }}</td>
                <td class="td-underline">{{ data_get($odrHdr, 'code') . ' - ' .data_get($odrHdr, 'ship_to_name') }}</td>
            </tr>
            <tr>
                <td>{{ "Địa chỉ giao: " }}</td>
                <td class="td-underline">{{ data_get($odrHdr, 'ship_to_add') }}</td>
            </tr>
            <tr>
                <td>{{ "Ghi chú: " }}</td>
                <td class="td-underline">{{ data_get($odrHdr, 'cus_notes') }}</td>
            </tr>
        </table>

        <table class="tb-border"
               style="border-collapse: collapse; font-size: 12px; width: 100%; margin-top: 20px; vertical-align: top">
            <thead>
            <tr>
                <th>{{ "STT" }}</th>
                <th>{{ "Mã số" }}</th>
                <th>{{ "Tên vật tư" }}</th>
                <th>{{ "ĐVT" }}</th>
                <th>{{ "Số lượng" }}</th>
                <th>{{ "Đơn giá" }}</th>
                <th>{{ "Thành tiền" }}</th>
            </tr>
            <tr style="text-align: center">
                <th>A</th>
                <th>B</th>
                <th>C</th>
                <th>D</th>
                <th>1</th>
                <th>2</th>
                <th>3</th>
            </tr>
            </thead>
            <tbody>
            @php
                $ttl_amount = 0;
            @endphp
            @foreach($orderDtls as $index => $orderDtl)
                @php
                    $lineAmount = data_get($orderDtl, 'price') * data_get($orderDtl, 'ttl_qty', 0);
                    $ttl_amount += $lineAmount;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ data_get($orderDtl, 'item.sku') }}</td>
                    <td>{{ data_get($orderDtl, 'item.item_name') }}</td>
                    <td>{{ data_get($orderDtl, 'item.uom.name') }}</td>
                    <td style="text-align: center">{{ data_get($orderDtl, 'ttl_qty') }}</td>
                    <td style="text-align: right">{{ number_format(data_get($orderDtl, 'price'), 0, ',') }}</td>
                    <td style="text-align: right">{{ number_format($lineAmount, 0, ',') }}</td>
                </tr>
            @endforeach
            <tr>
                <td></td>
                <td>{{ "Cộng" }}</td>
                <td style="text-align: center">{{ "x" }}</td>
                <td style="text-align: center">{{ "x" }}</td>
                <td style="text-align: center">{{ "x" }}</td>
                <td style="text-align: center">{{ "x" }}</td>
                <td style="text-align: right">{{ number_format($ttl_amount, 0, ',') }}</td>
            </tr>
            </tbody>
        </table>

        {{--        <div style="width: 100%;">--}}
        {{--            {{ "- Số chứng từ gốc kèm theo:      0 chứng từ gốc" }}--}}
        {{--        </div>--}}

        <div style="width: 100%; text-align: right; margin-top: 5px">
            <i>{{ $date }}</i>
        </div>

        <table style="border-collapse: collapse; width: 100%; margin-top: 20px; vertical-align: top; text-align: center;">
            <tr>
                <td width="50%">
                    <b>{{ "Người lập phiếu" }}</b><br/>
                    <i>{{ "(Ký, họ tên)" }}</i><br/>
                </td>
                <td width="50%">
                    <b>{{ "Người nhận hàng" }}</b><br/>
                    <i>{{ "(Ký, họ tên)" }}</i>
                </td>
            </tr>
        </table>
    </page>
    @if ($pageIndex != $num_of_copy)
        <div class="page-break"></div>
    @endif
@endfor
</body>
</html>
