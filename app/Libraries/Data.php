<?php

namespace App\Libraries;

use App\Entities\ConfigApply;
use App\Entities\CustomerInUser;
use App\Entities\User;
use App\Entities\Warehouse;
use App\Entities\WhsConfig;
use App\Exceptions\UserException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class Data
{
    private static $_data = null;
    private static $defaultUserCacheKey = 'info';
    private static $currentWarehouse;
    private static $currentUser;

    private static $configs = [];

    public static function getUserInfo()
    {
        if (self::$_data) {
            return self::$_data;
        }

        $info = self::getUserCache();
        if ($info) {
            self::$_data = $info;
            return self::$_data;
        }

        self::setUserInfo();
        self::$_data = self::getUserCache();
        return self::$_data;
    }

    public static function setUserCache($data, $key = null)
    {
        $prefix = Cache::getPrefix();

        $userCachePrefix = env('CACHE_USER_PREFIX', 'users');

        if ($userCachePrefix) {
            Cache::setPrefix($userCachePrefix);
        }

        Cache::put(self::getUserCacheKey($key), $data);

        if ($userCachePrefix) {
            Cache::setPrefix($prefix);
        }
    }

    public static function getUserCache($key = null)
    {
        $prefix = Cache::getPrefix();

        $userCachePrefix = env('CACHE_USER_PREFIX', 'users');

        if ($userCachePrefix) {
            Cache::setPrefix($userCachePrefix);
        }

        $result = Cache::get(self::getUserCacheKey($key));

        if ($userCachePrefix) {
            Cache::setPrefix($prefix);
        }

        return $result;
    }

    public static function clearUserCache($key = null)
    {
        $prefix = Cache::getPrefix();

        $userCachePrefix = env('CACHE_USER_PREFIX', 'users');

        if ($userCachePrefix) {
            Cache::setPrefix($userCachePrefix);
        }

        Cache::forget(self::getUserCacheKey($key));

        if ($userCachePrefix) {
            Cache::setPrefix($prefix);
        }
    }

    public static function clearCacheOfUser($userId)
    {
        $prefix = Cache::getPrefix();

        $userCachePrefix = env('CACHE_USER_PREFIX', 'users');

        if ($userCachePrefix) {
            Cache::setPrefix($userCachePrefix);
        }

        Cache::forget(self::getCacheKeyOfUser($userId));

        if ($userCachePrefix) {
            Cache::setPrefix($prefix);
        }
    }

    public static function hasUserCache($key = null)
    {
        $prefix = Cache::getPrefix();

        $userCachePrefix = env('CACHE_USER_PREFIX', 'users');

        if ($userCachePrefix) {
            Cache::setPrefix($userCachePrefix);
        }

        $result = Cache::has(self::getUserCacheKey($key));

        if ($userCachePrefix) {
            Cache::setPrefix($prefix);
        }

        return $result;
    }

    private static function getUserCacheKey($key = null)
    {
        $key = $key ?: self::$defaultUserCacheKey;

        return sprintf('%s:%s', Auth::id(), $key);
    }

    private static function getCacheKeyOfUser($userId, $key = null)
    {
        $key = $key ?: self::$defaultUserCacheKey;

        return sprintf('%s:%s', $userId, $key);
    }

    public static function getCurWhs()
    {
        self::getUserInfo();
        return self::$_data['whs_id'];
    }

    public static function getCustomersInCurWhs()
    {
        self::getUserInfo();
        return self::$_data['customers'] ?? [];
    }

    public static function getRoles()
    {
        self::getUserInfo();
        return self::$_data['roles'] ?? [];
    }

    public static function getLanguageCode()
    {
        self::getUserInfo();
        return self::$_data['language'];
    }

    public static function getCurrentWarehouse()
    {
        if (!self::$currentWarehouse) {
            $whsId = self::getCurWhs();
            self::$currentWarehouse = Warehouse::with(['configs'])->where('whs_id', $whsId)->first();
        }

        return self::$currentWarehouse;
    }

    public static function getCurrentUser()
    {
        if (!self::$currentUser) {
            self::$currentUser = Auth::user();
        }

        return self::$currentUser;
    }

    public static function getConfigDate()
    {
        self::getUserInfo();
        return self::$_data['configs']['DAT'];
    }

    public static function getConfigDateTime()
    {
        self::getUserInfo();
        return self::getConfigDate() . ' ' . self::$_data['configs']['TIM'];
    }

    public static function getConfigTimeZone()
    {
        self::getUserInfo();
        return self::$_data['configs']['TMZ'];
    }

    public static function getPalletPrefixs()
    {
        self::getUserInfo();
        return self::$_data['pallet_prefixs'];
    }

    public static function setOutInfoData()
    {
        self::getUserInfo();

        $lastWhsId = data_get(self::$_data, 'whs_id');
        $lastLanguage = data_get(self::$_data, 'language') ?? 'vi';
        $themeCode = data_get(self::$_data, 'theme_code') ?? 'theme_light';
        $menuType = data_get(self::$_data, 'menu_type') ?? 'menu_type_tree';
        $palletPrefixs = data_get(self::$_data, 'pallet_prefixs') ?? [];

        Data::setUserCache($lastWhsId, 'last_whs_id');
        Data::setUserCache($lastLanguage, 'last_language');
        Data::setUserCache($themeCode, 'theme_code');
        Data::setUserCache($menuType, 'menu_type');
        Data::setUserCache($palletPrefixs, 'pallet_prefixs');
    }

    public static function clearCacheInfoOfAllUsers()
    {
        $prefix = Cache::getPrefix();

        $userCachePrefix = env('CACHE_USER_PREFIX', 'users');

        if ($userCachePrefix) {
            Cache::setPrefix($userCachePrefix);
        }

        $users = User::select('id')->get();
        foreach ($users as $user) {
            $cacheKey = sprintf('%s:%s', $user->id, self::$defaultUserCacheKey);
            Cache::forget($cacheKey);
        }

        if ($userCachePrefix) {
            Cache::setPrefix($prefix);
        }
    }

    public static function setUserInfo()
    {
        $info = [];

        $language = self::getUserCache('last_language');

        $whsId = self::getUserCache('last_whs_id');

        if (!$whsId) {
            $userWarehouse = DB::connection('mysql')->table('user_warehouse')
                ->where('user_id', Auth::id())
                ->orderBy('whs_id', 'ASC')
                ->first();

            $whsId = data_get($userWarehouse, 'whs_id');
        }

        $roles = DB::connection('mysql')->table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', Auth::id())
            ->get()
            ->pluck('name')
            ->all();

        // ------- Start get customer of user base on role and last whs id ----------
        $customerInUserQuery = CustomerInUser::select(['cus_id', 'whs_id'])
            ->where('user_id', Auth::id());

        if ($whsId) {
            $customerInUserQuery->where('whs_id', $whsId);
        }

        $customerInUsers = $customerInUserQuery->groupBy(['cus_id'])->get();

        $customers = [];

        if (!count($customerInUsers)) {
            if (!$whsId) {
                $warehouse = DB::connection('mysql')->table('warehouses')->where('status', 'AC')->orderBy('whs_id', 'ASC')->first();

                if (!$warehouse) {
                    throw new UserException(Language::translate('Please create new Warehouse first!'));
                }

                $whsId = $warehouse->whs_id;
            }
        } else {
            $customerInUserFirst = $customerInUsers->first();
            $whsId = $customerInUserFirst->whs_id;

            foreach ($customerInUsers as $customerInUser) {
                if ($customerInUser->whs_id == $whsId) {
                    $customers[] = $customerInUser->cus_id;
                }
            }
        }

        // ------- End get customer of user base on role and last whs id ----------

        $palletPrefixs = DB::connection('mysql')
            ->table('pallet_prefixes')
            ->select([
                'code'
            ])
            ->get();

        $configs = ConfigApply::join('configs', 'configs.id', '=', 'config_applies.config_id')
            ->select([
                'configs.code',
                'configs.value'
            ])->pluck('value', 'code')->toArray();


        $info = array_merge($info, [
            'language' => $language ?? 'vi',
            'whs_id' => $whsId,
            'configs' => $configs,
            'customers' => $customers ?? [],
            'roles' => $roles ?? [],
            'pallet_prefixs' => $palletPrefixs->toArray(),
        ]);

        self::setUserCache($info);
    }

    public static function getWhsConfig($key = null)
    {
        $warehouse = self::getCurrentWarehouse();

        if (!self::$configs) {
            $configs = Config::get(WhsConfig::CONFIG_KEY);

            $dbConfigs = WhsConfig::select(['id', 'config_code', 'json_value'])
                ->where('whs_id', $warehouse->whs_id)
                ->whereIn('config_code', array_keys($configs))
                ->get()
                ->keyBy('config_code');

            if ($dbConfigs->count()) {
                $configs = array_merge($configs, $dbConfigs->toArray());
            }

            self::$configs = $configs;
        }

        if ($key) {
            $config = data_get(self::$configs, $key);

            if ($value = data_get($config, 'json_value')) {
                return $value;
            } else {
                return $config;
            }
        }

        return self::$configs;
    }
}