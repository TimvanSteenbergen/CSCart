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

function fn_janrain_generate_info()
{
    return __('janrain_general_info');
}

function fn_janrain_parse_app_domain($url)
{
    $result = parse_url($url);

    if (!empty($result['host'])) {
        return str_replace('.rpxnow.com', '', $result['host']);
    }

    return false;
}

function fn_janrain_fill_user_fields(&$exclude)
{
        $exclude[] = 'janrain_identifier';
}
