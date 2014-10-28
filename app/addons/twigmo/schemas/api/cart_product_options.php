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
    'object_name' => 'option',
    'key' => array('option_id'),
    'fields' => array (
        'option_id' => array (
            'name' => 'option_id'
        ),
        'option_name' => array (
            'name' => 'option_name'
        ),
        'value' => array (
            'name' => 'value'
        ),
        // selected variant params
        'variant_name' => array (
            'name' => 'variant_name'
        ),
        'modifier' => array (
            'name' => 'modifier'
        ),
        'modifier_type' => array (
            'name' => 'modifier_type'
        ),
        'weight_modifier' => array (
            'name' => 'weight_modifier'
        ),
        'weight_modifier_type' => array (
            'name' => 'weight_modifier_type'
        ),
        'point_modifier' => array (
            'name' => 'point_modifier'
        ),
        'point_modifier_type' => array (
            'name' => 'point_modifier_type'
        ),
    )
);
return $schema;
