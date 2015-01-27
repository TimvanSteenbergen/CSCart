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
namespace Tygh\StoreImport;

use Tygh\Exceptions\StoreImportException;
use Tygh\Registry;
use Tygh\Settings;
use Tygh\Addons\SchemesManager as AddonSchemesManager;
use Tygh\Less;
use Tygh\BlockManager\ProductTabs;
use Tygh\Helpdesk;
use Tygh\BlockManager\Layout;
use Tygh\Themes\Styles;
use Tygh\Addons\SchemesManager;

class General
{
    const CONNECTION_NAME = 'exim_stores';
    const TABLE_PREFIX = 'store_import';
    const VERSION_FOR_LICENSE_CHECK = 4;

    private static $unavailable_lang_vars = array();
    private static $supplier_settings = array();
    public static $default_language = 'en';

    public static function initiateImportedDB($store_data)
    {
        $db_conn = db_initiate(
            $store_data['db_host'],
            $store_data['db_user'],
            $store_data['db_password'],
            $store_data['db_name'],
            array(
                'dbc_name' => General::CONNECTION_NAME,
                'table_prefix' => $store_data['table_prefix']
            )
        );

        return $db_conn;
    }

    /**
     * Connects to database of imported cart
     *
     * @static
     * @return bool True on success, false otherwise
     */
    public static function connectToImportedDB($store_data)
    {
        if (db_connect_to(array('dbc_name' => General::CONNECTION_NAME), $store_data['db_name'])) {
            return true;
        } else {
            //We should break the process because another way imported DB can be broken
            throw new StoreImportException(self::getUnavailableLangVar('cant_connect_to_imported'));
        }
    }

    /**
     * Connects to original database
     *
     * @static
     * @return bool True on success, false otherwise
     */
    public static function connectToOriginalDB($params = array())
    {
        if (db_connect_to($params, Registry::get('config.db_name'))) {
            return true;
        } else {
            throw new StoreImportException(self::getUnavailableLangVar('cant_connect_to_original'));
        }
    }

    /**
     * Checks database connection
     *
     * @static
     * @param  array $store_data Array of store data
     * @return bool  True on success, false otherwise
     */
    public static function testDatabaseConnection($store_data)
    {
        $status = false;

        if (!empty($store_data['db_host']) && !empty($store_data['db_user']) && !empty($store_data['db_name'])) {
            $new_db = @General::initiateImportedDB($store_data);

            if ($new_db != null) {
                $status = true;
            }
        }

        General::connectToOriginalDB();

        return $status;
    }

    /**
     * Checks that table prefix correct
     *
     * @static
     * @param  array $store_data Array of store data
     * @return bool  True on success, false otherwise
     */
    public static function testTablePrefix($store_data)
    {
        $status = false;
        General::connectToImportedDB($store_data);

        $tables = db_get_array("SHOW TABLES LIKE '" . $store_data['table_prefix'] . "sessions';");

        if (!empty($tables)) {
            $status = true;
        }

        General::connectToOriginalDB();

        return $status;
    }

    /**
     * Returns config of cart placed by $cart_path
     *
     * @static
     * @param  string     $cart_path
     * @return array|bool Array of store data on success, false otherwise.
     */
    public static function getConfig($cart_path)
    {
        $cart_path = rtrim($cart_path, '/');
        if (file_exists($cart_path . '/config.local.php') && file_exists($cart_path . '/config.php')) {

            // Read settings from config.local.php
            $config_local_php = file_get_contents($cart_path . '/config.local.php');
            $config_local_php =  self::_removePhpComments($config_local_php);

            $config['db_host'] = self::_getVariable('db_host', $config_local_php);
            $config['db_name'] = self::_getVariable('db_name', $config_local_php);
            $config['db_user'] = self::_getVariable('db_user', $config_local_php);
            $config['db_password'] = self::_getVariable('db_password', $config_local_php);
            $config['table_prefix'] = self::_getConstant('TABLE_PREFIX', $config_local_php);

            $config['storefront'] = self::_getVariable('http_host', $config_local_php) . self::_getVariable('http_path', $config_local_php);
            $config['secure_storefront'] = self::_getVariable('https_host', $config_local_php) . self::_getVariable('https_path', $config_local_php);

            $config['crypt_key'] = self::_getVariable('crypt_key', $config_local_php);
            $config['admin_index'] = $config['storefront'] . '/' . self::_getVariable('admin_index', $config_local_php);

            //Read settings from config.php
            $config_php = file_get_contents($cart_path . '/config.php');
            $config_php =  self::_removePhpComments($config_php);
            $config['product_edition'] = self::_getConstant('PRODUCT_EDITION', $config_php);
            if (empty($config['product_edition'])) { // workaround for all versions, where edition was stored in PRODUCT_TYPE const
                $config['product_edition'] = self::_getConstant('PRODUCT_TYPE', $config_php);
            }
            $config['product_version'] = self::_getConstant('PRODUCT_VERSION', $config_php);
            $config['product_name'] = self::_getConstant('PRODUCT_NAME', $config_php);

            return $config;
        } else {
            return false;
        }
    }

    /**
     * Removes PHP comments
     *
     * @param  string $code PHP code
     * @return string PHP code without comments
     */
    private static function _removePhpComments($code)
    {
        return preg_replace("%//.*?\n%is", '', preg_replace("%/\*(.*?)\*/\s+%is", '', $code));
    }

    /**
     * Returns value of some variable from config
     *
     * @param  string $var_name Variable name
     * @param  string $config   config contents
     * @return string Variable value
     */
    private static function _getVariable($var_name, $config)
    {
        preg_match("%config\s*?\[\s*?['\"]" . $var_name . "['\"]\s*?\]\s*?=\s*?['\"](.*?)['\"]\s*?;%is", $config, $value);

        return !empty($value[1]) ? $value[1] : "";
    }

    /**
     * Returns value of some defined constant from config
     *
     * @param  string $var_name Variable name
     * @param  string $config   config contents
     * @return string Variable value
     */
    private static function _getConstant($var_name, $config)
    {
        preg_match("%define\s*?\(\s*?['\"]" . $var_name . "['\"]\s*?,\s*?['\"](.*?)['\"]\s*?\)\s*?;%is", $config, $value);

        return !empty($value[1]) ? $value[1] : "";
    }

    private static function _getImportClassName($str, $edition)
    {
        return 'Tygh\\StoreImport\\' . str_replace('_', 'T', $str);
    }

    public static function getImportSchema($store_data)
    {
        $import_schema = fn_get_schema('store_import', 'store_import');
        $product_version = self::_getVersionName($store_data['product_version']);
        $product_edition = strtolower(fn_get_edition_acronym($store_data['product_edition']));
        $import_schema = $import_schema[$product_edition];

        if (!empty($import_schema)) {
            foreach ($import_schema as $key => $value) {
                if (stripos($value, $product_version) === 5) {
                    break;
                } else {
                    unset($import_schema[$key]);
                }
            }
        }

        return array_values($import_schema);
    }

    public static function getImportClassesCascade($store_data)
    {
        $import_schema = self::getImportSchema($store_data);

        if (!empty($import_schema)) {
            $return = array();
            $edition = fn_get_edition_acronym($store_data['product_edition']);
            foreach ($import_schema as $value) {
                $return[] = self::_getImportClassName($value, $edition);
            }

            return $return;
        }

        return null;
    }

    private static function _getVersionName($product_version)
    {
        return str_replace('.', '', $product_version);
    }

    public static function getCompanies($store_data)
    {
        General::connectToImportedDB($store_data);

        $companies = db_get_array("SELECT * FROM ?:companies");

        return $companies;
    }

    public static function cloneImportedDB($store_data)
    {
        fn_set_progress('title', __('store_import.cloning_database'));
        fn_define('DB_MAX_ROW_SIZE', 10000);
        fn_define('DB_ROWS_PER_PASS', 40);

        General::connectToImportedDB($store_data);
        $tables = General::getTables($store_data['db_name'], $store_data['table_prefix']);
        $excluded_tables = array(
            $store_data['table_prefix'] . 'logs',
            $store_data['table_prefix'] . 'sessions',
            $store_data['table_prefix'] . 'stored_sessions',
            $store_data['table_prefix'] . 'user_session_products',
            $store_data['table_prefix'] . 'stat_browsers',
            $store_data['table_prefix'] . 'stat_ips',
            $store_data['table_prefix'] . 'stat_languages',
            $store_data['table_prefix'] . 'stat_product_search',
            $store_data['table_prefix'] . 'stat_requests',
            $store_data['table_prefix'] . 'stat_search_engines',
            $store_data['table_prefix'] . 'stat_search_phrases',
            $store_data['table_prefix'] . 'stat_sessions',
            $store_data['table_prefix'] . 'stat_banners_log',
        );
        $tables = array_diff($tables, $excluded_tables);
        $change_table_prefixes = array(
            'from' => $store_data['table_prefix'],
            'to' => self::formatPrefix(),
        );
        db_export_to_file(Registry::get('config.dir.database') . 'export.sql', $tables, true, true, false, true, true, $change_table_prefixes);

        General::connectToOriginalDB();
        self::_createExcludedTables($change_table_prefixes['to']);

        return db_import_sql_file(Registry::get('config.dir.database') . 'export.sql', 16384, true, true, false, false, false, true);
    }

    private static function _createExcludedTables($prefix)
    {
        db_query("
            CREATE TABLE `" . $prefix . "logs` (
                `log_id` mediumint(8) unsigned NOT NULL auto_increment,
                `user_id` mediumint(8) unsigned NOT NULL default '0',
                `timestamp` int(11) unsigned NOT NULL default '0',
                `type` varchar(16) NOT NULL default '',
                `event_type` char(1) NOT NULL default 'N',
                `action` varchar(16) NOT NULL default '',
                `object` char(1) NOT NULL default '',
                `content` text NOT NULL,
                `backtrace` text NOT NULL,
                `company_id` int(11) unsigned NOT NULL default '0',
                PRIMARY KEY  (`log_id`),
                KEY `object` (`object`),
                KEY (`type`, `action`)
            ) ENGINE=MyISAM DEFAULT CHARSET UTF8;
        ");

        db_query("
            CREATE TABLE `" . $prefix . "sessions` (
                `session_id` varchar(255) NOT NULL default '',
                `expiry` int(11) unsigned NOT NULL default '0',
                `data` mediumtext,
                PRIMARY KEY  (`session_id`),
                KEY `src` (`session_id`,`expiry`),
                KEY (`expiry`)
            ) Engine=MyISAM DEFAULT CHARSET UTF8;
        ");

        db_query("
            CREATE TABLE `" . $prefix . "stored_sessions` (
               `session_id` varchar(34) NOT NULL,
               `expiry` int(11) unsigned NOT NULL,
               `data` text NOT NULL,
               PRIMARY KEY  (`session_id`),
               KEY (`expiry`)
            ) Engine=MyISAM DEFAULT CHARSET UTF8;
        ");

        db_query("
            CREATE TABLE `" . $prefix . "user_session_products` (
                `user_id` int(11) unsigned NOT NULL default '0',
                `timestamp` int(11) unsigned NOT NULL default '0',
                `type` char(1) NOT NULL default 'C',
                `user_type` char(1) NOT NULL default 'R',
                `item_id` int(11) unsigned NOT NULL default '0',
                `item_type` char(1) NOT NULL default 'P',
                `product_id` mediumint(8) unsigned NOT NULL default '0',
                `amount` mediumint(8) unsigned NOT NULL default '1',
                `price` decimal(12,2) NOT NULL default '0.00',
                `extra` text NOT NULL,
                `session_id` varchar(34) NOT NULL default '',
                `ip_address` varchar(15) NOT NULL default '',
            PRIMARY KEY  (`user_id`,`type`,`item_id`,`user_type`),
            KEY `timestamp` (`timestamp`,`user_type`),
            KEY `session_id` (`session_id`)
            ) Engine=MyISAM DEFAULT CHARSET UTF8;
        ");

        if (fn_allowed_for('ULTIMATE')) {
            db_query("ALTER TABLE " . $prefix . "user_session_products ADD `company_id` int(11) unsigned NOT NULL");
            db_query("ALTER TABLE " . $prefix . "user_session_products DROP PRIMARY KEY");
            db_query("ALTER TABLE " . $prefix . "user_session_products ADD PRIMARY KEY(`user_id`, `type`, `user_type`, `item_id`, `company_id`)");
        }

        return true;
    }

    public static function formatPrefix()
    {
        return General::TABLE_PREFIX . '_';
    }

    public static function getTables($db_name, $db_prefix)
    {
        $tables = db_get_fields(
            'SELECT TABLE_NAME AS stmt
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = "' . $db_name . '"
            AND TABLE_NAME LIKE "' . $db_prefix . '%"
        ');

        return $tables;
    }

    private static function _replacePrefix($str, $table_prefix)
    {
        $new_prefix = self::formatPrefix();

        return str_replace($table_prefix, $new_prefix, $str);
    }

    public static function getFileName($store_data, $class, $prefix = '', $postfix = '')
    {
        $edition = fn_get_edition_acronym($store_data['product_edition']);
        $class_name = substr(strrchr($class, "\\"), 1);

        return ($prefix ? $prefix . '_' : '') . $edition . '_' . strtoupper($class_name) . ($postfix ? '_' . $postfix : '');
    }

    public static function getLangCodes()
    {
        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));

        return db_get_fields("SELECT lang_code FROM ?:languages");
    }

    public static function updateAltLanguages($table, $keys, $show_process = false)
    {
        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));
        $langs = self::getLangCodes();

        if (!is_array($keys)) {
            $keys = array($keys);
        }

        $i = 0;
        $step = 50;
        while ($items = db_get_array("SELECT * FROM ?:$table WHERE lang_code = ?s LIMIT $i, $step", self::$default_language)) {
            $i += $step;
            foreach ($items as $v) {
                foreach ($langs as $lang) {
                    $condition = array();
                    foreach ($keys as $key) {
                        $lang_var = $v[$key];
                        $condition[] = db_quote("$key = ?s", $lang_var);
                    }
                    $condition = implode(' AND ', $condition);
                    $exists = db_get_field("SELECT COUNT(*) FROM ?:$table WHERE $condition AND lang_code = ?s", $lang);
                    if (empty($exists)) {
                        $v['lang_code'] = $lang;
                        db_query("REPLACE INTO ?:$table ?e", $v);
                        if ($show_process) {
                            fn_echo(' .');
                        }
                    }
                }
            }
        }

        return true;
    }

    public static function uninstallAddons($addons = array())
    {
        if (!empty($addons)) {
            if (!is_array($addons)) {
                $addons = (array) $addons;
            }
            General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));

            db_query("DELETE FROM ?:addons WHERE addon IN (?a)", $addons);
            db_query("DELETE FROM ?:addon_descriptions WHERE addon IN (?a)", $addons);
            self::_removeAddonsSettings($addons);
        }

        return true;
    }

    private static function _removeAddonsSettings($addons)
    {
        $table_fields = fn_get_table_fields('addons');
        if (!isset($table_fields['options'])) {
            $addons_objects = db_get_fields("SELECT object_id FROM ?:settings_objects WHERE section_id IN (SELECT section_id FROM ?:settings_sections WHERE name IN (?a))", $addons);
            if (!empty($addons_objects)) {
                db_query("DELETE FROM ?:settings_descriptions WHERE object_id IN (?a) AND object_type = 'O'", $addons_objects);
                db_query("DELETE FROM ?:settings_variants WHERE object_id IN (?a)", $addons_objects);
                if (db_get_array("SHOW TABLES LIKE '?:settings_vendor_values'")) {
                    db_query("DELETE FROM ?:settings_vendor_values WHERE object_id IN (?a)", $addons_objects);
                }
            }
        }

        return true;
    }

    public static function getInstalledAddons()
    {
        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));

        return db_get_fields("SELECT addon FROM ?:addons");
    }

    public static function restoreSettings()
    {
        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));
        $was_settings_backed_up = db_get_field("SHOW TABLES LIKE '?:settings_objects_upg'");

        if (!empty($was_settings_backed_up)) {
            $addons = self::getInstalledAddons();
            $languages = db_get_fields("SELECT lang_code FROM ?:languages");

            foreach ($addons as $addon) {
                $addon_scheme = AddonSchemesManager::getScheme($addon);
                if (!empty($addon_scheme)) {
                    // Add optional language variables
                    $language_variables = $addon_scheme->getLanguageValues();
                    if (!empty($language_variables)) {
                        db_query('REPLACE INTO ?:language_values ?m', $language_variables);
                    }

                    fn_update_addon_settings($addon_scheme, false);

                    foreach ($languages as $lang_code) {
                        $description = $addon_scheme->getDescription($lang_code);
                        $addon_name = $addon_scheme->getName($lang_code);
                        db_query("UPDATE ?:addon_descriptions SET description = ?s, name = ?s WHERE addon = ?s AND lang_code = ?s", $description, $addon_name, $addon, $lang_code);
                    }
                }
            }

            $settings = db_get_array('SELECT * FROM ?:settings_objects_upg');
            foreach ($settings as $setting) {
                Settings::instance()->updateValue($setting['name'], $setting['value'], $setting['section_name'], false, null, false);
            }
            db_query('DROP TABLE ?:settings_objects_upg');

            $was_company_settings_backed_up = db_get_field("SHOW TABLES LIKE '?:settings_vendor_values_upg'");
            if (!empty($was_company_settings_backed_up)) {
                $company_settings = db_get_array('SELECT * FROM ?:settings_vendor_values_upg');
                foreach ($company_settings as $setting) {
                    Settings::instance($setting['company_id'])->updateValue($setting['name'], $setting['value'], $setting['section_name'], false, $setting['company_id'], false);
                }
                db_query('DROP TABLE ?:settings_vendor_values_upg');
            }
        }

        return true;
    }

    public static function processAddons($store_data, $class_name, $addons = array())
    {
        $addons = empty($addons) ? General::getInstalledAddons() : $addons;
        self::setEmptyProgressBar(self::getUnavailableLangVar('processing_addons'));
        if (empty($addons)) {
            return true;
        }

        foreach ($addons as $addon) {
            $sql_filename = Registry::get('config.dir.addons') . 'store_import/database/addons/' . General::getFileName($store_data, $class_name, $addon) . '.sql';
            $php_filename = Registry::get('config.dir.addons') . 'store_import/scripts/addons/' . General::getFileName($store_data, $class_name, $addon) . '.php';

            if (is_file($sql_filename)) {
                if (!db_import_sql_file($sql_filename, 16384, false, true, false, false, false, false)) {
                    return false;
                }
            }
            if (is_file($php_filename)) {
                include($php_filename);
            }
        }

        return true;
    }

    public static function processBlocks()
    {
        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));
        $blocks = db_get_array('SELECT block_id, properties FROM ?:bm_blocks WHERE type = ?s', 'product_filters');
        foreach ($blocks as $block) {
            if (!empty($block['properties'])) {
                $prop = unserialize($block['properties']);
                if ($prop['template'] == 'blocks/product_filters.tpl') {
                    $prop['template'] = 'blocks/product_filters/original.tpl';
                } elseif ($prop['template'] == 'blocks/product_filters_extended.tpl') {
                    $prop['template'] = 'blocks/product_filters/custom.tpl';
                }
                db_query("UPDATE ?:bm_blocks SET properties = ?s WHERE block_id = ?i", serialize($prop), $block['block_id']);
            }
        }

        return true;
    }

    public static function getDefaultCompany()
    {
        $company_data = db_get_hash_single_array("SELECT name, value FROM ?:settings_objects WHERE section_id = 5", array('name', 'value'));
        $new_company_data = array(
            'status' => 'A',
            'company' => $company_data['company_name'],
            'lang_code' => Registry::get('settings.Appearance.backend_default_language'),
            'address' => $company_data['company_address'],
            'city' => $company_data['company_city'],
            'state' => $company_data['company_state'],
            'country' => $company_data['company_country'],
            'zipcode' => $company_data['company_zipcode'],
            'email' => $company_data['company_site_administrator'],
            'phone' => $company_data['company_phone'],
            'fax' => $company_data['company_fax'],
            'url' => $company_data['company_website'],
            'timestamp' => time(),
        );

        return $new_company_data;
    }

    public static function createDefaultCompany($default_company)
    {
        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));

        return db_query("INSERT INTO ?:companies ?e", $default_company);
    }

    public static function getNotification($store_data)
    {
        $version_name = self::_getVersionName($store_data['product_version']);
        $langvar_name = 'store_import.' . $version_name . '_' . strtolower($store_data['product_edition']);

        return __($langvar_name, array(
            "[stores_section_link]" => fn_url('companies.manage'),
            "[manage_languages_link]" => fn_url('languages.manage'),
        ));
    }

    public static function replaceOriginalDB($store_data, $enable_non_imported_objects = false)
    {
        $exluded_tables = array(
            'stat_browsers',
            'stat_ips',
            'stat_languages',
            'stat_product_search',
            'stat_requests',
            'stat_search_engines',
            'stat_search_phrases',
            'stat_sessions',
            'stat_banners_log',
        );
        $obsolete_tables = fn_get_schema('store_import', 'obsolete_tables');
        if ($enable_non_imported_objects) {
            $exluded_tables = array_merge($exluded_tables, fn_get_schema('store_import', 'table_replacement'));
        }

        General::connectToOriginalDB();
        $orig_table_prefix = Registry::get('config.table_prefix');
        $db_name = Registry::get('config.db_name');
        $orig_tables = self::getTables($db_name, $orig_table_prefix);

        $new_table_prefix = General::formatPrefix();
        $imported_tables = self::getTables($db_name, $new_table_prefix);
        //Filter imported table. In case new database has not prefix the $orig_tables array will contain all tables include imported.
        $orig_tables = array_diff($orig_tables, $imported_tables);

        fn_set_progress('step_scale', count($orig_tables));
        foreach ($orig_tables as $table_name) {
            //Cant use lang vars because table can be droppep when we try to get lang var.
            fn_set_progress('echo', 'Dropping original tables', true);
            if (!in_array(str_replace($orig_table_prefix, '', $table_name), $exluded_tables)) {
                db_query("DROP TABLE $table_name");
            }
        }

        fn_set_progress('step_scale', count($imported_tables));
        foreach ($imported_tables as $table_name) {
            if (in_array(str_replace($new_table_prefix, '', $table_name), $obsolete_tables)) {
                db_query("DROP TABLE $table_name");
                continue;
            }
            fn_set_progress('echo', 'Renaming original tables', true);
            $new_table_name = str_replace($new_table_prefix, $orig_table_prefix, $table_name);
            if (!in_array(str_replace($new_table_prefix, '', $table_name), $exluded_tables)) {
                db_query("RENAME TABLE $table_name TO $new_table_name");
            }
        }

        return true;
    }

    public static function processBMBlocksTemplates()
    {
        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));
        $blocks = db_get_array("SELECT * FROM ?:bm_blocks WHERE type <> 'main'");

        foreach ($blocks as $key => $block) {
            $properties = unserialize($block['properties']);
            $properties['template'] = str_replace('common_templates/', 'common/', $properties['template']);
            db_query("UPDATE ?:bm_blocks SET properties = ?s WHERE block_id = ?i", serialize($properties), $block['block_id']);
        }

        return true;
    }

    public static function processBMProductFiltersBlockContent()
    {
        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));
        $block_contents = db_get_array("SELECT * FROM ?:bm_blocks_content WHERE block_id IN (SELECT block_id FROM ?:bm_blocks WHERE type = 'product_filters')");

        foreach ($block_contents as $key => $block_content) {
            $content = unserialize($block_content['content']);
            $content['items']['filling'] = 'manually';
            $block_content['content'] = serialize($content);
            db_query("REPLACE INTO ?:bm_blocks_content ?e", $block_content);
        }

        return true;
    }

    public static function supplierSettings($param)
    {
        return (!empty($param) && !empty(self::$supplier_settings[$param])) ? self::$supplier_settings[$param] : false;
    }

    public static function setSupplierSettings($params, $ignor = false)
    {
        if (empty(self::$supplier_settings) || $ignor) {
            self::$supplier_settings = $params;
            self::$supplier_settings['enabled'] = self::$supplier_settings['enabled'] == 'Y' ? true : false;
        }

        return true;
    }

    public static function createLogo($company_id = 0, $layout_id = 0, $filename = '', $img_path = '', $type = 'theme', $common_descr_object = 'Customer_logo')
    {
        $company_id = (int) $company_id;
        $layout_id = (int) $layout_id;
        $logo_id = db_query("INSERT INTO ?:logos ?e", array('layout_id' => $layout_id, 'company_id' => $company_id, 'type' => $type));
        $image_data = array(
            'name' => $filename,
            'path' => $img_path,
            'params' => array(
                'keep_origins' => true,
            ),
        );

        if (!file_exists($img_path) || filesize($img_path) === 0) {
            return false;
        }

        $img_id = fn_update_image($image_data, 0, 'logos');
        $image_link_data = array(
            'object_id' => $logo_id,
            'object_type' => 'logos',
            'image_id' => $img_id,
        );
        foreach (db_get_fields("SELECT lang_code FROM ?:languages") as $lang_code) {
            $descr = db_get_field("SELECT description FROM ?:common_descriptions WHERE object_id= ?i AND lang_code = ?s AND object_holder = ?s", $company_id, $lang_code, $common_descr_object);
            if (!empty($descr)) {
                db_query("REPLACE INTO ?:common_descriptions (object_id, description, lang_code, object_holder) VALUES (?i, ?s, ?s, 'images')", $img_id, $descr, $lang_code);
            }
        }

        return db_query("INSERT INTO ?:images_links ?e", $image_link_data);
    }

    public static function convertPresets403To411()
    {
        $themes_path = fn_get_theme_path('[themes]', 'C');
        $themes = fn_get_dir_contents($themes_path);

        foreach ($themes as $theme) {
            if (is_dir($themes_path . '/' . $theme . '/presets')) {
                rename($themes_path . '/' . $theme . '/presets', $themes_path . '/' . $theme . '/styles');

                $json = json_decode(fn_get_contents($themes_path . '/' . $theme . '/styles/manifest.json'), true);
                if (!empty($json)) {
                    $json['default_style'] = $json['default_preset'];
                    unset($json['default_preset']);

                    fn_put_contents($themes_path . '/' . $theme . '/styles/manifest.json', json_encode($json));
                }
            }
        }
    }

    public static function convertPresets401To402()
    {
        $theme_name = Registry::get('config.base_theme');
        $schema_path = fn_get_theme_path('[themes]/' . $theme_name . '/styles/schema.json', 'C');
        $schema = file_get_contents($schema_path);
        if (!empty($schema)) {
            $schema = json_decode($schema, true);
        }

        db_query('ALTER TABLE ?:bm_layouts CHANGE `preset_id` `preset_id` varchar(64) NOT NULL default ""');

        $presets = db_get_array('SELECT * FROM ?:theme_presets');

        foreach ($presets as $preset) {
            $preset['name'] = self::_formPresetName($preset['name']);
            //We should rename default preset to satori in order to prevent default preset creation.
            if ($preset['name'] == 'default') {
                $preset['name'] = 'satori';
            }

            db_query('UPDATE ?:bm_layouts SET preset_id = ?s WHERE preset_id = ?i', $preset['name'], $preset['preset_id']);

            $preset_path = fn_get_theme_path('[themes]/' . $theme_name . '/styles/data/', 'C');
            if (file_exists($preset_path . $preset['name'] . '.less')) {
                continue;
            }

            $preset_data = unserialize($preset['data']);

            $less = array();

            foreach ($preset_data as $section => $fields) {
                foreach ($fields as $field_id => $value) {
                    switch ($section) {
                    case 'general':
                        $less[$field_id] = empty($value) ? $schema[$section]['fields'][$field_id]['off'] : $schema[$section]['fields'][$field_id]['on'];
                        break;

                    case 'colors':
                        $less[$field_id] = $value;
                        break;

                    case 'fonts':
                        $less[$field_id] = $value['family'];

                        if (!empty($value['size'])) {
                            $field_name = $schema[$section]['fields'][$field_id]['properties']['size']['match'];
                            $field_value = $value['size'] . $schema[$section]['fields'][$field_id]['properties']['size']['unit'];

                            $less[$field_name] = $field_value;
                        }

                        if (!empty($value['style'])) {
                            foreach ($value['style'] as $style_type => $style_value) {
                                $field_name = $schema[$section]['fields'][$field_id]['properties']['style'][$style_type]['match'];
                                $field_value = $schema[$section]['fields'][$field_id]['properties']['style'][$style_type]['property'];

                                $less[$field_name] = $field_value;
                            }
                        }

                        break;

                    case 'backgrounds':
                        $value['transparent'] = isset($value['transparent']) ? $value['transparent'] : false;
                        $value['full_width'] = isset($value['full_width']) ? $value['full_width'] : false;

                        foreach ($value as $bg_name => $bg_value) {
                        switch ($bg_name) {
                            case 'color':
                                $field_name = $schema[$section]['fields'][$field_id]['properties']['color']['match'];
                                $less[$field_name] = $bg_value;
                                break;

                            case 'gradient':
                                $field_name = $schema[$section]['fields'][$field_id]['gradient']['match'];
                                $less[$field_name] = $bg_value;
                                break;

                            case 'image_data':
                                $less[$schema[$section]['fields'][$field_id]['properties']['pattern']] = !empty($bg_value) ? 'url("' . $bg_value . '")' : 'transparent';
                                break;

                            case 'repeat':
                                $field_name = $schema[$section]['fields'][$field_id]['properties']['repeat'];
                                if (!empty($field_name)) {
                                    $less[$field_name] = $bg_value;
                                }
                                break;

                            case 'attachment':
                                $field_name = $schema[$section]['fields'][$field_id]['properties']['attachment'];
                                if (!empty($field_name)) {
                                    $less[$field_name] = $bg_value;
                                }
                                break;

                            case 'full_width':
                                if (!isset($schema[$section]['fields'][$field_id]['copies'])) {
                                    break;
                                }
                                foreach ($schema[$section]['fields'][$field_id]['copies']['full_width'] as $copies) {
                                    if (!empty($value['full_width'])) {
                                        if (!empty($copies['inverse'])) {
                                            $less[$copies['match']] = $copies['default'];
                                        } elseif (isset($less[$copies['source']])) {
                                            $less[$copies['match']] = $less[$copies['source']];
                                        }
                                    } else {
                                        if (empty($copies['inverse'])) {
                                            $less[$copies['match']] = $copies['default'];
                                        }
                                    }
                                }
                                break;

                            case 'transparent':
                                if (!isset($schema[$section]['fields'][$field_id]['copies'])) {
                                    break;
                                }
                                foreach ($schema[$section]['fields'][$field_id]['copies']['transparent'] as $copies) {
                                    if (!empty($value['transparent'])) {
                                        if (!empty($copies['inverse'])) {
                                            $less[$copies['match']] = $copies['default'];
                                        } elseif (isset($less[$copies['source']])) {
                                            $less[$copies['match']] = $less[$copies['source']];
                                        }
                                    } else {
                                        if (empty($copies['inverse'])) {
                                            $less[$copies['match']] = $copies['default'];
                                        }
                                    }
                                }
                                break;

                            case 'image_name': break;

                            default:
                                fn_print_r('Unprocessed background property: ' . $bg_name);
                            }
                        }
                        break;

                    default:
                        fn_print_r('Error: Section ' . $section . ' was not processed');
                    }
                }
            }

            $less = Less::arrayToLessVars($less);

            file_put_contents(fn_get_theme_path('[themes]/' . $theme_name . '/styles/data/' . $preset['name'] . '.less', 'C'), $less);
        }

        db_query('DROP TABLE IF EXISTS ?:theme_presets');

        return true;
    }

    private static function _formPresetName($name)
    {
        $name = preg_replace('/\(.*?\)/', '', $name);
        $name = trim($name);
        $name = strtolower($name);
        $name = str_replace(' ', '_', $name);

        return $name;
    }

    public static function testStoreConfiguration($store_data)
    {
        $config_local_php = file_get_contents(Registry::get('config.dir.root') . '/config.local.php');

        if ($config_local_php) {
            $cache_backend = self::_getVariable('cache_backend', $config_local_php);
            if ($cache_backend != 'file') {
                return false;
            }
        }

        return true;
    }

    public static function checkEditionMapping($store_data)
    {
        if (in_array($store_data['product_version'], array('4.0.1', '4.0.2', '4.0.3', '4.1.1', '4.1.2', '4.1.3', '4.1.4', '4.1.5', '4.2.1', '4.2.2'))) {
            return false;
        }
        $mapping = array(
            '4' => array(
                'ult_ult',
                'mve_mve',
            ),
            '3' => array(
                'pro_ult',
                'ult_ult',
                'mve_mve',
            ),
            '2' => array(
                'pro_pro',
                'mve_mve',
                'pro_ult'
            ),
        );

        if (!empty($store_data)) {
            $orig_edition = fn_get_edition_acronym($store_data['product_edition']);
            $orig_version = substr($store_data['product_version'], 0, 1);
            $edition_str = $orig_edition . '_' . fn_get_edition_acronym(PRODUCT_EDITION);

            if (in_array(strtolower($edition_str), $mapping[$orig_version])) {
                return true;
            }
        }

        return false;
    }

    public static function addStatusColors()
    {
        $types = db_get_fields("SELECT type FROM ?:status_data GROUP BY type");
        $statuses = db_get_fields("SELECT status FROM ?:status_data GROUP BY status");
        $default_values = array(
            'B_O' => '#28abf6',
            'C_O' => '#97cf4d',
            'D_O' => '#ff5215',
            'F_O' => '#ff5215',
            'I_O' => '#c2c2c2',
            'O_O' => '#ff9522',
            'P_O' => '#97cf4d',
            'A_G' => '#97cf4d',
            'C_G' => '#c2c2c2',
            'P_G' => '#ff9522',
            'U_G' => '#28abf6',
        );

        foreach ($types as $type) {
            foreach ($statuses as $status) {
                $status_data = array(
                    'status' => $status,
                    'type' => $type,
                    'param' => 'color',
                    'value' => (isset($default_values[$status . '_' . $type]) ? $default_values[$status . '_' . $type] : '#23cfdb')
                );
                db_replace_into('status_data', $status_data);
            }
        }
    }

    public static function updateStatusColors()
    {
        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));
        $statuses = db_get_array("SELECT * FROM ?:status_data WHERE param = 'color'");
        foreach ($statuses as $status_data) {
            if (strpos($status_data['value'], '#') !== 0) {
                $status_data['value'] = '#' . ($status_data['value'] ? $status_data['value'] : 'ffffff');
                db_replace_into('status_data', $status_data);
            }
        }

    }

    public static function convertPrivileges()
    {
        db_query("ALTER TABLE ?:privileges ADD COLUMN `section_id` varchar(32) NOT NULL default ''");
        db_query("ALTER TABLE ?:privileges ADD KEY `section_id` (`section_id`)");

        $privilege_sections = db_query(
            "UPDATE ?:privileges, ?:privilege_descriptions"
            . " SET ?:privileges.section_id = ?:privilege_descriptions.section_id"
            . " WHERE ?:privileges.privilege = ?:privilege_descriptions.privilege"
        );

        $section_ids = array (
            1 => 'addons',
            2 => 'administration',
            3 => 'addons', // affiliate section is moved to addons
            4 => 'cart',
            5 => 'catalog',
            6 => 'cms',
            7 => 'design',
            8 => 'orders',
            9 => 'users',
            10 => 'vendors',
        );

        //Affiliate hack
        db_query(
            "REPLACE INTO ?:privilege_section_descriptions( section_id, description, lang_code )"
            . " SELECT '3', description, lang_code"
            . " FROM ?:privilege_section_descriptions"
            . " WHERE section_id = 1"
        );

        foreach ($section_ids as $prev_id => $new_id) {
            db_query("UPDATE ?:privileges SET section_id = ?s WHERE section_id = ?i", $new_id, $prev_id);

            // Update privilege sections titles
            db_query(
                "REPLACE INTO ?:language_values (`lang_code`, `name`, `value`)"
                . " SELECT lang_code, 'privilege_sections.$new_id', description"
                . " FROM ?:privilege_section_descriptions"
                . " WHERE ?:privilege_section_descriptions.section_id = ?i", $prev_id
            );

        }

        // Update privilege titles
        db_query(
            "REPLACE INTO ?:language_values (`lang_code`, `name`, `value`)"
            . " SELECT lang_code, CONCAT('privileges.', ?:privilege_descriptions.privilege) AS name, description"
            . " FROM ?:privilege_descriptions"
        );

        db_query("INSERT INTO `?:privileges` (privilege, is_default, section_id) VALUES"
            . " ('manage_translation', 'Y', 'administration'),"
            . " ('manage_storage', 'Y', 'administration'),"
            . " ('manage_design', 'Y', 'design'),"
            . " ('manage_themes', 'Y', 'design')"
        );

        db_query('DROP TABLE IF EXISTS ?:privilege_descriptions');
        db_query('DROP TABLE IF EXISTS ?:privilege_section_descriptions');

        return true;
    }

    public static function installAddonsTabs()
    {
        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));
        $addons = db_get_array("SELECT addon, status FROM ?:addons");
        if (fn_allowed_for('ULTIMATE')) {
            $companies = fn_get_all_companies_ids(true);
            foreach ($addons as $addon) {
                ProductTabs::instance()->deleteAddonTabs($addon['addon']);
                foreach ($companies as $company) {
                    ProductTabs::instance($company)->createAddonTabs($addon['addon']);
                }
                ProductTabs::instance()->updateAddonTabStatus($addon['addon'], $addon['status']);
            }
        } else {
            foreach ($addons as $addon) {
                ProductTabs::instance()->deleteAddonTabs($addon['addon']);
                ProductTabs::instance()->createAddonTabs($addon['addon']);
                ProductTabs::instance()->updateAddonTabStatus($addon['addon'], $addon['status']);
            }
        }

        return true;
    }

    public static function installAddons()
    {
        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));
        $addons = db_get_fields("SELECT addon FROM ?:addons");
        //fill original values in new database
        foreach ($addons as $addon) {
            $addon_scheme = SchemesManager::getScheme($addon);
            if ($addon_scheme) {
                if ($original = $addon_scheme->getOriginals()) {
                    db_query("REPLACE INTO ?:original_values ?e", array(
                        'msgctxt' => 'Addon:' . $addon,
                        'msgid' => $original['name']
                    ));

                    db_query("REPLACE INTO ?:original_values ?e", array(
                        'msgctxt' => 'AddonDescription:' . $addon,
                        'msgid' => $original['description']
                    ));
                }

                $language_variables = $addon_scheme->getLanguageValues(true);
                if (!empty($language_variables)) {
                    db_query('REPLACE INTO ?:original_values ?m', $language_variables);
                }
            }
        }

        General::connectToOriginalDB();
        foreach ($addons as $addon) {
            if (!db_get_field("SELECT status FROM ?:addons WHERE addon = ?s", $addon)) {
                fn_install_addon($addon, false);
            }
        }

        return true;
    }

    public static function checkCompanyCount($store_data)
    {
        $result = false;
        if ($store_data['product_edition'] == 'ULTIMATE') {
            General::connectToImportedDB($store_data);
            $companies_old = db_get_fields("SELECT company_id FROM ?:companies ORDER BY company_id ASC");
            General::connectToOriginalDB();
            $companies_new = db_get_fields("SELECT company_id FROM ?:companies ORDER BY company_id ASC");

            $result = ($companies_new === $companies_old);
        } elseif ($store_data['product_edition'] == 'PROFESSIONAL') {
            General::connectToOriginalDB();
            $companies_new = db_get_fields("SELECT company_id FROM ?:companies ORDER BY company_id ASC");

            $result = ((int) count($companies_new) === 1 && (int) $companies_new[0] === 1);
        } elseif ($store_data['product_edition'] == 'MULTIVENDOR') {
            General::connectToImportedDB($store_data);
            $companies_old = db_get_fields("SELECT company_id FROM ?:companies ORDER BY company_id ASC");
            General::connectToOriginalDB();
            $companies_new = db_get_fields("SELECT company_id FROM ?:companies ORDER BY company_id ASC");
            $companies_common = fn_array_merge($companies_new, $companies_old);

            if ($companies_new === $companies_common && (int) count($companies_old)+1 === (int) count($companies_new)) {
                $result = true;
            }
        }

        return $result;
    }

    //process 22x addons settings
    public static function processAddonsSettings($addons)
    {
        foreach ($addons as $addons_name => $addon_data) {
            if (!empty($addon_data['options'])) {
                foreach ($addon_data['options'] as $setting_name => $setting_value) {
                    db_query("UPDATE ?:settings_objects SET value = ?s WHERE name = ?s", $setting_value, $setting_name);
                }
            }
        }

        return true;
    }

    //enable addons imported from 22x versions
    public static function enableInstalledAddons($addons)
    {
        foreach ($addons as $addon_name => $addon_data) {
            foreach ($addon_data['names'] as $lang_code => $name) {
                db_query("INSERT INTO ?:addon_descriptions (addon, name, description, lang_code) VALUES (?s, ?s, '', ?s)", $addon_name, $name['description'], $lang_code);
            }

            db_query("INSERT INTO ?:addons (addon, status, version, priority, dependencies, conflicts, separate) VALUES (?s, ?s, '1.0', ?i, ?s, '', 0)", $addon_name, $addon_data['status'], $addon_data['priority'], $addon_data['dependencies']);
        }

        return true;
    }

    //Get installed addons with settings for the 22x version
    public static function get22xAddons()
    {
        $addons = db_get_hash_array("SELECT * FROM ?:addons ORDER BY priority", 'addon');
        foreach ($addons as $key => $addon) {
            $addons[$key]['names'] = db_get_hash_array("SELECT description, lang_code FROM ?:addon_descriptions WHERE object_type = 'A' AND addon = ?s", 'lang_code', $key);
            if (!empty($addon['options'])) {
                $addons[$key]['options'] = unserialize($addon['options']);
            }
        }

        return $addons;
    }

    public static function getFromToText($store_data)
    {
        $from = __('store_import.text_from', array(
            '[product_name]' => $store_data['product_name'],
            '[product_version]' => $store_data['product_version'],
            '[product_edition]' => ucfirst(strtolower($store_data['product_edition'])),
        ));

        $to = __('store_import.text_to', array(
            '[product_edition]' => PRODUCT_NAME,
            '[product_version]' => PRODUCT_VERSION,
        ));

        return array($from, $to);
    }

    public static function setProgressTitle($class_name)
    {
        $upgrate_to = substr($class_name, -3);
        fn_set_progress('title', __('store_import.progress_title', array('[to]' => $upgrate_to)));
    }

    public static function setDefaultLanguage($store_data)
    {
        General::connectToImportedDB($store_data);
        if (!empty($store_data) && in_array($store_data['product_version'], array('2.2.4', '2.2.5'))) {
            $default_language = db_get_field("SELECT value FROM ?:settings WHERE option_name = 'admin_default_language'");
        } elseif (!empty($store_data) && in_array($store_data['product_version'], array('3.0.1', '3.0.2', '3.0.3', '3.0.4', '3.0.5', '3.0.6'))) {
            $default_language = db_get_field("SELECT value FROM ?:settings_objects WHERE name = 'admin_default_language'");
        } else {
            $default_language = db_get_field("SELECT value FROM ?:settings_objects WHERE name = 'backend_default_language'");
        }
        General::connectToOriginalDB();

        self::$default_language = strtolower($default_language);

        return true;
    }

    public static function setActualLangValues()
    {
        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));
        $prefix = Registry::get('config.table_prefix');
        db_query("INSERT INTO ?:state_descriptions (SELECT state_id, ?s as lang_code, state FROM " . $prefix . "state_descriptions WHERE lang_code = ?s) ON DUPLICATE KEY UPDATE ?:state_descriptions.state_id = ?:state_descriptions.state_id", General::$default_language, DEFAULT_LANGUAGE);
        if (fn_allowed_for('ULTIMATE')) {
            db_query("INSERT  INTO ?:ult_language_values (SELECT ?s as lang_code, name, value, company_id FROM " . $prefix . "ult_language_values WHERE lang_code = ?s) ON DUPLICATE KEY UPDATE ?:ult_language_values.value = ?:ult_language_values.value", General::$default_language, DEFAULT_LANGUAGE);
        }

        $new_ver_langs = db_get_fields("SELECT lang_code FROM " . $prefix . "languages");
        foreach (db_get_fields("SELECT lang_code FROM ?:languages") as $lang_code) {
            $_lang_code = in_array($lang_code, $new_ver_langs) ? $lang_code : DEFAULT_LANGUAGE;
            //We can update only core settings descriptions because addons descriptions were updated during settings restore.
            db_query("INSERT INTO ?:settings_descriptions "
                    . "(SELECT object_id, object_type, ?s as lang_code, value, tooltip FROM " . $prefix . "settings_descriptions WHERE object_id IN "
                        . "(SELECT object_id FROM " . $prefix . "settings_objects WHERE section_id IN "
                            . "(SELECT section_id FROM " . $prefix . "settings_sections WHERE type = 'CORE')) "
                    . "AND lang_code = ?s) "
                    . "ON DUPLICATE KEY UPDATE ?:settings_descriptions.value = " . $prefix . "settings_descriptions.value, ?:settings_descriptions.tooltip = " . $prefix . "settings_descriptions.tooltip",
                    $lang_code, $_lang_code
            );
            db_query("INSERT INTO ?:language_values "
                    . "(SELECT ?s as lang_code, name, value FROM " . $prefix . "language_values WHERE lang_code = ?s) "
                    . "ON DUPLICATE KEY UPDATE ?:language_values.name = " . $prefix . "language_values.name",
                    $lang_code, $_lang_code
            );
        }

        db_query("REPLACE INTO ?:original_values (SELECT * FROM " . $prefix . "original_values)");

        return true;
    }

    public static function setEmptyProgressBar($text = '', $scale_count = 1)
    {
        if (empty($text)) {
            $text = self::getUnavailableLangVar('updating_data');
        }
        fn_set_progress('step_scale', $scale_count);
        fn_set_progress('echo', $text, true);
    }

    private static function _removeTempTables()
    {
        $prefix = General::formatPrefix();
        $fields = db_get_fields("SHOW TABLES LIKE '$prefix%'");

        if (!empty($fields)) {
            foreach ($fields as $field) {
                db_query("DROP TABLE IF EXISTS $field");
            }
        }

        return true;
    }

    private static function _setUnavailableLangVars()
    {
        self::$unavailable_lang_vars = array(
            'cant_connect_to_imported' => __('store_import.cant_connect_to_imported'),
            'cant_connect_to_original' => __('store_import.cant_connect_to_original'),
            'converting_orders' => __('store_import.converting_orders'),
            'processing_addons' => __('store_import.processing_addons'),
            'updating_languages' => __('store_import.updating_languages'),
            'updating_data' => __('store_import.updating_data'),
            'uc_searchanise_disabled' => __('uc_searchanise_disabled', array('[url]' => fn_url('addons.manage'))),
        );
    }

    public static function getUnavailableLangVar($name)
    {
        $langvars = self::$unavailable_lang_vars;

        return !empty($langvars[$name]) ? $langvars[$name] : '';
    }

    private static function _uninstallAllAddons()
    {
        $addons = db_get_fields("SELECT addon FROM ?:addons WHERE addon != 'store_import'");
        if (!empty($addons)) {
            foreach ($addons as $addon) {
                fn_uninstall_addon($addon, false);
            }
        }
    }

    public static function import($store_data, $actualize_data = false)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        fn_define('STORE_IMPORT', true);
        $log_dir = Registry::get('config.dir.store_import');
        fn_mkdir($log_dir);

        $logger = \Tygh\Logger::instance();
        $logger->logfile = $log_dir . date('Y-m-d_H-i') . '.log';

        if ($actualize_data) {
            $logos = self::_backupLogos();
        }

        $import_classes_cascade = self::getImportClassesCascade($store_data);
        $db_already_cloned = false;
        Registry::set('runtime.skip_sharing_selection', true);

        self::_removeTempTables();
        self::_setUnavailableLangVars();
        if (!$actualize_data) {
            self::_uninstallAllAddons();
        }

        fn_set_progress('parts', (count($import_classes_cascade) * 6) + 2);
        $result = !empty($import_classes_cascade) ? true : false;
        self::setDefaultLanguage($store_data);
        foreach ($import_classes_cascade as $class_name) {
            if ($result) {
                if (class_exists($class_name)) {
                    $obj = new $class_name($store_data);
                    $result = $db_already_cloned = $obj -> import($db_already_cloned);
                    Settings::instance()->reloadSections();
                } else {
                    $result = false;
                    fn_set_notification('E', __('error'), __('store_import.class_not_found'));
                    break;
                }
            } else {
                fn_set_notification('E', __('error'), __('store_import.import_failed'));
                break;
            }
        }

        Registry::set('runtime.skip_sharing_selection', false);

        if ($result) {
            General::setLicenseData();
            //First, we should install all addons from old version in the new version that all templates, etc were installed in the new version
            self::installAddons();
            //Next, we should install all tabs in the upgraded database (mostly for the old version, 2.2.x)
            self::installAddonsTabs();
            fn_clear_cache();

            if (!$actualize_data) {
                self::_removeRussianServices($store_data);
                if (fn_allowed_for('ULTIMATE')) {
                    $company_ids = db_get_fields("SELECT company_id FROM ?:companies");
                    foreach ($company_ids as $company_id) {
                        self::_installTheme($company_id);
                    }
                } else {
                    self::_installTheme();
                }
            }

            self::replaceOriginalDB($store_data, $actualize_data);

            fn_install_addon('store_import', false);
            self::_removeTempTables();

            if (defined('AJAX_REQUEST')) {
                Registry::get('ajax')->assign('non_ajax_notifications', true);
                Registry::get('ajax')->assign('force_redirection', fn_url('index.index'));
            }
            if ($actualize_data) {
                self::_restoreLogos($logos);
            }
            fn_set_progress('step_scale', '1');
            fn_set_progress('echo', __('store_import.done'), true);

            return true;
        }

        return false;
    }

    private static function _installTheme($company_id = null)
    {
        $theme_name = 'basic';
        $style = 'satori';
        fn_install_theme($theme_name, $company_id);
        $layout = Layout::instance($company_id)->getDefault($theme_name);
        Styles::factory($theme_name)->setStyle($layout['layout_id'], $style);
    }

    public static function updateStoreimportSetting($setting_data)
    {
        $si_data = unserialize(Settings::instance()->getValue('si_data', 'store_import'));
        $si_data = array_merge($si_data, $setting_data);
        Settings::instance()->updateValue('si_data', serialize($si_data), 'store_import');

        return true;
    }

    public static function setLicenseData()
    {
        General::connectToOriginalDB();
        $si_data = unserialize(Settings::instance()->getValue('si_data', 'store_import'));
        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));
        if (!empty($si_data['license_data'])) {
            Settings::instance()->updateValue('license_number', $si_data['license_data']['license_number']);
            Settings::instance()->updateValue('current_timestamp', $si_data['license_data']['current_timestamp']);
            fn_set_storage_data('store_mode', $si_data['license_data']['store_mode']);
            $_SESSION['last_status'] = 'ACTIVE';
            $_SESSION['mode_recheck'] = false;

            return true;
        }

        return false;
    }

    public static function copyProductsBlocks($store_data)
    {
        General::connectToImportedDB($store_data);
        $blocks_22x = db_get_hash_array("SELECT * FROM ?:blocks WHERE block_type = 'B' AND location = 'products'", 'block_id');
        if (empty($blocks_22x)) {
            General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));

            return true;
        }
        $descriptions_22x = db_get_hash_multi_array("SELECT * FROM ?:block_descriptions WHERE block_id IN (?a)", array('block_id', 'lang_code'), array_keys($blocks_22x));
        $blocks_links_22x = db_get_hash_multi_array("SELECT * FROM ?:block_links WHERE block_id IN (?a)", array('block_id', 'link_id'), array_keys($blocks_22x));

        General::connectToOriginalDB(array('table_prefix' => General::formatPrefix()));
        foreach ($blocks_22x as $block_22x_id => $block_22x_data) {
            $block_22x_data['properties'] = unserialize($block_22x_data['properties']);

            if (!isset($block_22x_data['properties']['fillings']) || $block_22x_data['properties']['fillings'] != 'manually') {
                continue;
            }

            $block_data = array(
                'type' => 'products',
                'properties' => serialize(array(
                    'template' => 'blocks/products/products.tpl',
                    'item_number' => $block_22x_data['properties']['item_number'],
                    'hide_options' => 'N',
                    'hide_add_to_cart_button' => $block_22x_data['properties']['hide_add_to_cart_button'],
                )),
            );

            $block_id = db_query("INSERT INTO ?:bm_blocks ?e", $block_data);

            $default_block_content = array(
                'snapping_id' => '0',
                'object_id' => '0',
                'object_type' => '',
                'block_id' => $block_id,
                'content' => serialize(array(
                    'items' => array(
                        'filling' => 'manually',
                        'item_ids' => '',
                    ),
                )),
            );

            foreach ($descriptions_22x[$block_22x_id] as $lang_code => $descr_data) {
                $default_block_content['lang_code'] = $lang_code;
                db_query("INSERT INTO ?:bm_blocks_content ?e", $default_block_content);
                foreach ($blocks_links_22x[$block_22x_id] as $key => $content_22x) {
                    if (!empty($content_22x['item_ids'])) {
                        $block_content = array(
                            'snapping_id' => '0',
                            'object_id' => $content_22x['object_id'],
                            'object_type' => 'products',
                            'block_id' => $block_id,
                            'lang_code' => $lang_code,
                            'content' => serialize(array(
                                'items' => array(
                                    'filling' => 'manually',
                                    'item_ids' => $content_22x['item_ids'],
                                ),
                            )),
                        );
                        db_query("INSERT INTO ?:bm_blocks_content ?e", $block_content);
                    }
                }
                db_query("INSERT INTO ?:bm_blocks_descriptions (block_id, lang_code, name) VALUES (?i, ?s, ?s)", $block_id, $lang_code, $descr_data['description']);

            }

            $snapping_data = array(
                'block_id' => $block_id,
                'grid_id' => '34', //We can use exact value because defaulr blocks and grids were created during import from 22x versions.
                'status' => 'A',
            );
            $snapping_id = db_query('INSERT INTO ?:bm_snapping ?e', $snapping_data);

            $object_ids = array();
            foreach ($blocks_links_22x[$block_22x_id] as $content_22x) {
                if ($content_22x['enable'] == 'N') {
                    $object_ids[] = $content_22x['object_id'];
                }
            }

            if (!empty($object_ids)) {
                $block_22x_statuses = array(
                    'snapping_id' => $snapping_id,
                    'object_ids' => implode(',', $object_ids),
                    'object_type' => 'products'
                );

                db_query('INSERT INTO ?:bm_block_statuses ?e', $block_22x_statuses);
            }
        }

        return true;
    }

    public static function checkLicense($store_data)
    {
        $result = true;
        General::connectToOriginalDB();
        $new_license_data = self::_getlicenseData();
        if (empty($new_license_data['license_number']) || !self::_getLicenseStatus($new_license_data)) {
            General::connectToImportedDB($store_data);
            $old_license_data = self::_getlicenseData($store_data);
            General::connectToOriginalDB();

            $result = !empty($old_license_data['license_number']) ? self::_getLicenseStatus($old_license_data) : false;
        }

        return $result;
    }

    private static function _getlicenseData($store_data = array())
    {
        $license_data = array(
            'current_timestamp' => self::_getTimestampData($store_data),
            'license_number' => self::_getLicenseNumber($store_data),
            'store_mode' => 'full',
        );

        return $license_data;
    }

    private static function _getTimestampData($store_data)
    {
        $result = '';
        if (!empty($store_data) && in_array($store_data['product_version'], array('2.2.4', '2.2.5'))) {
            $result = db_get_field("SELECT description FROM ?:settings_descriptions WHERE object_id = 70024");
        } elseif (!empty($store_data) && in_array($store_data['product_version'], array('3.0.1', '3.0.2', '3.0.3', '3.0.4', '3.0.5', '3.0.6'))) {
            $result = db_get_field("SELECT value FROM ?:settings_descriptions WHERE object_id = 70024");
            $result = (!empty($result)) ? $result : time();
        } else {
            $result = db_get_field("SELECT value FROM ?:settings_objects WHERE name = 'current_timestamp'");
        }

        return $result;
    }

    private static function _getLicenseNumber($store_data)
    {
        $result = '';
        if (!empty($store_data) && in_array($store_data['product_version'], array('2.2.4', '2.2.5'))) {
            $result = db_get_field("SELECT value FROM ?:settings WHERE option_name = 'license_number'");
        } else {
            $result = db_get_field("SELECT value FROM ?:settings_objects WHERE name = 'license_number'");
        }

        return $result;
    }

    private static function _getLicenseStatus($license_data)
    {
        if (empty($license_data['license_number'])) {
            return false;
        }

        $result = Helpdesk::checkStoreImportAvailability($license_data['license_number'], General::VERSION_FOR_LICENSE_CHECK);
        if ($result) {
            $si_data = unserialize(Settings::instance()->getValue('si_data', 'store_import'));
            $si_data['license_data'] = $license_data;
            Settings::instance()->updateValue('si_data', serialize($si_data), 'store_import');
        }

        return $result;
    }

    private static function _backupLogos()
    {
        $images['images_links'] = db_get_hash_array("SELECT * FROM ?:images_links WHERE object_type = 'logos'", 'image_id');
        if (!empty($images['images_links'])) {
            $images['images_data'] = db_get_hash_array("SELECT * FROM ?:images WHERE image_id IN (?a)", 'image_id', array_keys($images['images_links']));
            $images['images_descriptions'] = db_get_hash_multi_array("SELECT * FROM ?:common_descriptions WHERE object_id IN (?a)", array('lang_code','object_id'), array_keys($images['images_data']));
        }

        return $images;
    }

    private function _restoreLogos($images)
    {
        db_query("DELETE FROM ?:images WHERE image_id IN (SELECT image_id FROM ?:images_links WHERE object_type = 'logos')");
        db_query("DELETE FROM ?:common_descriptions WHERE object_id IN (SELECT image_id FROM ?:images_links WHERE object_type = 'logos')");
        db_query("DELETE FROM ?:images_links WHERE object_type = 'logos'");
        foreach ($images['images_data'] as $old_id => $data) {
            unset($data['image_id']);
            unset($images['images_links'][$old_id]['pair_id']);
            $image_link = $images['images_links'][$old_id];
            $new_image_id = db_query("INSERT INTO ?:images ?e", $data);
            $image_link['image_id'] = $new_image_id;
            foreach ($images['images_descriptions'] as $lang => $description) {
                if (isset($description[$old_id])) {
                    $new_description = $description[$old_id];
                    $new_description['object_id'] = $new_image_id;
                    db_query("REPLACE INTO ?:common_descriptions ?e", $new_description);
                }
            }
            db_query("REPLACE INTO ?:images_links ?e", $image_link);
        }

        return true;
    }

    public static function get22xSettings($settings_names = array())
    {
        $result = array();
        foreach ($settings_names as $setting_name) {
            $value = db_get_field("SELECT value FROM ?:settings WHERE option_name = ?s", $setting_name);
            if ($value) {
                $result[$setting_name] = $value;
            }
        }

        return $result;
    }

    public static function restore22xSavedSetting($settings_values)
    {
        if (!empty($settings_values)) {
            foreach ($settings_values as $name => $value) {
                db_query("UPDATE ?:settings_objects SET value = '$value' WHERE name = ?s", $name);
            }
        }

        return true;
    }

    public static function process402Settings()
    {
        $mapping = array(
            'allow_anonymous_shopping' => array(
                'Y' => 'allow_shopping',
                'P' => 'hide_price_and_add_to_cart',
                'B' => 'hide_add_to_cart',
            ),
            'alternative_currency' => array(
                'Y' => 'use_selected_and_alternative',
                'N' => 'use_only_selected',
            ),
            'min_order_amount_type' => array(
                'P' => 'only_products',
                'S' => 'products_with_shippings',
            ),
        );

        foreach ($mapping as $setting_name => $setting_data) {
            $old_value = db_get_field("SELECT value FROM ?:settings_objects_upg WHERE name = ?s", $setting_name);
            if (!empty($setting_data[$old_value])) {
                db_query("UPDATE ?:settings_objects_upg SET value = '$setting_data[$old_value]' WHERE name = ?s", $setting_name);
            }
        }

        return true;
    }

    public static function processPaymentCertificates($store_data)
    {
        $payment_methods = db_get_array('SELECT p.payment_id, pp.* FROM ?:payments p, ?:payment_processors pp WHERE pp.processor_id = p.processor_id AND p.processor_id != 0');
        $certificates_dir = Registry::get('config.dir.certificates');

        foreach ($payment_methods as $payment_method) {
            if (in_array($payment_method['processor_script'], array('paypal_express.php', 'paypal_pro.php', 'qbms.php'))) {
                $payment_data = fn_get_payment_method_data($payment_method['payment_id']);

                $certificate_filename = '';

                if (isset($payment_data['processor_params']['certificate_filename'])) {
                    $certificate_filename = $payment_data['processor_params']['certificate_filename'];
                } elseif (isset($payment_data['processor_params']['certificate'])) {
                    $certificate_filename = $payment_data['processor_params']['certificate'];
                }

                if ($certificate_filename) {
                    $filename = $payment_method['payment_id'] . '/' . $certificate_filename;
                    $old_certificate_file = $store_data['path'] . '/payments/certificates/' . $certificate_filename;

                    if (file_exists($old_certificate_file)) {
                        fn_mkdir($certificates_dir . $payment_method['payment_id']);
                        fn_copy($old_certificate_file, $certificates_dir . $filename);
                    } else {
                        $filename = '';
                    }

                    $payment_data['processor_params']['certificate_filename'] = $filename;

                    fn_update_payment($payment_data, $payment_method['payment_id']);
                }
            }

        }
    }

    public static function checkRussianEdition($store_data)
    {
        if (in_array($store_data['product_version'], array('2.2.4', '2.2.5', '3.0.1'))) {
            return false;
        }

        $addons_to_check = array(
            'exim_1c',
            'kupivkredit',
            'loginza',
            'kupivkredit',
            'yandex_market',
        );

        $result = false;

        General::connectToOriginalDB();
        $addons_to_check_status = db_get_fields("SELECT status FROM ?:addons WHERE addon IN (?a)", $addons_to_check);
        if (!empty($addons_to_check_status)) {
            $result = true;
        }

        $shippings_to_check = array(
            'russian_post',
            'ems',
            'edost',
        );
        $russian_shippings = db_get_fields("SELECT service_id FROM ?:shipping_services WHERE module IN (?a)", $shippings_to_check);
        if (!empty($russian_shippings)) {
            $result = true;
        }

        return $result;
    }

    private static function _removeRussianServices($store_data)
    {
        if (isset($store_data['russian_edition']) && $store_data['russian_edition'] !== true) {
            $addons = db_get_array("SELECT addon FROM ?:addons");

            if (in_array('exim_1c', $addons)) {
                db_query("ALTER TABLE ?:categories DROP external_id");
                db_query("ALTER TABLE ?:products DROP external_id");
                db_query("ALTER TABLE ?:product_features DROP external_id");
                db_query("ALTER TABLE ?:product_option_variants DROP external_id");
                db_query("ALTER TABLE ?:product_options_inventory DROP external_id");
                db_query("ALTER TABLE ?:users DROP external_id");
            }
            if (in_array('loginza', $addons)) {
                db_query("ALTER TABLE ?:users DROP loginza_identifier");
            }
            if (in_array('yandex_market', $addons)) {
                db_query("ALTER TABLE `?:products` DROP `yml_brand`, DROP `yml_origin_country`, DROP `yml_store`, DROP `yml_pickup`, DROP `yml_delivery`, DROP `yml_bid`, DROP `yml_cbid`");
            }
            $filename = Registry::get('config.dir.addons') . 'store_import/database/remove_russian_services.sql';
            if (file_exists($filename)) {
                db_import_sql_file($filename, 16384, true, true, false, false, false, true);
            }
        }
    }

    public static function copyPresetImages()
    {
        $theme_name = Registry::get('config.base_theme');
        $presets_path = fn_get_theme_path('[themes]/' . $theme_name . '/presets/data', 'C');
        $preset_images_path = fn_get_theme_path('[themes]/' . $theme_name . '/media/images/patterns', 'C');
        $files = fn_get_dir_contents($presets_path, false, true);

        foreach ($files as $file) {
            $content = fn_get_contents($presets_path . '/' . $file);
            if (preg_match('/@general_bg_image\: url\(["]?(.*?)["]?\)/', $content, $m)) {
                $image_name = fn_basename($m[1]);
                if (strpos($image_name, '?') !== false) {
                    list($image_name) = explode('?', $image_name);
                }
                if (file_exists($preset_images_path . '/' . $image_name)) {
                    $preset_dir = str_replace('.less', '', $file);

                    $new_path = $preset_images_path . '/' . $preset_dir;
                    fn_mkdir($new_path);
                    fn_copy($preset_images_path . '/' . $image_name, $new_path);

                    $content = str_replace($image_name, $preset_dir . '/' . $image_name, $content);
                    fn_put_contents($presets_path . '/' . $file, $content);
                }
            }
        }

        return true;
    }

    public static function validateStoreImportSettings($path = '', $store_data = array())
    {
        $result = true;
        $config = self::getConfig($path);
        if ($config !== false) {
            $store_data = array('path' => $path);
            $store_data = fn_array_merge($store_data, $config);
            $store_data['new_storefront_url'] = str_replace('http://', '', Registry::get('config.http_location'));
            $store_data['new_secure_storefront_url'] = str_replace('https://', '', Registry::get('config.https_location'));
            if (!self::checkEditionMapping($store_data)) {
                fn_set_notification('E', __('error'), __('store_import.edition_mapping_failed'));
                $result = false;
            } elseif (!self::testDatabaseConnection($store_data)) {
                fn_set_notification('E', __('error'), __('store_import.cannot_connect_to_database_server'));
                $result = false;
            } elseif (!self::checkLicense($store_data)) {
                fn_set_notification('E', __('error'), __('store_import.invalid_license'));
                $result = false;
            }
            $store_data['russian_edition'] = self::checkRussianEdition($store_data);
        } else {
            fn_set_notification('E', __('error'), __('store_import.this_is_not_cart_path'));
            $result = false;
        }

        return array($store_data, $result);
    }

    public static function convertScrollerBlocks()
    {
        $blocks = db_get_array("SELECT * FROM ?:bm_blocks WHERE properties LIKE '%products_scroller.tpl%'");
        $map = array(
            'slow' => '600',
            'normal' => '400',
            'fast' => '200',
        );
        if (!empty($blocks)) {
            foreach ($blocks as $block_data) {
                $block_data['properties'] = unserialize($block_data['properties']);
                if ($block_data['properties']['scroller_direction'] == 'up' || $block_data['properties']['scroller_direction'] == 'down') {
                    $block_data['properties'] = 'a:4:{s:8:"template";s:41:"blocks/products/products_multicolumns.tpl";s:11:"item_number";s:1:"N";s:17:"number_of_columns";s:1:"1";s:23:"hide_add_to_cart_button";s:1:"Y";}';
                } else {
                    $block_data['properties']['speed'] = $map[$block_data['properties']['speed']];
                    unset($block_data['properties']['scroller_direction']);
                    unset($block_data['properties']['easing']);
                    $block_data['properties'] = serialize($block_data['properties']);
                }
                db_query("REPLACE INTO ?:bm_blocks ?e", $block_data);
            }
        }

        return true;
    }

    public static function addEsStates()
    {
        $main_lang_code = db_get_field("SELECT value FROM ?:settings_objects WHERE name = 'admin_default_language'");
        $lang_codes = db_get_fields("SELECT lang_code FROM ?:languages WHERE lang_code != ?s", $main_lang_code);
        $data = array(
            'C' => 'A Coruña',
            'VI' => 'Álava',
            'AB' => 'Albacete',
            'A' => 'Alicante',
            'AL' => 'Almería',
            'O' => 'Asturias',
            'AV' => 'Ávila',
            'BA' => 'Badajoz',
            'PM' => 'Baleares',
            'B' => 'Barcelona',
            'BU' => 'Burgos',
            'CC' => 'Cáceres',
            'CA' => 'Cádiz',
            'S' => 'Cantabria',
            'CS' => 'Castellón',
            'CE' => 'Ceuta',
            'CR' => 'Ciudad Real',
            'CO' => 'Córdoba',
            'CU' => 'Cuenca',
            'GI' => 'Girona',
            'GR' => 'Granada',
            'GU' => 'Guadalajara',
            'SS' => 'Guipúzcoa',
            'H' => 'Huelva',
            'HU' => 'Huesca',
            'J' => 'Jaén',
            'LO' => 'La Rioja',
            'GC' => 'Las Palmas',
            'LE' => 'León',
            'L' => 'Lleida',
            'LU' => 'Lugo',
            'M' => 'Madrid',
            'MA' => 'Málaga',
            'ML' => 'Melilla',
            'MU' => 'Murcia',
            'NA' => 'Navarra',
            'OR' => 'Ourense',
            'P' => 'Palencia',
            'PO' => 'Pontevedra',
            'SA' => 'Salamanca',
            'TF' => 'Santa Cruz de Tenerife',
            'SG' => 'Segovia',
            'SE' => 'Sevilla',
            'SO' => 'Soria',
            'T' => 'Tarragona',
            'TE' => 'Teruel',
            'TO' => 'Toledo',
            'V' => 'Valencia',
            'VA' => 'Valladolid',
            'BI' => 'Vizcaya',
            'ZA' => 'Zamora',
            'Z' => 'Zaragoza',
        );

        foreach ($data as $state_code => $state_name) {
            $old_state_id = db_get_field("SELECT state_id FROM ?:states WHERE country_code = 'ES' AND code = ?s", $state_code);
            $state_id = db_query("REPLACE INTO ?:states (`country_code`, `code`, `status`) VALUES ('ES', ?s, 'A')", $state_code);
            db_query("REPLACE INTO ?:state_descriptions (`state_id`, `lang_code`, `state`) VALUES (?i, ?s, ?s)", $state_id, $main_lang_code, $state_name);
            db_query("UPDATE ?:destination_elements SET element = ?i WHERE element = ?i AND element_type = 'S'", $state_id, $old_state_id);
        }
    }
}
