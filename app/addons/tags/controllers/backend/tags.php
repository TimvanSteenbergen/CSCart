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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD']	== 'POST') {

    if ($mode == 'approve') {
        db_query("UPDATE ?:tags SET status = 'A' WHERE tag_id IN (?n)", $_REQUEST['tag_ids']);
    }

    if ($mode == 'disapprove') {
        db_query("UPDATE ?:tags SET status = 'D' WHERE tag_id IN (?n)", $_REQUEST['tag_ids']);
    }

    if ($mode == 'm_delete') {
        if (!empty($_REQUEST['tag_ids'])) {
            fn_delete_tags($_REQUEST['tag_ids']);
        }
    }

    if ($mode == 'm_update') {
        foreach ($_REQUEST['tags_data'] as $tag_id => $tag_data) {
            fn_update_tag($tag_data, $tag_id);
        }
    }

    if ($mode == 'update') {
        $tag_id = fn_update_tag($_REQUEST['tag_data'], $_REQUEST['tag_id']);
    }

    return array(CONTROLLER_STATUS_OK, "tags.manage");
}

if ($mode == 'manage') {
    $params = $_REQUEST;
    $params['count_objects'] = true;
    list($tags, $search) = fn_get_tags($params, Registry::get('settings.Appearance.admin_elements_per_page'));

    Registry::get('view')->assign('tags', $tags);
    Registry::get('view')->assign('search', $search);
    Registry::get('view')->assign('tag_objects', fn_get_tag_objects());

// ajax autocomplete mode
} elseif ($mode == 'list') {
    if (defined('AJAX_REQUEST')) {
        $tags = fn_get_tag_names(array('tag' => $_REQUEST['q']));
        Registry::get('ajax')->assign('autocomplete', $tags);

        exit();
    }

} elseif ($mode == 'delete' && !empty($auth['user_id'])) {
    if (!empty($_REQUEST['tag_id'])) {
        fn_delete_tag($_REQUEST['tag_id']);

    } elseif (!empty($_REQUEST['tag_data'])) {
        $params = $_REQUEST['tag_data'];
        $params['user_id'] = $auth['user_id'];
        fn_delete_tags_by_params($params);
    }

    if (defined('AJAX_REQUEST')) {
        Registry::get('ajax')->assign('tag_name', fn_get_tag_names($params));

        exit();
    }

    return array(CONTROLLER_STATUS_REDIRECT, "tags.manage");

} elseif ($mode == 'update'  && !empty($auth['user_id'])) {
    if (defined('AJAX_REQUEST')) {
        $params = $_REQUEST['tag_data'];
        $params['user_id'] = $auth['user_id'];
        fn_update_tag($params);

        Registry::get('ajax')->assign('tag_name', fn_get_tag_names($params));

        exit();
    }
}

function fn_get_tag_objects()
{
    $types = array();

    if (Registry::get('addons.tags.tags_for_products') == 'Y') {
        $types['P'] = array(
            'name' => 'products',
            'url' => 'products.manage',
        );
    }
    if (Registry::get('addons.tags.tags_for_pages') == 'Y') {
        $types['A'] = array(
            'name' => 'pages',
            'url' => 'pages.manage',
        );
    }

    fn_set_hook('get_tag_objects', $types);

    return $types;
}
