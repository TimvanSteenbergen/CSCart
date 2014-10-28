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
    'object_name' => 'feature',
    'fields' => array (
        'description' => array (
            'db_field' => 'description'
        ),
        'feature_type' => array (
            'db_field' => 'feature_type'
        ),
        'full_description' => array (
            'db_field' => 'full_description'
        ),
        'prefix' => array (
            'db_field' => 'prefix'
        ),
        'suffix' => array (
            'db_field' => 'suffix'
        ),
        'feature_value' => array (
            'process_get' => array (
                'func' => 'fn_twg_get_feature_value',
                'params' => array (
                    'feature_value' => array (
                        'db_field' => 'value'
                    ),
                    'feature_type' => array (
                        'db_field' => 'feature_type'
                    ),
                    'value_int' => array (
                        'db_field' => 'value_int'
                    ),
                    'variant_id' => array (
                        'db_field' => 'variant_id'
                    ),
                    'variants' => array (
                        'db_field' => 'variants'
                    ),
                )
            )
        ),
        'subfeatures' => array (
            'process_get' => array (
                'func' => 'Twigmo\\Core\\Api::getAsList',
                'params' => array (
                    'schema' => array (
                        'value' => 'product_features'
                    ),
                    'product_features' => array (
                        'db_field' => 'subfeatures'
                    )
                )
            )
        ),
    )
);
return $schema;
