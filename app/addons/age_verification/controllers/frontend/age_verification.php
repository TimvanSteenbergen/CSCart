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
    if ($mode == 'verify') {
        if (!empty($_REQUEST['age'])) {
            $age = intval($_REQUEST['age']);

            if ($age < 0) {
                $age = 0;
            }

            $_SESSION['auth']['age'] = $age;

            if (!empty($_REQUEST['redirect_url'])) {
                return array (CONTROLLER_STATUS_OK, $_REQUEST['redirect_url']);
            }

            return array (CONTROLLER_STATUS_REDIRECT, '');
        }
    }
}
