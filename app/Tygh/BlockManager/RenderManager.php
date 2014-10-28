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

use Tygh\Debugger;
use Tygh\Embedded;
use Tygh\Registry;

class RenderManager
{
    const ADMIN = 'admin';
    const CUSTOMER = 'customer';

    /**
     * Current rendered location data
     * @var array Location data
     */
    private $_location;

    /**
     * Containers from current rendered location
     * @var array List of containers data
     */
    private $_containers;

    /**
     * Grids from current rendered location
     * @var array List of grids data
     */
    private $_grids;

    /**
     * Blocks from current rendered location
     * @var array List of blocks data
     */
    private $_blocks;

    /**
     * Current rendered area
     * @var string Current rendered area
     */
    private $_area;

    /**
     * Link to global Smarty object
     * @var Core Link to global Smarty object
     */
    private $_view;

    /**
     * Current theme name
     * @var string Current theme name
     */
    private $_theme;

    /**
     * @var array|bool
     */
    private $_dynamic_object_scheme;

    /**
     * @var array
     */
    private $_parent_grid;

    /**
     * Loads location data, containers, grids and blocks
     *
     * @param string $dispatch       URL dispatch (controller.mode.action)
     * @param string $area           Area ('A' for admin or 'C' for custom
     * @param array  $dynamic_object
     * @param int    $location_id
     * @param string $lang_code      2 letters language code
     */
    public function __construct($dispatch, $area, $dynamic_object = array(), $location_id = 0, $lang_code = DESCR_SL)
    {
        Debugger::checkpoint('Start render location');
        // Try to get location for this dispatch
        if ($location_id > 0) {
            $this->_location = Location::instance()->getById($location_id, $lang_code);
        } else {
            $this->_location = Location::instance()->get($dispatch, $dynamic_object, $lang_code);
        }

        $this->_area = $area;

        if (!empty($this->_location)) {
            if (isset($dynamic_object['object_id']) && $dynamic_object['object_id'] > 0) {
                $this->_containers = Container::getListByArea($this->_location['location_id'], 'C');
            } else {
                $this->_containers = Container::getListByArea($this->_location['location_id'], $this->_area);
            }

            $this->_grids = Grid::getList(array(
                'container_ids' => Container::getIds($this->_containers)
            ));

            $blocks = Block::instance()->getList(
                array('?:bm_snapping.*','?:bm_blocks.*', '?:bm_blocks_descriptions.*'),
                Grid::getIds($this->_grids),
                $dynamic_object,
                null,
                null,
                $lang_code
            );

            $this->_blocks = $blocks;

            $this->_view = Registry::get('view');
            $this->_theme = self::_getThemePath($this->_area);
            $this->_dynamic_object_scheme = SchemesManager::getDynamicObject($this->_location['dispatch'], 'C');
        }
    }

    /**
     * Renders current location
     * @return string HTML code of rendered location
     */
    public function render()
    {
        if (!empty($this->_location)) {

            $this->_view->assign('containers', array(
                'top_panel' => $this->_renderContainer($this->_containers['TOP_PANEL']),
                'header' => $this->_renderContainer($this->_containers['HEADER']),
                'content' => $this->_renderContainer($this->_containers['CONTENT']),
                'footer' => $this->_renderContainer($this->_containers['FOOTER']),
            ));

            Debugger::checkpoint('End render location');

            return $this->_view->fetch($this->_theme . 'location.tpl');
        } else {
            return '';
        }
    }

    /**
     * Renders container
     * @param  array  $container Container data to be rendered
     * @return string HTML code of rendered container
     */
    private function _renderContainer($container)
    {
        static $layout_width = 0;
        if (empty($layout_width)) {
            $layout_width = Registry::get('runtime.layout.width');
        }

        $content = '';
        $container['width'] = $layout_width;

        $this->_view->assign('container', $container);

        if (isset($this->_grids[$container['container_id']]) && ($this->_area == 'A' || $container['status'] != 'D')) {
            $grids = $this->_grids[$container['container_id']];
            $grids = fn_build_hierarchic_tree($grids, 'grid_id');
            $grids = $this->sortGrids($grids);

            $this->_parent_grid = array();
            $content = $this->_renderGrids($grids);

            $this->_view->assign('content', $content);

            return $this->_view->fetch($this->_theme . 'container.tpl');

        } else {
            $this->_view->assign('content', '');

            if ($this->_area == 'A') {
                return $this->_view->fetch($this->_theme . 'container.tpl');
            }
        }

    }

    private function _renderGrids($grids)
    {
        $_grids_content = array();

        $extra = array(
            'width' => 0,
            'alpha' => 0,
            'omega' => 0,
        );

        foreach ($grids as $index => $grid) {
            if (!empty($extra['width'])) {
                if (!empty($grid['fluid_width'])) {
                    $grid['fluid_width'] += $extra['width'];
                    $grids[$index]['fluid_width'] = $grid['fluid_width'];
                }
                if (!empty($grid['width'])) {
                    $grid['width'] += $extra['width'];
                    $grids[$index]['width'] = $grid['width'];
                }

                if (!empty($extra['alpha'])) {
                    $grid['alpha'] = $extra['alpha'];
                }

                if (!empty($extra['omega'])) {
                    $grid['omega'] = $extra['omega'];
                }
            }

            $_content = trim($this->_renderGrid($grid));

            $extra = array(
                'width' => 0,
                'alpha' => 0,
                'omega' => 0,
            );

            if (empty($_content)) {
                if ((!empty($grid['alpha']) && empty($grid['omega'])) || (empty($grid['alpha']) && empty($grid['omega']))) {
                    $extra['width'] = empty($grid['fluid_width']) ? $grid['width'] : $grid['fluid_width'];

                    if (!empty($grid['alpha'])) {
                        $extra['alpha'] = $grid['alpha'];
                    }

                } elseif (empty($grid['alpha']) && !empty($grid['omega'])) {
                    $extra['width'] = empty($grid['fluid_width']) ? $grid['width'] : $grid['fluid_width'];
                    if (!empty($grids[$prev_index]['fluid_width'])) {
                        $grids[$prev_index]['fluid_width'] += $extra['width'];
                    }
                    if (!empty($grids[$prev_index]['width'])) {
                        $grids[$prev_index]['width'] += $extra['width'];
                    }

                    $grids[$prev_index]['omega'] = $grid['omega'];

                    $_grids_content[$prev_index] = $this->_renderGrid($grids[$prev_index]);
                }

            } else {
                $_grids_content[$index] = $_content;
            }

            $prev_index = $index;
        }

        $content = implode('', $_grids_content);

        return $content;
    }

    /**
     * Renders grid
     * @param  int    $grid Grid data to be rendered
     * @return string HTML code of rendered grid
     */
    private function _renderGrid($grid)
    {
        $content = '';

        if ($this->_area == 'A' || $grid['status'] != 'D') {
            if (isset($grid['children']) && !empty($grid['children'])) {
                $grid['children'] = fn_sort_array_by_key($grid['children'], 'grid_id');
                $grid['children'] = self::sortGrids($grid['children']);

                $parent_grid = $this->_parent_grid;
                $this->_parent_grid = $grid;

                $content = $this->_renderGrids($grid['children']);

                $this->_parent_grid = $parent_grid;
            } else {
                $content .= $this->renderBlocks($grid);
            }
        }

        $this->_view->assign('content', $content);
        $this->_view->assign('parent_grid', $this->_parent_grid);
        $this->_view->assign('grid', $grid);

        return $this->_view->fetch($this->_theme . 'grid.tpl');
    }

    /**
     * Renders blocks in grid
     * @param  array  $grid Grid data
     * @return string HTML code of rendered blocks
     */
    public function renderBlocks($grid)
    {
        $content = '';

        if (isset($this->_blocks[$grid['grid_id']])) {
            foreach ($this->_blocks[$grid['grid_id']] as $block) {
                $block['status'] = self::correctStatusForDynamicObject($block, $this->_dynamic_object_scheme);

                /**
                 * Actions before render block
                 * @param array $grid Grid data
                 * @param array $block Block data
                 * @param object $this Current RenderManager object
                 * @param string $content Rendered content of blocks
                 */
                fn_set_hook('render_blocks', $grid, $block, $this, $content);

                if ($this->_area == 'C' && $block['status'] == 'D') {
                    // Do not render block in frontend if it disabled
                    continue;
                }

                $content .= self::renderBlock($block, $grid, $this->_area);
            }
        }

        return $content;
    }

    /**
     * Corrects status if this block has different status for some dynamic object
     * @param array $block Block data
     * @param $dynamic_object_scheme
     * @return string Status A or D
     */
    public static function correctStatusForDynamicObject($block, $dynamic_object_scheme)
    {
        $status = $block['status'];
        // If dynamic object defined correct status
        if (!empty($dynamic_object_scheme['key'])) {
            $status = 'A';
            $object_key = $dynamic_object_scheme['key'];

            if ($block['status'] == 'A' && in_array($_REQUEST[$object_key], $block['items_array'])) {
                // If block enabled globally and disabled for some dynamic object
                $status = 'D';
            } elseif ($block['status'] == 'D' && !in_array($_REQUEST[$object_key], $block['items_array'])) {
                // If block disabled globally and not enabled for some dynamic object
                $status = 'D';
            }
        }

        return $status;
    }

    /**
     * Renders block
     * @static
     * @param  array  $block             Block data to be rendered
     * @param  string $content_alignment Alignment of block (float left, float, right, width 100%)
     * @param  string $area              Area ('A' for admin or 'C' for custom
     * @return string HTML code of rendered block
     */
    /**
     * Renders block
     *
     * @static
     * @param  array  $block       Block data to be rendered
     * @param  array  $parent_grid Parent grid data
     * @param  string $area        Area name
     * @return string
     */
    public static function renderBlock($block, $parent_grid = array(), $area = 'C')
    {
        if (SchemesManager::isBlockExist($block['type'])) {
            $view = Registry::get('view');

            $view->assign('parent_grid', $parent_grid);

            $content_alignment = !empty($parent_grid['content_align']) ? $parent_grid['content_align'] : 'FULL_WIDTH';
            $view->assign('content_alignment', $content_alignment);

            if ($area == 'C') {
                return self::renderBlockContent($block);
            } elseif ($area == 'A') {
                $scheme = SchemesManager::getBlockScheme($block['type'], array());
                if (!empty($scheme['single_for_location'])) {
                    $block['single_for_location'] = true;
                }
                $view->assign('block_data', $block);

                return $view->fetch(self::_getThemePath($area) . 'block.tpl');
            }
        }

        return '';
    }

    /**
     * Renders block content
     * @static
     * @param  array  $block Block data for rendering content
     * @return string HTML code of rendered block content
     */
    public static function renderBlockContent($block)
    {
        // Do not render block if it disabled in the frontend
        if (isset($block['is_disabled']) && $block['is_disabled'] == 1) {
            return '';
        }

        $smarty = Registry::get('view');
        $_tpl_vars = $smarty->getTemplateVars(); // save state of original variables

        // By default block is displayed
        $display_block = true;

        self::_assignBlockSettings($block);

        // Assign block data from DB
        Registry::get('view')->assign('block', $block);

        $theme_path = self::getCustomerThemePath();

        $block_scheme = SchemesManager::getBlockScheme($block['type'], array());

        $grid_id = !empty($block['grid_id']) ? $block['grid_id'] : 0;
        $cache_name = 'block_content_'
            . $block['block_id'] . '_' . $block['snapping_id'] . '_' . $block['type']
            . '_' . $grid_id . '_' . $block['object_id'] . '_' . $block['object_type']
        ;

        $register_cache = true;

        if (isset($block['content']['items']['filling']) && isset($block_scheme['content']['items']['fillings'][$block['content']['items']['filling']]['disable_cache'])) {
            $register_cache = !$block_scheme['content']['items']['fillings'][$block['content']['items']['filling']]['disable_cache'];
        }

        /**
         * Determines flags for Cache
         *
         * @param array  $block          Block data
         * @param string $cache_name     Generated name of cache
         * @param array  $block_scheme   Block scheme
         * @param bool   $register_cache Flag to register cache
         * @param bool   $display_block  Flag to display block
         */
        fn_set_hook('render_block_register_cache', $block, $cache_name, $block_scheme, $register_cache, $display_block);

        if ($register_cache) {
            self::_registerBlockCache($cache_name, $block_scheme);
        }

        $block_content = '';

        if (isset($block_scheme['cache']) && Registry::isExist($cache_name) == true && self::allowCache()) {
            $block_content = Registry::get($cache_name);
        } else {
            if ($block['type'] == 'main') {
                $block_content = self::_renderMainContent();
            } else {

                $title = $block['name'];
                if (Registry::get('runtime.customization_mode.live_editor')) {
                    $le_block_types = fn_get_schema('customization', 'live_editor_block_types');
                    if (!empty($le_block_types[$block['type']]) && !empty($le_block_types[$block['type']]['name'])) {
                        $title = sprintf('<span data-ca-live-editor-obj="block:name:%s">%s</span>',
                            $block['block_id'], $title
                        );
                    }
                }
                Registry::get('view')->assign('title', $title);

                if (!empty($block_scheme['content'])) {
                    foreach ($block_scheme['content'] as $template_variable => $field) {
                        /**
                         * Actions before render any variable of block content
                         * @param string $template_variable name of current block content variable
                         * @param array $field Scheme of this content variable from block scheme content section
                         * @param array $block_scheme block scheme
                         * @param array $block Block data
                         */
                        fn_set_hook('render_block_content_pre', $template_variable, $field, $block_scheme, $block);
                        $value = self::getValue($template_variable, $field, $block_scheme, $block);

                        // If block have not empty content - display it
                        if (empty($value)) {
                            $display_block = false;
                        }

                        Registry::get('view')->assign($template_variable, $value);
                    }
                }

                // Assign block data from scheme
                Registry::get('view')->assign('block_scheme', $block_scheme);
                if ($display_block && file_exists($theme_path . $block['properties']['template'])) {
                    $block_content = Registry::get('view')->fetch($block['properties']['template']);
                }
            }

            if (!empty($block['wrapper']) && file_exists($theme_path . $block['wrapper']) && $display_block) {
                Registry::get('view')->assign('content', $block_content);

                if ($block['type'] == 'main') {
                    Registry::get('view')->assign('title', !empty(\Smarty::$_smarty_vars['capture']['mainbox_title']) ? \Smarty::$_smarty_vars['capture']['mainbox_title'] : '', false);
                }
                $block_content = Registry::get('view')->fetch($block['wrapper']);
            } else {
                Registry::get('view')->assign('content', $block_content);
                $block_content = Registry::get('view')->fetch('views/block_manager/render/block.tpl');
            }

            fn_set_hook('render_block_content_after', $block_scheme, $block, $block_content);

            if (isset($block_scheme['cache']) && $display_block == true && self::allowCache()) {
                Registry::set($cache_name, $block_content);
            }
        }

        $wrap_id = $smarty->getTemplateVars('block_wrap');

        $smarty->clearAllAssign();
        $smarty->assign($_tpl_vars); // restore original vars
        \Smarty::$_smarty_vars['capture']['title'] = null;

        if ($display_block == true) {
            if (!empty($wrap_id)) {
                $block_content = '<div id="' . $wrap_id . '">' . $block_content . '<!--' . $wrap_id . '--></div>';
            }

            return trim($block_content);
        } else {
            return '';
        }
    }

    /**
     * Returns true if cache used for blocks
     *
     * @static
     * @return bool true if we may use cahce, false otherwise
     */
    public static function allowCache()
    {
        $use_cache = true;
        if (Registry::ifGet('config.tweaks.disable_block_cache', false) || Registry::get('runtime.customizaton_mode.design') || Registry::get('runtime.customizaton_mode.translation')) {
            $use_cache = false;
        }

        return $use_cache;
    }

    /**
     * Renders content of main content block
     * @return string HTML code of rendered block content
     */
    private static function _renderMainContent()
    {
        $smarty = Registry::get('view');
        $content_tpl = $smarty->getTemplateVars('content_tpl');

        return !empty($content_tpl) ? $smarty->fetch($content_tpl) : '';
    }

    /**
     * Renders or gets value of some variable of block content
     * @param  string $template_variable name of current block content variable
     * @param  array  $field             Scheme of this content variable from block scheme content section
     * @param  array  $block_scheme      block scheme
     * @param  array  $block             Block data
     * @return string Rendered block content variable value
     */
    public static function getValue($template_variable, $field, $block_scheme, $block)
    {
        $value = '';
        // Init value by default
        if (isset($field['default_value'])) {
            $value = $field['default_value'];
        }

        if (isset($block['content'][$template_variable])) {
            $value = $block['content'][$template_variable];
        }

        if ($field['type'] == 'enum') {
            $value = Block::instance()->getItems($template_variable, $block, $block_scheme);
        }

        if ($field['type'] == 'function' && !empty($field['function'][0]) && is_callable($field['function'][0])) {
            $callable = array_shift($field['function']);
            array_unshift($field['function'], $value, $block, $block_scheme);
            $value = call_user_func_array($callable, $field['function']);
        }

        return $value;
    }

    /**
     * Registers block cache
     * @param string $cache_name   Cache name
     * @param array  $block_scheme Block scheme data
     */
    private static function _registerBlockCache($cache_name, $block_scheme)
    {
        if (isset($block_scheme['cache'])) {
            $additional_level = '';

            $default_handlers = fn_get_schema('block_manager', 'block_cache_properties');

            if (isset($block_scheme['cache']['update_handlers']) && is_array($block_scheme['cache']['update_handlers'])) {
                $handlers = $block_scheme['cache']['update_handlers'];
            } else {
                $handlers = array();
            }

            $cookie_data = fn_get_session_data();
            $cookie_data['all'] = $cookie_data;

            $additional_level .= self::_generateAdditionalCacheLevel($block_scheme['cache'], 'request_handlers', $_REQUEST);
            $additional_level .= self::_generateAdditionalCacheLevel($block_scheme['cache'], 'session_handlers', $_SESSION);
            $additional_level .= self::_generateAdditionalCacheLevel($block_scheme['cache'], 'cookie_handlers', $cookie_data);
            $additional_level .= self::_generateAdditionalCacheLevel($block_scheme['cache'], 'auth_handlers', $_SESSION['auth']);
            $additional_level .= '|path=' . Registry::get('config.current_path');
            $additional_level .= Embedded::isEnabled() ? '|embedded' : '';
            $additional_level = !empty($additional_level) ? md5($additional_level) : '';

            $handlers = array_merge($handlers, $default_handlers['update_handlers']);

            $cache_level = isset($block_scheme['cache']['cache_level']) ? $block_scheme['cache']['cache_level'] : Registry::cacheLevel('html_blocks');
            Registry::registerCache($cache_name, $handlers, $cache_level . '__' . $additional_level);
        }
    }

    /**
     * Generates additional cache levels by storage
     *
     * @param  array  $cache_scheme Block cache scheme
     * @param  string $handler_name Name of handlers frocm block scheme
     * @param  array  $storage      Storage to find params
     * @return string Additional chache level
     */
    private static function _generateAdditionalCacheLevel($cache_scheme, $handler_name, $storage)
    {
        $additional_level = '';

        if (!empty($cache_scheme[$handler_name]) && is_array($cache_scheme[$handler_name])) {
            foreach ($cache_scheme[$handler_name] as $param) {
                $param = fn_strtolower(str_replace('%', '', $param));
                if (isset($storage[$param])) {
                    $additional_level .= '|' . $param . '=' . md5(serialize($storage[$param]));
                }
            }
        }

        return $additional_level;
    }

    /**
     * Removes compiled block templates
     * @return bool
     */
    public static function deleteTemplatesCache()
    {
        static $is_deleted = false;

        if (!$is_deleted) {

            // mark cache as outdated
            Registry::setChangedTables('bm_blocks');
            // run cache routines
            Registry::save();

            $is_deleted = true;
        }

        return $is_deleted;
    }

    /**
     * Sorts grids by order parameter
     *
     * @param  array $grids Hierarchic builded tree
     * @return array Sorted grids
     */
    public static function sortGrids($grids)
    {
        $static_grids = array();
        foreach ($grids as $id => $grid) {
            if ($grid['order'] == 0) {
                $static_grids[] = $id;
            }

            if (!empty($grid['children'])) {
                $grid['children'] = self::sortGrids($grid['children']);
            }

            $grids[$id] = $grid;
        }

        $grids = fn_sort_array_by_key($grids, 'order', SORT_ASC);
        $sorted_grids = array();

        foreach ($static_grids as $grid_id) {
            $sorted_grids += array($grid_id => $grids[$grid_id]);
            unset($grids[$grid_id]);
        }

        $sorted_grids += $grids;

        return $sorted_grids;
    }

    /**
     * Assigns block properties data to template
     * @param array $block Block data
     */
    private static function _assignBlockSettings($block)
    {
        if (isset($block['properties']) && is_array($block['properties'])) {
            foreach ($block['properties'] as $name => $value) {
                Registry::get('view')->assign($name, $value);
            }
        }

    }

    /**
     * Returns customer theme path
     * @static
     * @return string Path to customer theme folder
     */
    public static function getCustomerThemePath()
    {
        return fn_get_theme_path('[themes]/[theme]/templates/', 'C');
    }

    /**
     * Returns theme path for different areas
     * @static
     * @param  string $area Area ('A' for admin or 'C' for custom
     * @return string Path to theme folder
     */
    private static function _getThemePath($area = 'C')
    {
        if ($area == 'C') {
            $area = self::CUSTOMER;
        } elseif ($area == 'A') {
            $area = self::ADMIN;
        }

        return 'views/block_manager/render/';
    }
}
