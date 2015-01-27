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

if ($mode == 'details') {
    if (!empty($_REQUEST['order_id'])) {
        if ($discussion = fn_get_discussion($_REQUEST['order_id'], 'O', true, $_REQUEST)) {
            if ($discussion['type'] != 'D') {
                $navigation_tabs = Registry::get('navigation.tabs');
                $navigation_tabs['discussion'] = array(
                    'title' => __('communication'),
                    'js' => true
                );

                Registry::set('navigation.tabs', $navigation_tabs);

                Registry::get('view')->assign('discussion', $discussion);
            }
        }
    }
}
