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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'calculate') {
        list($total_price, $discount, $discounted_price) = fn_buy_together_calculate($_REQUEST, $auth);

        fn_echo(__('buy_together_calculation_information', array(
            '[total_price]' =>  $total_price,
            '[discount]' => $discount,
            '[combination_price]' => $discounted_price,
        )));
        exit();
    }

    return array(CONTROLLER_STATUS_OK, $_REQUEST['redirect_url']);
}
