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

define('AREA', 'C');
require dirname(__FILE__) . '/../../init.php';

$backtrace = debug_backtrace();
$processor = fn_basename(fn_unified_path($backtrace[0]['file']), '.php');

if (!fn_check_prosessor_status($processor)) {
    die('Access denied');
}
