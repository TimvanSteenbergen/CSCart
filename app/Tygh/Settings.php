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

namespace Tygh;

use Tygh\Registry;

/**
 *
 * Settings manager
 *
 */
class Settings
{
    /**
     * Instance of class
     * @static
     * @var Settings
     */
    private static $_instance;

    /**
     *
     * Section data array
     * @var array
     */
    private $_sections;

    /**
     * Name of current edition
     * @var string
     */
    private $_current_edition;

    /**
     * Root mode flag, ignores company ID
     */
    private $_root_mode = false;

    /**
     * Loads sections data and settings schema from DB
     */
    private function __construct()
    {
        $this->reloadSections();
    }

     /**
      * Reloads some sections data into internal storage
      *
      * @return bool Always true
      */
     public function reloadSections()
     {
         $this->_sections = $this->_getSections('', 0, true, false);

         return true;
     }

    /**
     * Returns static object of Settings class or create it if it is not exists.
     *
     * @return Settings Instance of class
     */
    public static function instance($company_id = null)
    {
        if (empty(self::$_instance)) {
            self::$_instance = new Settings();
        }

        if (!is_null($company_id)) {
            self::$_instance->_root_mode = false;

        } elseif (Registry::get('runtime.simple_ultimate')) {
            self::$_instance->_root_mode = true;
        }

        return self::$_instance;
    }

    /**
     * Constants defines sections
     *
     * @var string
     */
    const CORE_SECTION  = 'CORE';
    const ADDON_SECTION = 'ADDON';
    const TAB_SECTION   = 'TAB';
    const SEPARATE_TAB_SECTION = 'SEPARATE_TAB';

    /**
     * Prefixes defines
     *
     * @var string
     */
    const NONE   = 'NONE';
    const ROOT   = 'ROOT';
    const VENDOR = 'VENDOR';
    const VENDORONLY = 'VENDORONLY';

    /**
     * Object types for settings descriptions
     *
     * @var string
     */
    const VARIANT_DESCRIPTION = 'V';
    const SETTING_DESCRIPTION = 'O';
    const SECTION_DESCRIPTION = 'S';

    /**
     * Sets new edition for correct reinstalling addons settings after edition upgrade.
     *
     * @param string $edition Full edition name (new value of const PRODUCT_EDITION)
     */
    public function setNewEdition($edition)
    {
        $this->_current_edition = strtoupper(fn_get_edition_acronym($edition));
    }

    /**
     * Returns current edtition acronym
     *
     * @return string Edtition acronym
     */
    private function _getCurrentEdition()
    {
        if (empty($this->_current_edition)) {
            $this->_current_edition = strtoupper(fn_get_edition_acronym(PRODUCT_EDITION));
        }

        return $this->_current_edition . ':';
    }

    /**
     * Returns true if array $sections have item with key $section_name
     *
     * @param  array  $sections     List of sections
     * @param  string $section_name Section name to find in sections list
     * @return bool   True if section exists, false otherwise
     */
    public function sectionExists($sections, $section_name)
    {
        foreach ($sections as $section) {
            if ($section['name'] == $section_name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if specified section have visible options
     *
     * @param  string $section_name Section name
     * @param  string $section_type Section type
     * @return bool   True if visible options exists, false otherwise
     */
    public function optionsExists($section_name, $section_type)
    {
        $section_data = $this->getSectionByName($section_name, $section_type);
        $options = $this->_get_list(array('?:settings_objects.object_id'), $section_data['section_id'], '', false, ' AND ?:settings_objects.type <> "D"');

        return !empty($options);
    }

    /**
     * Gets all core setting sections
     *
     * @param  string $lang_code 2-letters Language identifier
     * @return array  List of setting sections
     */
    public function getCoreSections($lang_code = CART_LANGUAGE)
    {
        $_sections = $this->_getSections(Settings::CORE_SECTION, 0, true, true, $lang_code);
        $sections = Array();

        foreach ($_sections as &$section) {
            $sections[$section['name']] = $section;
            if (isset($section['name'])) {
                $sections[$section['name']]['section_id'] = $section['name'];
            }
            $sections[$section['name']]['object_type'] = 'S';
            if (isset($section['description'])) {
                $sections[$section['name']]['title'] =  $section['description'];
            }
            unset ($sections[$section['name']]['name']);
        }

        ksort($sections);

        return $sections;
    }

    /**
     * Gets all addon setting sections
     *
     * @return array List of setting sections
     */
    public function getAddons()
    {
        return $this->_getSections(Settings :: ADDON_SECTION);
    }

    /**
     * Gets setting section tabs
     *
     * @param  int    $parent_section_id Parent section identifier
     * @param  string $lang_code         2 letters language code
     * @return array  List of tab sections
     */
    public function getSectionTabs($parent_section_id, $lang_code = CART_LANGUAGE)
    {
        fn_get_schema('settings', 'actions.functions', 'php', true);

        $_tabs = $this->_getSections(array(Settings::TAB_SECTION, Settings::SEPARATE_TAB_SECTION), $parent_section_id, false, true, $lang_code);
        $tabs = Array();

        foreach ($_tabs as $tab) {
            if (isset($this->_sections[$parent_section_id]['name'])) {
                $func_name = 'fn_is_tab_' . fn_strtolower($this->_sections[$parent_section_id]['name']) . '_' . $tab['name'] . '_available';

                if (function_exists($func_name) && $func_name() === false) {
                    continue;
                }
            }

            $tabs[$tab['name']] = $tab;
            $tabs[$tab['name']]['parent_id'] = $parent_section_id;
        }

        return $tabs;
    }

    /**
     * Gets section data by name and type
     *
     * @param  string $name             Section name
     * @param  string $type             Type of section. Use Settings class constant to set this value
     * @param  bool   $use_access_level Use or ignore edition and type access conditions (ROOT, VENDOR, etc...)
     * @return array  Section data
     */
    public function getSectionByName($name, $type = Settings::CORE_SECTION, $use_access_level = true)
    {
        return db_get_row(
            "SELECT * FROM ?:settings_sections "
            . "WHERE name = ?s AND type = ?s ?p",
            $name, $type, $this->_generateEditionCondition('?:settings_sections', $use_access_level)
        );
    }

    /**
     * Gets translated section name by id
     *
     * @param  int    $section_id Section identifier
     * @param  string $lang_code  2 letters language code
     * @return string Section name
     */
    public function getSectionName($section_id, $lang_code = CART_LANGUAGE)
    {
        return db_get_field(
            "SELECT ?:settings_descriptions.value FROM ?:settings_sections "
            . "LEFT JOIN ?:settings_descriptions "
                ."ON ?:settings_descriptions.object_id = ?:settings_sections.section_id AND object_type = ?s "
            . "WHERE section_id = ?i AND ?:settings_descriptions.lang_code = ?s",
            Settings::SECTION_DESCRIPTION, $section_id, $lang_code
        );
    }

    /**
     * Gets internal section name by section id
     *
     * @param  string $section_id Section identifier
     * @return string Section name
     */
    public function getSectionTextId($section_id)
    {
        return $this->_sections[$section_id]['name'];
    }

    /**
     * Returns sections
     *
     * @param  mixed  $section_type     Section type (one or several sections can be passed as string or array). Use constants of Settings class to set this value.
     * @param  int    $parent_id        Id of parent section
     * @param  bool   $generate_href    Generate href to core section // FIXME: Bad style
     * @param  bool   $use_access_level Use or ignore edition and type access conditions (ROOT, MSE:VENDOR, etc...)
     * @param  string $lang_code        2 letters language code
     * @return array  List of sections
     */
    private function _getSections($section_type = '', $parent_id = 0, $generate_href = true, $use_access_level = true, $lang_code = '')
    {
        $condition = $this->_generateEditionCondition('?:settings_sections', $use_access_level);
        $values = '';
        $join = '';

        if ($parent_id != 0) {
            $condition .= db_quote(" AND ?:settings_sections.parent_id = ?i", $parent_id);
        }
        if (!empty($section_type)) {
            $section_type = is_array($section_type) ? $section_type : array($section_type);
            $condition .= db_quote(" AND ?:settings_sections.type IN (?a)", $section_type);
        }

        if (!empty($lang_code)) {
            $join =  db_quote(
                " LEFT JOIN ?:settings_descriptions "
                    . "ON ?:settings_descriptions.object_id = ?:settings_sections.section_id "
                        . "AND object_type = ?s AND ?:settings_descriptions.lang_code = ?s",
                Settings::SECTION_DESCRIPTION, $lang_code
            );
            $values .= ', ?:settings_descriptions.value as description, object_id, object_type';
        } else {
            $values .= ', ?:settings_sections.name as description';
        }

        //TODO: Fix generating link for core sections
        if ($generate_href) {
            $values .= ', CONCAT(\'settings.manage?section_id=\', ?:settings_sections.name) as href ';
        }

        $sections = db_get_hash_array(
            'SELECT ?:settings_sections.name, ?:settings_sections.section_id, '
            . '?:settings_sections.position, ?:settings_sections.type ?p '
            . 'FROM ?:settings_sections ?p'
            . "WHERE 1 ?p ORDER BY ?:settings_sections.position",
            'section_id',
            $values,
            $join,
            $condition
        );

        return $sections;
    }

    /**
     * Updates settings section
     *
     * Section data must be array in this format (example):
     * Array (
     *      'section_id'   => 1,
     *      'parent_id'    => 3,
     *      'edition_type' => 'ROOT,VENDOR',
     *      'name'         => 'Appearance',
     *      'position'     => 10,
     *      'type'         => 'CORE',
     * );
     *
     * If some parameter will be skipped and function not update it field.
     * If section_id skipped function adds new variant and retuns id of new record.
     *
     * @param  string   $section_data Aray of section data
     * @return bool|int Section identifier if section was created, true un success update, false otherwise
     */
    public function updateSection($section_data)
    {
        if (!$this->_checkEdition($section_data)) {
            return false;
        }

        $section_id = db_replace_into ('settings_sections', $section_data);
        $this->_sections = $this->_getSections();

        return $section_id;
    }

    /**
     * Removes setting section.
     *
     * @param  int  $section_id Section identifier
     * @return bool true or false if $name or $lang_code or value is empty
     */
    public function removeSection($section_id)
    {
        if (!empty($section_id)) {
            $sections = db_get_fields("SELECT section_id FROM ?:settings_sections WHERE section_id = ?i OR parent_id = ?i", $section_id, $section_id);
            if (!empty($sections)) {
                db_query("DELETE FROM ?:settings_sections WHERE section_id IN (?n)", $sections);
                db_query("DELETE FROM ?:settings_descriptions WHERE object_id IN (?n) AND object_type = ?s", $sections, Settings::SECTION_DESCRIPTION);

                $setting_ids = db_get_fields("SELECT object_id FROM ?:settings_objects WHERE section_id IN (?n)", $sections);
                if (!empty($setting_ids)) {
                    db_query("DELETE FROM ?:settings_descriptions WHERE object_id IN (?n) AND object_type = ?s", $setting_ids, Settings::SETTING_DESCRIPTION);

                    $variant_ids =  db_get_fields("SELECT variant_id FROM ?:settings_variants WHERE object_id IN (?n)",$setting_ids);
                    if (!empty($variant_ids)) {
                        db_query("DELETE FROM ?:settings_variants WHERE object_id IN (?n)", $setting_ids);
                        db_query("DELETE FROM ?:settings_descriptions WHERE object_id IN (?n) AND object_type = ?s", $variant_ids, Settings::VARIANT_DESCRIPTION);
                    }

                    if (fn_allowed_for('ULTIMATE')) {
                        db_query("DELETE FROM ?:settings_vendor_values WHERE object_id IN (?n)", $setting_ids);
                    }

                    db_query("DELETE FROM ?:settings_objects WHERE object_id IN (?n)", $setting_ids);
                }

                $this->_sections = $this->_getSections();
            }
        } else {
            $this->_generateError(__('unable_to_delete_setting_description'), __('empty_key_value'));

            return false;
        }

        return true;
    }

    /**
     * Gets list of settings including all information
     *
     * @param  int     $section_id     Section identifier
     * @param  int     $section_tab_id Section tab identifier
     * @param  boolean $plain_list     Get list without division into sections
     * @param  int     $company_id     Company identifier
     * @param  string  $lang_code      2 letters language code
     * @return array   List of settings
     */
    public function getList($section_id = 0, $section_tab_id = 0, $plain_list = false, $company_id = null, $lang_code = CART_LANGUAGE)
    {
        $settings = array();

        $edition_condition = $this->_generateEditionCondition('?:settings_objects', true);

        $_settings = $this->_get_list(
            array(
                '?:settings_objects.object_id as object_id',
                '?:settings_objects.name as name',
                'section_id',
                'section_tab_id',
                'type',
                'edition_type',
                'position',
                'is_global',
                'handler',
                'parent_id'
            ),
            $section_id, $section_tab_id, false, $edition_condition, false, $company_id, $lang_code
        );

        if ($plain_list) {
            $settings = $_settings;

        } else {

            foreach ($_settings as $setting) {

                $setting = $this->_processSettingData($setting, $lang_code);

                if (($section_tab_id != 0) && ($section_id != 0)) {
                    $settings[$setting['object_id']] = $setting;
                } elseif (($section_id != 0)) {
                    $settings[$setting['section_tab_name']][$setting['object_id']] = $setting;
                } else {
                    $settings[$setting['section_name']][$setting['section_tab_name']][$setting['object_id']] = $setting;
                }
            }
        }

        return $settings;
    }

    /**
     * Gets all information of requested setting by name
     *
     * @param  string $setting_name Name of setting (Example: 'orders_per_page')
     * @param  int    $company_id   Company identifier
     * @param  string $lang_code    2 letters language code
     * @return array  Setting information
     */
    public function getSettingDataByName($setting_name, $company_id = null, $lang_code = CART_LANGUAGE)
    {
        $edition_condition = $this->_generateEditionCondition('?:settings_objects', true);

        $condition = db_quote(' AND name = ?s', $setting_name);

        $_setting = $this->_get_list(
            array(
                '?:settings_objects.object_id as object_id',
                '?:settings_objects.name as name',
                'section_id',
                'section_tab_id',
                'type',
                'edition_type',
                'position',
                'is_global',
                'handler',
                'parent_id'
            ),
            '', '', false, $condition, false, $company_id, $lang_code
        );

        if (!isset($_setting[0])) {
            return false;
        }

        $_setting = $_setting[0];
        $_setting = $this->_processSettingData($_setting, $lang_code);

        return $_setting;
    }

    /**
     * Gets additional setting data, such as section name, setting variants, value in correct format, etc.
     * Execute handler functions for this setting
     *
     * @param array $setting Setting data (example):
     *
     * Array (
     *      'handler' => '',
     *      'section_id' => '5',
     *      'section_tab_id' => '0',
     *      'edition_type' => 'ROOT,ULT:VENDOR',
     *      'name' => 'secure_connection',
     *      'object_id' => '34',
     *      'type' => 'C',
     *      'value' => 'Y',
     * )
     *
     * @return array Prepared setting data
     */
    private function _processSettingData($setting, $lang_code)
    {
        $_sections = $this->_sections;

        // Execute custom function for generate info from handler if it exists
        if (!empty($setting['handler'])) {
            $args = explode(',', $setting['handler']);
            $func = array_shift($args);
            if (function_exists($func)) {
                $setting['info'] = call_user_func_array($func, $args);
            } else {
                $setting['info'] = "Something goes wrong";
            }
        }

        if (isset($_sections[$setting['section_id']])) {
            $setting['section_name'] = ($setting['section_id'] == 0 && !isset($_sections[$setting['section_id']])) ? 'General' : $_sections[$setting['section_id']]['name'];
            $setting['section_tab_name'] = ($setting['section_tab_id'] == 0 && !isset($_sections[$setting['section_tab_id']])) ? 'main' : $_sections[$setting['section_tab_id']]['name'];
        } else {
            $setting['section_name'] = $setting['section_tab_name'] = '';
        }

        // Check if this options may be updated for all vendors
        $edition_type = explode(',', $setting['edition_type']);
        if (fn_allowed_for('ULTIMATE') && !$this->_getCompanyId() && (in_array('ULT:VENDOR', $edition_type) || in_array('VENDOR', $edition_type))) {
            $setting['update_for_all'] = true;
        }

        $setting['variants'] = $this->getVariants($setting['section_name'], $setting['name'], $setting['section_tab_name'], $setting['object_id'], $lang_code);

        $force_parse = $setting['type'] == 'N' ? true : false;
        $setting['value'] = $this->_unserialize($setting['value'], $force_parse);

        return $setting;
    }

    /**
     * Gets settings values from db applying all permission and edition filters
     *
     * @param  string     $section_name Section name
     * @param  string     $section_type Section type. Use CSettings class constants
     * @param  bool       $hierarchy    If it's false settings will be returned as plain list
     * @param  int        $company_id   Company identifier
     * @return array|bool List of settings values on success, false otherwise
     */
    public function getValues($section_name = '', $section_type = Settings::CORE_SECTION, $hierarchy = true, $company_id = null)
    {
        $settings = array();

        $section_id = '';
        $section_tab_id = '';

        if ($section_name) {
            $section = $this->getSectionByName($section_name, $section_type, false);

            if (!isset($section['section_id'])) {
                return false;
            }

            if ($section['parent_id'] != 0) {
                $section_id = $section['parent_id'];
                $section_tab_id = $section['section_id'];
            } else {
                $section_id = $section['section_id'];
            }
        }
        $_result = $this->_get_list(
            array(
                '?:settings_objects.object_id as object_id',
                'name',
                'section_id',
                'section_tab_id',
                'type',
                'position',
                'is_global'
            ), $section_id, $section_tab_id, true, false, false, $company_id
        );

        $_sections = $this->_sections;

        if ($_result) {
            foreach ($_result as $_row) {
                $section_name = ($_row['section_id'] != 0 && isset($_sections[$_row['section_id']])) ? $_sections[$_row['section_id']]['name'] : '';
                $section_tab_name = ($_row['section_tab_id'] != 0 && isset($_sections[$_row['section_tab_id']])) ? $_sections[$_row['section_tab_id']]['name'] : '';

                $force_parse = $_row['type'] == 'N' ? true : false;
                if (!empty($_row['section_tab_id']) && $hierarchy) {
                    $settings[$section_name][$section_tab_name][$_row['name']] = $this->_unserialize($_row['value'], $force_parse);
                } elseif (!empty($_row['section_id']) && $hierarchy) {
                    $settings[$section_name][$_row['name']] = $this->_unserialize($_row['value'], $force_parse);
                } else {
                    $settings[$_row['name']] = $this->_unserialize($_row['value'], $force_parse);
                }
            }

            if (empty($section_id) || !$hierarchy) {
                return $settings;
            } elseif (!empty($section_id) && empty($section_tab_id)) {
                return $settings[$section_name];
            } elseif (!empty($section_tab_id)) {
                return $settings[$section_id][$section_tab_id];
            }
        }

        return $settings;
    }

    /**
     * Gets setting value from database
     *
     * @param string $setting_name Setting name
     * @param string $section_name Section name
     * @param int    $company_id   Company identifier
     *
     * @return mixed|bool Setting value on success, false otherwise
     */
    public function getValue($setting_name, $section_name, $company_id = null)
    {
        if (!empty($setting_name)) {
            $id = $this->getId($setting_name, $section_name);
            $condition = db_quote(' AND ?:settings_objects.object_id = ?i', $id);
            $_setting = $this->_get_list(
                array('?:settings_objects.object_id as object_id', '?:settings_objects.type as object_type'),
                '', '', false, $condition, false, $company_id
            );

            if (isset($_setting[0]['value'])) {
                $force_parse = $_setting[0]['object_type'] == 'N' ? true : false;
                $value = $this->_unserialize($_setting[0]['value'], $force_parse);
            } else {
                return false;
            }

            return $value;
        } else {
            return false;
        }
    }

    /**
     * Gets setting data for setting id
     *
     * @param  int        $object_id  Setting object identifier
     * @param  int        $company_id Company identifier
     * @return array|bool Setting data on success, false otherwise
     */
    public function getData($object_id, $company_id = null)
    {
        if (!empty($object_id)) {
            $condition = db_quote(' AND ?:settings_objects.object_id = ?i', $object_id);
            $_setting = $this->_get_list(
                array(
                    '?:settings_objects.object_id as object_id',
                    'section_id',
                    'section_tab_id',
                    'name'
                ),
                '', '', false, $condition, false, $company_id
            );

            if (!isset($_setting[0])) {
                return false;
            }
            $_setting = $_setting[0];
            $_setting['section_id'] = ($_setting['section_id'] == 0) ? 'General' : $this->_sections[$_setting['section_id']]['name'];
            $_setting['section_tab_id'] = ($_setting['section_tab_id'] == 0) ? 'main' : $this->_sections[$_setting['section_tab_id']]['name'];

            return $_setting;
        } else {
            return false;
        }
    }

    /**
     * Gets setting id for section name and setting name
     *
     * @param  string   $section_name Setting name
     * @param  string   $setting_name Section name
     * @return int|bool Setting ID or false if $section_name or $setting_name are empty
     */
    public function getId($setting_name, $section_name = '')
    {
        if (!empty($setting_name)) {
            if (!empty($section_name)) {
                $section_condition = db_quote(" AND ?:settings_sections.name = ?s", $section_name);
            } else {
                $section_condition = '';
            }

            return db_get_field(
                "SELECT object_id FROM ?:settings_objects "
                . "LEFT JOIN ?:settings_sections ON ?:settings_objects.section_id = ?:settings_sections.section_id "
                . "WHERE ?:settings_objects.name = ?s ?p",
                $setting_name,
                $section_condition
            );
        } else {
            return false;
        }
    }

    /**
     * Updates all setting paramentrs include descriptions and variants.
     *
     * @param  array $setting_data        List of setting data @see CSettings::_update
     * @param  array $variants            List of variants data to update with seting @see CSettings::updateVariant
     * @param  array $descriptions        List of descriptions data to update with seting @see CSettings::updateDescription description type will be setted automaticly
     * @param  bool  $force_cache_cleanup Force registry cleanup after setting was updated
     * @return int   Setting identifier if it was created, true un success update, false otherwise
     */
    public function update($setting_data, $variants = null, $descriptions = null, $force_cache_cleanup = false)
    {
        $id = $this->_update($setting_data);

        if (!empty($id)) {
            if (is_array($variants)) {
                foreach ($variants as $variant_data) {
                    $variant_data['object_id'] = $id;
                    $this->updateVariant($variant_data);
                }
            }

            if (is_array($descriptions)) {
                foreach ($descriptions as $description_data) {
                    $description_data['object_id'] = $id;

                    $this->updateDescription($description_data);
                }
            }
        }

        if ($force_cache_cleanup) {
            Registry::cleanup();
        }

        return $id;
    }

    /**
     * Updates setting
     * Settings data must be array in this format (example):
     *
     * Array (
     *      'object_id' =>      2,
     *      'name' =>           'use_shipments',
     *      'section_id' =>     2,
     *      'section_tab_id' => 0,
     *      'type' =>           'C',
     *      'position' =>       55,
     *      'is_global' =>      'Y'
     * )
     *
     * If some parameter will be skipped and function not update it field.
     * If object_id skipped function adds new setting and retuns id of new record.
     *
     * For update setting value please use specific functions
     *
     * @param  array $setting_data Array of setting fields
     * @return int   Setting identifier if section was created, true un success update, false otherwise
     */
    private function _update($setting_data)
    {
        if (!$this->_checkEdition($setting_data)) {
            return false;
        }

        $data = $setting_data;

        // Delete value if exist
        if (!empty($data['value'])) {
            unset($data['value']);
        }

        $object_id = db_replace_into('settings_objects', $data);

        return $object_id;
    }

    /**
     * Updates value of setting by section name and setting name
     *
     * @param  string $section_name        Section name
     * @param  string $setting_name        Setting name
     * @param  string $setting_value       Setting value
     * @param  bool   $force_cache_cleanup Force registry cleanup after setting was updated
     * @param  int    $company_id          Company identifier
     * @return bool   Always true
     */
    public function updateValue($setting_name, $setting_value, $section_name = '', $force_cache_cleanup = false, $company_id = null, $execute_functions = true)
    {
        if (!empty($setting_name)) {
            $object_id = $this->getId($setting_name, $section_name);
            $this->updateValueById($object_id, $setting_value, $company_id, $execute_functions);

            if ($force_cache_cleanup) {
                Registry::cleanup();
            }
        }

        return true;
    }

    /**
     * Updates setting value. If $value and $object_id is empty function return false and generate error notification.
     *
     * @param  int    $object_id  Setting identifier
     * @param  string $value      New value
     * @param  string $company_id Company identifier
     * @return bool   True on success, false otherwise
     */
    public function updateValueById($object_id, $value, $company_id = null, $execute_functions = true)
    {
        if (!empty($object_id)) {
            fn_get_schema('settings', 'actions.functions', 'php', true);

            $value = $this->_serialize($value);

            $edition_types = db_get_field('SELECT edition_type FROM ?:settings_objects WHERE object_id = ?i', $object_id);

            $table = "";
            $data = array(
                'object_id' => $object_id,
                'value'     => $value,
            );

            if (fn_allowed_for('ULTIMATE') && $this->_getCompanyId($company_id)) {
                $need_edition_types = array(
                    Settings::VENDOR,
                    $this->_getCurrentEdition() . Settings::VENDOR,
                    $this->_getCurrentEdition() . Settings::VENDORONLY,
                );
                if (array_intersect($need_edition_types, explode(',', $edition_types))) {
                    $table = "settings_vendor_values";
                    $data['company_id'] = $this->_getCompanyId($company_id);
                }
            } else {
                if (strpos($edition_types, $this->_getCurrentEdition() . Settings::NONE) === false) {
                    $table = "settings_objects";
                }
            }

            if (!empty($table)) {
                $old_data = $this->getData($object_id, $company_id);

                // Value types should be converted to the same one to compare
                if (!is_array($old_data['value'])) {
                  $old_data['value'] = (string) $old_data['value'];
                }

                if (!is_array($value)) {
                  $value = (string) $value;
                }

                // If option value was changed execute user function if it exists
                if (isset($old_data['value']) && $old_data['value'] !== $value && $execute_functions) {
                    $core_func_name = 'fn_settings_actions_' . fn_strtolower($old_data['section_id']) . '_' . (!empty($old_data['section_tab_id']) && $old_data['section_tab_id'] != 'main' ? $old_data['section_tab_id'] . '_' : '') . $old_data['name'];
                    if (function_exists($core_func_name)) {
                        $core_func_name($data['value'], $old_data['value']);
                    }

                    $addon_func_name  = 'fn_settings_actions_addons_'  . fn_strtolower($old_data['section_id']) . '_' . fn_strtolower($old_data['name']);
                    if (function_exists($addon_func_name)) {
                        $addon_func_name($data['value'], $old_data['value']);
                    }
                }

                db_replace_into($table, $data);

            } else {
                $message = __('unable_to_update_setting_value') . ' (' . $object_id . ')';
                $this->_generateError($message, __('you_have_no_permissions'));

                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * Check if setting exists
     *
     * @param  string $section_name Setting name
     * @param  string $setting_name Section name
     * @return bool   True if setting exists, false otherwise
     */
    public function isExists($setting_name, $section_name = '')
    {
        return ($this->getId($setting_name, $section_name) === null) ? false : true;
    }

    /**
     * Removes setting and all related data
     *
     * @param  string $section_name Setting name
     * @param  string $setting_name Section name
     * @return bool   Always true
     */
    public function remove($setting_name, $section_name = '')
    {
        return $this->removeById($this->getId($setting_name, $section_name));
    }

    /**
     * Removes setting and all related data by id
     *
     * @param  int  $setting_id Setting identifier
     * @return bool Always true
     */
    public function removeById($setting_id)
    {
        db_query('DELETE FROM ?:settings_objects WHERE object_id = ?i', $setting_id);
        $this->removeDescription($setting_id, Settings::SETTING_DESCRIPTION);
        $this->removeSettingVariants($setting_id);

        if (fn_allowed_for('ULTIMATE')) {
            $this->resetAllVendorsSettings($setting_id);
        }

        return true;
    }

    /**
     * Removes all settings values for vendor
     *
     * @param  int  $company_id Company identifier
     * @return bool Always true
     */
    public function removeVendorSettings($company_id)
    {
        return db_query('DELETE FROM ?:settings_vendor_values WHERE company_id = ?i', $company_id);
    }

    /**
     * Removes all vendors values of setting
     *
     * @param  int  $object_id Setting object identifier
     * @return bool Always true
     */
    public function resetAllVendorsSettings($object_id)
    {
        return db_query('DELETE FROM ?:settings_vendor_values WHERE object_id = ?i', $object_id);
    }

    /**
     * Gets setting value for all vendors
     *
     * @param string $setting_name Setting name
     * @param string $section_name Section name
     *
     * @return array|bool Array of setting values with company_ids as keys on success, false otherwise
     */
    public function getAllVendorsValues($setting_name, $section_name = '')
    {
        if (fn_allowed_for('ULTIMATE') && !empty($setting_name)) {
            $settings = array();

            $fields = array(
                '?:companies.company_id',
                '?:settings_objects.object_id as object_id',
                '?:settings_objects.type as object_type',
                'IF(?:settings_vendor_values.value IS NULL, ?:settings_objects.value, ?:settings_vendor_values.value) as value'
            );

            $id = $this->getId($setting_name, $section_name);

            $join = db_quote('LEFT JOIN ?:settings_objects ON ?:settings_objects.object_id = ?i', $id);
            $join .= ' LEFT JOIN ?:settings_vendor_values ON ?:settings_vendor_values.object_id = ?:settings_objects.object_id AND ?:settings_vendor_values.company_id = ?:companies.company_id';

            $group = ' GROUP BY ?:companies.company_id';

            $fields = implode(', ', $fields);
            $settings = db_get_hash_single_array("SELECT ?p FROM ?:companies ?p WHERE 1 ?p ORDER BY ?:companies.company", array('company_id', 'value'), $fields, $join, $group);

            return $settings;
        } else {
            return false;
        }
    }

    /**
     * Function retuns variants for setting objects
     *
     * Usage (examples):
     *  // Addons
     *  Settings::instance->get_variants('affiliate', 'payment_period');
     *
     *  // Core same as addons but if $section_tab_name is empty it will be setted to 'main'
     *  Settings::instance->get_variants('general', 'feedback_type');
     *
     *  // Return variants only by setting id, but function not check custom variant functions
     *  Settings::instance->get_variants('', '', '', 40);
     *
     *  // Return variants only by setting id, and checks custom variant functions
     *  Settings::instance->get_variants('affiliate', 'payment_period', '', 40);
     *
     * @param  string $section_name     Setting name
     * @param  string $setting_name     Section name
     * @param  string $section_tab_name Section tab name
     * @param  int    $object_id        Id of setting in setting_objects table
     * @param  string $lang_code        2 letters language code
     * @return array  Array of variants or empty array if this setting have no variants
     */
    public function getVariants($section_name, $setting_name, $section_tab_name = '', $object_id = null, $lang_code = CART_LANGUAGE)
    {
        fn_get_schema('settings', 'variants.functions', 'php', true);

        $variants = array();

        // Generate custom variants
        $addon_variant_func = 'fn_settings_variants_addons_'  . fn_strtolower($section_name) . '_' . fn_strtolower($setting_name);

        $core_variant_func = (
            'fn_settings_variants_'
            . fn_strtolower($section_name) . '_'
            . ($section_tab_name != 'main' ? fn_strtolower($section_tab_name) . '_' : '')
            . fn_strtolower($setting_name)
        );

        if (function_exists($addon_variant_func)) {
            $variants = $addon_variant_func();
        } elseif (function_exists($core_variant_func)) {
            $variants = $core_variant_func();
        } else {
            // If object id is 0 try to get it from section name and setting name
            if ($object_id === null || $object_id === 0) {
                $object_id = $this->getId($setting_name, $section_name);
            }

            if (($object_id !== null && $object_id !== 0) || $object_id == 'all') {
                if ($object_id == 'all') {
                    $object_condition = '';
                } else {
                    $object_condition = db_quote('?:settings_variants.object_id = ?i AND', $object_id);
                }
                $_variants = db_get_array(
                    "SELECT ?:settings_variants.*, ?:settings_descriptions.value, ?:settings_descriptions.object_type "
                    . "FROM ?:settings_variants "
                        . "INNER JOIN ?:settings_descriptions "
                            ."ON ?:settings_descriptions.object_id = ?:settings_variants.variant_id AND object_type = ?s "
                    . "WHERE ?p ?:settings_descriptions.lang_code = ?s ORDER BY ?:settings_variants.position"
                    , Settings::VARIANT_DESCRIPTION, $object_condition, $lang_code
                );

                fn_update_lang_objects('variants', $_variants);

                foreach ($_variants as $variant) {
                    if ($object_id == 'all') {
                        $variants[$variant['name']] = array(
                            'value' => $variant['value'],
                        );
                    } else {
                        $variants[$variant['name']] = $variant['value'];
                    }
                }
            } else {
                if (Debugger::isActive() || defined('DEVELOPMENT')) {
                    $message = str_replace("[option_id]", $setting_name, __('setting_has_no_variants'));
                    fn_set_notification('E', __('error'), $message);
                }

                return $variants;
            }
        }

        return $variants;
    }


    public function getVariant($section_name, $setting_name, $variant_name, $lang_code = CART_LANGUAGE)
    {
        $object_id = $this->getId($setting_name, $section_name);
        $object_condition = db_quote('?:settings_variants.object_id = ?i AND', $object_id);

        $variant = db_get_row(
            "SELECT ?:settings_variants.*, ?:settings_descriptions.value, ?:settings_descriptions.object_type "
            . "FROM ?:settings_variants "
                . "LEFT JOIN ?:settings_descriptions "
                    . "ON ?:settings_descriptions.object_id = ?:settings_variants.variant_id AND object_type = ?s AND ?:settings_descriptions.lang_code = ?s"
            . "WHERE ?p ?:settings_variants.name = BINARY ?s"
            , Settings::VARIANT_DESCRIPTION, $lang_code, $object_condition, $variant_name
        );

        return $variant;
    }

    /**
     * Updates variant of setting.
     *
     * Variant data must be array in this format (example):
     * Array (
     *      'variant_id' => 1
     *      'object_id'  => 3,
     *      'name'       => 'hide',
     *      'position'   => 10,
     * );
     *
     * If some parameter will be skipped and function not update it field.
     * If variant_id skipped function adds new variant and retuns id of new record.
     *
     * @param  string   $variant_data Aray of variant data
     * @return bool|int Variant identifier if variant was created, true un success update, false otherwise
     */
    public function updateVariant($variant_data)
    {
        return db_replace_into ('settings_variants', $variant_data);
    }

    /**
     * Removes variant by id
     * If $variant_id is empty function return false and generate error notification.
     *
     * @param  int  $variant_id Variant identifier
     * @return bool true or false if $variant_id or value is empty
     */
    public function removeVariant($variant_id)
    {
        if (!(empty($variant_id))) {
            db_query("DELETE FROM ?:settings_variants WHERE variant_id = ?i", $variant_id);
            $this->removeDescription($variant_id, Settings::VARIANT_DESCRIPTION);
        } else {
            $this->_generateError(__('unable_to_delete_setting_variant'), __('empty_key_value'));

            return false;
        }

        return true;
    }

    /**
     * Removes all setting variants
     *
     * @param  string $setting_id Setting identifier
     * @return bool   true or false if $setting_id is empty
     */
    public function removeSettingVariants($setting_id)
    {
        if (!(empty($setting_id))) {
            $variants = db_get_fields("SELECT variant_id FROM ?:settings_variants WHERE object_id = ?i", $setting_id);

            foreach ($variants as $variant_id) {
                $this->removeVariant($variant_id);
            }
        } else {
            $this->_generateError(__('unable_to_delete_setting_variant'), __('empty_key_value'));

            return false;
        }

        return true;
    }

    /**
     * Get setting description
     *
     * @param  int               $object_id   Identifier of object that has description
     * @param  string            $object_type Type of object (Use CSettings *_DESCRIPTION constants)
     * @param  string            $lang_code   @ letters language code
     * @return array|bool|string Setting ID or false if $section_name or $setting_name are empty
     */
    public function getDescription($object_id, $object_type, $lang_code = CART_LANGUAGE)
    {
        if (!empty($object_id) && !empty($object_type) && !empty($lang_code)) {
            return db_get_field(
                "SELECT value FROM ?:settings_descriptions "
                . "WHERE object_id = ?i AND object_type = ?s AND lang_code = ?s",
                $object_id, $object_type, $lang_code
            );
        } else {
            return false;
        }
    }

    /**
     * Updates settings description.
     * If $object_id, $object_type or $lang_code or value is empty function return false and generate error notification.
     *
     * Description data must be array in this format (example):
     *  array(
     *      'value'     => 'General',
     *      'tooltip'   => 'General tab',
     *      'object_id' => '1',
     *      'object_type' => 'S',
     *      'lang_code' => 'en'
     *  )
     *
     * If some parameter will be skipped and function not update it field.
     * If name or lang_code skipped function adds new description and returns true.
     *
     * @param  array $description_data Description data
     * @return bool  True on success, false otherwise
     */
    public function updateDescription($description_data)
    {
        if (!(empty($description_data['object_type']) || empty($description_data['object_id']) || empty($description_data['lang_code']))) {
            db_replace_into ('settings_descriptions', $description_data);
        } else {
            $this->_generateError(__('unable_to_update_setting_description'), __('empty_key_value'));

            return false;
        }

        return true;
    }

    /**
     * Removes description of some setting object
     * If $name or $lang_code or value is empty function return false and generate error notification.
     *
     * @param  string $object_id   Setting object id
     * @param  string $object_type Type of object to remove variant
     * @param  string $lang_code   2 letters language code
     * @return bool   true or false if $name or $lang_code or value is empty
     */
    public function removeDescription($object_id, $object_type, $lang_code = '')
    {
        if (!empty($object_id) && !empty($object_type)) {
            $lang_condition = "";
            if (!empty($lang_code)) {
                $lang_condition = db_quote("AND lang_code = ?s", $lang_code);
            }

            db_query(
                "DELETE FROM ?:settings_descriptions WHERE object_id = ?i AND object_type = ?s ?p",
                $object_id, $object_type, $lang_condition
            );
        } else {
            $this->_generateError(__('unable_to_delete_setting_description'), __('empty_key_value'));

            return false;
        }

        return true;
    }

    /**
     * Generates error notification
     *
     * @param  string $action Action thae was happen
     * @param  string $reason Reason, why the error notification must be showed
     * @param  string $table  Table name (optional)
     * @return bool   Always true
     */
    private function _generateError($action, $reason, $table = '')
    {
        $message = str_replace("[reason]", $reason, $action);
        if (!empty($table)) {
            $message = str_replace("[table]", $table, $message);
        }

        fn_log_event('settings', 'error', $message);

        if (Debugger::isActive() || defined('DEVELOPMENT')) {
            fn_set_notification('E', __('error'), $message);
        }

        return true;
    }

    /**
     * Returns plain list of settings
     *
     * @param  mixed      $fields          String in SQL format with fields to get from db
     * @param  string     $section_id      If defined function returns list of option for this section
     * @param  string     $section_tab_id  If defined function returns list of option for this tab of section
     * @param  bool       $no_headers      If true function gets all settings that type is not 'H'
     * @param  string     $extra_condition Extra SQL condition
     * @param  bool       $is_global       If true return oly global options
     * @param  int        $company_id      Company identifier
     * @param  string     $lang_code       2 letters language code
     * @return array|bool List of settings on success, false otherwise
     */
    private function _get_list($fields, $section_id = '', $section_tab_id = '', $no_headers = false, $extra_condition = '', $is_global = true, $company_id = null, $lang_code = '')
    {
        $global_condition = $is_global ? " AND is_global = 'Y'" : '';
        $condition = (!empty($section_id)) ? db_quote(" AND section_id = ?s", $section_id) : $global_condition;
        $condition .= (!empty($section_tab_id)) ? db_quote(" AND section_tab_id = ?s", $section_tab_id) : '';
        $condition .= $this->_generateEditionCondition('?:settings_objects', false);
        if ($no_headers) {
            $condition .= " AND ?:settings_objects.type <> 'H'";
        }

        $join = '';
        $value = '?:settings_objects.value AS value';

        if (fn_allowed_for('ULTIMATE') && $this->_getCompanyId($company_id)) {
            $join .= db_quote('LEFT JOIN ?:settings_vendor_values ON ?:settings_vendor_values.object_id = ?:settings_objects.object_id AND company_id = ?i ', $this->_getCompanyId($company_id));
            $value = 'IF(?:settings_vendor_values.value IS NULL, ?:settings_objects.value, ?:settings_vendor_values.value) as value';
        }

        if (!empty($lang_code)) {
            $join .= db_quote(
                "LEFT JOIN ?:settings_descriptions "
                . "ON ?:settings_descriptions.object_id = ?:settings_objects.object_id "
                    . "AND ?:settings_descriptions.object_type = ?s AND lang_code = ?s",
                'O', $lang_code
            );
            $fields[] = db_quote('?:settings_descriptions.value as description');
            $fields[] = db_quote('?:settings_descriptions.tooltip as tooltip');
            $fields[] = db_quote('?:settings_descriptions.object_type as object_type');
        } else {
            $fields[] = db_quote('?:settings_objects.name as description');
        }

        $fields[] = $value;
        $fields = implode(', ', $fields);

        return db_get_array('SELECT ?p FROM ?:settings_objects ?p WHERE 1 ?p ORDER BY ?:settings_objects.position', $fields, $join, $condition.$extra_condition);
    }

    /**
     * Generate SQL condition for edition types
     *
     * @param  string $table            Name of table that condition generated. Must be in SQL notation with placeholder for place database prefix.
     * @param  bool   $use_access_level Use or ignore edition and type access conditions (ROOT, MSE:VENDOR, etc...)
     * @return string SQL condition
     */
    private function _generateEditionCondition($table, $use_access_level = true)
    {
        $edition_conditions = $_edition_conditions = array();

        if ($use_access_level && $this->_getCompanyId() && fn_allowed_for('ULTIMATE')) {
            $_edition_conditions[] = 'VENDOR';
        } else {
            $_edition_conditions[] = 'ROOT';
            if (fn_allowed_for('ULTIMATE')) {
                $_edition_conditions[] = 'VENDOR';
            }
        }

        foreach ($_edition_conditions as $edition_condition) {
            $edition_conditions[] = "FIND_IN_SET('$edition_condition', $table.edition_type)";
            $edition_conditions[] = "FIND_IN_SET('" . $this->_getCurrentEdition() . $edition_condition."', $table.edition_type)";
        }

        return ' AND ('.implode(' OR ', $edition_conditions).')';
    }

    /**
     * Unpacks setting value
     *
     * @param  mixed $value       Setting value
     * @param  bool  $force_parse
     * @return mixed Unpacked value
     */
    private function _unserialize($value, $force_parse = false)
    {
        if (strpos($value, '#M#') === 0) {
            parse_str(str_replace('#M#', '', $value), $value);
        } elseif ($force_parse) {
            parse_str($value, $value);
        }

        return $value;
    }

    /**
     * Packs setting value
     *
     * @param  mixed $value Setting value
     * @return mixed Packed value
     */
    private function _serialize($value)
    {
        if (is_array($value)) {
            $value = '#M#' . implode('=Y&', $value) . '=Y';
        }

        return $value;
    }

    /**
     * Checks that this setting or section may be updated in current edition
     *
     * @param  array $object Some setting object data to check
     * @return bool  true on success, false otherwise
     */
    private function _checkEdition($object)
    {
        $allow = true;

        if (!empty($object['edition_type'])) {
            $edition_names = $this->_getCurrentEdition();
            $setting_editions = explode(",", $object['edition_type']);

            if(array_search("ROOT", $setting_editions) === false
                && array_search("VENDOR", $setting_editions) === false
                && array_search($edition_names . "ROOT", $setting_editions) === false
                && array_search($edition_names . "VENDOR", $setting_editions) === false
                && array_search("NONE", $setting_editions) === false
            ) {
                $allow = false;
            }
        }

        return $allow;
    }

    /**
     * Verifying access to the settings section under the company ID
     *
     * @param  string $section_id Section identifier
     * @param  int    $company_id company ID
     * @return bool   true on success, false otherwise
     */
    public function checkPermissionCompanyId($section_id, $company_id)
    {
        $allow = true;

        if (fn_allowed_for('ULTIMATE')) {
            $section = $this->getSectionByName($section_id, Settings::CORE_SECTION, false);

            if (!empty($section['edition_type'])) {
                $edition_names = $this->_getCurrentEdition();
                $setting_editions = explode(",", $section['edition_type']);

                if (array_search("NONE", $setting_editions) === false) {
                    if ($company_id) {

                        if (array_search("VENDOR", $setting_editions) === false
                             && array_search($edition_names . "VENDOR", $setting_editions) === false
                             && $company_id != 0
                        ) {
                            $allow = false;
                        }

                    } else {

                        if (array_search("ROOT", $setting_editions) === false) {
                            $allow = false;
                        }
                    }
                }
            }
        }

        return $allow;
    }

    /**
     * Gets company ID
     * @param  int   $company_id company ID
     * @return mixed company ID if defined, false otherwise
     */
    private function _getCompanyId($company_id = null)
    {
        if ($this->_root_mode) {
            return false;
        }

        if (!empty($company_id)) {
            return $company_id;
        } elseif (Registry::get('runtime.company_id')) {
            return Registry::get('runtime.company_id');
        }
    }
}
