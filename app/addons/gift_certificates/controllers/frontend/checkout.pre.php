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

if (in_array($mode, array('cart', 'checkout', 'place_order')) && isset($_SESSION['cart']['use_gift_certificates'])) {
    $company_id = Registry::get('runtime.company_id');
    $codes = fn_check_gift_certificate_code(array_keys($_SESSION['cart']['use_gift_certificates']), true, $company_id);

    $remove_codes = array_diff_key($_SESSION['cart']['use_gift_certificates'], !empty($codes) ? $codes : array());
    $removed_codes = false;

    if (!empty($remove_codes)) {
        foreach ($remove_codes as $code => $value) {
            unset($_SESSION['cart']['use_gift_certificates'][$code]);
        }
        $removed_codes = true;
    }

    if ($removed_codes) {
        fn_set_notification('W', __('warning'), __('warning_gift_cert_deny', array(
            '[codes]' => implode(', ', array_keys($remove_codes))
        )), 'K');
    }

    if ($mode == 'place_order') {
        fn_calculate_cart_content($_SESSION['cart'], $auth, 'A', true, 'F');
    }

    return;
}
