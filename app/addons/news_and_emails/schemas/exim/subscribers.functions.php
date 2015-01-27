<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

function fn_import_date_to_timestamp($date = '')
{
    $date = !empty($date) ? $date : date("H:i:s");

    return strtotime($date);
}

function fn_export_convert_mailing_list($list_id, $lang_code)
{
    $lang_code = !empty($lang_code) ? $lang_code : DEFAULT_LANGUAGE;
    if (!empty($list_id)) {
        return db_get_field("SELECT object FROM ?:common_descriptions WHERE object_holder = 'mailing_lists' AND object_id = ?i AND lang_code = ?s", $list_id, $lang_code);
    } else {
        return '';
    }
}

function fn_import_convert_mailing_list($list_name)
{
    return db_get_field("SELECT object_id FROM ?:common_descriptions WHERE object_holder = 'mailing_lists' AND object = ?s", $list_name);
}
