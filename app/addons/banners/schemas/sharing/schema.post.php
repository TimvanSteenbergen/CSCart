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

$schema['banners'] = array(
        'controller' => 'banners',
        'mode' => 'update',
        'type' => 'tpl_tabs',
        'params' => array(
            'object_id' => '@banner_id',
            'object' => 'banners'
        ),
        'table' => array(
            'name' => 'banners',
            'key_field' => 'banner_id',
        ),
        'request_object' => 'banner_data',
        'have_owner' => true,
);

return $schema;
