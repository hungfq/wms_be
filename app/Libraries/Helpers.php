<?php

namespace App\Libraries;

use Illuminate\Support\Str;

class Helpers
{
    public static function sortBuilder(&$query, $attributes = [], $mapFields = [], $ignoreUpdatedAt = false)
    {
        $table = $query->getModel()->getTableName();

        //process map fields
        if (isset($attributes['sort']) && !empty($mapFields)) {
            foreach ($attributes['sort'] as $key => $val) {
                if (isset($mapFields[$key])) {
                    $attributes['sort'][$mapFields[$key]] = $val;
                } else {
                    $attributes['sort']["{$table}.$key"] = $val;
                }

                unset($attributes['sort'][$key]);
            }
        }

        $validConditions = ['asc', 'desc'];

        if (empty($attributes['sort']) && !$ignoreUpdatedAt) {
            $attributes['sort'] = [
                "{$table}.updated_at" => 'DESC'
            ];
        }

        if (isset($attributes['sort'])) {
            foreach ($attributes['sort'] as $key => $value) {
                if (!$value) {
                    $value = 'asc';
                }

                if (!in_array(Str::lower($value), $validConditions)) {
                    continue;
                }

                $query->orderBy($key, $value);
            }
        }
    }
}
