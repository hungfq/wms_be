<?php

namespace App\Libraries;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class ExportCustom implements FromView, WithEvents
{
    public $data;
    public $fileName;

    public function __construct($data, $fileName = 'ExcelTemplate')
    {
        $this->data = $data;
        $this->fileName = $fileName;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $worksheet = $event->sheet->getDelegate();
                $temp = [];
                $strLimit = 50;

                foreach ($worksheet->getRowIterator() as $row) {
                    foreach ($row->getCellIterator() as $cell) {
                        $typeFormat = '0.000';
                        $value = $cell->getValue();
                        $dataType = $cell->getDataType();

                        if (strstr($value, '="')) {
                            $value = str_replace(['="'], '', $value);
                            $cell->setValueExplicit($value, DataType::TYPE_STRING);
                        }

                        if ($value === null || $value === '') {
                            $cell->setValue(' ');
                        }

                        $value = $cell->getValue();

                        $formattedNumber = preg_replace('/[^0-9,]/', '', $value);

                        // Định dạng lại số với ký tự ngăn cách phần ngàn
                        $formattedNumber = number_format((float)str_replace(',', '', $formattedNumber));

                        // Kiểm tra và định dạng số thực cho các ô chứa giá trị số thập phân
                        if ($dataType === DataType::TYPE_NUMERIC && is_numeric($value) && $typeFormat) {
                            $cell->getStyle()->getNumberFormat()->setFormatCode($typeFormat);
                        }

                        // Kiểm tra và định dạng số thực cho các ô chứa giá trị số ngàn
                        if ($dataType === DataType::TYPE_NUMERIC && is_int($value)) {
                            $cell->getStyle()->getNumberFormat()->setFormatCode('#,##0');
                        }

                        // Kiểm tra và định dạng số thực cho các ô chứa giá trị số ngàn
                        if ($dataType === DataType::TYPE_NUMERIC && $formattedNumber == $value) {
                            $cell->getStyle()->getNumberFormat()->setFormatCode('#,##0');
                        }

                        //Kiểm tra và giới hạn chiều rộng của hàng
                        if ((int)mb_strlen(trim($value)) > $strLimit && !in_array($cell->getColumn(), $temp)) {
                            $temp[] = $cell->getColumn();
                        }
                    }
                }

                foreach ($this->excelColumnRange('A', 'AZ') as $column) {
                    if (in_array($column, $temp)) {
                        $event->sheet->getColumnDimension($column)->setWidth(50);
                    } else {
                        $event->sheet->getColumnDimension($column)->setAutoSize(true);
                    }
                }
            },
        ];
    }

    public function view(): View
    {
        return view($this->fileName, $this->data);
    }

    function excelColumnRange($lower, $upper)
    {
        ++$upper;
        for ($i = $lower; $i !== $upper; ++$i) {
            yield $i;
        }
    }
}
