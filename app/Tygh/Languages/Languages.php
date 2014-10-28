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

namespace Tygh\Languages;

use Tygh\Registry;
use Tygh\Settings;
use Tygh\Languages\Values as LanguageValues;
use Tygh\Languages\Po;

class Languages
{
    /**
    * Defines default translator language code
    *
    * @const TRANSLATION_LANGUAGE 2-letters language code
    */
    const TRANSLATION_LANGUAGE = 'en';

    /**
     * Gets list of languages by specified params
     *
     * @param array $params Extra condition for languages
     *      lang_code    - 2-letters language identifier
     *      lang_id      - integer language number (key in DB)
     *      name         - Name of language
     *      status       - 1-letter status code (A - active, H - hidden, D - disabled)
     *      country_code - Linked country code
     * @param string $hash_key Keys of returned array with languages.
     *  Example:
     *   hash_key - lang_code
     *      [en] => array(data)
     *      [bg] => array(data)
     *
     *   hash_key - lang_id
     *      [1] => array(data)
     *      [7] => array(data)
     * @return array $langs_data Languages list
     */
    public static function get($params, $hash_key = 'lang_code')
    {
        $condition = array();

        if (!empty($params['lang_code'])) {
            $condition[] = db_quote('lang_code = ?s', $params['lang_code']);
        }

        if (!empty($params['lang_id'])) {
            $condition[] = db_quote('lang_id = ?s', $params['lang_id']);
        }

        if (!empty($params['name'])) {
            $condition[] = db_quote('name = ?s', $params['name']);
        }

        if (!empty($params['status'])) {
            $condition[] = db_quote('status = ?s', $params['status']);
        }

        if (!empty($params['country_code'])) {
            $condition[] = db_quote('country_code = ?s', $params['country_code']);
        }

        if (fn_allowed_for('ULTIMATE:FREE')) {
            $condition[] = db_quote('1 OR lang_code = ?s', Registry::get('settings.Appearance.' . fn_get_area_name(AREA) . '_default_language'));
        }

        if (!empty($condition)) {
            $condition = 'WHERE ' . implode(' AND ', $condition);
        } else {
            $condition = '';
        }

        $langs_data = db_get_hash_array('SELECT * FROM ?:languages ?p', $hash_key, $condition);

        $langs_data = self::_checkFreeAvailability($langs_data);

        return $langs_data;
    }

    /**
     * Gets list of all languages defined in store
     * used for adding desciptions and etc.
     *
     * @param  boolean $edit Flag that determines if language list is used to be edited
     * @return array   $languages Languages list
     */
    public static function getAll($edit = false)
    {
        $languages = db_get_hash_array("SELECT ?:languages.* FROM ?:languages", 'lang_code');
        $languages = self::_checkFreeAvailability($languages);

        /**
         * Adds additional languages to all language list
         *
         * @param array   $languages Languages list
         * @param boolean $edit      Flag that determines if language list is used to be edited
         */
        fn_set_hook('get_translation_languages', $languages, $edit);

        return $languages;
    }

    /**
     * Updates language
     *
     * @param  array  $language_data Language data
     * @param  string $lang_id       language id
     * @return string language id
     */
    public static function update($language_data, $lang_id)
    {
        if (!$language_data || empty($language_data['lang_code'])) {
            return false;
        }

        /**
         * Changes language data before update
         *
         * @param array  $language_data Language data
         * @param string $lang_id       language id
         */
        fn_set_hook('update_language_pre', $language_data, $lang_id);

        $language_data['lang_code'] = trim($language_data['lang_code']);
        $language_data['lang_code'] = substr($language_data['lang_code'], 0, 2);

        $action = false;

        $is_exists = db_get_field("SELECT COUNT(*) FROM ?:languages WHERE lang_code = ?s AND lang_id <> ?i", $language_data['lang_code'], $lang_id);

        if (!empty($is_exists)) {
            fn_set_notification('E', __('error'), __('error_lang_code_exists', array(
                '[code]' => $language_data['lang_code']
            )));

            $lang_id = false;

        } elseif (empty($lang_id)) {
            if (!empty($language_data['lang_code']) && !empty($language_data['name'])) {
                $lang_id = db_query("INSERT INTO ?:languages ?e", $language_data);
                $clone_from =  !empty($language_data['from_lang_code']) ? $language_data['from_lang_code'] : CART_LANGUAGE;

                fn_clone_language($language_data['lang_code'], $clone_from);

                $action = 'add';
            }

        } else {
            $res = db_query("UPDATE ?:languages SET ?u WHERE lang_id = ?i", $language_data, $lang_id);
            if (!$res) {
                $lang_id = null;
            }

            $action = 'update';
        }

        /**
         * Adds additional actions after language update
         *
         * @param array  $language_data Language data
         * @param string $lang_id       language id
         * @param string $action        Current action ('add', 'update' or bool false if failed to update language)
         */
        fn_set_hook('update_language_post', $language_data, $lang_id, $action);

        return $lang_id;
    }

    /**
     * Removes languages
     *
     * @param  array  $lang_ids     List of language ids
     * @param  string $default_lang Default language code
     * @return array  Deleted lang codes
     */
    public static function deleteLanguages($lang_ids, $default_lang = DEFAULT_LANGUAGE)
    {
        /**
         * Adds additional actions before languages deleting
         *
         * @param array $lang_ids List of language ids
         */
        fn_set_hook('delete_languages_pre', $lang_ids);

        $db_descr_tables = fn_get_description_tables();

        $lang_codes = db_get_hash_single_array("SELECT lang_id, lang_code FROM ?:languages WHERE lang_id IN (?n)", array('lang_id', 'lang_code'), (array) $lang_ids);
        $deleted_lang_codes = array();

        foreach ($lang_codes as $lang_code) {

            if ($lang_code == $default_lang) {
                fn_set_notification('W', __('warning'), __('warning_not_deleted_default_language', array(
                    '[lang_name]' => db_get_field("SELECT name FROM ?:languages WHERE lang_code = ?s", $lang_code)
                )), '', 'language_is_default');
                continue;
            }

            $res = db_query("DELETE FROM ?:languages WHERE lang_code = ?s", $lang_code);

            if ($res) {
                $deleted_lang_codes[] = $lang_code;
            }

            if (!fn_allowed_for('ULTIMATE:FREE')) {
                db_query("DELETE FROM ?:localization_elements WHERE element_type = 'L' AND element = ?s", $lang_code);
            }

            foreach ($db_descr_tables as $table) {
                db_query("DELETE FROM ?:$table WHERE lang_code = ?s", $lang_code);
            }
        }

        self::saveLanguagesIntegrity();

        /**
         * Adds additional actions after languages deleting
         *
         * @param array $lang_ids   List of language ids
         * @param array $lang_codes List of language codes
         * @param array $deleted_lang_codes List of deleted language codes
         */
        fn_set_hook('delete_languages_post', $lang_ids, $lang_codes, $deleted_lang_codes);

        return $deleted_lang_codes;
    }

    /**
     *
     * @param  string $default_lang
     * @return bool
     */
    public static function saveLanguagesIntegrity($default_lang = CART_LANGUAGE)
    {
        $avail = db_get_field("SELECT COUNT(*) FROM ?:languages WHERE status = 'A'");
        if (!$avail) {
            $default_lang_exists = db_get_field("SELECT COUNT(*) FROM ?:languages WHERE lang_code = ?s", $default_lang);
            if (!$default_lang_exists) {
                $default_lang = db_get_field("SELECT lang_code FROM ?:languages WHERE status = 'H' LIMIT 1");
                if (!$default_lang) {
                    $default_lang = db_get_field("SELECT lang_code FROM ?:languages LIMIT 1");
                }
            }
            db_query("UPDATE ?:languages SET status = 'A' WHERE lang_code = ?s", $default_lang);
        }

        $settings_checks = array(
            'frontend' => 'A',
            'backend' => array('A', 'H')
        );

        $settings_changed = false;
        foreach ($settings_checks as $zone => $statuses) {
            $available = db_get_field("SELECT COUNT(*) FROM ?:languages WHERE lang_code = ?s AND status IN (?a)", Registry::get('settings.Appearance.' . $zone . '_default_language'), $statuses);
            if (!$available) {
                $first_avail_code = db_get_field("SELECT lang_code FROM ?:languages WHERE status IN (?a) LIMIT 1", $statuses);
                Settings::instance()->updateValue($zone . '_default_language', $first_avail_code, 'Appearance');
                $settings_changed = true;
            }
        }

        if (fn_allowed_for('MULTIVENDOR')) {
            db_query("UPDATE ?:companies SET lang_code = ?s WHERE lang_code NOT IN (SELECT lang_code FROM ?:languages WHERE status = 'A')", Registry::get('settings.Appearance.backend_default_language'));
        }
        if ($settings_changed) {
            fn_set_notification('W', __('warning'), __('warning_default_language_disabled', array(
                '[link]' => fn_url('settings.manage?section_id=Appearance')
            )));
        }

        return true;
    }

    /**
     * Returns only active languages list (as lang_code => array(name, lang_code, status, country_code)
     *
     * @param string $default_value  Default value defined in Block scheme
     * @param array  $block          filled block data
     * @param array  $block_scheme   Scheme of current block
     * @param bool   $include_hidden if true get hidden languages too
     * @param array  $params         extra params
     *      area - get languages for specified area. Default: "C"
     * @return array Languages list
     */
    public static function getActive($default_value = '', $block = array(), $block_scheme = array(), $include_hidden = false, $params = array())
    {
        $language_condition = $include_hidden ? "WHERE status <> 'D'" : "WHERE status = 'A'";

        $area = isset($params['area']) ? $params['area'] : AREA;
        if (fn_allowed_for('ULTIMATE:FREE') && $area == 'C') {
            $language_condition .= db_quote(' AND lang_code = ?s', DEFAULT_LANGUAGE);
        }

        $languages = db_get_hash_array("SELECT lang_code, name, status, country_code FROM ?:languages ?p", 'lang_code', $language_condition);
        $languages = self::_checkFreeAvailability($languages);

        return $languages;
    }

    /**
     * Gets only active languages list (as lang_code => name)
     *
     * @param  bool  $include_hidden if true get hiddenlanguages too
     * @return array languages list
     */
    public static function getSimpleLanguages($include_hidden = false)
    {
        $language_condition = $include_hidden ? "WHERE status <> 'D'" : "WHERE status = 'A'";

        if (fn_allowed_for('ULTIMATE:FREE')) {
            $language_condition .= db_quote(' OR lang_code = ?s', DEFAULT_LANGUAGE);
        }

        $languages = db_get_hash_single_array("SELECT lang_code, name FROM ?:languages ?p", array('lang_code', 'name'), $language_condition);
        $languages = self::_checkFreeAvailability($languages, true);

        return $languages;
    }

    /**
     * Returns active and hidden languages list (as lang_code => array(name, lang_code, status, country_code)
     *
     * @param  string $area           Area ('A' for admin or 'C' for customer)
     * @param  bool   $include_hidden if true get hidden languages too
     * @return array  Languages list
     */
    public static function getAvailable($area = AREA, $include_hidden = false)
    {
        $join = $order_by = "";
        $condition = $include_hidden ? "l.status <> 'D'" : "l.status = 'A'";
        $default_language = Registry::get('settings.Appearance.' . fn_get_area_name($area) . '_default_language');

        if ($area == 'C') {
            if (!fn_allowed_for('ULTIMATE:FREE')) {
                if (defined('CART_LOCALIZATION')) {
                    $join = " LEFT JOIN ?:localization_elements AS c ON c.element = l.lang_code AND c.element_type = 'L'";
                    $condition .= db_quote(' AND c.localization_id = ?i', CART_LOCALIZATION);
                    $order_by = " ORDER BY c.position ASC";
                }

            } elseif (fn_allowed_for('ULTIMATE:FREE')) {
                $condition .= db_quote(' AND lang_code = ?s', $default_language);
            }
        }

        if (fn_allowed_for('ULTIMATE:FREE')) {
            $condition .= db_quote(' OR lang_code = ?s', $default_language);
        }

        $languages = db_get_hash_array("SELECT l.* FROM ?:languages AS l ?p WHERE ?p ?p", 'lang_code', $join, $condition, $order_by);
        $languages = self::_checkFreeAvailability($languages, true);

        return $languages;
    }

    /**
     * Gets meta information from the PO file
     *
     * @param  string $base_path Root dir
     * @param  string $pack_file PO file name (without .po extension)
     * @param  bool   $reinstall Use this flag, if pack was alread installed before to get META information
     * @return array  List of lang pack meta information
     */
    public static function getLangPacksMeta($base_path = '', $pack_file = '', $reinstall = false, $check_installed = true)
    {
        if ($check_installed) {
            $installed_languages = fn_get_translation_languages(true);
        } else {
            $installed_languages = array();
        }

        $path = empty($base_path) ? Registry::get('config.dir.lang_packs') : $base_path;
        $langs = array();

        if (!empty($pack_file)) {
            $langs_packs = array($pack_file);
        } else {
            $_packs = fn_get_dir_contents($path, true, false, '.po');
            foreach ($_packs as $_pack) {
                $langs_packs[] = $_pack . '/core.po';
            }
        }

        foreach ($langs_packs as $pack_name) {
            $pack_meta = Po::getMeta($path . $pack_name);

            if (!is_array($pack_meta)) {
                fn_set_notification('E', __('error'), $pack_meta);
                continue;
            }

            if (isset($installed_languages[$pack_meta['lang_code']]) && !$reinstall) {
                continue;
            }

            $langs[] = $pack_meta;
        }

        if (!empty($pack_file) && !empty($langs)) {
            return reset($langs);
        }

        return $langs;
    }

    /**
     * Explodes meta data by variables
     * Example:
     *  array(
     *      'Pack-Name: English',
     *      'Lang-Code: EN',
     *      'Country-Code: US'
     *  )
     *
     * @param  array $msg list of meta data
     * @return array Exploded properties
     *  Example:
     *   array(
     *      'name' => 'english',
     *      'lang_code' => 'en',
     *      'country_code' => 'us',
     *   )
     */
    public static function readMetaProperties($msg)
    {
        $properties = array();

        foreach ($msg as $_prop) {
            if (!empty($_prop)) {
                list($name, $value) = explode(':', $_prop);
                $name = strtolower(str_replace('-', '_', trim($name)));

                $properties[$name] = trim($value);
            }
        }

        return $properties;
    }

    /**
     * Checks if PO Meta information is valid
     *
     * @param  array $meta PO Meta-data
     * @return bool  true if meta information is valid
     */
    public static function isValidMeta($meta)
    {
        if (empty($meta)) {
            return false;
        }

        $required_fields = array(
            'lang_code',
            'name',
            'country_code',
        );

        foreach ($required_fields as $field) {
            if (empty($meta[$field])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Installs new language from PO pack
     *
     * @param string $pack_path Path to PO file
     * @param array  $params
     *  reinstall: Skip adding new language
     *  validate_lang_code:Check meta information (lang_code) with updated language data (lang_code) and forbid to update if does not match
     *  force_lang_code: Skip meta lang code and use this one in all laguage variables
     * @return int Language ID
     */
    public static function installLanguagePack($pack_path, $params = array())
    {
        $default_params = array(
            'reinstall' => false, // Skip adding new language
            'validate_lang_code' => '', // Check meta information (lang_code) with updated language data (lang_code) and forbid to update if does not match
            'force_lang_code' => '', // Skip meta lang code and use this one in all laguage variables
        );

        $params = array_merge($default_params, $params);

        if (!file_exists($pack_path)) {
            fn_set_notification('E', __('error'), __('unable_to_read_resource', array(
                '[file]' => fn_get_rel_dir($pack_path)
            )));

            return false;
        }

        $lang_meta = self::getLangPacksMeta(dirname($pack_path) . '/', basename($pack_path), $params['reinstall']);
        if (!self::isValidMeta($lang_meta)) {
            // Failed to read meta data of new language
            fn_set_notification('E', __('error'), __('check_po_file'));

            return false;
        }

        if (!empty($params['validate_lang_code']) && $lang_meta['lang_code'] != $params['validate_lang_code']) {
            fn_set_notification('E', __('error'), __('po_meta_error_validating_lang_code'));

            return false;
        }

        $lc = false;

        if (!Registry::get('runtime.company_id')) {

            if (!$params['reinstall']) {
                $language_data = array(
                    'lang_code' => $lang_meta['lang_code'],
                    'name' => $lang_meta['name'],
                    'country_code' => $lang_meta['country_code'],
                );
                $lc = self::update($language_data, 0);
            } else {
                $lc = true;
            }

            if ($lc !== false) {
                fn_save_languages_integrity();

                $query = array();
                $original_values_query = array();
                $iteration = 0;
                $max_vars_in_query = 500;

                if (!empty($params['force_lang_code'])) {
                    $lang_meta['lang_code'] = $params['force_lang_code'];
                }

                $lang_data = Po::getValues($pack_path, 'Languages');

                if (!is_array($lang_data)) {
                    fn_set_notification('E', __('error'), $lang_data);

                    return array();
                }

                foreach ($lang_data as $var_name => $var_data) {
                    if (!empty($var_name)) {
                        $id = $var_data['id'];
                        $value = implode('', $var_data['msgstr']);
                        $original_value = $var_data['msgid'];
                        $value = empty($value) ? $original_value : $value;

                        $query[] = db_quote('(?s, ?s, ?s)', $lang_meta['lang_code'], trim($id), trim($value));
                        $original_values_query[] = db_quote('(?s, ?s)', $var_name, trim($original_value));
                    }

                    if ($iteration > $max_vars_in_query) {
                        self::executeLangQueries('language_values', array('lang_code', 'name', 'value'), $query);
                        self::executeLangQueries('original_values', array('msgctxt', 'msgid'), $original_values_query);

                        $query = array();
                        $iteration = 0;
                    }

                    $iteration++;
                }

                self::executeLangQueries('language_values', array('lang_code', 'name', 'value'), $query);
                self::executeLangQueries('original_values', array('msgctxt', 'msgid'), $original_values_query);

                $settings_sections = Po::getValues($pack_path, 'SettingsSections');
                $query = array();
                $original_values_query = array();
                $iteration = 0;

                foreach ($settings_sections as $var_name => $var_data) {
                    if (!empty($var_name)) {
                        $value = implode('', $var_data['msgstr']);
                        $original_value = $var_data['msgid'];
                        $value = empty($value) ? $original_value : $value;

                        if (!empty($var_data['parent'])) {
                            $parent_id = db_get_field('SELECT section_id FROM ?:settings_sections WHERE name = ?s AND type = ?s', $var_data['parent'], Settings::ADDON_SECTION);
                            $section_id = db_get_field('SELECT section_id FROM ?:settings_sections WHERE name = ?s AND parent_id = ?i', $var_data['id'], $parent_id);
                        } else {
                            $section_id = db_get_field('SELECT section_id FROM ?:settings_sections WHERE name = ?s', $var_data['id']);
                        }

                        if (empty($section_id)) {
                            continue;
                        }

                        $query[] = db_quote('(?i, ?s, ?s, ?s)', $section_id, 'S', $lang_meta['lang_code'], trim($value), trim($original_value));
                        $original_values_query[] = db_quote('(?s, ?s)', trim($var_name), trim($original_value));
                    }

                    if ($iteration > $max_vars_in_query) {
                        self::executeLangQueries('settings_descriptions', array('object_id', 'object_type', 'lang_code', 'value'), $query);
                        self::executeLangQueries('original_values', array('msgctxt', 'msgid'), $original_values_query);

                        $query = array();
                        $iteration = 0;
                    }

                    $iteration++;
                }

                self::executeLangQueries('settings_descriptions', array('object_id', 'object_type', 'lang_code', 'value'), $query);
                self::executeLangQueries('original_values', array('msgctxt', 'msgid'), $original_values_query);

                $original_values_query = array();
                $setting_options = Po::getValues($pack_path, 'SettingsOptions');

                foreach ($setting_options as $var_name => $var_data) {
                    if (!empty($var_name)) {
                        $value = implode('', $var_data['msgstr']);
                        $original_value = $var_data['msgid'];
                        $value = empty($value) ? $original_value : $value;
                        $object = Settings::instance()->getId($var_data['id'], $var_data['parent']);

                        if (empty($object)) {
                            continue;
                        }

                        $query = array(
                            'object_id' => $object,
                            'object_type' => 'O',
                            'lang_code' => $lang_meta['lang_code'],
                            'value' => trim($value)
                        );

                        $update = array(
                            'value' => trim($value)
                        );

                        $original_values_query[] = db_quote('(?s, ?s)', trim($var_name), trim($original_value));

                        db_query('INSERT INTO ?:settings_descriptions ?e ON DUPLICATE KEY UPDATE ?u', $query, $update);

                        self::executeLangQueries('original_values', array('msgctxt', 'msgid'), $original_values_query);
                    }
                }

                $original_values_query = array();
                $settings_tooltips = Po::getValues($pack_path, 'SettingsTooltips');

                foreach ($settings_tooltips as $var_name => $var_data) {
                    if (!empty($var_name)) {
                        $value = implode('', $var_data['msgstr']);
                        $original_value = $var_data['msgid'];
                        $value = empty($value) ? $original_value : $value;
                        $object = Settings::instance()->getId($var_data['id'], $var_data['parent']);

                        if (empty($object)) {
                            continue;
                        }

                        $query = array(
                            'object_id' => $object,
                            'object_type' => 'O',
                            'lang_code' => $lang_meta['lang_code'],
                            'tooltip' => trim($value)
                        );

                        $update = array(
                            'tooltip' => trim($value)
                        );

                        $original_values_query[] = db_quote('(?s, ?s)', trim($var_name), trim($original_value));

                        db_query('INSERT INTO ?:settings_descriptions ?e ON DUPLICATE KEY UPDATE ?u', $query, $update);

                        self::executeLangQueries('original_values', array('msgctxt', 'msgid'), $original_values_query);
                    }
                }

                $setting_variants = Po::getValues($pack_path, 'SettingsVariants');
                $query = array();
                $original_values_query = array();
                $iteration = 0;

                foreach ($setting_variants as $var_name => $var_data) {
                    if (!empty($var_name)) {
                        $value = implode('', $var_data['msgstr']);
                        $original_value = $var_data['msgid'];
                        $value = empty($value) ? $original_value : $value;

                        $object = Settings::instance()->getVariant($var_data['section'], $var_data['parent'], $var_data['id']);

                        if (empty($object)) {
                            continue;
                        }

                        $query[] = db_quote('(?i, ?s, ?s, ?s)', $object['variant_id'], 'V', $lang_meta['lang_code'], trim($value));
                        $original_values_query[] = db_quote('(?s, ?s)', trim($var_name), trim($original_value));
                    }

                    if ($iteration > $max_vars_in_query) {
                        self::executeLangQueries('settings_descriptions', array('variant_id', 'object_type', 'lang_code', 'value'), $query);
                        self::executeLangQueries('original_values', array('msgctxt', 'msgid'), $original_values_query);

                        $query = array();
                        $iteration = 0;
                    }

                    $iteration++;
                }

                self::executeLangQueries('settings_descriptions', array('object_id', 'object_type', 'lang_code', 'value'), $query);
                self::executeLangQueries('original_values', array('msgctxt', 'msgid'), $original_values_query);

                $addons = Po::getValues($pack_path, 'Addons');
                $query = array();
                $original_values_query = array();

                if (!empty($addons)) {
                    foreach ($addons as $var_name => $var_data) {
                        if (!empty($var_name)) {
                            $value = implode('', $var_data['msgstr']);
                            $original_value = $var_data['msgid'];
                            $value = empty($value) ? $original_value : $value;

                            if ($var_data['parent'] == 'name') {
                                db_query('UPDATE ?:addon_descriptions SET name = ?s WHERE addon = ?s AND lang_code = ?s', trim($value), $var_data['id'], $lang_meta['lang_code']);
                            } else {
                                db_query('UPDATE ?:addon_descriptions SET description = ?s WHERE addon = ?s AND lang_code = ?s', trim($value), $var_data['id'], $lang_meta['lang_code']);
                            }

                            $original_values_query[] = db_quote('(?s, ?s)', trim($var_name), trim($original_value));
                        }
                    }

                    self::executeLangQueries('original_values', array('msgctxt', 'msgid'), $original_values_query);
                }

                $profile_fields = Po::getValues($pack_path, 'ProfileFields');
                $query = array();
                $original_values_query = array();

                foreach ($profile_fields as $var_name => $var_data) {
                    if (!empty($var_name)) {
                        $value = implode('', $var_data['msgstr']);
                        $original_value = $var_data['msgid'];
                        $value = empty($value) ? $original_value : $value;

                        $field_ids = db_get_fields('SELECT field_id FROM ?:profile_fields WHERE field_name = ?s', $var_data['id']);

                        if (empty($field_ids)) {
                            continue;
                        }

                        foreach ($field_ids as $field_id) {
                            $query[] = db_quote('(?i, ?s, ?s, ?s)', $field_id, trim($value), 'F', $lang_meta['lang_code']);
                        }
                        $original_values_query[] = db_quote('(?s, ?s)', trim($var_name), trim($original_value));
                    }
                }

                self::executeLangQueries('profile_field_descriptions', array('object_id', 'description', 'object_type', 'lang_code'), $query);
                self::executeLangQueries('original_values', array('msgctxt', 'msgid'), $original_values_query);

                if (!$params['reinstall']) {
                    fn_set_notification('N', __('notice'), __('text_changes_saved'));
                }
                $_suffix = '';
            }
        }

        return $lc;
    }

    /**
     * Inserts new data to the description tables.
     *
     * @param  string $table  Table name without prefix
     * @param  array  $fields List of table fields to be updated
     * @param  array  $data   New data
     * @return bool   db_query result
     */
    public static function executeLangQueries($table, $fields, $query)
    {
        if (empty($query)) {
            return false;
        }

        return db_query('REPLACE INTO ?:' . $table . ' (' . implode(',', $fields) . ') VALUES ' . implode(', ', $query));
    }

    public static function getOriginalValues($context)
    {
        return db_get_hash_array('SELECT * FROM ?:original_values WHERE msgctxt LIKE ?l', 'msgctxt', $context . ':%');
    }

    /**
     * Creates PO file for specified Language
     *
     * @param string $lang_code 2-letters language code (Example: "en", "ru")
     * @param string $output    Output destination
     *      screen - Output countent direct to browser page
     *      download - Force file downloading
     *      server - upload file to the config.dir.lang_packs directory
     * @param string $output_file Default name is $lang_code . '.po'
     */
    public static function createPoFile($lang_code, $output = 'download', $output_file = '')
    {
        // Translation packs should not include "Not translated" language data
        $allow_overlap = $lang_code == 'en' ? true : false;

        $filename = fn_create_temp_file();
        if (empty($output_file)) {
            $output_file = $lang_code . '.po';
        }

        $langs = self::get(array('lang_code' => $lang_code));
        $lang = $langs[$lang_code];

        Po::createHeader($filename, $lang);

        // Export Language values
        list($values) = LanguageValues::getVariables(array(), 0, $lang_code);
        $original_values = self::getOriginalValues('Languages');

        foreach ($values as $_id => $value) {
            $values[$_id]['original_value'] = isset($original_values['Languages::' . $value['name']]) ? $original_values['Languages::' . $value['name']]['msgid'] : '';
        }

        $values = Po::convert($values, array(), $allow_overlap);
        Po::putValues('Languages', $values, $filename);

        // Export "SettingsVariants"
        $values = Settings::instance()->getVariants('', '', '', 'all', $lang_code);
        $original_values = self::getOriginalValues('SettingsVariants');

        foreach ($values as $_id => $value) {
            $values[$_id]['original_value'] = isset($original_values['SettingsVariants::' . $_id]) ? $original_values['SettingsVariants::' . $_id]['msgid'] : '';
        }
        $values = Po::convert($values, array(
            'id' => '%key'
        ), $allow_overlap);
        Po::putValues('SettingsVariants', $values, $filename);

        // Export Settings Sections
        $values = Settings::instance()->getCoreSections($lang_code);
        $original_values = self::getOriginalValues('SettingsSections');

        foreach ($values as $_id => $value) {
            $values[$_id]['original_value'] = isset($original_values['SettingsSections::' . $value['section_id']]) ? $original_values['SettingsSections::' . $value['section_id']]['msgid'] : '';
        }
        $values = Po::convert($values, array(
            'id' => 'section_id',
            'value' => 'description'
        ), $allow_overlap);
        Po::putValues('SettingsSections', $values, $filename);

        // Export Settings Options
        $values = Settings::instance()->getList(0, 0, true, null, $lang_code);
        $original_values = self::getOriginalValues('SettingsOptions');

        foreach ($values as $_id => $value) {
            $values[$_id]['original_value'] = isset($original_values['SettingsOptions::' . $value['name']]) ? $original_values['SettingsOptions::' . $value['name']]['msgid'] : '';
        }
        $values = Po::convert($values, array(
            'id' => 'name',
            'value' => 'description'
        ), $allow_overlap);
        Po::putValues('SettingsOptions', $values, $filename);

        // Export Addons data (name, description)
        list($addons) = fn_get_addons(array('type' => 'installed'), 0, $lang_code);

        $values = array();
        foreach ($addons as $addon_id => $addon) {
            $values[] = array(
                'name' => $addon_id,
                'value' => $addon['name'],
                'original_value' => $addon['originals']['name'],
            );
        }

        $values = Po::convert($values, array(), $allow_overlap);
        Po::putValues('Addon', $values, $filename);

        $values = array();
        foreach ($addons as $addon_id => $addon) {
            $values[] = array(
                'name' => $addon_id,
                'value' => $addon['description'],
                'original_value' => $addon['originals']['description'],
            );
        }
        $values = Po::convert($values, array(), $allow_overlap);
        Po::putValues('AddonDescription', $values, $filename);

        // Export Profile fields
        $profile_fields = fn_get_profile_fields('ALL', array(), $lang_code);
        $original_values = self::getOriginalValues('ProfileFields');
        $values = array();

        foreach ($profile_fields as $zone => $fields) {
            foreach ($fields as $field_id => $field) {
                $values[] = array(
                    'name' => $field['field_name'],
                    'value' => $field['description'],
                    'original_value' => isset($original_values['ProfileFields::' . $field['field_name']]) ? $original_values['ProfileFields::' . $field['field_name']]['msgid'] : '',
                );
            }
        }

        $values = Po::convert($values, array(), $allow_overlap);
        Po::putValues('ProfileFields', $values, $filename);

        switch ($output) {
            case 'screen':
                header("Content-type: text/plain");
                readfile($filename);
                exit;
                break;

            case 'server':
                fn_copy($filename, Registry::get('config.dir.lang_packs') . $output_file);
                break;

            case 'download':
                fn_get_file($filename, $output_file);
                break;
        }
    }

    /**
     * Sets new default language for backend/frontend
     *
     * @param  string $lang_code 2-letters language code (en, ru, etc)
     * @return bool   Always true
     */
    public static function changeDefaultLanguage($lang_code)
    {
        Settings::instance()->updateValue('backend_default_language', $lang_code);
        Settings::instance()->updateValue('frontend_default_language', $lang_code);

        Registry::set('settings.Appearance.backend_default_language', $lang_code);
        Registry::set('settings.Appearance.frontend_default_language', $lang_code);

        return true;
    }

    public static function installCrowdinPack($path, $params)
    {
        $path = fn_remove_trailing_slash($path) . '/';

        if (file_exists($path . 'core.po') && is_dir($path . 'addons') && is_dir($path . 'editions')) {

            $lang_meta = self::getLangPacksMeta($path, 'core.po', true);

            if (empty($lang_meta['lang_code'])) {
                $result = false;
                fn_set_notification('E', __('error'), __('broken_po_pack'));

            } else {
                fn_copy($path, Registry::get('config.dir.lang_packs') . $lang_meta['lang_code'] . '/');
                $path = Registry::get('config.dir.lang_packs') . $lang_meta['lang_code'] . '/';

                $result = self::installLanguagePack($path . 'core.po', $params);
            }

            if ($result) {

                $po_files_list = array();

                if (fn_allowed_for('MULTIVENDOR') && file_exists($path . 'editions/mve.po')) {
                    $po_files_list[] = $path . 'editions/mve.po';
                }

                list($addons) = fn_get_addons(array('type' => 'installed'));

                foreach ($addons as $addon_id => $addon) {
                    if (file_exists($path . 'addons/' . $addon_id . '.po')) {
                        $po_files_list[] = $path . 'addons/' . $addon_id . '.po';
                    }
                }

                foreach ($po_files_list as $po_file) {
                    $result = self::installLanguagePack($po_file, array('reinstall' => true));

                    if (!$result) {
                        break;
                    }
                }
            }

        } else {
            fn_set_notification('E', __('error'), __('broken_po_pack'));

            return false;
        }

        return true;
    }

    /**
     * Installs new language from ZIP pack
     *
     * @param string $path   Path to ZIP file
     * @param array  $params
     *  reinstall: Skip adding new language
     *  validate_lang_code:Check meta information (lang_code) with updated language data (lang_code) and forbid to update if does not match
     *  force_lang_code: Skip meta lang code and use this one in all laguage variables
     * @return int Language ID
     */
    public static function installZipPack($path, $params = array())
    {
        $result = false;

        // Extract language pack and check the permissions
        $extract_path = Registry::get('config.dir.cache_misc') . 'tmp/language_pack/';

        // Re-create source folder
        fn_rm($extract_path);
        fn_mkdir($extract_path);

        fn_copy($path, $extract_path . 'pack.zip');

        if (fn_decompress_files($extract_path . 'pack.zip', $extract_path)) {
            fn_rm($extract_path . 'pack.zip');
            $result = self::installCrowdinPack($extract_path, $params);
        } else {
            fn_set_notification('E', __('error'), __('broken_po_pack'));
        }

        return $result;
    }

    /**
     * Removes new lines symbols and escapes quotes
     *
     * @param  string $value String to be escaped
     * @return string Escaped string
     */
    private static function _processPoValues($value)
    {
        $value = addslashes($value);
        $value = str_replace(array("\r\n", "\n", "\r"), '', $value);

        return trim($value);
    }

    private static function _checkFreeAvailability($languages, $remove_disabled = false)
    {
        if (fn_allowed_for('ULTIMATE:FREE')) {
            $default_language = Registry::get('settings.Appearance.' . fn_get_area_name(AREA) . '_default_language');

            foreach ($languages as $index => $language) {
                $lang_code = is_array($language) && isset($language['lang_code']) ? $language['lang_code'] : $index;
                if ($default_language != $lang_code) {
                    if ($remove_disabled) {
                        unset($languages[$index]);
                    } else {
                        $languages[$index]['status'] = 'D';
                    }
                } else {
                    if (!$remove_disabled) {
                        $languages[$index]['status'] = 'A';
                    }
                }
            }
        }

        return $languages;
    }
}
