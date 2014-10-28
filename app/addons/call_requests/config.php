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

define('CALL_REQUESTS_BLOCK_CONTENT',
    '{if $addons.call_requests.status == "A"}' . PHP_EOL
    . '<div class="ty-cr-phone-number-link">' . PHP_EOL
    . '    <div class="ty-cr-phone">{call_phone}</div>' . PHP_EOL
    . '    <div class="ty-cr-link">{call_request}</div>' . PHP_EOL
    . '</div>' . PHP_EOL
    . '{/if}'
);
