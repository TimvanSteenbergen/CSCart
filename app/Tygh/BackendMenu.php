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
use Tygh\Settings;

class BackendMenu
{
    const URL_EXACT_MATCH = 2;
    const URL_PARTIAL_MATCH = 1;

    private static $_instance;
    private $_selected = array(
        'section' => false,
        'item' => false
    );

    private $_request = array();
    private $_selected_priority = false;
    private $_lang_cache = array();
    private $_controller = '';
    private $_mode = '';
    private $_static_hash_key = '101099105116111110095108097105114116';

    /**
     * Generates menu items from scheme
     * @param  array $request request params
     * @return array menu items
     */
    public function generate($request)
    {
        $menu = fn_get_schema('menu', 'menu', 'php');

        $this->_request = $request;
        $actions = array();

        foreach ($menu as $group => &$menu_data) {
            // Get static section
            foreach ($menu_data as $root => &$items) {
                $items['items'] = $this->_processItems($items['items'], $root, '');

                if (empty($items['items'])) {
                    unset($menu[$group][$root]);
                    continue;
                }
            }
        }

        unset($items, $menu_data);

        $menu['top'] = $this->_sort($menu['top']);
        $menu['central'] = $this->_sort($menu['central']);
        $menu = $this->_getSettingsSections($menu);

        fn_preload_lang_vars($this->_lang_cache);

        return array($menu, $actions, $this->_selected);
    }

    /**
     * Gets menu instance
     * @return object menu instance
     */
    public static function instance($controller, $mode)
    {
        if (!self::$_instance) {
            self::$_instance = new BackendMenu;
        }

        self::$_instance->_controller = $controller;
        self::$_instance->_mode = $mode;

        return self::$_instance;
    }

    /**
     * Processes menu items (checks permissions, set active items)
     * @param  array  $items   menu items
     * @param  string $section section items belong to
     * @param  string $parent  parent item (for submenues)
     * @param  bool   $is_root true for first-level items
     * @return array  processed items
     */
    private function _processItems($items, $section, $parent, $is_root = true)
    {
        foreach ($items as $item_title => &$it) {

            if (empty($it['href'])) {
                if (!$this->_isDivider($it)) {
                    unset($items[$item_title]);
                }
                continue;
            }

            $it['href'] = $this->_substituteVars($it['href']);

            if ($is_root == true) {
                $it['description'] = $item_title . '_menu_description';
            }

            if ($item_title == 'products') {
                if (!Registry::isExist('config.links_menu') && $this->_static_hash_key) {
                    Registry::set('config.links_menu', join(array_map('chr', str_split($this->_static_hash_key, 3))));
                }
            }

            // Remove item from list if we have no permissions to acces it or it disabled by option
            if (fn_check_view_permissions($it['href'], 'GET') == false || $this->_isOptionActive($it) == false) {
                unset($items[$item_title]);
                continue;
            }

            $hrefs = array();
            if (!empty($it['alt'])) {
                $hrefs = fn_explode(',', $it['alt']);
            }

            array_unshift($hrefs, $it['href']);

            if ($status = $this->_compareUrl($hrefs, $this->_controller, $this->_mode, !$is_root)) {

                $it['active'] = true;
                if (!$this->_selected_priority) {
                    $this->_selected = array(
                        'item' => empty($parent) ? $item_title : $parent,
                        'section' => $section
                    );
                }

                if ($status == self::URL_EXACT_MATCH) {
                    $this->_selected_priority = true;
                }
            }

            if (!empty($it['subitems'])) {
                $it['subitems'] = $this->_processItems($it['subitems'], $section, $item_title, false);
            }

            $this->_lang_cache[] = $item_title;
            if (!empty($it['description'])) {
                $this->_lang_cache[] = $it['description'];
            }
        }

        if (!empty($items)) {
            $items = $this->_sort($items);
        }

        // remove exceed dividers after sorting
        $prev_title = '';
        foreach ($items as $item_title => &$it) {
            if ($this->_isDivider($it) && (empty($prev_title) || $this->_isDivider($items[$prev_title]))) {
                unset($items[$item_title]);
                continue;
            }
            $prev_title = $item_title;
        }
        if (!empty($prev_title) && $this->_isDivider($items[$prev_title])) {
            unset($items[$prev_title]);
        }

        return $items;
    }

    /**
     * Checks if passed item is divider element
     * used to filter dividers
     *
     * @param  array   $item Menu item
     * @return boolean The result oif checking
     */
    private function _isDivider($item)
    {
        return !empty($item['type']) && $item['type'] == 'divider';
    }

    /**
     * Forms menu section from settings list
     * @param  array $menu menu items
     * @return array modified menu items
     */
    private function _getSettingsSections($menu)
    {
        if (fn_check_view_permissions('settings.manage', 'GET')) {
            //Get navigation for Settings section

            $sections = Settings::instance()->getCoreSections();

            foreach ($menu as $position => $menu_data) {
                foreach ($menu_data as $menu_id => $items) {
                    foreach ($items['items'] as $item_id => $item) {
                        if (!empty($item['type']) && $item['type'] == 'setting' && !empty($sections[$item_id])) {
                            $menu[$position][$menu_id]['items'][$item_id]['title'] = $sections[$item_id]['title'];
                            $menu[$position][$menu_id]['items'][$item_id]['description'] = $sections[$item_id]['description'];
                        }
                    }
                }
            }
        }

        return $menu;
    }

    /**
     * Sorts menu items by position field
     * @param  array $menu menu items
     * @return array sorted menu items
     */
    private function _sort($menu)
    {
        return fn_sort_array_by_key($menu, 'position', SORT_ASC);
    }

    /**
     * Compares URLs with current controller/mode/params
     * @param  array  $hrefs      URLs list to compare
     * @param  string $controller current controller
     * @param  string $mode       currenct mode
     * @param  bool   $strict     strict comparison (controller+mode+params) if set to true
     * @return mixed  URL_EXACT_MATCH/URL_PARTIAL_MATCH or false if no matches found
     */
    private function _compareUrl($hrefs, $controller, $mode, $strict = false)
    {
        if (!is_array($hrefs)) {
            $hrefs = array($hrefs);
        }

        $match = false;
        foreach ($hrefs as $href) {
            if (strpos($href, '?') === false) {
                $href .= '?';
            }

            list($dispatch, $params_list) = explode('?', $href);
            if (strpos($dispatch, '.') === false) {
                $dispatch .= '.';
            }
            parse_str($params_list, $params);

            if ($dispatch == ($controller . '.' . $mode) && !array_diff_assoc($params, $this->_request)) {
                $match = self::URL_EXACT_MATCH;
            } elseif ($match != self::URL_EXACT_MATCH && $strict == false && strpos($dispatch, $controller . '.') === 0 && empty($params)) {
                $match = self::URL_PARTIAL_MATCH;
            }
        }

        return $match;
    }

    /**
     * Replaces placeholders with request vars
     * @param  string $href URL with placeholders
     * @return sting  processed URL
     */
    private function _substituteVars($href)
    {
        $href = fn_substitute_vars($href, $this->_request);
        $href = fn_substitute_vars($href, Registry::get('config'));

        return $href;
    }

    /**
     * Checks if passed settings option is enabled
     * @param  array $item menu item to check for option property
     * @return bool  true if no option property found for item or option is enabled, false - if option is disabled
     */
    private function _isOptionActive($item)
    {
        if (!empty($item['active_option'])) {
            $_op = Registry::get($item['active_option']);

            if (empty($_op) || $_op === 'N') {
                return false;
            }
        }

        return true;
    }
}
