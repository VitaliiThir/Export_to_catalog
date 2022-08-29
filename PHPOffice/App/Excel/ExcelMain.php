<?php

namespace PHPOffice\App\Excel;

use DateTime;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use SplFileInfo;

class ExcelMain
{
    /**
     * @var array|string[]
     */
    public array $cells_head_need = [
        'ID',
        'Название',
        'Артикул',
        'Цена'
    ];

    /**
     * @var array
     */
    public array $check_cells = [
        1 => [
            'NOT_EMPTY' => true,
            'IS_NUM' => false
        ],
        2 => [
            'NOT_EMPTY' => true,
            'IS_NUM' => false
        ],
        3 => [
            'NOT_EMPTY' => true,
            'IS_NUM' => true
        ]
    ];

    /**
     * @param $root_file_path
     * @return array
     */
    public function excel_to_array($root_file_path): array
    {

        $spreadsheet = IOFactory::load($root_file_path);

        $cells = array();

        foreach ($spreadsheet->getWorksheetIterator() as $data) {
            $cells = $data->toArray();
        }

        return $cells;
    }

    /**
     * @return string
     */
    public function table_head_html(): string
    {
        $cells_head_need_html = "";

        foreach ($this->cells_head_need as $key => $item) {
            $item = $key == 1 ? "<th class='text-start'>$item</th>" : "<th>$item</th>";
            $cells_head_need_html .= $item;
        }

        return $cells_head_need_html;
    }

    /**
     * @param $arr
     * @param $path
     * @return void
     */
    public function create_excel($arr, $path)
    {
        $excel_cells = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($arr as $row_key => $item) {
            $row_cnt = $row_key + 1;

            foreach ($item as $key => $value) {
                $sheet->setCellValue("$excel_cells[$key]$row_cnt", $value);
            }

        }

        $writer = new Xls($spreadsheet);
        $writer->save($_SERVER['DOCUMENT_ROOT'] . $path);
    }

    /**
     * @return array
     */
    public function get_current_date_time(): array
    {
        $date = new DateTime();

        return [
            'file' => $date->format('d_m_Y__H_i_s'),
            'iblock' => $date->format('d.m.Y H:i:s')
        ];
    }

    /**
     * @param $file
     * @return string
     */
    public function get_file_extension($file): string
    {
        $file_info = new SplFileInfo($file);
        return $file_info->getExtension();
    }
}