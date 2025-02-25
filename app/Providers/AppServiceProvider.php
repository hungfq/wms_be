<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Writer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Schema::defaultStringLength(191);
        Writer::listen(BeforeExport::class, function () {
            //
        });

        Writer::listen(BeforeWriting::class, function () {
            //
        });

        Sheet::listen(BeforeSheet::class, function () {
            //
        });

        Sheet::listen(AfterSheet::class, function () {
            //
        });

        Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
            $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
        });
        Writer::macro('setCreator', function (Writer $writer, string $creator) {
            $writer->getDelegate()->getProperties()->setCreator($creator);
        });
        Sheet::macro('setOrientation', function (Sheet $sheet, $orientation) {
            $sheet->getDelegate()->getPageSetup()->setOrientation($orientation);
        });
    }

    public function boot()
    {
        if ( env('APP_ENV') === 'local' ) {
            app("db")->connection()->setEventDispatcher(app("events"));

            DB::listen(function($query) {
                $qs = str_replace(array('?'), array('\'%s\''), $query->sql);
                $qs = vsprintf($qs, $query->bindings);
                $qs .= sprintf(' [%s ms]', $query->time);
                Log::info($qs);
            });
        }
    }
}
