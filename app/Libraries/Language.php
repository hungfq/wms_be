<?php

namespace App\Libraries;

use App\Entities\LanguageHdr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Language
{
    private static $userLangKey = null;
    private static $lgType = null;
    private static $lgTypeFE = null;
    private static $languages = null;
    private static $languagesFE = null;
    private static $messages = null;

    public static function addMessage($messageInput)
    {
        if(empty(trim($messageInput))) {
            return null;
        }

        if (!self::$userLangKey) {
            $data = new Data();
            $lgCode = $data->getLanguageCode();
            self::$userLangKey = !empty($lgCode) ? $lgCode : 'vi';
        }

        if (!self::$lgType) {
            self::$lgType = env('LANGUAGE_TYPE') ?? 'BE';
        }

        if (!self::$messages) {
            self::$messages = LanguageHdr::select('languages.message')
                ->where('languages.lg_type', self::$lgType)
                ->pluck('message', 'message')->all();
        }

        $message = trim($messageInput);

        if (!isset(self::$messages[$message])) {
            $userId = 1;

            $id = DB::connection('mysql')->table('languages')->insertGetId([
                'message' => $message,
                'lg_type' => self::$lgType,
                'created_by' => $userId,
                'updated_by' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'deleted_at' => '1999-01-01 00:00:00',
                'deleted' => 0
            ]);

            DB::connection('mysql')->table('language_dtl')->insert([
                'lg_id' => $id,
                'language_code' => self::$userLangKey,
                'translate' => '',
                'created_by' => $userId,
                'updated_by' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'deleted_at' => '1999-01-01 00:00:00',
                'deleted' => 0
            ]);

            self::$messages[$message] = $messageInput;
        }

        return $messageInput;
    }

    public static function translate($messageInput, ...$params)
    {
        if(! $messageInput) {
            return null;
        }

        if (!self::$userLangKey) {
            $data = new Data();
            $lgCode = $data->getLanguageCode();
            self::$userLangKey = !empty($lgCode) ? $lgCode : 'vi';
        }

        if (!self::$lgType) {
            self::$lgType = env('LANGUAGE_TYPE') ?? 'BE';
        }

        if (!self::$languages) {
            self::$languages = LanguageHdr::select('languages.message', DB::raw("IF(dtl.translate IS NULL, '', dtl.translate) as translate"))
                ->join('language_dtl AS dtl', 'dtl.lg_id', '=', 'languages.lg_id')
                ->where('languages.lg_type', self::$lgType)
                ->where('dtl.language_code', self::$userLangKey)
                ->pluck('translate', 'message')->all();
        }

        $message = trim($messageInput);

        if (isset(self::$languages[$message])) {
            $translation = self::$languages[$message];
        } else {
            $userId = Auth::id();
            $id = DB::connection('mysql')->table('languages')->where('message', $message)->where('languages.lg_type', self::$lgType)->value('lg_id');

            if (!$id) {
                $id = DB::connection('mysql')->table('languages')->insertGetId([
                    'message' => $message,
                    'lg_type' => self::$lgType,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'deleted_at' => '1999-01-01 00:00:00',
                    'deleted' => 0
                ]);
            }

            $isExists = DB::connection('mysql')->table('language_dtl')->where('lg_id', $id)->where('language_code', self::$userLangKey)->exists();

            if (!$isExists) {
                DB::connection('mysql')->table('language_dtl')->insert([
                    'lg_id' => $id,
                    'language_code' => self::$userLangKey,
                    'translate' => $message,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'deleted_at' => '1999-01-01 00:00:00',
                    'deleted' => 0
                ]);
            }

            self::$languages[$message] = '';
            $translation =  $messageInput;
        }

        $translation = !empty($translation) ? $translation : $messageInput;

        if (!empty($params)) {
            foreach ($params as $key => $param) {
                $translation = str_replace("{{$key}}", $param, $translation);
            }
        }

        return $translation;
    }

    public static function translateMulti($messageInput)
    {
        if (empty($messageInput)) {
            return $messageInput;
        }

        foreach ($messageInput as $key => $msg) {
            $messageInput[$key] = static::translate($msg);
        }

        return $messageInput;
    }

    public static function translateMultiFE($messageInput)
    {
        if (empty($messageInput)) {
            return $messageInput;
        }

        foreach ($messageInput as $key => $msg) {
            $messageInput[$key] = static::translateFE($msg);
        }

        return $messageInput;
    }

    public static function translateFE($messageInput)
    {
        if(! $messageInput) {
            return null;
        }

        if (!self::$userLangKey) {
            $data = new Data();
            $lgCode = $data->getLanguageCode();
            self::$userLangKey = !empty($lgCode) ? $lgCode : 'vi';
        }

        if (!self::$lgTypeFE) {
            self::$lgTypeFE = 'FE';
        }

        if (!self::$languagesFE) {
            self::$languagesFE = LanguageHdr::select('languages.message', 'dtl.translate')
                ->join('language_dtl AS dtl', 'dtl.lg_id', '=', 'languages.lg_id')
                ->where('languages.lg_type', self::$lgTypeFE)
                ->where('dtl.language_code', self::$userLangKey)
                ->pluck('translate', 'message')->all();
        }

        return self::$languagesFE[trim($messageInput)] ?? $messageInput;
    }
}