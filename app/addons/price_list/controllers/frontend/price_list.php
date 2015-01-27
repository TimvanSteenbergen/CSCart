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

if ($mode == 'view') {

    fn_disable_live_editor_mode();
    if (empty($_REQUEST['display'])) {
        die('Access denied');
    }

    $types = array('pdf', 'xls');

    $price_schema = fn_get_schema('price_list', 'schema');
    $selected_fields = Registry::get('addons.price_list.price_list_fields');

    $modes = fn_price_list_get_pdf_layouts();

    // Check the available libs
    foreach ($types as $type) {
        if (!empty($modes[$type])) {
            foreach ($modes[$type] as $f_mode) {
                if ($_REQUEST['display'] == fn_basename($f_mode, '.php')) {
                    include_once Registry::get('config.dir.addons') . '/price_list/templates/' . $type . '/' . $f_mode;

                    $meta_redirect_url = urlencode(fn_url('price_list.view?display=' . fn_basename($f_mode, '.php'), 'C', 'http'));

                    if (!empty($_REQUEST['return_url'])) {
                        $base_url = $_REQUEST['return_url'];
                    } else {
                        $base_url = (empty($_SERVER['HTTP_REFERER']) ? fn_url() . '?' : $_SERVER['HTTP_REFERER']);
                        if (strpos('?', $base_url) === false) {
                            $base_url .= '?';
                        }
                    }

                    return array(CONTROLLER_STATUS_REDIRECT, $base_url . '&meta_redirect_url=' . $meta_redirect_url, true);
                }
            }
        }
    }
}
