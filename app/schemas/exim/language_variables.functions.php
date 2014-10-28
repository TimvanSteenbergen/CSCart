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

function fn_import_check_translations_lang_code(&$primary_object_id, &$object, &$processed_data, &$skip_record)
{
    static $valid_codes = array();

    if (empty($valid_codes)) {
        $valid_codes = db_get_fields('SELECT lang_code FROM ?:languages');
    }

    if (!in_array($object['lang_code'], $valid_codes)) {
        $skip_record = true;
        $processed_data['S']++;
    }
}
