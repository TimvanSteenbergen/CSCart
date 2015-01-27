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

$categories = db_get_fields("SELECT object_id FROM ?:seo_names WHERE type='c' AND lang_code = (SELECT value FROM ?:settings_objects_upg WHERE name='frontend_default_language')");
if (!empty($categories)) {
    foreach ($categories as $category_id) {
        $path = db_get_field("SELECT id_path FROM ?:categories WHERE category_id = ?i", $category_id);
        $apath = explode('/', $path);
        array_pop($apath);
        db_query("UPDATE ?:seo_names SET path = ?s WHERE type='c' AND object_id = ?i", implode('/', $apath), $category_id);
    }
}

$products = db_get_fields("SELECT object_id FROM ?:seo_names WHERE type='p' AND lang_code = (SELECT value FROM ?:settings_objects_upg WHERE name='frontend_default_language')");
if (!empty($products)) {
    $condition = '';
    if (fn_allowed_for('ULTIMATE')) {
        $condition = fn_get_company_condition('c.company_id', false);
        $condition = !empty($condition) ? " AND ($condition OR $field = 0)" : '';
    }
    foreach ($products as $product_id) {
        $path = db_get_hash_single_array("SELECT c.id_path, p.link_type FROM ?:categories as c LEFT JOIN ?:products_categories as p ON p.category_id = c.category_id WHERE p.product_id = ?i ?p", array('link_type', 'id_path'), $product_id, $condition);
        $_path = !empty($path['M']) ? $path['M'] : $path['A'];
        db_query("UPDATE ?:seo_names SET path = ?s WHERE type='p' AND object_id = ?i", $_path, $product_id);
    }
}

$pages = db_get_fields("SELECT object_id FROM ?:seo_names WHERE type='a' AND lang_code = (SELECT value FROM ?:settings_objects_upg WHERE name='frontend_default_language')");
if (!empty($pages)) {
    foreach ($pages as $page_id) {
        $path = db_get_field("SELECT id_path FROM ?:pages WHERE page_id = ?i", $page_id);
        $apath = explode('/', $path);
        array_pop($apath);
        db_query("UPDATE ?:seo_names SET path = ?s WHERE type='a' AND object_id = ?i", implode('/', $apath), $page_id);
    }
}
