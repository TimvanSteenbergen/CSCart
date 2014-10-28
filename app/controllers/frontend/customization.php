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

if ($mode == 'disable_mode') {
    if (!empty($_REQUEST['type'])) {
        $c_mode = $_REQUEST['type'];
        $avail_modes = array_keys(fn_get_customization_modes());
        
        if (!in_array($c_mode, $avail_modes)) {
            return array(CONTROLLER_STATUS_NO_PAGE);
        }
        
        if ($c_mode == 'theme_editor') {
            unset($_SESSION['demo_customize_theme']);
        }

        fn_update_customization_mode(array($c_mode => 'disable'));

        return array(CONTROLLER_STATUS_OK, 'index.index');
    }
}
