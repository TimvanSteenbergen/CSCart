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

$_REQUEST['news_id'] = empty($_REQUEST['news_id']) ? 0 : $_REQUEST['news_id'];

if ($mode == 'view') {

    fn_add_breadcrumb(__('news'), "news.list");

    $news_data = fn_get_news_data($_REQUEST['news_id']);
    if (empty($news_data)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    fn_add_breadcrumb($news_data['news']);

    Registry::get('view')->assign('news', $news_data);

} elseif ($mode == 'list') {

    fn_add_breadcrumb(__('news'));

    list($news, $search) = fn_get_news($_REQUEST, Registry::get('settings.Appearance.elements_per_page'), CART_LANGUAGE);

    Registry::get('view')->assign('news', $news);
    Registry::get('view')->assign('search', $search);
}
