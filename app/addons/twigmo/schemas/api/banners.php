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
    'object_name' => 'banner',
    'fields' => array (
        'banner_id' => array (
            'db_field' => 'banner_id'
        ),
        'type' => array (
            'db_field' => 'type'
        ),
        'target' => array (
            'db_field' => 'target'
        ),
        'url' => array (
            'process_get' => array (
                'func' => 'fn_twg_get_banner_url',
                'params' => array (
                    'banner_id' => array (
                        'db_field' => 'banner_id'
                    ),
                    'url' => array (
                        'db_field' => 'url'
                    )
                )
            )
        ),
        'description' => array (
            'db_field' => 'description'
        ),
        'onclick' => array (
            'process_get' => array (
                'func' => 'fn_twg_get_banner_onclick',
                'params' => array (
                    'url' => array (
                        'db_field' => 'url'
                    ),
                    'target' => array (
                        'db_field' => 'target'
                    ),
                    'type' => array (
                        'db_field' => 'type'
                    ),
                    'banner_id' => array (
                        'db_field' => 'banner_id'
                    ),
                )
            )
        ),
        'banner' => array (
            'db_field' => 'banner'
        ),
        'image' => array (
            'process_get' => array (
                'func' => 'Twigmo\\Core\\Functions\\Image\\TwigmoImage::getApiImageData',
                'params' => array (
                    'image_pair' => array (
                        'db_field' => 'main_pair'
                    ),
                    'type' => array (
                        'param' => 'banner'
                    )
                )
            ),
        ),
    )
);
return $schema;
