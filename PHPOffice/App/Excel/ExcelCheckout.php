<?php

namespace PHPOffice\App\Excel;

use PHPOffice\App\AdminShop;
use PHPOffice\App\Config;
use PHPOffice\App\Props;
use PHPOffice\App\Request;
use PHPOffice\App\Util;

class ExcelCheckout extends ExcelMain
{

    /**
     * @var array
     */
    private array $request;

    /**
     * @var string
     */
    private string $root_file_path;

    /**
     * @var array
     */
    private array $cells;

    /**
     * @param array $request
     */
    public function __construct(array $request = array())
    {
        $this->request = $request;

        $this->root_file_path = $_SERVER['DOCUMENT_ROOT'] . $this->request['file_path'];

        $this->cells = $this->excel_to_array($this->root_file_path);
        $this->file_checkout($this->cells, $this->root_file_path, $this->request);
    }

    /**
     * @param $cells
     * @param $root_file_path
     * @return void
     */
    private function check_table_headers($cells, $root_file_path)
    {
        $fail_head = false;
        $fail_head_arr = '';

        foreach ($cells[0] as $key => $cell) {
            $head_res = trim(mb_strtoupper($cell));
            $head_need = mb_strtoupper($this->cells_head_need[$key]);

            if ($head_res != $head_need || $cell == '') $fail_head = true;

            if ($head_res != $head_need && $cell != '') {
                $fail_head_arr .= "<td class='user-table-cell-fail'><span>$cell</span></td>";
            } elseif ($cell == '') {
                $fail_head_arr .= "<td class='user-table-cell-empty'><b>*</b>Заголовок «<b>$this->cells_head_need[$key]</b>» не должно быть пустым</td>";
            } else {
                $fail_head_arr .= "<td>$cell</td>";
            }
        }

        if ($fail_head) {
            unlink($root_file_path);

            Request::json_response(
                'fail',
                "<div class='server-response'>
                        <div class='d-flex justify-content-between align-items-center pb-2 pt-1'>
                            <h5 class='py-2'><b>(!) Ошибка в заголовках таблицы</b></h5>
                            <button class='btn btn-success btn-save btn-sm' onclick='$(`[data-tab=\"btn-save\"]`).trigger(`click`)'>Повторить действия</button>
                        </div>
                        <table class='user-table table table-light table-hover'><tr>{$this->table_head_html()}</tr><tr>$fail_head_arr</tr></table>
                      </div>"
            );
        }
    }

    /**
     * @param $cells
     * @param $root_file_path
     * @return void
     */
    private function check_table_cells($cells, $root_file_path): void
    {

        $tr_html = '';
        $new_cells_arr = [];

        foreach ($cells as $key => $cell) {
            if ($key > 0) {
                $new_cells_arr[] = $cell;
            }
        }

        $double_rows = Util::array_unique_key($new_cells_arr, Config::$table_column_index_required, true);

        if (!empty($double_rows)) {
            foreach ($double_rows as $row) {
                $td_html = '';

                foreach ($row as $td) {
                    $td_html .= "<td>$td</td>";
                }

                $td_html = "<tr>$td_html</tr>";
                $tr_html .= $td_html;
            }
        }

        if ($tr_html == '') {
            foreach ($cells as $key_row => $row) {
                if ($key_row > 0) {
                    $td_fail = false;
                    $td_html = '';
                    $current_row = $key_row + 1;

                    foreach ($row as $key_td => $td) {
                        if ($this->check_cells[$key_td]['NOT_EMPTY'] && $td == '') {
                            $td_html .= "<td class='user-table-cell-empty'><b>* Поле не должно быть пустым</b></td>";
                            $td_fail = true;
                        } elseif ($this->check_cells[$key_td]['IS_NUM'] && Util::test($td)) {
                            $td_html .= "<td class='user-table-cell-empty'>
                                <b>* В значении «<i style='color: #d20000;font-style: normal'>" . $td . "</i>» должны быть только цифры</b>
                             </td>";
                            $td_fail = true;
                        } else {
                            $td_html .= "<td>$td</td>";
                        }
                    }

                    if ($td_fail) {
                        $td_html = "<tr><td><b style='color: #b80000'>$current_row</b></td>$td_html</tr>";
                        $tr_html .= $td_html;
                    }

                }
            }
        }

        if ($tr_html != '') {
            unlink($root_file_path);

            if (!empty($double_rows)) {
                Request::json_response(
                    "fail",
                    "
                        <div class='d-flex justify-content-between align-items-center pb-2 pt-1'>
                            <h5 class='py-2'>
                                <b>(!) В файле найдены товары с дублируемым артикулом (<i>" . count($double_rows) . " строк</i>)</b>
                            </h5>
                            <button class='btn btn-success btn-save btn-sm' onclick='$(`[data-tab=\"btn-save\"]`).trigger(`click`)'>Повторить действия</button>
                        </div>
                        <table class='user-table table table-light table-hover'>
                            <tr>{$this->table_head_html()}</tr>
                            $tr_html
                        </table>
                        "
                );
            } else {
                Request::json_response(
                    'fail',
                    "<div class='d-flex justify-content-between align-items-center pb-2 pt-1'>
                            <h5 class='py-2'><b>(!) Ошибки заполнения таблицы</b></h5>
                            <button class='btn btn-success btn-save btn-sm' onclick='$(`[data-tab=\"btn-save\"]`).trigger(`click`)'>Повторить действия</button>
                          </div>
                          <table class='user-table table table-light'>
                              <tr><th>№ строки<br>в Excel</th>{$this->table_head_html()}</tr>
                              $tr_html
                          </table>"
                );
            }
        }

    }

    /**
     * @param string $root_file_path
     * @param $request
     * @return void
     */
    private function file_is_ok(string $root_file_path, $request)
    {
        if (file_exists($root_file_path)) {

            Props::setPropsFileData(AdminShop::get_shop_id(), Config::$shop_ib_id,
                [
                    'FILE_NAME' => $request['file_name'],
                    'FILE_DATE' => $request['file_date'],
                    'FILE_PATH' => $request['file_path']
                ]);

            Request::json_response(
                'ok',
                'Файл успешно сохранен и готов к экспорту.<br>
                              Действия по экспорту товаров в каталог выполняются в разделе <b>«' . Config::$tabs_arr['export']['name'] . '».</b>',
                $request['file_path']
            );

        }
    }

    /**
     * @param $cells
     * @param $root_file_path
     * @param array $request
     * @return void
     */
    private function file_checkout($cells, $root_file_path, array $request = array())
    {
        $this->check_table_headers($cells, $root_file_path);
        $this->check_table_cells($cells, $root_file_path);
        $this->file_is_ok($root_file_path, $request);
    }
}