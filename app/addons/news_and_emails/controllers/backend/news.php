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
use Tygh\BlockManager\Block;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    fn_trusted_vars('news', 'news_data');

    //
    // Delete news
    //
    if ($mode == 'm_delete') {
        foreach ($_REQUEST['news_ids'] as $v) {
            fn_delete_news($v);
        }

        $suffix = ".manage";
    }

    //
    // Add/update news
    //
    if ($mode == 'update') {
        if (!empty($_REQUEST['news_data'])) {
            $news_id = fn_update_news($_REQUEST['news_id'], $_REQUEST['news_data'], DESCR_SL);
        }

        if (empty($news_id)) {
            $suffix = ".manage";
        } else {
            $suffix = ".update?news_id=$news_id" . (!empty($_REQUEST['news_data']['block_id']) ? "&selected_block_id=" . $_REQUEST['news_data']['block_id'] : "");
        }
    }

    return array(CONTROLLER_STATUS_OK, "news$suffix");
}

if ($mode == 'add') {

    // [Page sections]
    Registry::set('navigation.tabs', array (
        'detailed' => array (
            'title' => __('general'),
            'js' => true
        ),
        'addons' => array (
            'title' => __('addons'),
            'js' => true
        )
    ));
    // [/Page sections]

} elseif ($mode == 'update') {

    $news_data = fn_get_news_data($_REQUEST['news_id'], DESCR_SL);

    if (empty($news_data)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    // [Page sections]
    $tabs = array (
        'detailed' => array (
            'title' => __('general'),
            'js' => true
        ),
        'addons' => array (
            'title' => __('addons'),
            'js' => true
        )
    );

    Registry::set('navigation.tabs', $tabs);
    // [/Page sections]

    Registry::get('view')->assign('news_data', $news_data);

} elseif ($mode == 'manage' || $mode == 'picker') {

    list($news, $search) = fn_get_news($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'), DESCR_SL);

    Registry::get('view')->assign('news', $news);
    Registry::get('view')->assign('search', $search);

} elseif ($mode == 'delete') {
    if (!empty($_REQUEST['news_id'])) {
        fn_delete_news($_REQUEST['news_id']);
    }

    return array(CONTROLLER_STATUS_REDIRECT, "news.manage");
}

//
// News picker
//
if ($mode == 'picker') {
    Registry::get('view')->display('addons/news_and_emails/pickers/news/picker_contents.tpl');
    exit;
}


