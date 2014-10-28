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

if ($mode == 'add' || $mode == 'update') {
    $page_type = isset($_REQUEST['page_type']) ? $_REQUEST['page_type'] : '';
    if (empty($page_type) && !empty($_REQUEST['page_id'])) {
        $page_data = fn_get_page_data($_REQUEST['page_id']);
        $page_type = $page_data['page_type'];
    }

    if ($page_type == PAGE_TYPE_FORM) {
        // [Page sections]
        Registry::set('navigation.tabs.build_form', array (
            'title' => __('form_builder'),
            'js' => true
        ));
        // [/Page sections]
    }

    Registry::get('view')->assign('selectable_elements', implode('', fn_form_builder_selectable_elements()));
}

if ($mode == 'update') {
    list($elements, $form) = fn_get_form_elements($_REQUEST['page_id'], false, DESCR_SL);
    Registry::get('view')->assign('form', $form);
    Registry::get('view')->assign('elements', $elements);
}
