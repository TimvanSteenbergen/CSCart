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

require './init_payment.php';

$ref = empty($_REQUEST['ref']) ? '0' : $_REQUEST['ref'];

$order_id = (strpos($ref, '_')) ? substr($ref, 0, strpos($ref, '_')) : $ref;

fn_redirect(fn_url("payment_notification.notify.nok?payment=proxypay3&order_id=$order_id"));
