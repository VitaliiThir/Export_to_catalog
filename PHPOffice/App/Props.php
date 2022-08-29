<?php

namespace PHPOffice\App;

use CIBlockElement;
use CModule;

class Props
{
    public static function setPropsFileData($el_id, $ib_id, $arr, $clear_active = false, $clear_moderation = false)
    {
        if (!CModule::IncludeModule('iblock')) return;

        if (!$clear_active && !$clear_moderation) {
            CIBlockElement::SetPropertyValuesEx($el_id, $ib_id, $arr);
        }

        if ($clear_active) {
            CIBlockElement::SetPropertyValuesEx($el_id, $ib_id, [
                'FILE_NAME' => '',
                'FILE_DATE' => '',
                'FILE_PATH' => ''
            ]);
        }

        if ($clear_moderation) {
            CIBlockElement::SetPropertyValuesEx($el_id, $ib_id, [
                'MODER_FILE' => '',
                'FILE_DATE_MODER' => ''
            ]);
        }
    }

    public static function getPropsFileData($ib_id, $el_id, $arr)
    {
        if (!CModule::IncludeModule('iblock')) return false;

        $prop_db = CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => $ib_id, 'ID' => $el_id],
            false, false,
            $arr
        );

        if ($props = $prop_db->Fetch()) {
            return $props;
        }

        return false;
    }

}