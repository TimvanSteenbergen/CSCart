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
use Tygh\Navigation\LastView;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'manage') {

    $discussion_object_types = fn_get_discussion_objects();
    if (empty($_REQUEST['object_type'])) {
        reset($discussion_object_types);
        $_REQUEST['object_type'] = key($discussion_object_types); // FIXME: bad style
    }

    $_url = fn_query_remove(Registry::get('config.current_url'), 'object_type', 'page');
    foreach ($discussion_object_types as $obj_type => $obj) {
        if ($obj_type == 'E' && Registry::ifGet('addons.discussion.home_page_testimonials', 'D') == 'D') {
            continue;
        }

        $_name = ($obj_type != 'E') ? __($obj) . ' ' . __('discussion_title_' . $obj) : __('discussion_title_' . $obj); // FIXME!!! Bad style

        Registry::set('navigation.tabs.' . $obj, array (
            'title' => $_name,
            'href' => $_url . '&object_type=' . $obj_type,
        ));

    }

    list($posts, $search) = fn_get_discussions($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'));

    if (!empty($posts)) {
        foreach ($posts as $k => $v) {
            $posts[$k]['object_data'] = fn_get_discussion_object_data($v['object_id'], $v['object_type'], DESCR_SL);
        }
    }

    Registry::get('view')->assign('posts', $posts);
    Registry::get('view')->assign('search', $search);
    Registry::get('view')->assign('discussion_object_type', $_REQUEST['object_type']);
    Registry::get('view')->assign('discussion_object_types', $discussion_object_types);
}

function fn_get_discussions($params, $items_per_page)
{
    // Init filter
    $params = LastView::instance()->update('discussion', $params);

    // Set default values to input params
    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    // Define fields that should be retrieved
    $fields = array (
        '?:discussion_posts.*',
        '?:discussion_messages.message',
        '?:discussion_rating.rating_value',
        '?:discussion.*'
    );

    // Define sort fields
    $sortings = array (
        'object' => "?:discussion.object_type",
        'name' => "?:discussion_posts.name",
        'ip_address' => "?:discussion_posts.ip_address",
        'timestamp' => "?:discussion_posts.timestamp",
        'status' => "?:discussion_posts.status",
        'date' => "?:orders.timestamp",
        'total' => "?:orders.total",
    );

    $sorting = db_sort($params, $sortings, 'timestamp', 'desc');

    $condition = $join = '';

    if (isset($params['name']) && fn_string_not_empty($params['name'])) {
        $condition .= db_quote(" AND ?:discussion_posts.name LIKE ?l", "%".trim($params['name'])."%");
    }

    if (isset($params['message']) && fn_string_not_empty($params['message'])) {
        $condition .= db_quote(" AND ?:discussion_messages.message LIKE ?l", "%".trim($params['message'])."%");
    }

    if (!empty($params['type'])) {
        $condition .= db_quote(" AND ?:discussion.type = ?s", $params['type']);
    }

    if (!empty($params['status'])) {
        $condition .= db_quote(" AND ?:discussion_posts.status = ?s", $params['status']);
    }

    if (!empty($params['post_id'])) {
        $condition .= db_quote(" AND ?:discussion_posts.post_id = ?i", $params['post_id']);
    }

    if (isset($params['ip_address']) && fn_string_not_empty($params['ip_address'])) {
        $condition .= db_quote(" AND ?:discussion_posts.ip_address = ?s", trim($params['ip_address']));
    }

    if (!empty($params['rating_value'])) {
        $condition .= db_quote(" AND ?:discussion_rating.rating_value = ?i", $params['rating_value']);
    }

    if (!empty($params['object_type'])) {
        $condition .= db_quote(" AND ?:discussion.object_type = ?s", $params['object_type']);
    }

    $condition .= fn_get_discussion_company_condition('?:discussion.company_id');

    if (!empty($params['period']) && $params['period'] != 'A') {
        list($params['time_from'], $params['time_to']) = fn_create_periods($params);
        $condition .= db_quote(" AND (?:discussion_posts.timestamp >= ?i AND ?:discussion_posts.timestamp <= ?i)", $params['time_from'], $params['time_to']);
    }

    $join .= " INNER JOIN ?:discussion ON ?:discussion.thread_id = ?:discussion_posts.thread_id";
    $join .= " INNER JOIN ?:discussion_messages ON ?:discussion_messages.post_id = ?:discussion_posts.post_id";
    $join .= " INNER JOIN ?:discussion_rating ON ?:discussion_rating.post_id = ?:discussion_posts.post_id";

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:discussion_posts $join WHERE 1 $condition");
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $posts = db_get_array("SELECT " . implode(',', $fields) . " FROM ?:discussion_posts $join WHERE 1 $condition $sorting $limit");

    return array($posts, $params);
}
