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

class XmlScheme1 extends AXmlScheme
{
    public function getSections()
    {
        $sections = array();

        if (isset($this->_xml->opt_settings->section)) {
            foreach ($this->_xml->opt_settings->section as $section) {
                if (isset($section['name']) && (string) $section['name'] != 'general') {
                    $id = (string) $section['name'];
                    $name = __($id, '', $this->getDefaultLanguage());
                } else {
                    $id = 'general';
                    $name = 'General';
                }

                $sections[] = array(
                    'id' => $id,
                    'name' => $name,
                    'translations' => array(array('lang_code' => $this->getDefaultLanguage(), 'name' => $id, 'value' => $name)),
                    'edition_type' => $this->_getEditionType($section)
                );
            }
        } elseif (isset($this->_xml->opt_settings)) {
                $sections[] = array(
                    'id' => 'general',
                    'name' => 'General',
                    'translations' => array(array('lang_code' => $this->getDefaultLanguage(), 'name' => 'general', 'value' => 'General')),
                    'edition_type' => 'ROOT'
                );
        }

        return $sections;
    }

    public function getSettings($section_id)
    {
        $settings = array();

        $section = $this->_xml->xpath("//*[@name='$section_id']");
        if (is_array($section) && !empty($section)) {
            $section = current($section);

            foreach ($section->item as $setting) {
                $settings[] = $this->_getSettingItem($setting);
            }
        } elseif ($section_id == 'general') {
            $sections_tabs = isset($this->_xml->opt_settings->section) ? $this->_xml->opt_settings->section : $this->_xml->opt_settings;
            foreach ($sections_tabs as $tab_pos => $tab) {
                foreach ($tab->item as $k => $setting) {
                    if (!empty($setting['id'])) {
                        $settings[] = $this->_getSettingItem($setting);
                    }
                }
            }
        }

        return $settings;
    }

    protected function getQueries($mode = '')
    {
        if ($mode != 'uninstall') {
            return $this->_xml->xpath("//opt_queries/*[@for='install' or not(@for)]");
        } else {
            return $this->_xml->xpath("//opt_queries/*[@for='uninstall']");
        }
    }

    protected function _getLangVarsSectionName()
    {
        return '//opt_language_variables';
    }

    public function getDefaultLanguage()
    {
        return DEFAULT_LANGUAGE;
    }
}
