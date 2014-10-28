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

namespace Tygh\Navigation;

class Breadcrumbs
{
    private static $_instance;
    private static $_schema_params = array (
        'title' => true,
        'link' => true,
        'nofollow' => false
    );

    private $_links = array();
    private $_request = array();
    private $_prev_request = array();
    private $_area = AREA;
    private $_schema = false;

    /**
     * Breadcrumbs constructor
     *
     * @param  string $controller      Controller
     * @param  string $mode            Mode
     * @param  string $area            Area code
     * @param  array  $request         Request params
     * @param  array  $request_history History request params
     * @return void
     */
    public function __construct($controller = '', $mode = '', $area = AREA, $request =  array(), $prev_request = array())
    {
        $this->_request = $request;
        $this->_prev_request = $prev_request;
        $this->_area = $area;
        $this->_initSchema($controller, $mode);
        $this->_addSchemaLinks();
    }

    /**
     * Gets instance, parameters for constructor may be passed
     *
     * @param  string $controller      Controller
     * @param  string $mode            Mode
     * @param  string $area            Area code
     * @param  array  $request         Request params
     * @param  array  $request_history History request params
     * @return object instance
     */
    public static function instance($controller = '', $mode = '', $area = AREA, $request =  array(), $prev_request = array())
    {
        if (!self::$_instance) {
            self::$_instance = new Breadcrumbs($controller, $mode, $area, $request, $prev_request);
        }

        return self::$_instance;
    }

    /**
     * Gets breadcrumbs items
     * @return array breadcrumbs items
     */
    public function getLinks()
    {
        return $this->_links;
    }

    /**
     * Adds new node the breadcrumbs
     *
     * @param  string  $lang_value name of language variable
     * @param  string  $link       breadcrumb URL
     * @param  boolean $nofollow   Include or not "nofollow" attribute
     * @param  boolean $is_first   Flag that defines if parameter should be added to the beging (default false)
     * @return boolean True if breadcrumbs were added, false otherwise
     */
    public function addLink($title, $link = '', $nofollow = false, $is_first = false)
    {
        if ($this->_area == 'A' && !fn_check_view_permissions($link, 'GET')) {
            return false;
        }

        fn_set_hook('add_breadcrumb', $title, $link);

        $item = array(
            'title' => $title,
            'link' => $link,
            'nofollow' => $nofollow,
        );

        if ($is_first) {
            array_unshift($this->_links, $item);
        } else {
            $this->_links[] = $item;
        }

        return true;
    }

    /**
     * Gets breadcrumbs schema for current dispatch
     *
     * @param  string $controller Controller
     * @param  string $mode       Mode
     * @return array  Parsed items
     */
    private function _initSchema($controller, $mode)
    {
        $name = ($this->_area == 'A') ? 'backend' : 'frontend';
        $schema = fn_get_schema('breadcrumbs', 'backend', 'php');

        $dispatch = $controller . '.' . $mode;

        if ($mode == 'add' && empty($schema[$dispatch])) {
            // Use 'update' breadcrumbs for 'add' mode if 'add' breadcrumbs not defined
            $dispatch = $controller . '.update';
        }

        if (!empty($schema) && !empty($schema[$dispatch])) {
            $this->_schema = $schema[$dispatch];
        }

        return true;
    }

    /**
     * Gets breadcrumbs items from schema
     *
     * @return boolean Flag that determines if items added
     */
    private function _addSchemaLinks()
    {
        $result = false;

        if (!empty($this->_schema)) {
            foreach ($this->_schema as $schema_item) {
                $add_link = false;

                if (!empty($schema_item['type']) && $schema_item['type'] == 'search') {
                    // search link
                    if (!empty($schema_item['prev_dispatch']) && !empty($this->_prev_request['dispatch']) && $schema_item['prev_dispatch'] == $this->_prev_request['dispatch']) {
                        if (!empty($this->_prev_request['is_search'])) {
                            // add search results link if search used on previous page
                            // FIX ME: move to function if link types quantity increases
                            $add_link = true;
                            $this->_has_history_links = true;
                        }
                    }
                } elseif (!empty($schema_item['prev_check_func'])) {
                    // custom lnk depending of history
                    $params = $this->_request;
                    $params['prev_request'] = $this->_prev_request;
                    $add_link = $this->_callSchemaMethod($schema_item['prev_check_func'], $params);
                } else {
                    // simple link
                    $add_link =  true;
                }

                if ($add_link && $item = $this->_parseSchemaItem($schema_item)) {
                    $this->addLink($item['title'], $item['link'], $item['nofollow']);
                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * Gets breadcrumb item from schema
     *
     * @param  array $schema_item Item schema
     * @return array Parsed item
     */
    private function _parseSchemaItem($schema_item)
    {
        $item = array();

        if (!empty($schema_item['function'])) {
            $item = $this->_callSchemaMethod($schema_item['function']);
        }

        foreach (self::$_schema_params as $name => $is_required) {
            if (empty($item[$name])) {
                $item[$name] = !empty($schema_item[$name]) ? $this->_getSchemaParam($name, $schema_item[$name]) : false;
            }

            if ($is_required && empty($item[$name])) {
                return false;
            }

        }

        return $item;
    }

    /**
     * Calls method described in schema and return the result
     *
     * @param  array $args         Parameter arguments
     * @param  array $param_values Array of params that should be passed to function, use current request by default
     * @return mixed Result
     */
    private function _callSchemaMethod($args, $param_values = array())
    {
        $result = '';

        $param_values = !empty($param_values) ? $param_values : $this->_request;

        $function = array_shift($args);
        if (is_callable($function)) {
            foreach ($args as $arg) {
                if (strpos($arg, '@') !== false) {
                    $_opt = str_replace('@', '', $arg);
                    $params[] = isset($param_values[$_opt]) ? $param_values[$_opt] : '';
                } else {
                    $params[] = $arg;
                }
            }

            $result = call_user_func_array($function, $params);
        }

        return $result;
    }

    /**
     * Parses common schema parameter types
     *
     * @param  array  $name  Parameter name
     * @param  array  $param Parameter data
     * @return string Result of parse
     */
    private function _getSchemaParam($name, $param)
    {
        $result = '';
        if (is_array($param)) {
            if (!empty($param['function'])) {
                $result = $this->_callSchemaMethod($param['function']);
            }
        } else {
            $result = $param;

            if ($name == 'title') {
                $result = __($result);

            } elseif ($name == 'link') {
                $result = fn_substitute_vars($result, $this->_request);

            }
        }

        return $result;
    }

}
