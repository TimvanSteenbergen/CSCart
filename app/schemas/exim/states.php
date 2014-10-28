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

return array(
    'section' => 'translations',
    'pattern_id' => 'states',
    'name' => __('states'),
    'key' => array('state_id'),
    'order' => 1,
    'table' => 'states',
    'references' => array(
        'state_descriptions' => array(
            'reference_fields' => array('state_id' => '#key', 'lang_code' => '#lang_code'),
            'join_type' => 'LEFT'
        ),
    ),
    'options' => array(
        'lang_code' => array(
            'title' => 'language',
            'type' => 'languages',
            'default_value' => array(DEFAULT_LANGUAGE),
        ),
    ),
    'export_fields' => array(
        'State' => array(
            'db_field' => 'state',
            'table' => 'state_descriptions',
            'required' => true,
            'multilang' => true,
        ),
        'Language' => array(
            'table' => 'state_descriptions',
            'db_field' => 'lang_code',
            'type' => 'languages',
            'required' => true,
            'multilang' => true
        ),
        'Code' => array(
            'db_field' => 'code',
            'required' => true,
            'alt_key' => true,
        ),
        'Country code' => array(
            'db_field' => 'country_code',
            'required' => true,
            'alt_key' => true,
        ),
    ),
);
