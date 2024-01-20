<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Libraries\Config;
use Illuminate\Support\Facades\Storage;

trait HasImageTrait
{
    public static $localStorage;
    public static $uploadDir;

    public static function setFolder($folder)
    {
        self::$uploadDir = $folder;
    }

    public function getFullPathAttribute()
    {
        return sprintf('%s/%s/%s', env('DOC_PREFIX_ENV'), $this->uploadDir(), $this->image_name);
    }

    public function uploadDir()
    {
        return static::$uploadDir ?? static::UPLOAD_DIR;
    }

    public function getExpireUrlAttribute()
    {
        if (!$this->isExistInStorage()) {
            return null;
        }

        return Storage::temporaryUrl($this->full_path, Carbon::now()->addMinutes(Config::get('IMAGE_EXPIRE_TIME_IN_SECOND')));
    }

    public function getPublicUrlAttribute()
    {
        if (!$this->isExistInStorage()) {
            return null;
        }

        Storage::setVisibility($this->full_path, 'public');

        return Storage::url($this->full_path);
    }

    protected function isExistInStorage()
    {
        return Storage::has($this->full_path);
    }

    public function downloadToLocal()
    {
        if (!$this->isExistInStorage()) {
            return;
        }

        if (!static::$localStorage) {
            static::$localStorage = Storage::disk('public');
        }

        $image = Storage::get($this->full_path);

        static::$localStorage->put($this->full_path, $image);

        $this->setAttribute('local_path', storage_path('app/public/' . $this->full_path));
    }
}