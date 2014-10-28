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

$schema['rss_feed'] = array (
    'content' => array (
        'filling' => array(
            'type' => 'selectbox',
            'values' => array (
                'products' => 'products',
            ),
            'default_value' => 'products',
            'values_settings' => array(
                'products' => array(
                    'settings' => array(
                        'rss_sort_by' => array (
                            'type' => 'selectbox',
                            'values' => array (
                                'A' => 'rss_created',
                                'U' => 'rss_updated'
                            )
                        ),
                        'rss_display_sku' => array (
                            'type' => 'checkbox',
                        ),
                        'rss_display_image' => array (
                            'type' => 'checkbox',
                        ),
                        'rss_display_price' => array (
                            'type' => 'checkbox',
                        ),
                        'rss_display_original_price' => array (
                            'type' => 'checkbox',
                        ),
                        'rss_display_add_to_cart' => array (
                            'type' => 'checkbox',
                        ),
                    )
                )
            )
        )
    ),
    'templates' => array (
        'addons/rss_feed/blocks/rss_feed.tpl' => array(),
    ),
    'wrappers' => 'blocks/wrappers',
    'settings' => array (
        'max_item' => array (
            'type' => 'input',
            'default_value' => '3'
        ),
        'feed_title' => array (
            'type' => 'input',
            'default_value' => ''
        ),
        'feed_description' => array (
            'type' => 'input',
            'default_value' => ''
        ),
    )
);

return $schema;
