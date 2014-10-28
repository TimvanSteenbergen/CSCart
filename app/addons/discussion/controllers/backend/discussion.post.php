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

    if ($mode == 'm_delete') {
        if (!empty($_REQUEST['delete_posts']) && is_array($_REQUEST['delete_posts'])) {
            foreach ($_REQUEST['delete_posts'] as $p_id => $v) {
                fn_discussion_delete_post($p_id);
            }
        }
    }

    return array(CONTROLLER_STATUS_OK);
}

if ($mode == 'delete') {
    if (!empty($_REQUEST['post_id'])) {
        fn_discussion_delete_post($_REQUEST['post_id']);
    }
}

if ($mode == 'update') {
    $discussion = array();
    if (!empty($_REQUEST['discussion_type'])) {
        $discussion = fn_get_discussion(0, $_REQUEST['discussion_type'], true, $_REQUEST);
    }

    if (!empty($discussion) && $discussion['type'] != 'D' && Registry::ifGet('addons.discussion.home_page_testimonials', 'N') != 'D') {
        if (fn_allowed_for('MULTIVENDOR') || fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
            Registry::set('navigation.tabs.discussion', array (
                'title' => __('discussion_title_home_page'),
                'js' => true,
            ));
        }
    } else {
        $discussion['is_empty'] = true;

    }

    Registry::get('view')->assign('discussion', $discussion);
}
