<!DOCTYPE html>
﻿<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta charset="utf-8"/>
    <title>{{$titleName}}</title>
    <style type="text/css">
        h4 {
            margin-bottom: 5px;
        }

        h1 {
            color: #504e4f;
            font-size: 32px;
            font-weight: 100;
            margin: 0;
        }

        h2 {
            color: #364150;
            text-transform: uppercase;
            margin: 5px 0;
            font-weight: 500;
        }

        body {
            background-color: #fff;
            color: #000;
            font-size: 14px;
            font-family: "Calibri";
            width: 21cm;
            height: 29.7cm;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
            margin: 0 auto;

        }

        .table-style thead tr th {
            font-weight: 400;
            text-transform: uppercase;
            text-align: left;
            color: #a9a9a9;
            font-size: 19px;
        }

        .table-style tbody tr td {
            padding-top: 5px;
            vertical-align: top;
            padding-bottom: 20px;
        }

        .table-style tbody tr td b {
            font-size: 20px;
            font-weight: 600;
            color: #282828;
        }

        .table-style1 thead tr th {
            color: #36c6d3;
            padding: 0 5px;
            border-bottom: 1px dashed #d2d2d2;
            font-size: 16px;
            text-transform: uppercase;
            font-weight: bold;
            text-align: left;
            border-right: 1px solid #e3f1f2;
        }

        .table-style1 tbody tr td {
            border-bottom: 1px dashed #ddd;
            padding: 0 5px;
            color: #595959;
        }

        .table-style1 tbody tr:last-child td {
            border-bottom: none;
        }

        .table-style1 tbody tr td.title {
            border-right: 1px solid #ddd;
            background-color: #e3f1f2;
            width: 30%;
        }

        .table-style2 {
            border: 1px solid #a9a9a9;
            margin: 15px 0;
        }

        .table-style2 thead tr th {
            font-weight: 600;
            border-bottom: 1px solid #c0c0c0;
            text-align: center;
            background-color: #0d5aa7;
            border-right: 1px solid #c1c1c1;
            color: white;
            line-height: 20px;
        }

        .table-style2 tbody tr td {
            border-right: 1px dashed #d5d5d5;
            border-bottom: 1px dashed #d5d5d5;
            color: #595959;
            line-height: 30px;
        }

        .table-style2 tbody tr td:last-child {
            border-right: none;
        }

        .table-style2 tbody tr td.title {
            font-weight: 600;
            color: #364150;
        }

        .table-style2 tbody tr:last-child td {
            border-bottom: none
        }

        .table-style3 thead tr th {
            font-weight: 600;
            text-align: left;
            color: #364150;
            text-transform: capitalize;
        }

        .table-style3 tr td {
            line-height: 20px;
            font-size: 14px;
            color: #595959;
            padding-right: 5px;
        }

        .table-style3 tr td b {
            color: #1c1c1c;
        }
    </style>
</head>
<body>

<table class="full-width" style="width: 100%; margin-bottom: 10px;" cellpadding="0" cellspacing="0">
    <tbody>
        <tr>
            <td style="vertical-align: middle; text-align: left; width: 30%">
                <img width="150" src="{{ common_wms_assets('images/logo-elmich.png') }}"/>
            </td>   
            <td style="text-align: center; white-space: nowrap; width: 40%">
                <h3>{{\Illuminate\Support\Str::upper($titleName)}}</h3>
            </td>
            <td style="text-align: right; width: 30%">

            </td>
        </tr>
    </tbody>
</table>
@if($data)
    <table class="table-style2" cellpadding="0" cellspacing="0" width="100%">
        <thead>
        <tr>
            @foreach($titles as $title)
                <th style="text-align: center; width: auto">{{$title}}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                @foreach($item as $val)
                    <td style="text-align: left; width: auto; vertical-align: middle; padding: 2px;">{{$val}}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
@endif

</body>
</html>
