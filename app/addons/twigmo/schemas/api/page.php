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
    'fields' => array (
        'page_id' => array (
            'db_field' => 'page_id'
        ),
        'title' => array (
            'db_field' => 'page'
        ),
        'description' => array (
            'db_field' => 'description'
        ),
        'form' => array (
            'process_get' => array (
                'func' => 'fn_twg_api_get_form_info',
                'params' => array (
                    'form' => array (
                        'db_field' => 'form'
                    )
                )
            ),
        )
    )
);
return $schema;
