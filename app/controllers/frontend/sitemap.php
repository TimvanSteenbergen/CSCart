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
use Tygh\Settings;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'view') {

    fn_add_breadcrumb(__('sitemap'));
    $sitemap_settings = Settings::instance()->getValues('Sitemap');
    Registry::get('view')->assign('sitemap_settings', $sitemap_settings);

    if ($sitemap_settings['show_cats'] == 'Y') {
        if ($sitemap_settings['show_rootcats_only'] == 'Y') {
            $categories = fn_get_plain_categories_tree(0, true);
            $sitemap['categories'] = array();

            foreach ($categories as $c) {
                if ($c['level'] == 0) {
                    $sitemap['categories'][] = $c;
                }
            }
        } else {
            $sitemap['categories_tree'] = fn_get_plain_categories_tree(0, true);
        }
    }

    if ($sitemap_settings['show_site_info'] == 'Y') {
        $_params = array(
            'get_tree' => 'plain',
            'status' => 'A'
        );
        list($sitemap['pages_tree']) = fn_get_pages($_params);
    }

    $section_fields = array(
        's.*',
        '?:common_descriptions.object as section',
    );

    $section_tables = array(
        '?:sitemap_sections AS s',
    );

    $section_left_joins = array(
        db_quote('?:common_descriptions ON s.section_id = ?:common_descriptions.object_id AND ?:common_descriptions.object_holder = "sitemap_sections" AND ?:common_descriptions.lang_code = ?s', CART_LANGUAGE),
    );

    $section_conditions = array(
        db_quote('status = ?s', 'A'),
    );

    $section_orders = array(
        's.position',
    );

    fn_set_hook('sitemap_get_sections', $section_fields, $section_tables, $section_left_joins, $section_conditions, $section_orders);

    $custom_sections = db_get_array('SELECT ' . implode(', ', $section_fields) . ' FROM ' . implode(', ', $section_tables) . ' LEFT JOIN ' . implode(', ', $section_left_joins) . ' WHERE ' . implode(' AND ', $section_conditions) . ' ORDER BY ' . implode(', ', $section_orders));

    foreach ($custom_sections as $k => $section) {
        $links = db_get_array("SELECT link_id, link_href, section_id, status, position, link_type, description, object as link FROM ?:sitemap_links LEFT JOIN ?:common_descriptions ON ?:common_descriptions.object_id = ?:sitemap_links.link_id AND ?:common_descriptions.object_holder = 'sitemap_links' AND ?:common_descriptions.lang_code = ?s WHERE section_id = ?i ORDER BY position, link", CART_LANGUAGE, $section['section_id']);

        if (!empty($links)) {
            foreach ($links as $key => $link) {
                $sitemap['custom'][$section['section']][$key]['link'] = $link['link'];
                $sitemap['custom'][$section['section']][$key]['link_href'] = $link['link_href'];
                $sitemap['custom'][$section['section']][$key]['description'] = $link['description'];
            }
        }
    }

    Registry::get('view')->assign('sitemap', $sitemap);
}
