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

namespace Tygh\StoreImport\Pro;
use Tygh\StoreImport\General;
use Tygh\Registry;
use Tygh\Settings;
use Tygh\Menu;

class F225T301
{
    protected $store_data = array();
    protected $main_sql_filename = 'pro_F225T301.sql';

    public function __construct($store_data)
    {
        $store_data['product_edition'] = 'PROFESSIONAL';
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

        General::connectToImportedDB($this->store_data);

        $supplier_settings = $this->getSupplierSettings();
        General::setSupplierSettings($supplier_settings);

        $default_language = db_get_field(
            "SELECT value FROM ?:settings WHERE option_name = 'customer_default_language' AND section_id = 'Appearance'"
        );

        $settings_to_be_saved = array(
            'use_email_as_login',
            'admin_default_language',
            'customer_default_language',
            'disable_shipping',
            'fedex_enabled',
            'ups_enabled',
            'usps_enabled',
            'dhl_enabled',
            'aup_enabled',
            'can_enabled',
            'swisspost_enabled',
            'seo_product_type',
            'seo_category_type',
            'single_url',
            'seo_language',
        );

        $settings_to_be_saved_values = General::get22xSettings($settings_to_be_saved);

        $addons = General::get22xAddons();

        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));

        $main_sql = Registry::get('config.dir.addons') . 'store_import/database/' . $this->main_sql_filename;
        if (is_file($main_sql)) {
            //Process main sql
            if (!db_import_sql_file($main_sql)) {
                return false;
            }
        }

        General::processAddons($this->store_data, __CLASS__, array_keys($addons));

        if (!empty($addons)) {
            General::processAddonsSettings($addons);
        }
        General::enableInstalledAddons($addons);
        General::setEmptyProgressBar();
        $this->_importMenu();
        $this->_copyImages();
        $this->_copyFiles();
        $this->_patchProfileFields();
        $this->_normalizeProductViews();
        $this->_normalizeUserGroupIds();
        $this->_fixLanguagesMissedInImported($default_language);
        General::addStatusColors();
        General::copyProductsBlocks($this->store_data);

        General::restore22xSavedSetting($settings_to_be_saved_values);

        General::setEmptyProgressBar();

        return true;
    }

    private function _getSettings()
    {
        return db_get_array('SELECT * FROM ?:settings');
    }

    private function _importMenu()
    {
        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));
        db_query("DELETE FROM ?:menus");

        $top_menu_id = Menu::update(array(
            'lang_code' => DEFAULT_LANGUAGE,
            'name' => 'Top menu',
            'status' => 'A'
        ));

        db_query("UPDATE ?:static_data SET param_5 = ?i WHERE section = 'A'", $top_menu_id);

        $quick_menu_id = Menu::update(array(
            'lang_code' => DEFAULT_LANGUAGE,
            'name' => 'Quick menu',
            'status' => 'A'
        ));

        db_query("UPDATE ?:static_data SET param_5 = ?i WHERE section = 'N'", $quick_menu_id);
        db_query("UPDATE ?:static_data SET section = 'A' WHERE section = 'N'");

        $blocks = db_get_array(
            "SELECT ?:bm_blocks_content.block_id, content FROM ?:bm_blocks_content "
            . "LEFT JOIN ?:bm_blocks ON ?:bm_blocks.block_id = ?:bm_blocks_content.block_id "
            . "WHERE type = 'menu'"
        );

        foreach ($blocks as $block) {
            $content = unserialize($block['content']);

            if (isset($content['menu'])) {
                $content['menu'] = $quick_menu_id;
                db_query("UPDATE ?:bm_blocks_content SET content = ?s WHERE block_id = ?i", serialize($content), $block['block_id']);
            }
        }
    }

    private function _copyImages()
    {
        $images_dirs = fn_get_dir_contents($this->store_data['path'] . '/images', true, false, '', $this->store_data['path'] . '/images/');

        $img_path_info = Registry::get('config.storage.images');
        foreach ($images_dirs as $dir) {
            fn_copy($dir, $img_path_info['dir'] . fn_basename($dir), true);
        }

        return true;
    }

    private function _copyFiles()
    {
        if (is_dir($this->store_data['path'] . '/var/attachments')) {
            fn_copy($this->store_data['path'] . '/var/attachments', DIR_ROOT . '/var/attachments', true);
        }

        $type_path_info = Registry::get('config.storage.downloads');
        fn_copy($this->store_data['path'] . '/var/downloads', $type_path_info['dir'], true);

        return true;
    }

    private function _patchProfileFields()
    {
        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));

        $field_id = db_get_field("SELECT field_id FROM ?:profile_fields WHERE field_name = 'email' AND section = 'C'");

        $billing_email_id = db_query(
            "INSERT INTO ?:profile_fields (field_name, profile_show, profile_required, checkout_show, checkout_required, "
            . "partner_show, partner_required, field_type, position, is_default, section, matching_id, class) "
            . "VALUES ('email', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'E', '105', 'Y', 'B', '33', 'billing-email')"
        );

        $shipping_email_id = db_query(
            "INSERT INTO ?:profile_fields (field_name, profile_show, profile_required, checkout_show, checkout_required, "
            . "partner_show, partner_required, field_type, position, is_default, section, matching_id, class) "
            . "VALUES ('email', 'N', 'N', 'N', 'N', 'N', 'N', 'E', '205', 'Y', 'S', ?i, 'shipping-email')",
                $billing_email_id
            );

        db_query(
            "INSERT INTO ?:profile_field_descriptions (object_id, description, object_type, lang_code) "
            . "VALUES (?i, 'E-mail', 'F', ?s);",
                $billing_email_id, DEFAULT_LANGUAGE
            );

        foreach (fn_get_translation_languages() as $lang_code => $value) {
            db_query(
                "REPLACE INTO ?:profile_field_descriptions (object_id, description, object_type, lang_code) "
                . "VALUES (?i, 'E-mail', 'F', ?s), (?i, 'E-mail', 'F', ?s);",
                    $shipping_email_id, $lang_code, $billing_email_id, $lang_code
                );
        }

        db_query(
            "UPDATE ?:profile_fields SET matching_id = ?i WHERE field_id = ?i ", $shipping_email_id, $billing_email_id
        );

        db_query("DELETE FROM ?:profile_fields WHERE field_id = ?i ", $field_id);
        db_query("DELETE FROM ?:profile_field_descriptions WHERE object_id = ?i AND object_type='F'", $field_id);

        return true;
    }

    private function _normalizeProductViews()
    {
        db_query(
            "UPDATE ?:products SET details_layout = 'default_template' "
            . "WHERE details_layout IN ('modern_template', 'old_style_template')"
        );

        db_query(
            "UPDATE ?:categories SET product_details_layout = 'default_template' "
            . "WHERE product_details_layout IN ('modern_template', 'old_style_template')"
        );

        db_query(
            "UPDATE ?:categories SET default_layout = 'products_multicolumns' "
            . "WHERE default_layout IN ('products', 'products_grid', 'products_multicolumns2', 'products_multicolumns3')"
        );

        $selected_layouts = array(
            'products_multicolumns' => 'products_multicolumns',
            'products_without_options' => 'products_without_options',
            'short_list' => 'short_list'
        );

        db_query(
            "UPDATE ?:categories SET selected_layouts = ?s WHERE selected_layouts <> '' ",
            serialize($selected_layouts)
        );

        return true;
    }

    private function _normalizeUserGroupIds()
    {
        db_query("UPDATE ?:products SET usergroup_ids = 0 WHERE usergroup_ids ='' ");

        return true;
    }

    protected function _fixLanguagesMissedInImported($copy_from_language)
    {
        General::connectToImportedDB($this->store_data);

        $languages = db_get_hash_array('SELECT * FROM ?:languages', 'lang_code');

        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));
        $existing = db_get_hash_array('SELECT * FROM ?:languages', 'lang_code');

        $missed_languages = array_diff_key($existing, $languages);

        if (!empty($missed_languages)) {
            foreach ($missed_languages as $lang_code => $language_data) {
                fn_clone_language($language_data['lang_code'], $copy_from_language);
            }
        }

        return true;
    }

    protected function getSupplierSettings()
    {
        $supplier_settings['enabled'] = db_get_field("SELECT value FROM ?:settings WHERE option_name = 'enable_suppliers'");
        $supplier_settings['display_supplier'] = db_get_field("SELECT value FROM ?:settings WHERE option_name = 'display_supplier'");
        $supplier_settings['display_shipping_methods_separately'] = db_get_field("SELECT value FROM ?:settings WHERE option_name = 'display_shipping_methods_separately'");

        return $supplier_settings;
    }
}
