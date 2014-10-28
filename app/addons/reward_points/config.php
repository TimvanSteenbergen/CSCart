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

//
// Defined variables
//

define('ORDER_DATA_POINTS_GAIN', 'A');

define('PRODUCT_REWARD_POINTS', 'P');
define('CATEGORY_REWARD_POINTS', 'C');
define('GLOBAL_REWARD_POINTS', 'A');

define('POINTS', 'W');
define('POINTS_MODIFIER_TYPE', 'R');
define('POINTS_IN_USE', 'I');

//
//These constants define the reason for the change of points
//
define('CHANGE_DUE_ORDER', 'O');
define('CHANGE_DUE_USE', 'I');
define('CHANGE_DUE_RMA', 'R');
define('CHANGE_DUE_ADDITION', 'A');
define('CHANGE_DUE_SUBTRACT', 'S');
define('CHANGE_DUE_ORDER_DELETE', 'D');
define('CHANGE_DUE_ORDER_PLACE', 'P');
