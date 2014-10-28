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

use Tygh\Registry;

include_once(Registry::get('config.dir.schemas') . 'exim/language_variables.functions.php');

return array(
    'section' => 'translations',
    'pattern_id' => 'language_variables',
    'name' => __('language_variables'),
    'key' => array('name', 'lang_code'),
    'order' => 1,
    'table' => 'language_values',
    'condition' => array(
        'conditions' => array('lang_code' => '@lang_code'),
    ),
    'options' => array(
        'lang_code' => array(
            'title' => 'language',
            'type' => 'languages',
            'default_value' => array(DEFAULT_LANGUAGE),
        ),
    ),
    'export_fields' => array(
        'Name' => array(
            'db_field' => 'name',
            'alt_key' => true,
            'required' => true,
            'multilang' => true
        ),
        'Value' => array(
            'db_field' => 'value',
            'required' => true,
            'multilang' => true
        ),
        'Language' => array(
            'db_field' => 'lang_code',
            'alt_key' => true,
            'required' => true,
            'multilang' => true
        ),
    ),
    'import_process_data' => array(
        'check_lang_code' => array(
            'function' => 'fn_import_check_translations_lang_code',
            'args' => array('$primary_object_id', '$object', '$processed_data', '$skip_record'),
            'import_only' => true,
        ),
    ),
);
