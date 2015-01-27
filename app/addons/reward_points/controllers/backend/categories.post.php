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
    return;
}

if ($mode == 'update') {

    // Add new tab to page sections
    // [Page sections]
    // Add new tab to page sections
    Registry::set('navigation.tabs.reward_points', array (
        'title' => __('reward_points'),
        'js' => true
    ));

    // [/Page sections]

    Registry::get('view')->assign('reward_points', fn_get_reward_points($_REQUEST['category_id'], CATEGORY_REWARD_POINTS));
    Registry::get('view')->assign('object_type', CATEGORY_REWARD_POINTS);

} elseif ($mode == 'add') {

    // Add new tab to page sections
    // [Page sections]
    Registry::set('navigation.tabs.reward_points', array (
        'title' => __('reward_points'),
        'js' => true
    ));
    // [/Page sections]

    Registry::get('view')->assign('object_type', CATEGORY_REWARD_POINTS);
}

Registry::get('view')->assign('reward_usergroups', fn_array_merge(fn_get_default_usergroups(), fn_get_usergroups('C')));

/** /Body **/
