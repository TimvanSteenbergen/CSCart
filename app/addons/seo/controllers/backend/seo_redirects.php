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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'update') {
        if (!empty($_REQUEST['redirect_data'])) {
            fn_seo_update_redirect($_REQUEST['redirect_data'], 0);
        }
    }

    if ($mode == 'm_update') {
        if (!empty($_REQUEST['seo_redirects'])) {
            foreach ($_REQUEST['seo_redirects'] as $redirect_id => $redirect_data) {
                fn_seo_update_redirect($redirect_data, $redirect_id);
            }
        }
    }

    if ($mode == 'm_delete') {
        if (!empty($_REQUEST['redirect_ids'])) {
            foreach ($_REQUEST['redirect_ids'] as $redirect_id) {
                fn_delete_seo_redirect($redirect_id);
            }
        }
    }

    return array(CONTROLLER_STATUS_OK, "seo_redirects.manage");
}

if ($mode == 'manage') {

    list($seo_redirects, $search) = fn_get_seo_redirects($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'));

    Registry::get('view')->assign('seo_redirects', $seo_redirects);
    Registry::get('view')->assign('search', $search);
    Registry::get('view')->assign('seo_vars', fn_get_seo_vars());

} elseif ($mode == 'delete') {
    if (!empty($_REQUEST['redirect_id'])) {
        fn_delete_seo_redirect($_REQUEST['redirect_id']);
    }

    return array(CONTROLLER_STATUS_OK, "seo_redirects.manage");
}

function fn_get_seo_redirects($params = array(), $items_per_page = 0, $lang_code = DESCR_SL)
{
    // Init filter
    $params = LastView::instance()->update('seo_redirects', $params);

    // Set default values to input params
    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);
    $condition = '';
    
    if (isset($params['src']) && fn_string_not_empty($params['src'])) {
        $condition .= db_quote(" AND src LIKE ?l", "%" . trim($params['src']) . "%");
    }

    if (!empty($params['type'])) {
        $condition .= db_quote(" AND type = ?s", $params['type']);
    }

    if (!empty($params['lang_code'])) {
        $condition .= db_quote(" AND lang_code = ?s", $params['lang_code']);
    }    

    $condition .= fn_get_seo_company_condition('?:seo_redirects.company_id');

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:seo_redirects WHERE 1 ?p", $condition);
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $seo_redirects = db_get_hash_array("SELECT * FROM ?:seo_redirects WHERE 1 ?p ORDER BY src $limit", 'redirect_id', $condition);

    if (!empty($seo_redirects)) {
        foreach ($seo_redirects as $key => $seo_redirect) {
            $seo_redirects[$key]['parsed_url'] = fn_generate_seo_url_from_schema($seo_redirect);
        }
    }


    return array($seo_redirects, $params);
}

function fn_delete_seo_redirect($redirect_id)
{
    db_query("DELETE FROM ?:seo_redirects WHERE redirect_id = ?i", $redirect_id);
}
