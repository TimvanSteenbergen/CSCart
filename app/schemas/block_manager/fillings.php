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
    'manual' => array (
        'type' => 'picker',
    ),
    'newest' => array (
        'period' => array (
            'type' => 'selectbox',
            'values' => array (
                'A' => 'any_date',
                'D' => 'today',
                'HC' => 'last_days',
            ),
            'default_value' => 'any_date'
        ),
        'last_days' => array (
            'type' => 'input',
            'default_value' => 1
        ),
        'limit' => array (
            'type' => 'input',
            'default_value' => 3
        ),
        'cid' => array (
            'type' => 'picker',
            'option_name' => 'filter_by_categories',
            'picker' => 'pickers/categories/picker.tpl',
            'picker_params' => array(
                    'multiple' => true,
                    'use_keys' => 'N',
                    'view_mode' => 'table',
                    'no_item_text' => __('default_filter_by_location'),
            ),
            'unset_empty' => true, // remove this parameter from params list if the value is empty
        ),
    ),
    'recent_products' => array (
        'limit' => array (
            'type' => 'input',
            'default_value' => 3
        ),
    ),
    'most_popular' => array (
        'limit' => array (
            'type' => 'input',
            'default_value' => 3
        ),
        'cid' => array (
            'type' => 'picker',
            'option_name' => 'filter_by_categories',
            'picker' => 'pickers/categories/picker.tpl',
            'picker_params' => array(
                    'multiple' => true,
                    'use_keys' => 'N',
                    'view_mode' => 'table',
                    'no_item_text' => __('default_filter_by_location'),
            ),
            'unset_empty' => true, // remove this parameter from params list if the value is empty
        ),
    ),
);
