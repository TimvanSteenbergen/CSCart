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
    'table' => 'images',
    'object_name' => 'image',
    'key' => array('image_id'),
    'fields' => array (
        'image_id' => array (
            'db_field' => 'image_id'
        ),
        'pair_id' => array (
            'db_field' => 'pair_id'
        ),
        'image_x' => array (
            'db_field' => 'image_x'
        ),
        'image_y' => array (
            'db_field' => 'image_y'
        ),
        'alt' => array (
            'name' => 'alt'
        ),
        'file_name' => array (
            'name' => 'file_name',
        ),
        'type' => array (
            'name' => 'type'
        ),
        'data' => array (
            'name' => 'data',
        ),
        'deleted' => array (
            'name' => 'deleted'
        ),
        'url' => array (
            'name' => 'url',
        )
    )
);
return $schema;
