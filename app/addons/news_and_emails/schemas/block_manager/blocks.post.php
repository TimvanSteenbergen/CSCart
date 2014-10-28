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

use Tygh\Registry;

$schema['news'] = array (
    'content' => array (
        'items' => array (
            'remove_indent' => true,
            'hide_label' => true,
            'type' => 'enum',
            'object' => 'news',
            'items_function' => 'fn_get_news',
            'fillings' => array (
                'manually' => array (
                    'picker' => 'addons/news_and_emails/pickers/news/picker.tpl',
                    'picker_params' => array (
                        'type' => 'links',
                    ),
                ),
                'newest' => array (
                    'params' => array (
                        'sort_by' => 'timestamp'
                    )
                ),
                'news_plain' => array (
                    'settings' => array(
                        'limit' => array (
                            'type' => 'input',
                            'default_value' => 3,
                        )
                    )
                )
            ),
        ),
    ),
    'templates' => array (
        'addons/news_and_emails/blocks/news.tpl' => array(),
        'addons/news_and_emails/blocks/news_text_links.tpl' => array(),
    ),
    'wrappers' => 'blocks/wrappers',
    'cache' => array (
        'update_handlers' => array('news'),
    ),
);

if (Registry::get('addons.rss_feed.status') == 'A') {
    $schema['rss_feed']['content']['filling']['values']['news'] = 'news';
}

return $schema;
