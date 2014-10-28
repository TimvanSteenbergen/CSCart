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
        'element_id' => array (
            'db_field' => 'element_id'
        ),
        'element_type' => array (
            'db_field' => 'element_type'
        ),
        'value' => array (
            'db_field' => 'value'
        ),
        'required' => array (
            'db_field' => 'required'
        ),
        'description' => array (
            'db_field' => 'description'
        ),
        'variants' => array (
            'process_get' => array (
                'func' => 'fn_twg_api_get_form_elements',
                'params' => array (
                    'elements' => array (
                        'db_field' => 'variants'
                    ),
                )
            ),
        ),
    )
);
return $schema;
