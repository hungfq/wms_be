<?php

namespace App\Libraries;

use Carbon\Carbon;
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

    public static function formatNumber($number)
    {
        return number_format($number, 0, '.', ',');
    }

    public static function formatNumberTotalM3($number)
    {
        if (!$number || in_array($number, ['0.000', '0.0000'])) {
            return 0;
        }

        $number = number_format($number, 3, '.', ',');

        return self::formatNumberTypeFloat($number);
    }

    public static function formatNumberTypeFloat($val)
    {
        if (strpos($val, ',')) {

            $values = explode(',', $val);

            $endValue = $values[count($values) - 1];

            unset($values[count($values) - 1]);

            $intNumber = implode(',', $values) . ',';

            preg_match("#^([0-9]*)(\.([0-9]*?))(0*)$#", trim($endValue), $o);
            return ($intNumber ? $intNumber : '') . $o[1] . ($o[2] != '.' ? $o[2] : '');
        } else {
            preg_match("#^([+\-]|)([0-9]*)(\.([0-9]*?))(0*)$#", trim($val), $o);
            return $o[1] . sprintf('%d', $o[2]) . ($o[3] != '.' ? $o[3] : '');
        }
    }

    public static function formatToDate($value)
    {
        if (empty($value)) {
            return $value;
        }

        $date = Carbon::createFromFormat('Y-m-d H:i:s', new Carbon($value), 'UTC');
        return $date->setTimeZone(Data::getConfigTimeZone())->format(Data::getConfigDate());
    }

    public static function formatToDateTime($value)
    {
        if (empty($value)) {
            return $value;
        }

        $dateTime = Carbon::createFromFormat('Y-m-d H:i:s', new Carbon($value), 'UTC');
        return $dateTime->setTimeZone(Data::getConfigTimeZone())->format(Data::getConfigDateTime());
    }

    public static function formatToDateTimeUTC($value)
    {
        if (empty($value)) {
            return $value;
        }

        $dateTime = Carbon::createFromFormat('Y-m-d H:i:s', new Carbon($value), 'UTC');
        return $dateTime->format(Data::getConfigDateTime());
    }

    public static function getTxtYesOrNoOfSerial($isSerial, $withTranslate = false)
    {
        $string = $isSerial ? 'Yes' : 'No';
        if ($withTranslate) {
            $string = Language::translate($string);
        }
        return $string;
    }

    public static function calculateCartonQty($qty, $pack_size)
    {
        $ctnTtl = floor($qty / $pack_size);
        $pieceRemain = $qty - ($ctnTtl * $pack_size);

        return [
            'piece_init' => $pack_size,
            'ctn_ttl' => $ctnTtl,
            'piece_remain' => $pieceRemain,
        ];
    }
}
