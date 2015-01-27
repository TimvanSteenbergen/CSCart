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
    if ($mode == 'update') {
        if (!empty($_REQUEST['posts'])) {
            fn_update_discussion_posts($_REQUEST['posts']);
        }
    }

    return;
}

if ($mode == 'update') {
    if (!fn_allowed_for('ULTIMATE')) {
        $discussion = fn_get_discussion($_REQUEST['company_id'], 'M', true, $_REQUEST);
        if (!empty($discussion) && $discussion['type'] != 'D') {
            Registry::set('navigation.tabs.discussion', array (
                'title' => __('discussion_title_company'),
                'js' => true
            ));

            Registry::get('view')->assign('discussion', $discussion);
        }
    }
}
