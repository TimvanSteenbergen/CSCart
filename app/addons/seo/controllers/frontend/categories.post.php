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

if ($mode == 'view' && !empty($_REQUEST['category_id'])) {

    $seo_canonical = array();
    $search = Registry::get('view')->getTemplateVars('search');

    if ($search['total_items'] > $search['items_per_page']) {
        $pagination = fn_generate_pagination($search);

        if ($pagination['prev_page']) {
            $seo_canonical['prev'] = fn_url('categories.view?category_id=' . $_REQUEST['category_id'] . '&page=' . $pagination['prev_page']);
        }
        if ($pagination['next_page']) {
            $seo_canonical['next'] = fn_url('categories.view?category_id=' . $_REQUEST['category_id'] . '&page=' . $pagination['next_page']);
        }
    }

    $seo_canonical['current'] = fn_url('categories.view?category_id=' . $_REQUEST['category_id'] . ($search['page'] > 1 ? '&page=' . $search['page'] : ''));

    Registry::get('view')->assign('seo_canonical', $seo_canonical);
}
