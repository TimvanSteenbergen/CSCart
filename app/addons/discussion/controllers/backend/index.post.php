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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

// No action for vendor at the index
if (Registry::get('runtime.company_id') && fn_allowed_for('MULTIVENDOR')) {
    return;
}

if ($mode == 'set_post_status') {

    $new_status = ($_REQUEST['new_status'] === 'A') ? 'A' : 'D';
    db_query("UPDATE ?:discussion_posts SET ?u WHERE post_id = ?i", array('status' => $new_status), $_REQUEST['post_id']);

    $post = db_get_row("SELECT * FROM ?:discussion_posts WHERE post_id = ?i", $_REQUEST['post_id']);
    Registry::get('view')->assign('post', $post);
    if (defined('AJAX_REQUEST')) {
        Registry::get('view')->display('addons/discussion/views/index/components/dashboard_status.tpl');
        exit;
    }

    return array(CONTROLLER_STATUS_OK, fn_url());
}

if ($mode == 'delete_post' && defined('AJAX_REQUEST')) {
    db_query("DELETE FROM ?:discussion_messages WHERE post_id = ?i", $_REQUEST['post_id']);
    db_query("DELETE FROM ?:discussion_rating WHERE post_id = ?i", $_REQUEST['post_id']);
    db_query("DELETE FROM ?:discussion_posts WHERE post_id = ?i", $_REQUEST['post_id']);

    return array(CONTROLLER_STATUS_OK, fn_url());
}

$latest_posts = db_get_array(
    "SELECT a.post_id, a.ip_address, a.status, a.timestamp, b.object_id, b.object_type as object_type, b.type as type, a.name, c.message, d.rating_value "
    . "FROM ?:discussion_posts as a INNER JOIN ?:discussion as b ON a.thread_id = b.thread_id ?p "
    . "LEFT JOIN ?:discussion_messages as c ON a.post_id = c.post_id LEFT JOIN ?:discussion_rating as d ON a.post_id = d.post_id "
    . "ORDER BY a.timestamp DESC LIMIT 5",
    fn_get_discussion_company_condition('b.company_id')
);

if (!empty($latest_posts)) {
    foreach ($latest_posts as $k => $v) {
        $latest_posts[$k]['object_data'] = fn_get_discussion_object_data($v['object_id'], $v['object_type'], DESCR_SL);
        $latest_posts[$k]['rating'] = fn_get_discussion_rating($v['rating_value']);
    }
}

Registry::get('view')->assign('discussion_objects', fn_get_discussion_objects());
Registry::get('view')->assign('latest_posts', $latest_posts);

if ($mode == 'delete_post' && defined('AJAX_REQUEST')) { // FIXME - bad style
    Registry::get('view')->display('addons/discussion/views/index/components/dashboard.tpl');
    exit;
}
