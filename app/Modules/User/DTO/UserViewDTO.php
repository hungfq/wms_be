<?php

namespace App\Modules\User\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class UserViewDTO extends FlexibleDataTransferObject
{
    public $search;
    public $type;
    public $is_active;
    public $not_done_any_topic;
    public $ignore_schedule_id;
    public $ignore_ids;
    public $limit;
    public $sort;

    public static function fromRequest($request = null)
    {
        $request = $request ?? app('request');

        return new self([
            'search' => $request->input('search'),
            'type' => $request->input('type'),
            'is_active' => $request->input('is_active'),
            'not_done_any_topic' => $request->input('not_done_any_topic'),
            'ignore_schedule_id' => $request->input('ignore_schedule_id'),
            'ignore_ids' => $request->input('ignore_ids') ?? [],
            'limit' => $request->input('limit'),
            'sort' => $request->input('sort'),
        ]);
    }
}