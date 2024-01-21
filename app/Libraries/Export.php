<?php

namespace App\Libraries;

use App\Entities\TableConfig;
use Dingo\Api\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;

class Export
{
    const TYPE_PDF = 'PDF';
    const TYPE_EXCEL = 'XLSX';
    private static $tableName = false;

    private static function formatData(array $data, array $title)
    {
        if (empty($data)) {
            return [];
        }

        $dataSave = [];

        foreach ($data as $itmK => $item) {
            $temp = [];

            foreach ($title as $key => $field) {
                $values = explode("|", $key);
                $value = "";

                if (count($values) == 1) {
                    $value = data_get($item, $values[0], null);
                } else if (!empty($values[1])) {
                    switch ($values[1]) {
                        case "*":
                            $value = data_get($item, $values[0], null) * data_get($item, $values[2], null);
                            break;
                        case ".":
                            $value = trim(data_get($item, $values[0], null) . " " .
                                data_get($item, $values[2], null));
                            break;
                        case "format_datetime":
                            $value = Helpers::formatToDateTime(data_get($item, $values[0], null));
                            break;
                        case "format_datetime_utc":
                            $value = Helpers::formatToDateTimeUTC(data_get($item, $values[0], null));
                            break;
                        case "format_date":
                            if (count(explode(',', data_get($item, $values[0], null))) > 1) {
                                $value = [];
                                foreach (explode(',', data_get($item, $values[0], null)) as $date) {
                                    $value[] = Helpers::formatToDate($date);
                                }
                                $value = implode(', ', $value);
                            } else {
                                $value = Helpers::formatToDate(data_get($item, $values[0], null));
                            }
                            break;
                        case "translate":
                            $value = Language::translate(data_get($item, $values[0], null));
                            break;
                        case "format_string":
                            $value = '="' . data_get($item, $values[0], null);
                            break;
                        case "format_number":
                            $value = Helpers::formatNumber(data_get($item, $values[0], null));
                            break;
                        case "format_number_new":
                            $value = (int)str_replace(',', '', data_get($item, $values[0], null));
                            break;
                        case "format_number_m3":
                            $value = Helpers::formatNumberTotalM3(data_get($item, $values[0], null));
                            break;
                    }
                }

                $value = (is_numeric($value) || str_contains($values[0], 'add')) ? $value : mb_strtoupper($value);

                $temp[] = $value;
            }

            ++$itmK;
            $dataSave[$itmK] = $temp;
        }

        return $dataSave;
    }

    private static function formatTitle(array $title)
    {
        if (empty($title)) {
            return [];
        }

        $titleNew = [];

        if (!empty(self::$tableName)) {
            $value = TableConfig::where(['table_name' => self::$tableName, 'user_id' => Data::getCurrentUser()->id])->value('value');

            if (!empty($value)) {
                $titleFormat = [];

                foreach ($title as $key => $field) {
                    $values = explode("|", $key);
                    $titleFormat[$values[0]] = $key;
                }

                foreach ($value as $item) {
                    if (!$item['isShow']) {
                        continue;
                    }

                    if (isset($titleFormat[$item['name']])) {
                        $titleNew[$titleFormat[$item['name']]] = $item['title'];
                    } else {
                        $titleNew[$item['name']] = $item['title'];
                    }
                }
            }
        }

        $titleNew = !empty($titleNew) ? $titleNew : $title;

        return Language::translateMulti($titleNew);
    }

    public static function pdf(array $title, array $data, $fileName, $titleName, $view = null)
    {
        $title = static::formatTitle($title);
        $data = static::formatData($data, $title);

        $view = $view ? $view : 'commonView::PdfTemplate';

        $pdf = new Mpdf(['orientation' => 'L']);

        $html = (string)view($view, [
            'data' => $data,
            'titles' => $title,
            'titleName' => Language::translate($titleName),
        ]);

        $pdf->WriteHTML($html);
        $pdf->Output("{$fileName}.pdf", "D");
    }

    public static function excel(array $title, array $data, $fileName, $titleName, $view = null)
    {
        $title = static::formatTitle($title);
        $data = static::formatData($data, $title);

        $view = $view ? $view : 'commonView::ExcelTemplate';

        ob_end_clean();
        return Excel::download(new ExportCustom([
            'data' => $data,
            'titles' => $title,
            'titleName' => Language::translate($titleName),
        ], $view), "{$fileName}.xlsx");
    }

    public static function export($type, array $title, array $data, $fileName, $titleName)
    {
        if (!empty($type)) {
            self::$tableName = Request::capture()->input('table_name') ?? false;

            switch (strtoupper($type)) {
                case self::TYPE_PDF:
                    Export::pdf($title, $data, $fileName, $titleName);
                    break;
                case self::TYPE_EXCEL || 'XLS':
                    return Export::excel($title, $data, $fileName, $titleName);
            }
        }
    }
}
