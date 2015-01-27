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

/** Inclusions **/
/** /Inclusions **/

/** Body **/

$section_id = isset($_REQUEST['section_id']) ? intval($_REQUEST['section_id']) : '0';
$link_id = isset($_REQUEST['link_id']) ? intval($_REQUEST['link_id']) : '0';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $suffix = '';

    if ($mode == 'update_sitemap') {
        $section_data = $_REQUEST;

        $section_id = fn_update_sitemap($section_data, $section_id);

        $link_data = array();

        // Add new links
        if (isset($section_data['add_link_data'])) {
            $link_data = array_merge($link_data, $section_data['add_link_data']);
        }

        // Update section links
        if (isset($section_data['link_data'])) {
            $link_data = array_merge($link_data, $section_data['link_data']);
        }

        fn_update_sitemap_links($link_data, $section_id);

        $suffix = ".manage";
    }

    return array(CONTROLLER_STATUS_OK, "sitemap$suffix");
}

// -------------------------------------- GET requests -------------------------------

// Collect section methods data
if ($mode == 'update') {

    if (empty($section_id)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    $params = array('section_id' => $section_id);
    $sections = fn_get_sitemap_sections($params);

    if (empty($sections)) {
        return array(CONTROLLER_STATUS_DENIED);
    }

    Registry::get('view')->assign('section', reset($sections));

    $links = fn_get_sitemap_links($section_id);
    Registry::get('view')->assign('links', $links);

// Show all section methods
} elseif ($mode == 'manage') {
    $sections = fn_get_sitemap_sections();
    Registry::get('view')->assign('sitemap_sections', $sections);

} elseif ($mode == 'delete_section') {
    if (!empty($section_id)) {
        fn_delete_sitemap_sections((array) $section_id);
    }

    return array(CONTROLLER_STATUS_REDIRECT, "sitemap.manage");

}

/** /Body **/
