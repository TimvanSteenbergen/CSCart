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

namespace Tygh\BlockManager;

use Tygh\Registry;

class SchemesManager
{
    /**
     * Static storage for already read schemes
     * @var array Static storage for already read schemes
     */
    private static $schemes;

    /**
     * Returns list of dispatches and it's descriptions
     * @static
     * @param  string $lang_code 2 letter language code
     * @return array  List of dispatch descriptions as dispatch => description
     */
    public static function getDispatchDescriptions($lang_code = DESCR_SL)
    {
        $descriptions = self::_getScheme('dispatch_descriptions');

        foreach ($descriptions as $dispatch => $lang_var) {
            $descriptions[$dispatch] = __($lang_var, '', $lang_code);
        }

        return $descriptions;
    }

    /**
     * Returns dynamic object data
     * @static
     * @param  string     $dispatch URL dispatch (controller.mode.action)
     * @param  string     $area     Area ('A' for admin or 'C' for customer)
     * @return array|bool Array of dynamic object data, false otherwise
     */
    public static function getDynamicObject($dispatch, $area = 'A')
    {
        $area = self::_normalizeArea($area);

        $objects = self::_getScheme('dynamic_objects');

        foreach ($objects as $object_type => $properties) {
            if (isset($properties[$area]) && $properties[$area] == $dispatch) {
                $properties['object_type'] = $object_type;

                return $properties;
            }
        }

        return false;
    }

    /**
     * Returns dynamic object data
     * @static
     * @param  string     $type Type of dinamic object
     * @return array|bool Array of dynamic object data, false otherwise
     */
    public static function getDynamicObjectByType($type)
    {
        $objects = self::_getScheme('dynamic_objects');
        if (isset($objects[$type])) {
            return $objects[$type];
        }

        return array();
    }

    /**
     * Checks existing block with $block_type in block manager scheme
     * @static
     * @param  string $block_type Block type. Thirst key of scheme array
     * @return bool
     */
    public static function isBlockExist($block_type)
    {
        $scheme = self::_getScheme('blocks');

        return isset($scheme[$block_type]);
    }

    /**
     * Gets scheme for some block type
     * @static
     * @param  string $block_type Block type. Thirst key of scheme array
     * @param  array  $params     Request params
     * @param  bool   $no_cache   Do not get scheme from cache
     * @return array  Array of block scheme data
     */
    public static function getBlockScheme($block_type, $params, $no_cache = false)
    {
        $scheme = self::_getScheme('blocks');

        $cache_name = 'scheme_block_' . $block_type;

        Registry::registerCache($cache_name, array('addons'), Registry::cacheLevel('static'));

        if (Registry::isExist($cache_name) == true && $no_cache == false) {
            return Registry::get($cache_name);
        } else {
            if (isset($scheme[$block_type])) {
                // Get all data for this block type
                $_block_scheme = $scheme[$block_type];

                $_block_scheme['type'] = $block_type;

                // Update templates data
                $_block_scheme['templates'] = self::_prepareTemplates($_block_scheme);
                $_block_scheme['wrappers'] = self::_prepareWrappers($_block_scheme);
                $_block_scheme['content'] = self::prepareContent($_block_scheme, $params);
                $_block_scheme = self::_prepareSettings($_block_scheme);

                Registry::set($cache_name, $_block_scheme);

                return $_block_scheme;
            }
        }

        return array();
    }

    /**
     * Generates content section of block scheme
     * @static
     * @param  array $block_scheme   Scheme of block
     * @param  array $request_params Request params
     * @return array Content section of block scheme
     */
    public static function prepareContent($block_scheme, $request_params)
    {
        $content = array();

        if (isset($block_scheme['content']) && is_array($block_scheme['content'])) {
            foreach ($block_scheme['content'] as $name => $params) {
                $params = self::_getValue($params);
                if (is_array($params)) {
                    foreach ($params as $param_name => $param_value) {
                        $content[$name][$param_name] = $param_value;
                        // Merge with fillings settings
                        if ($param_name == 'fillings') {
                            $fillings = self::_getScheme('fillings');
                            foreach ($param_value as $filling_name => $filling_param) {
                                if (isset($fillings[$filling_name])) {
                                    $content[$name][$param_name][$filling_name]['settings'] = $fillings[$filling_name];
                                }

                                $content[$name][$param_name][$filling_name] = self::_prepareSettings($content[$name][$param_name][$filling_name]);

                                if (!self::isFillingAvailable($request_params, $block_scheme, $filling_name)) {
                                    unset($content[$name][$param_name][$filling_name]);
                                }
                            }
                        }
                    }
                } else {
                    $content[$name] = $params;
                }
            }
        }

        return $content;
    }

    /**
     * Returns available filling for this template or no
     * @static
     * @param  array  $params       Request params
     * @param  array  $block_scheme Scheme of block
     * @param  string $filling_name name of filling
     * @return bool   True if filling is available for this template, false otherwise
     */
    public static function isFillingAvailable($params, $block_scheme, $filling_name)
    {
        if (isset($params['properties']['template'])) {
            $template = $params['properties']['template'];
            if (isset($block_scheme['templates'][$template]['fillings'])) {
                return in_array($filling_name, $block_scheme['templates'][$template]['fillings']);
            }
        }

        return true;
    }

    /**
     * Generates templates section of block scheme
     * @static
     * @param  array $block_scheme Scheme of block
     * @return array Templates section of block scheme
     */
    private static function _prepareTemplates($block_scheme)
    {
        $templates = array();

        if (isset($block_scheme['templates'])) {
            $_all_templates = self::_getScheme('templates');
            $block_scheme['templates'] = self::_getValue($block_scheme['templates']);

            $theme_path = RenderManager::getCustomerThemePath();

            if (is_array($block_scheme['templates'])) {
                foreach ($block_scheme['templates'] as $path => $template) {
                    if (isset($_all_templates[$path])) {
                        $template = array_merge($template, $_all_templates[$path]);
                    }

                    if (empty($template['name'])) {
                        $template['name'] = self::generateTemplateName($path, $theme_path);;
                    }

                    $templates[$path] = $template;
                }
            }
        }

        return self::_prepareSettings($templates);
    }

    /**
     * Generates additional params for settings array
     * @param  array $scheme
     * @return array
     */
    private static function _prepareSettings($scheme)
    {
        if (!empty($scheme['settings']) && is_array($scheme['settings'])) {
            foreach ($scheme['settings'] as $name => $value) {
                $scheme['settings'][$name] = self::_getValue($value);
            }
        }

        return $scheme;
    }

    /**
     * Generates wrappers section of block scheme
     * @static
     * @param  array $block_scheme Scheme of block
     * @return array Wrappers section of block scheme
     */
    public static function _prepareWrappers($block_scheme)
    {
        $wrappers = array();

        if (isset($block_scheme['wrappers'])) {
            return self::_getValue($block_scheme['wrappers']);
        }

        return $wrappers;
    }

    /**
     * Returns all block types
     * @static
     * @param  string $lang_code 2 letter language code
     * @return array  List of block types with name, icon and type
     */
    public static function getBlockTypes($lang_code = CART_LANGUAGE)
    {
        $scheme = self::_getScheme('blocks');
        $types = array();

        foreach ($scheme as $type => $params) {
            $types[$type] = array(
                'type' => $type,
                'name' => __('block_' . $type, '', $lang_code),
                'icon' => '/media/images/block_manager/block_icons/default.png'
            );

            if (!empty($params['icon'])) {
                $types[$type]['icon'] = $params['icon'];
            }
        }

        $types = fn_sort_array_by_key($types, 'name');

        return $types;
    }

    /**
     * Removes blocks that cannot be on $location or can be only singular for this $location and already exist on it
     * for $location and allready exists on it
     *
     * To define that kind of block use hide_on_locations and single_for_location keys in blocks scheme
     *
     * @param  array  $blocks   List of blocks
     * @param  array $location Array with location data
     * @return array  Filtered list of blocks
     */
    public static function filterByLocation($blocks, $location)
    {
        $scheme = self::_getScheme('blocks');

        foreach ($blocks as $block_key => $block) {
            if (!empty($block['type'])) {
                $type = $block['type'];
                if (!empty($scheme[$type]['hide_on_locations'])) {
                    if (array_search($location['dispatch'], $scheme[$type]['hide_on_locations']) !== false) {
                        unset($blocks[$block_key]);
                        continue;
                    }
                }
                if (!empty($block['type']) && !empty($scheme[$type]['single_for_location'])) {
                    $blocks[$block_key]['single_for_location'] = true;
                    $block_exists = Block::instance()->getBlocksByTypeForLocation($type, $location['location_id']);
                    if (!empty($block_exists)) {
                        unset($blocks[$block_key]);
                    }
                }
            }
        }

        return $blocks;
    }

    /**
     * Gets scheme and place it in private storage
     * @static
     * @param $target
     * @param $name
     * @return mixed
     */
    private static function _getScheme($name, $target = 'block_manager')
    {
        if (empty(self::$schemes[$name])) {
            self::$schemes[$name] = fn_get_schema($target, $name);
        }

        return self::$schemes[$name];
    }

    /**
     * Returns 'customer' or 'admin' for 'C' or 'A'
     * @param  string $area Area ('A' for admin or 'C' for customer)
     * @return string
     */
    private static function _normalizeArea($area)
    {
        if ($area == 'A') {
            $area = 'admin_dispatch';
        } else {
            $area = 'customer_dispatch';
        }

        return $area;
    }

    /**
     * Generates scheme data
     * @static
     * @param  mixed       $item Item from scheme
     * @return array|mixed
     */
    private static function _getValue($item)
    {
        // check, are there any function
        if (is_array($item)) {
            if (!empty($item[0]) && is_callable($item[0])) {
                // If it's a function execute it and return it result
                $callable = array_shift($item);
                return call_user_func_array($callable, $item);
            } elseif (!empty($item['data_function'][0]) && is_callable($item['data_function'][0])) {
                // If it's a data function, get the values
                $callable = array_shift($item['data_function']);
                $item['values'] = call_user_func_array($callable, $item['data_function']);
            }

            return $item;
        }

        // check for custom folder with templates
        $_dir = Registry::get('config.dir.root') . '/' . $item;
        if (is_dir($_dir)) {
            // If it's dir with templates return list of templates
            return fn_get_dir_contents($_dir, false, true);
        }

        // check for templates in the theme dir
        $theme_path = RenderManager::getCustomerThemePath();
        $tpl_path = $theme_path . $item;

        if (is_file($tpl_path)) {
            return array(
                strval($item) => array(
                    'name' => self::generateTemplateName($item, $theme_path)
                )
            );
        }

        // check for templates in given folder and addons too

        $tpl_files = fn_get_dir_contents($tpl_path, false, true, '.tpl', $item . '/');

        foreach (Registry::get('addons') as $addon => $addon_data) {
            if ($addon_data['status'] == 'A') {
                $_content = fn_get_dir_contents($theme_path . "addons/$addon/$item", false, true, '.tpl', "addons/$addon/$item/");
                if (!empty($_content)) {
                    $tpl_files = fn_array_merge($tpl_files, $_content, false);
                }
            }
        }

        if (!empty($tpl_files)) {
            $result = array();
            foreach ($tpl_files as $file) {
                $result[$file]['name'] = self::generateTemplateName($file, $theme_path);
            }

            return $result;
        }

        // if nothing was generated above, return given value
        return $item;
    }

    /**
     * Generates template name from language value
     * from {*block-description: *} comment from template.
     * @static
     * @param  string $path      Path to template
     * @param  string $theme_path Path to theme
     * @return string Name of template
     */
    public static function generateTemplateName($path, $theme_path, $area = AREA)
    {
        $name = fn_get_file_description($theme_path . $path, 'block-description', true);

        if (empty($name)) {
            $name = fn_basename($path, '.tpl');
        }

        if ($area == 'A') {
            $name = __($name);
        }

        return $name;
    }
}
