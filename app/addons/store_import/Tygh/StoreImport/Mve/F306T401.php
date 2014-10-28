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

namespace Tygh\StoreImport\Mve;
use Tygh\StoreImport\General;
use Tygh\Shippings\Shippings;
use Tygh\Registry;
use Crypt_Blowfish;

class F306T401
{
    protected $store_data = array();
    protected $exclude_files = array('.htaccess', 'index.php');
    protected $main_sql_filename = 'mve_F306T401.sql';

    public function __construct($store_data)
    {
        $store_data['product_edition'] = 'MULTIVENDOR';
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

        $skin_name = $this->_getSkinName();
        $default_company = General::getDefaultCompany();

        General::processAddons($this->store_data, __CLASS__);
        $this->_processCurrencies();
        $this->_processLanguages();

        $main_sql = Registry::get('config.dir.addons') . 'store_import/database/' . $this->main_sql_filename;
        if (is_file($main_sql)) {
            //Process main sql
            if (!db_import_sql_file($main_sql)) {
                return false;
            }
        }

        General::uninstallAddons(array('twigmo', 'searchanise', 'live_help', 'exim_store', 'webmail'));

        $default_company_id = (int) General::createDefaultCompany($default_company);
        $this->_updateCompanyId($default_company_id);
        $this->_processPayments();
        $this->_processBMContainers();
        $this->_createLayouts();
        $this->_convertOrders($default_company_id);
        $this->_processImages($skin_name);
        $this->_processFiles('downloads');
        $this->_processFiles('attachments');
        $this->_processFiles('custom_files');
        $this->_processLanguageValues();
        $this->_processProducts($default_company_id);
        $this->_processShippings($default_company_id);
        General::updateStatusColors();
        General::processBMBlocksTemplates();
        General::processBMProductFiltersBlockContent();

        General::setEmptyProgressBar(General::getUnavailableLangVar('updating_languages'));
        General::updateAltLanguages('language_values', 'name');
        General::updateAltLanguages('settings_descriptions', array('object_id', 'object_type'));
        General::updateAltLanguages('shipping_service_descriptions', 'service_id');
        General::updateAltLanguages('privilege_descriptions', 'privilege');
        General::updateAltLanguages('privilege_section_descriptions', 'section_id');
        General::updateAltLanguages('state_descriptions', 'state_id');
        General::updateAltLanguages('country_descriptions', 'code');
        General::updateAltLanguages('bm_blocks_descriptions', 'block_id');
        General::updateAltLanguages('bm_locations_descriptions', 'location_id');
        General::updateAltLanguages('bm_blocks_content', array('snapping_id', 'object_id', 'object_type', 'block_id'));

        return true;
    }

    protected function _processProducts($default_company)
    {
        return db_query("UPDATE ?:products SET company_id = ?i WHERE company_id = 0", $default_company);
    }

    protected function _processShippings($default_company)
    {
        $shipping_ids = implode(', ', db_get_fields("SELECT * FROM ?:shippings WHERE status = 'A' AND company_id = 0"));

        return db_query("UPDATE ?:companies SET shippings = ?s WHERE company_id = ?i", $shipping_ids, $default_company);
    }

    protected function _convertOrders($default_company_id)
    {
        $limit = 50;
        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));

        db_query("UPDATE ?:order_data SET type='A' WHERE type='G'"); //change marker for addon Reward points
        $orders_count = db_get_field("SELECT COUNT(*) FROM ?:orders");
        $crypt = new Crypt_Blowfish($this->store_data['crypt_key']);
        $crypt_new = new Crypt_Blowfish(Registry::get('config.crypt_key'));
        $location_fields = array(
            's_firstname as firstname',
            's_lastname as lastname',
            's_address as address',
            's_address_2 as address_2',
            's_city as city',
            's_state as state',
            's_country as country',
            's_zipcode as zipcode',
            's_phone as phone',
            's_address_type as address_type',
        );

        fn_set_progress('step_scale', ceil($orders_count / $limit));
        for ($i = 0; $i <= floor($orders_count / $limit); $i++) {
            $order_ids = db_get_fields("SELECT order_id FROM ?:orders LIMIT ?i, ?i", $i * $limit, $limit);
            $data = array();
            $shipment_items = array();
            fn_set_progress('echo', '<br />' . General::getUnavailableLangVar('converting_orders'), true);

            foreach ($order_ids as $order_id) {
                $order_info = array();
                $order_info['shipping'] = unserialize(db_get_field("SELECT data FROM ?:order_data WHERE type='L' AND order_id = ?i", $order_id));
                $order_info['products'] = db_get_hash_array("SELECT * FROM ?:order_details WHERE order_id = ?i", 'item_id', $order_id);
                $location = db_get_row("SELECT ?p FROM ?:orders WHERE order_id = ?i", implode(',', $location_fields), $order_id);

                if ($order_info['products']) {
                    foreach ($order_info['products'] as $cart_id => $product) {
                        if ($product['extra']) {
                            $order_info['products'][$cart_id]['extra'] = unserialize($product['extra']);
                        }
                    }
                }
                $products = array();

                foreach ($order_info['products'] as $cart_id => $product) {
                    $products[$cart_id] = $product['extra'];
                    $products[$cart_id]['amount'] = $product['amount'];
                    $products[$cart_id]['product_id'] = $product['product_id'];
                }

                $product_groups = $this->_groupProductsListOrder($products);
                $shippings = array();

                foreach ($product_groups as $key_group => $group) {
                    if (!empty($order_info['shipping'])) {
                        foreach ($order_info['shipping'] as $shipping_id => $shipping) {
                            $_shipping = $shipping;
                            $_shipping['shipping_id'] = $shipping_id;
                            unset($_shipping['rates']);

                            if (isset($_shipping['tracking_number'])) {
                                $shipment = array(
                                    'shipping_id' => $shipping_id,
                                    'tracking_number' => $_shipping['tracking_number'],
                                    'carrier' => strtolower($_shipping['carrier']),
                                );

                                $shipment_id = db_query("INSERT INTO ?:shipments ?e", $shipment);

                                foreach ($group['products'] as $cart_id => $product) {
                                    $shipment_items[] = array(
                                        'item_id' => $cart_id,
                                        'shipment_id' => $shipment_id,
                                        'order_id' => $order_id,
                                        'product_id' => $product['product_id'],
                                        'amount' => $product['amount'],
                                    );
                                }

                                unset($_shipping['tracking_number']);
                                unset($_shipping['carrier']);
                            }

                            if (!empty($shipping_id)) {
                                foreach ($shipping['rates'] as $company_id => $rate) {
                                    if ($company_id == $group['company_id'] || ($company_id == 0 && $group['company_id'] == $default_company_id)) {
                                        $_shipping['rate'] = $rate;
                                        $_shipping['group_key'] = $key_group;
                                        $product_groups[$key_group]['shippings'][$shipping_id] = $_shipping;
                                        $product_groups[$key_group]['chosen_shippings'][] = $_shipping;
                                        $product_groups[$key_group]['package_info'] = !empty($_shipping['packages_info']) ? $_shipping['packages_info'] : array();
                                        $product_groups[$key_group]['package_info']['location'] = $location;

                                        $shippings[$shipping_id] = !empty($shippings[$shipping_id]) ? $shippings[$shipping_id] : $_shipping;
                                        $shippings[$shipping_id]['rates'][$key_group] = $shipping_id;
                                    }
                                }
                            }
                        }
                    }
                }

                $payment_info = db_get_field("SELECT data FROM ?:order_data WHERE type='P' AND order_id = ?i", $order_id);
                if (!empty($payment_info)) {
                    $payment_info = $crypt->decrypt(base64_decode($payment_info));
                    $payment_info = base64_encode($crypt_new->encrypt($payment_info));

                    $data[] = array (
                        'order_id' => $order_id,
                        'type' => 'P', //payment information
                        'data' => $payment_info,
                    );
                }

                $data[] = array (
                    'order_id' => $order_id,
                    'type' => 'G', //groups information
                    'data' => serialize($product_groups),
                );

                $data[] = array (
                    'order_id' => $order_id,
                    'type' => 'L', //shippings information
                    'data' => serialize(array_values($shippings)),
                );

                fn_echo(' .');
            }

            if (!empty($data)) {
                db_query("REPLACE INTO ?:order_data ?m", $data);
            }

            if (!empty($shipment_items)) {
                db_query("REPLACE INTO ?:shipment_items ?m", $shipment_items);
            }
        }

        db_query("UPDATE ?:shipments SET carrier = LOWER(carrier);");
        db_query("UPDATE ?:shipments SET carrier = 'usps' WHERE carrier = 'usp';");
        db_query("UPDATE ?:shipments SET carrier = 'fedex' WHERE carrier = 'fdx';");
        db_query("UPDATE ?:shipments SET carrier = 'swisspost' WHERE carrier = 'chp';");
    }

    protected function _groupProductsListOrder($products)
    {
        $groups = array();

        foreach ($products as $key_product => $product) {

            $product_is_exist = db_get_field("SELECT product_id FROM ?:products WHERE product_id = ?i", $product['product_id']);
            if (empty($product_is_exist)) {
                continue;
            }

            $company_id = $product['company_id'];

            if (empty($groups[$company_id])) {
                $group_name = db_get_field("SELECT company FROM ?:companies WHERE company_id = ?i", $company_id);

                $groups[$company_id] = array(
                    'name' => $group_name,
                    'company_id' => (int) $company_id,
                );
            }
            $groups[$company_id]['products'][$key_product] = $product;
        }

        foreach ($groups as $key_group => $group) {
            $all_edp_free_shipping = true;
            $all_free_shipping = true;
            $free_shipping = true;
            $shipping_no_required = true;
            foreach ($group['products'] as $product) {
                if ($product['is_edp'] != 'Y' || $product['edp_shipping'] == 'Y') {
                    $all_edp_free_shipping = false;
                }
                if (empty($product['free_shipping']) || $product['free_shipping'] != 'Y') {
                    $all_free_shipping = false;
                }
                if (($product['is_edp'] != 'Y' || $product['edp_shipping'] == 'Y') && (empty($product['free_shipping']) || $product['free_shipping'] != 'Y')) {
                    $free_shipping = false;
                }
                if (empty($product['shipping_no_required']) || $product['shipping_no_required'] != 'Y') {
                    $shipping_no_required = false;
                }
            }
            $groups[$key_group]['all_edp_free_shipping'] = $all_edp_free_shipping;
            $groups[$key_group]['all_free_shipping'] = $all_free_shipping;
            $groups[$key_group]['free_shipping'] = $free_shipping;
            $groups[$key_group]['shipping_no_required'] = $shipping_no_required;
        }

        return array_values($groups);
    }

    protected static function _processCurrencies()
    {
        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));

        $currencies = db_get_array("SELECT * FROM ?:currencies");

        db_query("DELETE FROM ?:currencies");
        db_query("ALTER TABLE ?:currencies DROP PRIMARY KEY");
        db_query("ALTER TABLE ?:currencies ADD currency_id mediumint(8) unsigned NOT NULL auto_increment PRIMARY KEY");
        db_query("ALTER TABLE ?:currencies CHANGE currency_code currency_code varchar(10) NOT NULL default '' UNIQUE KEY");

        foreach ($currencies as $currency) {
            $new_cur_id = db_query("INSERT INTO ?:currencies ?e", $currency);
        }

        return true;
    }

    protected static function _processLanguages()
    {
        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));

        $languages = db_get_array("SELECT * FROM ?:languages");

        db_query("DELETE FROM ?:languages");
        db_query("ALTER TABLE ?:languages DROP PRIMARY KEY;");
        db_query("ALTER TABLE ?:languages ADD country_code char(2) NOT NULL DEFAULT ''");
        db_query("ALTER TABLE ?:languages ADD lang_id mediumint(8) unsigned NOT NULL auto_increment PRIMARY KEY");
        db_query("ALTER TABLE ?:languages CHANGE lang_code lang_code char(2) NOT NULL DEFAULT '' UNIQUE KEY");

        foreach ($languages as $language) {
            $language['country_code'] = $language['lang_code'];
            $language['lang_code'] = strtolower($language['lang_code']);
            if ($language['lang_code'] == 'si') {
                $language['lang_code'] = 'sl';
                $language['country_code'] = 'SL';
            }
            $new_lang_id = db_query("INSERT INTO ?:languages ?e", $language);
        }

        return true;
    }

    protected function _createLayouts()
    {
        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));
        $layout_data = array(
            'name' => 'Main',
            'is_default' => 1,
            'width' => 16,
            'theme_name' => Registry::get('config.base_theme'),
            'preset_id' => db_get_field("SELECT preset_id FROM ?:theme_presets WHERE is_default = 1")
        );

        $layout_id = db_query("INSERT INTO ?:bm_layouts ?e", $layout_data);
        if (!empty($layout_id)) {
            db_query("UPDATE ?:bm_locations SET layout_id = ?i", $layout_id);
        }

        return true;
    }

    private function _processBMContainers()
    {
        $position_mapping = array(
            'TOP' => 'HEADER',
            'CENTRAL' => 'CONTENT',
            'BOTTOM' => 'FOOTER',
        );

        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));

        $positions = db_get_hash_single_array("SELECT container_id, position FROM ?:bm_containers", array('container_id', 'position'));

        db_query("ALTER TABLE ?:bm_containers CHANGE position position enum('TOP_PANEL','HEADER','CONTENT','FOOTER') NOT NULL");
        db_query("ALTER TABLE ?:bm_containers ADD status varchar(1) NOT NULL DEFAULT 'A'");

        foreach ($positions as $container_id => $position) {
            db_query("UPDATE ?:bm_containers SET position = ?s WHERE container_id = ?i", $position_mapping[$position], $container_id);
        }

        //We need to create empty containers with TOP_PANEL position for each location
        $locations = db_get_fields("SELECT location_id FROM ?:bm_locations");
        $container_data = array(
            'position' => 'TOP_PANEL',
            'width' => 16,
            'status' => 'A'
        );
        foreach ($locations as $location_id) {
            $container_data['location_id'] = $location_id;
            db_query("INSERT INTO ?:bm_containers ?e", $container_data);
        }

        //recalcuate alpha and omega
        $containers = db_get_fields("SELECT container_id FROM ?:bm_containers");
        db_query("UPDATE ?:bm_grids SET alpha = 0, omega = 0");
        foreach ($containers as $container_id) {
            $this->_correctAlphaOmega($container_id);
        }

        return true;
    }

    protected function _correctAlphaOmega($container_id, $parent_grid_id = 0, $max_total_width = 16)
    {
        $parent_grids = db_get_hash_single_array("SELECT grid_id, width FROM ?:bm_grids WHERE parent_id = ?i AND container_id = ?i ORDER BY grid_id ASC", array('grid_id', 'width'), $parent_grid_id, $container_id);
        if (!empty($parent_grids)) {
            $total_width = 0;
            $total_count = count($parent_grids);
            $i = 0;
            foreach ($parent_grids as $grid_id => $width) {
                $i++;

                if ($total_width == 0) {
                    db_query("UPDATE ?:bm_grids SET alpha = 1 WHERE grid_id = ?i", $grid_id);
                }

                $total_width += $width;

                if ($total_width > $max_total_width) {
                    db_query("UPDATE ?:bm_grids SET omega = 1 WHERE grid_id = ?i", $prev_grid_id);
                }

                if ($total_width == $max_total_width || $i == $total_count) {
                    db_query("UPDATE ?:bm_grids SET omega = 1 WHERE grid_id = ?i", $grid_id);
                    $total_width = 0;
                }

                $prev_grid_id = $grid_id;
                $this->_correctAlphaOmega($container_id, $grid_id, $width);
            }
        }

        return true;
    }

    protected static function _processPayments()
    {
        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));
        $params = db_get_hash_single_array("SELECT payment_id, params FROM ?:payments", array('payment_id', 'params'));
        db_query("ALTER TABLE ?:payments DROP params");
        db_query("ALTER TABLE ?:payments ADD processor_params text NOT NULL");
        foreach ($params as $payment_id => $processor_params) {
            db_query("UPDATE ?:payments SET processor_params = ?s WHERE payment_id = ?i", $processor_params, $payment_id);
        }

        return true;
    }

    protected function _processImages($skin_name)
    {
        $store_data = $this->store_data;
        $img_path_info = Registry::get('config.storage.images');
        fn_copy($store_data['path'] . '/images', $img_path_info['dir'] . '/' . $img_path_info['prefix'], true, $this->exclude_files);
        $logos_exist = db_get_array("SELECT image_id FROM ?:images_links WHERE object_type = 'logos'");
        if (!$logos_exist) {
            $root_manifest = parse_ini_file($store_data['path'] . '/skins/' . $skin_name . '/manifest.ini', true);
            $layout_id = db_get_field("SELECT layout_id FROM ?:bm_layouts WHERE is_default = '1'");
            $root_company_logo_path = $store_data['path'] . '/skins/' . $skin_name . '/customer/images/' . $root_manifest['Customer_logo']['filename'];
            General::createLogo(0, $layout_id, $root_manifest['Customer_logo']['filename'], $root_company_logo_path, 'theme', 'Customer_logo');

            $root_company_favicon_img_path = $store_data['path'] . '/skins/' . $skin_name . '/customer/images/icons/favicon.ico';
            General::createLogo(0, $layout_id, 'favicon.ico', $root_company_favicon_img_path, 'favicon');

            $root_company_mail_img_path = $store_data['path'] . '/skins/' . $skin_name . '/mail/images/' . $root_manifest['Mail_logo']['filename'];
            General::createLogo(0, 0, $root_manifest['Mail_logo']['filename'], $root_company_mail_img_path, 'mail', 'Mail_logo');

            if (isset($root_manifest['Gift_certificate_logo'])) {
                $root_company_gc_img_path = $store_data['path'] . '/skins/' . $skin_name . '/mail/images/' . $root_manifest['Gift_certificate_logo']['filename'];
                General::createLogo(0, 0, $root_manifest['Gift_certificate_logo']['filename'], $root_company_gc_img_path, 'gift_cert', 'Gift_certificate_logo');
            }

            $company_data = db_get_array("SELECT company_id, logos FROM ?:companies");
            foreach ($company_data as $company) {
                $manifest = !empty($company['logos']) ? unserialize($company['logos']) : array();

                $theme_img_path = !empty($manifest['Customer_logo']) ? $store_data['path'] . '/images/' . $manifest['Customer_logo']['filename'] : $root_company_logo_path;
                $theme_name = !empty($manifest['Customer_logo']) ? $manifest['Customer_logo']['filename'] : $root_manifest['Customer_logo']['filename'];
                General::createLogo($company['company_id'], 0, fn_basename($theme_name), $theme_img_path, 'theme', 'Customer_logo');

                $mail_img_path = !empty($manifest['Mail_logo']) ? $store_data['path'] . '/images/' . $manifest['Mail_logo']['filename'] : $root_company_mail_img_path;
                $mail_logo_name = !empty($manifest['Mail_logo']) ? $manifest['Mail_logo']['filename'] : $root_manifest['Mail_logo']['filename'];
                General::createLogo($company['company_id'], 0, fn_basename($mail_logo_name), $mail_img_path, 'mail', 'Mail_logo');

                if (isset($root_manifest['Gift_certificate_logo'])) {
                    $gc_img_path = !empty($manifest['Gift_certificate_logo']) ? $store_data['path'] . '/images/' . $manifest['Gift_certificate_logo']['filename'] : $root_company_gc_img_path;
                    $gc_name = !empty($manifest['Gift_certificate_logo']) ? $manifest['Gift_certificate_logo']['filename'] : $root_manifest['Gift_certificate_logo']['filename'];
                    General::createLogo($company['company_id'], 0, fn_basename($gc_name), $gc_img_path, 'gift_cert', 'Gift_certificate_logo');
                }
            }
            db_query("DELETE FROM ?:common_descriptions WHERE object_holder IN ('Customer_logo', 'Mail_logo', 'Admin_logo', 'Gift_certificate_logo')");
        }

        return true;
    }

    protected function _processFiles($type)
    {
        $type_path_info = Registry::get('config.storage.' . $type);
        if (is_dir($this->store_data['path'] . '/var/' . $type)) {
            fn_copy($this->store_data['path'] . '/var/' . $type, $type_path_info['dir'] . $type_path_info['prefix'], true, $this->exclude_files);
        }

        return true;
    }

    protected function _processLanguageValues()
    {
        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));

        $lang_codes = General::getLangCodes();;
        $prefix = Registry::get('config.table_prefix');

        $descr_tables = $this->_getDescriptionTables();
        foreach ($lang_codes as $lang_code) {
            foreach ($descr_tables as $descr_table) {
                db_query("UPDATE $descr_table SET lang_code = ?s WHERE lang_code = ?s", $lang_code, strtoupper($lang_code));
                if ($lang_code == 'sl') {
                    db_query("UPDATE $descr_table SET lang_code = 'sl' WHERE lang_code = 'SI'");
                }
            }
        }

        if (in_array('sl', $lang_codes)) {
            $sl_langvars = db_get_array("SELECT * FROM ?:language_values WHERE lang_code = 'sl'");
            db_query("DELETE FROM ?:language_values WHERE lang_code = 'sl'");
            db_query("UPDATE ?:language_values SET lang_code = 'sl' WHERE lang_code = 'SI'");
            db_query("REPLACE INTO ?:language_values ?m", $sl_langvars);
        }

        return true;
    }

    protected function _getDescriptionTables()
    {
        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));

        $prefix = General::formatPrefix();
        $description_tables = db_get_fields("SHOW TABLES LIKE '" . $prefix . "%_descriptions'");
        $description_tables[] = $prefix . 'product_features_values';
        $description_tables[] = $prefix . 'bm_blocks_content';
        $description_tables[] = $prefix . 'companies';
        $description_tables[] = $prefix . 'orders';
        $description_tables[] = $prefix . 'users';

        return $description_tables;
    }

    protected function _getSkinName()
    {
        return db_get_field("SELECT value FROM ?:settings_objects WHERE name = 'skin_name_customer'");
    }

    protected function _updateCompanyId($default_company_id)
    {
        $limit = 50;
        $prefix = General::formatPrefix();
        $tables = array(
            $prefix . 'orders',
            $prefix . 'products',
            $prefix . 'product_options',
            $prefix . 'pages',
            $prefix . 'users',
            $prefix . 'settings_vendor_values',
            $prefix . 'vendor_payouts',
        );

        $tables = db_get_fields(
            "SELECT TABLE_NAME "
            . "FROM INFORMATION_SCHEMA.COLUMNS "
            . "WHERE TABLE_SCHEMA = ?s AND COLUMN_NAME = ?s AND TABLE_NAME LIKE ?s AND TABLE_NAME IN (?a)",
            Registry::get('config.db_name'), 'company_id', $prefix . '%', $tables
        );

        foreach ($tables as $table_name) {
            if ($table_name !== $prefix . 'users') {
                db_query("UPDATE `?p` SET `company_id` = ?i WHERE `company_id` = ?i", $table_name, $default_company_id, 0);
            } else {
                db_query("UPDATE `?p` SET `company_id` = ?i WHERE `company_id` = ?i AND `user_type` = 'V'", $table_name, $default_company_id, 0);
            }
        }

        $count_product_orders = db_get_field("SELECT COUNT(*) FROM ?:order_details");

        if ($count_product_orders > 0) {
            for ($i = 0; $i <= floor($count_product_orders / $limit); $i++) {
                $products = db_get_array("SELECT item_id, order_id, extra FROM ?:order_details LIMIT ?i, ?i", $i * $limit, $limit);

                if (is_array($products)) {
                    foreach ($products as $product) {
                        $extra = unserialize($product['extra']);
                        $extra['company_id'] = empty($extra['company_id']) ? $default_company_id : $extra['company_id'];
                        $product['extra'] = serialize($extra);
                        db_query("UPDATE ?:order_details SET extra = ?s WHERE item_id = ?i AND order_id = ?i", $product['extra'], $product['item_id'], $product['order_id']);
                    }
                }
            }
        }

        //Hack for the root admin.
        db_query("UPDATE ?:users SET company_id = 0 WHERE user_type = 'A' AND is_root = 'Y'");
    }
}
