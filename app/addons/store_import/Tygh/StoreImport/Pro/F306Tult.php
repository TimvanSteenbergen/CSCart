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

class F306Tult
{
    protected $store_data = array();
    protected $main_sql_filename = 'F306Tult.sql';

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

        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));

        $default_company = General::getDefaultCompany();

        $supplier_settings = $this->getSupplierSettings();
        General::setSupplierSettings($supplier_settings);
        $enabledSuppliers = General::supplierSettings('enabled') ? true : false;

        $main_sql = Registry::get('config.dir.addons') . 'store_import/database/' . $this->main_sql_filename;
        if (is_file($main_sql)) {
            //Process main sql
            if (!db_import_sql_file($main_sql)) {
                return false;
            }
        }

        if ($enabledSuppliers) {
            $this->_importSuppliers();
        }

        General::processAddons($this->store_data, __CLASS__);

        General::setEmptyProgressBar();
        $this->_deleteAllCompanies();
        $default_company_id = (int) General::createDefaultCompany($default_company);
        $this->_fillSharingTable($default_company_id);
        $this->_updateCompanyId($default_company_id);
        $this->_setStorefromUrl($default_company_id, $this->store_data);

        General::setEmptyProgressBar();

        return true;
    }

    protected function _importSuppliers()
    {
        $suppliers = array();
        $shippings = array();

        db_query("CREATE TABLE `?:suppliers` (
                `supplier_id` mediumint(8) UNSIGNED NOT NULL auto_increment,
                `company_id` int(11) unsigned NOT NULL,
                `name` varchar(255),
                `address` varchar(255) NOT NULL,
                `city` varchar(64) NOT NULL,
                `state` varchar(32) NOT NULL,
                `country` char(2) NOT NULL,
                `zipcode` varchar(16) NOT NULL,
                `email` varchar(128) NOT NULL,
                `phone` varchar(32) NOT NULL,
                `fax` varchar(32) NOT NULL,
                `url` varchar(128) NOT NULL,
                `timestamp` int(11) unsigned NOT NULL DEFAULT '0',
                `status` char(1) NOT NULL default 'A',
                PRIMARY KEY  (`supplier_id`),
                KEY `company` (`company_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=UTF8");

        db_query("CREATE TABLE `?:supplier_links` (
                `supplier_id` mediumint(8) UNSIGNED NOT NULL,
                `object_id` int(11) UNSIGNED NOT NULL,
                `object_type` char(1),
                PRIMARY KEY  (`supplier_id`, `object_id`, `object_type`)
            ) ENGINE=MyISAM DEFAULT CHARSET=UTF8");

        $companies = db_get_array("SELECT * FROM ?:companies");

        if (!empty($companies)) {
            foreach ($companies as $company) {
                $suppliers[] = array(
                    'supplier_id' => $company['company_id'],
                    'company_id' => 0,
                    'name' => $company['company'],
                    'address' => $company['address'],
                    'city' => $company['city'],
                    'state' => $company['state'],
                    'country' => $company['country'],
                    'zipcode' => $company['zipcode'],
                    'email' => $company['email'],
                    'phone' => $company['phone'],
                    'fax' => $company['fax'],
                    'url' => $company['url'],
                    'timestamp' => $company['timestamp'],
                    'status' => $company['status']
                );

                $shippings[$company['company_id']] = explode(',', $company['shippings']);
            }

            db_query("INSERT INTO ?:suppliers ?m", $suppliers);

            db_query("INSERT INTO ?:supplier_links SELECT company_id as supplier_id, product_id as object_id, 'P' as object_type FROM ?:products WHERE company_id > 0");
            db_query("INSERT INTO ?:supplier_links SELECT company_id as supplier_id, shipping_id as object_id, 'S' as object_type FROM ?:shippings WHERE company_id > 0");

            foreach ($shippings as $supplier_id => $shipping_ids) {
                db_query("INSERT INTO ?:supplier_links SELECT ?i as supplier_id, shipping_id as object_id, 'S' as object_type FROM ?:shippings WHERE company_id = 0 AND shipping_id IN (?a)", $supplier_id, $shipping_ids);
            }
        }
    }

    protected function _deleteAllCompanies()
    {
        db_query("TRUNCATE TABLE ?:companies");

        return true;
    }

    protected function _updateCompanyId($default_company_id)
    {
        $prefix = General::formatPrefix();

        $tables = db_get_fields(
            "SELECT TABLE_NAME "
            . "FROM INFORMATION_SCHEMA.COLUMNS "
            . "WHERE TABLE_SCHEMA = ?s AND COLUMN_NAME = ?s AND TABLE_NAME LIKE ?s",
            Registry::get('config.db_name'), 'company_id', $prefix . '%'
        );

        foreach ($tables as $table_name) {
            if ($table_name !== $prefix . 'users') {
                db_query("UPDATE `?p` SET `company_id` = ?i", $table_name, $default_company_id);
            } else {
                db_query("UPDATE `?p` SET `company_id` = ?i WHERE `user_type` <> 'A' OR `is_root` <> 'Y'", $table_name, $default_company_id);
            }
        }

        $count_product_orders = db_get_field("SELECT COUNT(*) FROM ?:order_details");

        if ($count_product_orders > 0) {
            for ($i = 0; $i <= floor($count_product_orders/50); $i++) {
                $products = db_get_array("SELECT item_id, order_id, extra FROM ?:order_details LIMIT ?i, ?i", $i * 50, 50);

                if (is_array($products)) {
                    foreach ($products as $product) {
                        $extra = unserialize($product['extra']);
                        if (!empty($extra['company_id'])) {
                            $extra['supplier_id'] = $extra['company_id'];
                        }
                        $extra['company_id'] = $default_company_id;
                        $product['extra'] = serialize($extra);
                        db_query("UPDATE ?:order_details SET extra = ?s WHERE item_id = ?i AND order_id = ?i", $product['extra'], $product['item_id'], $product['order_id']);
                    }
                }
            }
        }

        //Hack for the root admin.
        db_query("UPDATE ?:users SET company_id = 0 WHERE user_type = 'A' AND is_root = 'Y'");

        $common_descriptions_objects = array(
            'Customer_logo',
            'Admin_logo',
            'Gift_certificate_logo',
            'Mail_logo',
            'Signin_logo'
        );

        //Suppliers create several unnesessary records during creation. We should remove them
        db_query("DELETE FROM ?:common_descriptions WHERE object_holder IN (?a) AND object_id <> 0", $common_descriptions_objects);
        db_query("UPDATE ?:common_descriptions SET object_id = ?i WHERE object_holder IN (?a)", $default_company_id,  $common_descriptions_objects);
    }

    protected function _fillSharingTable($default_company_id)
    {
        $objects = array(
            'shippings' => 'shipping_id',
            'payments' => 'payment_id',
            'languages' => 'lang_code',
            'currencies' => 'currency_code',
            'promotions' => 'promotion_id',
            'pages' => 'page_id',
            'product_features' => 'feature_id',
            'product_filters' => 'filter_id',
            'profile_fields' => 'field_id',
            'usergroups' => 'usergroup_id',
            'static_data' => 'param_id',
        );

        if (General::supplierSettings('enabled')) {
            $objects['suppliers'] = 'supplier_id';
        }

        //Process addons sharing data
        //We do not include form buider and polls because 'pages' object included in the common objects list
        $addons_objects = array(
            'banners' => array(
                'banners' => 'banner_id',
            ),
            'news_and_emails' => array(
                'mailing_lists' => 'list_id',
                'news' => 'news_id',
            ),
            'store_locator' => array(
                'store_locations' => 'store_location_id',
            ),
        );
        $addons = General::getInstalledAddons();
        foreach ($addons_objects as $addon => $data) {
            if (in_array($addon, $addons)) {
                $objects = fn_array_merge($data, $objects);
            }
        }

        foreach ($objects as $object => $field) {
            db_query("REPLACE INTO ?:ult_objects_sharing (share_company_id, share_object_id, share_object_type) SELECT '$default_company_id', $field, '$object' FROM ?:$object");
        }

        db_query("REPLACE INTO ?:ult_objects_sharing (share_company_id, share_object_id, share_object_type) SELECT '$default_company_id', option_id, 'product_options' FROM ?:product_options WHERE product_id IN (SELECT DISTINCT product_id FROM ?:ult_product_descriptions)");
        db_query("REPLACE INTO ?:ult_objects_sharing (share_company_id, share_object_id, share_object_type) SELECT '$default_company_id', option_id, 'product_options' FROM ?:product_options");

        return true;
    }

    protected function getSupplierSettings()
    {
        $supplier_settings['enabled'] = db_get_field("SELECT value FROM ?:settings_objects WHERE name = 'enable_suppliers'");
        $supplier_settings['display_supplier'] = db_get_field("SELECT value FROM ?:settings_objects WHERE name = 'display_supplier'");
        $supplier_settings['display_shipping_methods_separately'] = db_get_field("SELECT value FROM ?:settings_objects WHERE name = 'display_shipping_methods_separately'");

        return $supplier_settings;
    }

    protected function _setStorefromUrl($company_id, $store_data)
    {
        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));
        db_query("UPDATE ?:companies SET storefront = ?s, secure_storefront = ?s WHERE company_id = ?i", $store_data['new_storefront_url'], $store_data['new_secure_storefront_url'], $company_id);

        return true;
    }
}
