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

namespace Tygh\StoreImport\Ult;
use Tygh\StoreImport\General;
use Tygh\Registry;

class F415T421
{
    protected $store_data = array();
    protected $main_sql_filename = 'ult_F415T421.sql';

    public function __construct($store_data)
    {
        $store_data['product_edition'] = 'ULTIMATE';
        $this->store_data = $store_data;
    }

    public function import($db_already_cloned)
    {
        General::setProgressTitle(__CLASS__);
        if (!$db_already_cloned) {
            if (!General::cloneImportedDB($this->store_data)) {
                return false;
            }
        } else {
            General::setEmptyProgressBar(__('importing_data'));
            General::setEmptyProgressBar(__('importing_data'));
        }

        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));
        General::processAddons($this->store_data, __CLASS__);

        $main_sql = Registry::get('config.dir.addons') . 'store_import/database/' . $this->main_sql_filename;
        if (is_file($main_sql)) {
            //Process main sql
            if (!db_import_sql_file($main_sql)) {
                return false;
            }
        }
        $seo_status = db_get_field("SELECT status FROM ?:addons WHERE addon = 'seo'");
        if (!empty($seo_status)) {
            $seo_multi_language = db_get_field("SELECT value FROM ?:settings_objects_upg WHERE name = 'multi_language'");
            $seo_multi_language_vendor = db_get_hash_single_array("SELECT company_id, value FROM ?:settings_vendor_values_upg WHERE object_id = (SELECT object_id FROM ?:settings_objects_upg WHERE name = 'multi_language')", array('company_id', 'value'));
            db_query("UPDATE ?:settings_objects_upg SET value = 'category_nohtml' WHERE value = 'category' AND name = 'seo_category_type'");
            db_query("UPDATE ?:settings_vendor_values_upg SET value = 'category_nohtml' WHERE value = 'category' AND object_id = (SELECT object_id FROM ?:settings_objects_upg WHERE name = 'seo_category_type')");
            db_query("UPDATE ?:settings_objects_upg SET value = 'category' WHERE value = 'file' AND name = 'seo_category_type'");
            db_query("UPDATE ?:settings_vendor_values_upg SET value = 'category' WHERE value = 'file' AND object_id = (SELECT object_id FROM ?:settings_objects_upg WHERE name = 'seo_category_type')");

            $categories = db_get_fields("SELECT object_id FROM ?:seo_names WHERE type='c' AND lang_code = (SELECT value FROM ?:settings_objects WHERE name='frontend_default_language')");
            if (!empty($categories)) {
                foreach ($categories as $category_id) {
                    $path = db_get_field("SELECT id_path FROM ?:categories WHERE category_id = ?i", $category_id);
                    $apath = explode('/', $path);
                    array_pop($apath);
                    db_query("UPDATE ?:seo_names SET path = ?s WHERE type='c' AND object_id = ?i", implode('/', $apath), $category_id);
                }
            }
            $products = db_get_fields("SELECT object_id FROM ?:seo_names WHERE type='p' AND lang_code = (SELECT value FROM ?:settings_objects WHERE name='frontend_default_language')");
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
            $pages = db_get_fields("SELECT object_id FROM ?:seo_names WHERE type='a' AND lang_code = (SELECT value FROM ?:settings_objects WHERE name='frontend_default_language')");
            if (!empty($pages)) {
                foreach ($pages as $page_id) {
                    $path = db_get_field("SELECT id_path FROM ?:pages WHERE page_id = ?i", $page_id);
                    $apath = explode('/', $path);
                    array_pop($apath);
                    db_query("UPDATE ?:seo_names SET path = ?s WHERE type='a' AND object_id = ?i", implode('/', $apath), $page_id);
                }
            }
        }

        General::restoreSettings();
        if (!empty($seo_status)) {
            db_query("UPDATE ?:settings_objects SET value = ?s WHERE name = 'non_latin_symbols'", $seo_multi_language);
            foreach ($seo_multi_language_vendor as $company_id => $value) {
                db_query("REPLACE INTO ?:settings_vendor_values VALUES ((SELECT object_id FROM ?:settings_objects WHERE name = 'non_latin_symbols'), ?i, ?s)", $company_id, $value);
            }
            $map = array(
                'product_file' => 'file',
                'product_category' => 'page',
            );
            $setting = db_get_field("SELECT value FROM ?:settings_objects WHERE name = 'seo_product_type'");
            db_query("UPDATE ?:settings_objects SET value = ?s WHERE name = 'seo_page_type'", $map[$setting]);
            $obj_id = db_get_field("SELECT object_id FROM ?:settings_objects WHERE name = 'seo_page_type'");
            $settings = db_get_array("SELECT * FROM ?:settings_vendor_values WHERE object_id = (SELECT object_id FROM ?:settings_objects WHERE name = 'seo_product_type')");
            if (!empty($settings)) {
                foreach ($settings as $vendor_setting) {
                    db_query("UPDATE ?:settings_vendor_values SET value = ?s WHERE object_id = ?i AND company_id = ?i", $map[$vendor_setting['value']], $obj_id, $vendor_setting['company_id']);
                }
            }
            db_query("UPDATE ?:settings_objects SET value = 'file' WHERE name = 'seo_other_type'");
            db_query("UPDATE ?:settings_vendor_values SET value = 'file' WHERE object_id = (SELECT object_id FROM ?:settings_objects WHERE name = 'seo_other_type')");

            $empty_product_seo_names = db_get_fields("SELECT object_id FROM ?:seo_names WHERE type = 'p' AND object_id NOT IN (SELECT product_id FROM ?:products)");
            if ($empty_product_seo_names) {
                db_query("DELETE FROM ?:seo_names WHERE object_id IN (?n)", $empty_product_seo_names);
            }
            $empty_pages_seo_names = db_get_fields("SELECT object_id FROM ?:seo_names WHERE type = 'a' AND object_id NOT IN (SELECT page_id FROM ?:pages)");
            if ($empty_product_seo_names) {
                db_query("DELETE FROM ?:seo_names WHERE object_id IN (?n)", $empty_product_seo_names);
            }
            $empty_categories_seo_names = db_get_fields("SELECT object_id FROM ?:seo_names WHERE type = 'c' AND object_id NOT IN (SELECT category_id FROM ?:categories)");
            if ($empty_categories_seo_names) {
                db_query("DELETE FROM ?:seo_names WHERE object_id IN (?n)", $empty_categories_seo_names);
            }
            $empty_features_seo_names = db_get_fields("SELECT object_id FROM ?:seo_names WHERE type = 'e' AND object_id NOT IN (SELECT feature_id FROM ?:product_features)");
            if ($empty_features_seo_names) {
                db_query("DELETE FROM ?:seo_names WHERE object_id IN (?n)", $empty_features_seo_names);
            }
            $_empty_product_seo_names = db_get_array("
                SELECT ?:seo_names.*
                FROM ?:categories
                RIGHT JOIN ?:products_categories ON ?:categories.category_id = ?:products_categories.category_id
                RIGHT JOIN ?:seo_names ON ?:products_categories.product_id = ?:seo_names.object_id AND ?:categories.company_id = ?:seo_names.company_id
                WHERE ?:categories.category_id IS NULL AND ?:seo_names.type = 'p'"
            );
            foreach ($_empty_product_seo_names as $value) {
                db_query("DELETE FROM ?:seo_names WHERE object_id = ?i AND company_id = ?i AND type = 'p'", $value['object_id'], $value['company_id']);
            }
        }

        if (db_get_field("SELECT status FROM ?:addons WHERE addon = 'suppliers'")) {
            $query_parts = array();
            $shippings = fn_get_shippings(true);

            foreach ($shippings as $shipping_id => $shipping_name) {
                $query_parts[] = db_quote('(?i, ?i, ?s)', 0, $shipping_id, 'S');
            }

            if (!empty($query_parts)) {
                db_query('REPLACE INTO ?:supplier_links VALUES ' . implode(', ', $query_parts));
            }
        }

        $products_with_empty_categories = db_get_array("SELECT category_id, product_id FROM ?:products_categories WHERE category_id NOT IN (SELECT category_id FROM ?:categories)");
        db_query("DELETE FROM ?:products_categories WHERE category_id NOT IN (SELECT category_id FROM ?:categories)");
        foreach ($products_with_empty_categories as $k => $data) {
            $product_to_delete = db_get_field("SELECT product_id FROM ?:products_categories WHERE product_id = ?i", $data['product_id']);
            if (!$product_to_delete) {
                fn_delete_product($data['product_id']);
            } else {
                $product_category = db_get_field("SELECT category_id FROM ?:products_categories WHERE product_id = ?i", $product_to_delete);
                $company_id = db_get_field("SELECT company_id FROM ?:categories WHERE category_id = ?i", $product_category);
                db_query("UPDATE ?:products_categories SET link_type = 'A' WHERE product_id = ?i", $product_to_delete);
                db_query("UPDATE ?:products_categories SET link_type = 'M' WHERE product_id = ?i AND category_id = ?i", $product_to_delete, $product_category);
                db_query("UPDATE ?:products SET company_id = ?i WHERE product_id = ?i", $company_id, $product_to_delete);
            }
        }

        $products_with_category = db_get_hash_array("SELECT ?:products_categories.product_id, ?:categories.company_id  FROM ?:products_categories LEFT JOIN ?:categories ON ?:categories.category_id = ?:products_categories.category_id LEFT JOIN ?:products ON ?:products.product_id = ?:products_categories.product_id WHERE ?:products.company_id = ?:categories.company_id", 'product_id');
        $products_with_foreign_category = db_get_array("SELECT ?:products_categories.product_id, ?:categories.company_id, ?:products.company_id as old_company_id  FROM ?:products_categories LEFT JOIN ?:categories ON ?:categories.category_id = ?:products_categories.category_id LEFT JOIN ?:products ON ?:products.product_id = ?:products_categories.product_id WHERE ?:products.company_id != ?:categories.company_id AND ?:products_categories.product_id NOT IN (?n)", array_keys($products_with_category));
        if (!empty($products_with_foreign_category)) {
            foreach ($products_with_foreign_category as $key => $data) {
                db_query("UPDATE ?:products SET company_id = ?i WHERE product_id = ?i", $data['company_id'], $data['product_id']);
                if (!empty($seo_status)) {
                    db_query("DELETE FROM ?:seo_names WHERE type = 'p' AND object_id = ?i AND company_id = ?i", $data['product_id'], $data['old_company_id']);
                }
            }
        }

        General::setEmptyProgressBar();
        General::setEmptyProgressBar();
        General::setEmptyProgressBar();
        General::setEmptyProgressBar();
        return true;
    }
}
