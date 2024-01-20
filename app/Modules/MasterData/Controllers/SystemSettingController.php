<?php

namespace App\Modules\MasterData\Controllers;

use App\Entities\WhsConfig;
use App\Http\Controllers\ApiController;
use App\Libraries\Config;
use Illuminate\Http\Request;

class SystemSettingController extends ApiController
{
    protected $request;

    protected $settings = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->settings = Config::get(WhsConfig::CONFIG_KEY);
    }
    public function getSettingsOfWhs($whsId)
    {
        $settings = WhsConfig::select(['config_code', 'json_value', 'is_show'])
            ->where('whs_id', $whsId)
            ->whereIn('config_code', array_keys($this->settings))
            ->get();

        $settings = $settings->keyBy('config_code')->toArray();

        $settings = array_merge($this->settings, $settings);

        return ['data' => $settings];
    }

    public function upsertSettingsOfWhs($whsId)
    {
        $settings = $this->request->all();

        foreach ($settings as $configCode => $value) {
            $attributes = [
                'config_code' => $configCode,
                'config_value' => $configCode,
                'json_value' => $value
            ];

            WhsConfig::updateOrCreate(
                ['whs_id' => $whsId, 'config_code' => $configCode],
                $attributes
            );
        }

        return $this->responseSuccess();
    }
}
