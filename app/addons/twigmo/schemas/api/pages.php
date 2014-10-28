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

$schema = array (
    'object_name' => 'page',
    'fields' => array (
        'page_id' => array (
            'db_field' => 'page_id'
        ),
        'page_type' => array (
            'db_field' => 'page_type'
        ),
        'title' => array (
            'db_field' => 'page'
        ),
        'link' => array (
            'db_field' => 'link'
        ),
        'new_window' => array (
            'db_field' => 'new_window'
        ),
        'onclick' => array (
            'process_get' => array (
                'func' => 'fn_twg_get_page_onclick',
                'params' => array (
                    'url' => array (
                        'db_field' => 'link'
                    ),
                    'page_type' => array (
                        'db_field' => 'page_type'
                    ),
                    'page_id' => array (
                        'db_field' => 'page_id'
                    ),
                )
            )
        ),
    )
);
return $schema;
