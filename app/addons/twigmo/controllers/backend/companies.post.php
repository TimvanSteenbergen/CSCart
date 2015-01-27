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

if ( !defined('AREA') )    { die('Access denied');    }

use Tygh\Registry;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $mode == 'update') {
    $_SESSION['twg_need_update_connection'] = true;
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && $mode == 'update') {
    if (!empty($_SESSION['twg_need_update_connection'])) {
        $view = Registry::get('view');
        $view->assign('stats', fn_twg_get_ajax_reconnect_code());
        $_SESSION['twg_need_update_connection'] = false;
    }
}

?>
