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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

// types
define('NEWSLETTER_TYPE_NEWSLETTER', 'N');
define('NEWSLETTER_TYPE_TEMPLATE', 'T');
define('NEWSLETTER_TYPE_AUTORESPONDER', 'A');

fn_register_hooks(
    'is_accessible_discussion',
    'get_discussion_object_data',
    'get_discussion_objects',
    'get_block_locations',
    'localization_objects',
    'save_log',
    'sitemap_link_object',
    'customer_search_objects',
    'generate_rss_feed',
    'get_predefined_statuses',
    'delete_company',

    array('get_news_data', '', 'seo'),
    array('get_news_data_post', '', 'seo'),
    array('get_news', '', 'seo'),
    array('get_news_post', '', 'seo'),
    array('update_news', '', 'seo'),
    array('delete_news', '', 'seo')
);

if (fn_allowed_for('ULTIMATE')) {
    fn_register_hooks(
        'ult_check_store_permission'
    );
}
