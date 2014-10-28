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
    if ($mode == 'update' || $mode == 'add') {
        if (fn_email_is_blocked($_REQUEST['user_data'], true)) {
            fn_save_post_data('user_data');

            return array(CONTROLLER_STATUS_REDIRECT, "profiles.update" . ((AREA == 'A' && !empty($_REQUEST['user_id'])) ? "?user_id=$_REQUEST[user_id]" : ''));
        }
    }

    return;
}

/** /Body **/
