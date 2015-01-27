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

define('EBAY_TRANSACTION_EXPIRATION', 30 * 24 * 3600); // 30 days
define('EBAY_CHECK_CATEGORIES_PERIOD', 24 * 3600); // 1 day
define('EBAY_CHECK_SITES_PERIOD', 7 * 24 * 3600); // 7 day
define('EBAY_CHECK_SHIPPINGS_PERIOD', 24 * 3600); // 1 day
define('EBAY_TRANSACTION_EXECUTION_TIME', 600); // 10 mins, to be sure that transaction is failed
