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

if ($mode == 'view') {
    if (!empty($_REQUEST['page'])) {
        $page = intval($_REQUEST['page']);
        $filename = fn_get_cache_path(false) . 'google_sitemap/sitemap' . $page . '.xml';
    } else {
        $page = 0;
        $filename = fn_get_cache_path(false) . 'google_sitemap/sitemap.xml';
    }

    if (file_exists($filename)) {
        header("Content-Type: text/xml;charset=utf-8");

        readfile($filename);
        exit();

    } else {
        set_time_limit(3600);

        fn_google_sitemap_get_content($page);
    }
}
