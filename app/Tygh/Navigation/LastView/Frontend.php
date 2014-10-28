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

namespace Tygh\Navigation\LastView;

/**
 * Last view frontend class
 */
class Frontend extends ACommon
{
    protected $_view_controller;

    /**
     * Create new last view instance object
     *
     * @param  string $area Area identifier
     * @return void
     */
    public function __construct($area = AREA)
    {
        parent::__construct($area);
        $this->_view_controller = !empty($this->_schema['view_controller']) ? $this->_schema['view_controller'] : $this->_controller;
    }

    /**
     * Prepares params for search
     *
     * @param array @params Request params
     * @return boolean Always true
     */
    public function prepare(&$params)
    {
        if (!empty($this->_schema) && !empty($this->_schema['list_mode']) && ($this->_schema['list_mode'] == $this->_mode || (is_array($this->_schema['list_mode']) && in_array($this->_mode, $this->_schema['list_mode']))) && isset($this->_schema['func'])) {
            $_params = $params;
            unset($_params['dispatch'], $_params['page']);

            $data = array (
                'params' => serialize($_params),
                'view_results' => serialize(array('items_ids' => array(), 'total_pages' => 0, 'items_per_page' => 0, 'total_items' => 0)),
            );

            $this->_updateCurrentView($data);

            $params['save_view_results'] = $this->_schema['item_id'];

        }

        return true;
    }

    /**
     * Init search view
     *
     * @param  string $object object to init view for
     * @param  array  $params request parameters
     * @return array  filtered params
    */
    public function update($object, $params)
    {
        return $params;
    }

    /**
     * Gets current view data
     *
     * @return array View data
     */
    protected function _getCurrentView()
    {
        $data = array();

        $data = !empty($_SESSION['last_view']['lv_' . $this->_view_controller]) ? $_SESSION['last_view']['lv_' . $this->_view_controller] : array();

        return $data;
    }

    /**
     * Saves current view
     *
     * @param  array   $data View data
     * @return boolean Always true
     */
    protected function _updateCurrentView($data)
    {
        if (!empty($_SESSION['last_view']['lv_' . $this->_view_controller])) {
            $data = array_merge($_SESSION['last_view']['lv_' . $this->_view_controller], $data);
        }

        $_SESSION['last_view']['lv_' . $this->_view_controller] = $data;

        return true;
    }

    /**
     * Checks if prev/next links should be shown on current page
     *
     * @param  array   $params Page request params
     * @return boolean Result of checking
     */
    protected function _isNeedViewTools($params)
    {
        $result = false;
        if (!empty($this->_schema) && isset($this->_schema['item_id']) && isset($params[$this->_schema['item_id']])) {
            if (!empty($this->_schema['view_mode']) && $this->_schema['view_mode'] == $this->_mode) {
                $result = true;
            }

            if ($this->_mode == 'view') {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Initiates default view tools
     * if item was not found in default result
     *
     * @param array @params Request params
     * @return boolean Flag that determines if tools were inited
     */
    public function _initDefaultViewTools($params)
    {
        if (!empty($this->_schema['default_navigation'])) {
            $nav = $this->_schema['default_navigation'];

            if ((empty($nav['mode']) || $nav['mode'] == $this->_mode) && is_callable($nav['function'])) {
                $update_data = call_user_func_array($nav['function'], array('params' => $params));

                if (!empty($update_data)) {
                    $this->_updateCurrentView($update_data);

                    return $this->_initViewTools($params);
                }
            }
        }

        return false;
    }

    /**
     * Initiates view tools
     *
     * @param array @params Request params
     * @return boolean Flag that determines if tools were inited
     */
    public function _initViewTools($params)
    {
        if (!empty($params['n_items']) && !empty($params['n_plain'])) {
            $items = explode(",", $params['n_items']);
            $current_id = $params[$this->_schema['item_id']];
            $prev_id = $next_id = $current_pos = 0;

            if (in_array($current_id, $items)) {
                $total_items = count($items);

                for ($i = 0; $i < count($items); $i++) {
                    if ($items[$i] == $current_id) {

                        $prev_id = !empty($items[$i - 1]) ? $items[$i - 1] : 0;
                        $next_id = !empty($items[$i + 1]) ? $items[$i + 1] : 0;
                        $current_pos = $i + 1;
                        break;
                    }
                }
            }

            if (!empty($next_id) || !empty($prev_id)) {
                $this ->_setViewTools($current_pos, $next_id, $prev_id, $total_items);

                return true;
            }
        }

        return parent::_initViewTools($params);
    }
}
