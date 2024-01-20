<?php

namespace App\Traits;

trait GetConfigTrait
{

    /**
     * @param $keyOrType
     *
     * @return array|string
     */
    public static function get($keyOrType)
    {
        $configs = array_change_key_case(self::$configs, CASE_UPPER);

        return array_get($configs, strtoupper($keyOrType), null);
    }

    /**
     * @param $groupName
     * @param null $key
     * @return |null
     */
    public static function getByKey($groupName, $key = null)
    {
        $configs = array_change_key_case(self::$configs, CASE_UPPER);

        if (empty($configs[strtoupper($groupName)])) {
            return null;
        }

        if (!empty($key)) {
            $config = is_array($configs[strtoupper($groupName)]) ?
                array_change_key_case($configs[strtoupper($groupName)], CASE_UPPER) : [];

            return array_get($config, strtoupper($key), null);
        }

        $status = self::$configs[strtoupper($groupName)] ?: null;

        return !is_array($status) ? $status : null;
    }

    /**
     * @param $value
     * @param null $groupName
     * @return false|int|string|null
     */
    public static function getByValue($value, $groupName = null)
    {

        if (!empty($groupName)) {
            if (empty(self::$configs[strtoupper($groupName)])) {
                return null;
            }

            $array = self::$configs[strtoupper($groupName)];

            return array_search(strtoupper($value), array_map('strtoupper', $array));
        }

        return array_search(strtoupper($value), array_map(function ($e) {
            if (!is_array($e)) {
                return strtoupper($e);
            }
        }, self::$configs));
    }
}