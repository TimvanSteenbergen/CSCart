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

$edit_step = !empty($_SESSION['edit_step']) ? $_SESSION['edit_step'] : '';

if ($mode == 'checkout' && $edit_step == 'step_one') {

    $providers_list = fn_hybrid_auth_get_providers_list();
    if (!empty($providers_list)) {
        Registry::get('view')->assign('providers_list', $providers_list);
        Registry::get('view')->assign('redirect_url', "checkout.checkout");
    }
}
