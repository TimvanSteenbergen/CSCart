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

namespace Tygh\Addons;

use Tygh\Registry;
use Tygh\Languages\Languages;
use Tygh\Languages\Po;

class XmlScheme3 extends XmlScheme2
{
    protected $poparser = null;

    /**
     * Install all langvars from addon PO files
     *
     * @param  bool  $only_originals Gets only original values instead of language values
     * @return array List of language value or originals
     */
    public function getLanguageValues($only_originals = false)
    {
        $addon_id = (string) $this->_xml->id;
        $lang_dir_path = Registry::get('config.dir.addons') . $addon_id . '/lang/';

        $default_lang_pack = $this->getPoPath($this->getDefaultLanguage());

        $language_variables = parent::getLanguageValues($only_originals);

        foreach (fn_get_translation_languages() as $lang_code => $_v) {
            $lang_data = array();

            $po_path = $this->getPoPath($lang_code);
            if (!empty($po_path)) {
                $lang_data = Po::getValues($po_path, 'Languages');
            }

            if (!empty($default_lang_pack)) {
                $lang_data = array_merge(Po::getValues($default_lang_pack, 'Languages'), $lang_data);
            }

            foreach ($lang_data as $var_name => $var_data) {
                $value = implode('', $var_data['msgstr']);
                $original_value = $var_data['msgid'];
                $value = empty($value) ? $original_value : $value;

                if ($only_originals) {
                    $language_variables[] = array(
                        'msgctxt' => $var_name,
                        'msgid' => $original_value,
                    );
                } else {
                    $language_variables[] = array(
                        'lang_code' => $lang_code,
                        'name' => $var_data['id'],
                        'value' => $value,
                    );
                }
            }
        }

        return $language_variables;
    }

    /**
     * Returns addons text name from xml.
     * @param  string $lang_code
     * @return string
     */
    public function getName($lang_code = CART_LANGUAGE)
    {
        $addon_id = (string) $this->_xml->id;
        $addon_translations = $this->getPoValues($lang_code, 'Addons');

        if (!empty($addon_translations['Addons' . \I18n_Pofile::DELIMITER . 'name' . \I18n_Pofile::DELIMITER . $addon_id])) {
            $name = $addon_translations['Addons' . \I18n_Pofile::DELIMITER . 'name' . \I18n_Pofile::DELIMITER . $addon_id]['value'];
        } else {
            $name = parent::getName($lang_code);
        }

        return $name;
    }

    /**
     * Removes original values and values from languages and description tables
     * TODO: Make proper cleanup of PO language variables. Only XML langvars remove now.
     */
    public function uninstallLanguageValues()
    {
        $addon_id = (string) $this->_xml->id;

        db_query('DELETE FROM ?:original_values WHERE msgctxt IN (?a)', array('Addons' . \I18n_Pofile::DELIMITER . 'name' . \I18n_Pofile::DELIMITER . $addon_id, 'Addons' . \I18n_Pofile::DELIMITER . 'description' . \I18n_Pofile::DELIMITER . $addon_id));

        $originals = $this->getLanguageValues(true);

        if (!empty($originals)) {
            foreach ($originals as $original) {
                $name = explode(\I18n_Pofile::DELIMITER, $original['msgctxt']);

                db_query('DELETE FROM ?:original_values WHERE msgctxt = ?s AND msgid = ?s', $original['msgctxt'], $original['msgid']);
                db_query("DELETE FROM ?:language_values WHERE name = ?s", $name[1]);

                if (fn_allowed_for('ULTIMATE')) {
                    db_query("DELETE FROM ?:ult_language_values WHERE name = ?s", $name[1]);
                }
            }
        }

        parent::uninstallLanguageValues();
    }

    /**
     * Returns addons text description from xml.
     * @param  string $lang_code
     * @return string
     */
    public function getDescription($lang_code = CART_LANGUAGE)
    {
        $addon_id = (string) $this->_xml->id;
        $addon_translations = $this->getPoValues($lang_code, 'Addons');

        if (!empty($addon_translations['Addons' . \I18n_Pofile::DELIMITER .'description' . \I18n_Pofile::DELIMITER . $addon_id])) {
            $description = $addon_translations['Addons' . \I18n_Pofile::DELIMITER . 'description' . \I18n_Pofile::DELIMITER . $addon_id]['value'];
        } else {
            $description = parent::getDescription($lang_code);
        }

        return $description;
    }

    public function getSections()
    {
        $addon_id = (string) $this->_xml->id;
        $default_lang = $this->getDefaultLanguage();
        $po_sections = $this->getPoValues($default_lang, 'SettingsSections');

        $sections = array();
        if (isset($this->_xml->settings->sections->section)) {
            foreach ($this->_xml->settings->sections->section as $section) {
                $_id = 'SettingsSections' . \I18n_Pofile::DELIMITER . $addon_id . \I18n_Pofile::DELIMITER . (string) $section['id'];
                if (isset($po_sections[$_id])) {
                    $name = $po_sections[$_id]['value'];
                } else {
                    $name = (string) $section->name;
                }

                $_section = array(
                    'id' => (string) $section['id'],
                    'name' => $name,
                    'translations' => $this->_getTranslations($section, 'SettingsSections', $addon_id),
                    'edition_type' => $this->_getEditionType($section)
                );

                if (!empty($section['outside_of_form'])) {
                    $_section['separate'] = true;
                }

                $sections[] = $_section;
            }
        }

        return $sections;
    }

    public function getSettings($section_id)
    {
        $settings = array();

        $section = $this->_xml->xpath("//section[@id='$section_id']");

        if (!empty($section) && is_array($section)) {
            $section = current($section);

            if (isset($section->items->item)) {
                foreach ($section->items->item as $setting) {
                    $settings[] = $this->_getSettingItem($setting);
                }
            }
        }

        return $settings;
    }

    /**
     * Returns translations of description and addon name.
     * @return array|bool
     */
    public function getAddonTranslations()
    {
        $name = $this->_getTranslations($this->_xml, 'Addons', 'name');
        $description = $this->_getTranslations($this->_xml, 'Addons', 'description', 'description');

        return fn_array_merge($name, $description);
    }

    /**
     * Gets original values for language-dependence name/description
     *
     * @return array Original values
     */
    public function getOriginals()
    {
        $originals = array();

        $addon_id = (string) $this->_xml->id;
        $pack = $this->getPoPath($this->getDefaultLanguage());

        if (file_exists($pack)) {
            $values = Po::getValues($pack, 'Addons');

            foreach ($values as $value) {
                if ($value['parent'] == 'name') {
                    $originals['name'] = $value['msgid'];
                } elseif ($value['parent'] == 'description') {
                    $originals['description'] = $value['msgid'];
                }
            }
        }

        return $originals;
    }

    private function getPoValues($lang_code, $section)
    {
        $addon_id = (string) $this->_xml->id;
        $result = array();
        $pack = $this->getPoPath($lang_code);
        $default_pack = $this->getPoPath($this->getDefaultLanguage());

        if ($default_pack != $pack && file_exists($default_pack)) {
            $result = $this->parsePoContent(Po::getValues($default_pack, $section));
        }

        if (file_exists($pack)) {
            $result = fn_array_merge($result, $this->parsePoContent(Po::getValues($pack, $section)));
        }

        return $result;
    }

    private function parsePoContent($po_parsed_content)
    {
        $formatted_po_values = array();

        foreach ($po_parsed_content as $var_id => $var_data) {
            $value = implode('', $var_data['msgstr']);
            $original_value = $var_data['msgid'];

            $formatted_po_values[$var_id] = array(
                'id' => $var_data['id'],
                'parent' => $var_data['parent'],
                'section' => $var_data['section'],
                'value' => empty($value) ? $original_value : $value
            );
        }

        return $formatted_po_values;
    }

    /**
     * Returns all translations for xml_node for all installed languages if it is presents in addon xml
     * @param $xml_node
     * @return array|bool
     */
    protected function _getTranslations($xml_node, $type = '', $parent_id = '', $value_name = 'value')
    {
        $po_values = array();
        $translations = array();

        // Generate id from attribute or property
        if (isset($xml_node['id'])) {
            $id = (string) $xml_node['id'];
        } elseif (isset($xml_node->id)) {
            $id = (string) $xml_node->id;
        } else {
            return false;
        }

        $default_language = $this->getDefaultLanguage();
        $po_values[$default_language] = $this->getPoValues($default_language, $type);
        $po_id = $type . (!empty($parent_id) ? \I18n_Pofile::DELIMITER . $parent_id : '') . \I18n_Pofile::DELIMITER . $id;

        if (isset($po_values[$default_language][$po_id])) {
            $default_value = $po_values[$default_language][$po_id]['value'];
        } else {
            $default_value = (string) $xml_node->name;
        }

        $default_translation = array(
            'lang_code' => $default_language,
            'name' => $id,
            $value_name => $default_value,
        );

        // Fill all languages by default laguage values
        foreach (Languages::getAll() as $lang_code => $_v) {
            if (empty($po_values[$lang_code][$po_id])) {
                $po_values[$lang_code] = $this->getPoValues($lang_code, $type);
            }

            $value = isset($po_values[$lang_code][$po_id])
                ? $po_values[$lang_code][$po_id]['value']
                : $xml_node->xpath("translations/item[(not(@for) or @for='name') and @lang='$lang_code']");

            if (!empty($value) && is_array($value)) {
                $value = (string) current($value);
            }

            $translations[] = array(
                'lang_code' => $lang_code,
                'name' => $default_translation['name'],
                $value_name => !empty($value) ? $value : $default_translation[$value_name],
            );
        }

        return $translations;
    }

    /**
     * Returns array of setting item data from xml node
     * @param $xml_node
     * @return array
     */
    protected function _getSettingItem($xml_node)
    {
        $addon_id = (string) $this->_xml->id;
        $default_language = $this->getDefaultLanguage();

        foreach (fn_get_translation_languages() as $lang_code => $_v) {
            $items[$lang_code] = $this->getPoValues($lang_code, 'SettingsOptions');
        }

        if (isset($xml_node['id'])) {
            $_types = $this->_getTypes();

            $translations = $this->_getTranslations($xml_node, 'SettingsOptions', $addon_id);
            $tooltip_translations = $this->_getTranslations($xml_node, 'SettingsTooltips', $addon_id, 'tooltip');

            $setting = array(
                'edition_type' =>  $this->_getEditionType($xml_node),
                'id' => (string) $xml_node['id'],
                'name' => isset($items[$default_language][(string) $xml_node['id']]) ? $items[$default_language][(string) $xml_node['id']] : (string) $xml_node->name,
                'type' => isset($_types[(string) $xml_node->type]) ? $_types[(string) $xml_node->type] : '',
                'translations' => fn_array_merge($translations, $tooltip_translations),
                'default_value' => isset($xml_node->default_value) ? (string) $xml_node->default_value : '',
                'variants' => $this->_getVariants($xml_node),
                'handler' => isset($xml_node->handler) ? (string) $xml_node->handler : '',
                'parent_id' => isset($xml_node['parent_id']) ? (string) $xml_node['parent_id'] : '',
            );

            return $setting;
        } else {
            return array();
        }
    }

    /**
     * Returns array of variants of setting item from xml node
     * @param $xml_node
     * @return array
     */
    protected function _getVariants($xml_node)
    {
        $addon_id = (string) $this->_xml->id;
        $option_id = (string) $xml_node['id'];
        $variants = $this->getPoValues($this->getDefaultLanguage(), 'SettingsOptions');

        $variants = array();
        if (isset($xml_node->variants)) {
            foreach ($xml_node->variants->item as $variant) {
                $variants[] = array(
                    'id' => (string) $variant['id'],
                    'name' => isset($variants[(string) $variant['id']]) ? $variants[(string) $variant['id']] : (string) $variant->name,
                    'translations' => $this->_getTranslations($variant, 'SettingsVariants', $addon_id . \I18n_Pofile::DELIMITER . $option_id),
                );
            }
        }

        return $variants;
    }

    /**
     * Gets path to PO translation for specified language
     *
     * @param  string      $lang_code 2-letters language identifier
     * @return string|bool Path to file if exists of false otherwise
     */
    protected function getPoPath($lang_code)
    {
        $addon_id = (string) $this->_xml->id;
        $po_path = Registry::get('config.dir.lang_packs') . $lang_code . '/addons/' . $addon_id . '.po';

        if (file_exists($po_path)) {
            return $po_path;
        }

        return false;
    }
}
