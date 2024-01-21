<?php
?>
<table>
    <thead>
    <tr>
        <th style="vertical-align: top; padding: 4px; height: 44px;"><img width="160" src="{{ common_wms_assets('images/logo-elmich.png') }}"></th>
        <th colspan="{{count($titles) - 1}}" style="text-align: center; vertical-align: center; font-weight: 400; font-size: 18px">{{\Illuminate\Support\Str::upper($titleName)}}</th>
    </tr>
    <tr>
        @foreach($titles as $title)
            <th></th>
        @endforeach
    </tr>
    <tr>
        @foreach($titles as $title)
            <th style="background-color: #0d5aa7; color: #ffffff; vertical-align: center; height: 30px; border: 1px solid #7a7a7a; text-align: center; width: 18px">{{$title}}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @if($data)
        @foreach($data as $item)
        <tr>
            @foreach($item as $value)
                <td>{{$value}}</td>
            @endforeach
        </tr>
        @endforeach
    @endif
    </tbody>
</table>
