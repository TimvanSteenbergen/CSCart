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

use Tygh\Registry;

/**
 * Last view abstract class
 */
abstract class ACommon
{
    protected $_scheme;
    protected $_controller;
    protected $_mode;
    protected $_action;
    protected $_auth;

    /**
     * Prepares params for search
     *
     * @param array @params Request params
     * @return boolean Always true
     */
    abstract public function prepare(&$params);

    /**
     * Init search view params
     *
     * @param  string $object object to init view for
     * @param  array  $params request parameters
     * @return array  filtered params
    */
    abstract public function update($object, $params);

    /**
     * Gets current view data
     *
     * @return array View data
     */
    abstract protected function _getCurrentView();

    /**
     * Saves current view
     *
     * @param  array   $data View data
     * @return boolean Always true
     */
    abstract protected function _updateCurrentView($data);

    /**
     * Checks if prev/next links should be shown on current page
     *
     * @param  array   $params Page request params
     * @return boolean Result of checking
     */
    abstract protected function _isNeedViewTools($params);

    /**
     * Create new last view instance object
     *
     * @param  string $area Area identifier
     * @return void
     */
    public function __construct($area = AREA)
    {
        $schema_name = fn_get_area_name($area);
        $this->_controller = Registry::get('runtime.controller');
        $this->_mode = Registry::get('runtime.mode');
        $this->_action = Registry::get('runtime.action');

        $common_schema = fn_get_schema('last_view', $schema_name);
        $this->_schema = !empty($common_schema[$this->_controller]) ? $common_schema[$this->_controller] : array();

        $this->_auth = & $_SESSION['auth'];
    }

    /**
     * Inits Last View
     *
     * @param array @params Request params
     * @return boolean Flag that determines if view tools inited
     */
    public function init(&$params)
    {
        $this->_saveViewResults($params);

        if (($this->_isNeedViewTools($params)) && !$this->_initViewTools($params)) {
             $this->_initDefaultViewTools($params);
        }
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
        $data = $this->_getCurrentView();

        if (empty($data)) {
            return false;
        }

        $view_results = unserialize($data['view_results']);
        if (empty($view_results['items_ids'])) {
            return false;
        }

        $items_ids = $view_results['items_ids'];

        $current_id = $params[$this->_schema['item_id']];
        $prev_id = $next_id = $current_page = $current_pos = 0;

        foreach ($items_ids as $page => $items) {
            if (in_array($current_id, $items)) {
                for ($i = 0; $i < count($items); $i++) {
                    if ($items[$i] == $current_id) {
                        $prev_id = !empty($items[$i - 1]) ? $items[$i - 1] : 0;
                        $next_id = !empty($items[$i + 1]) ? $items[$i + 1] : 0;
                        $current_page = $page;
                        $current_pos = $i + 1;
                        break;
                    }
                }
            }
        }

        if (empty($current_pos)) {
            return false;
        }

        $next_page = $current_page + 1;
        $prev_page = $current_page - 1;
        $current_pos += $prev_page * $view_results['items_per_page'];
        $update_view = false;

        if (empty($next_id) && ($next_page <= $view_results['total_pages'])) {
            if (!empty($items_ids[$next_page])) {
                $next_id = !empty($items_ids[$next_page][0]) ? $items_ids[$next_page][0] : 0;
            } else {
                $next_items_ids = $this->_getAnotherPageIds($data['params'], $view_results['items_per_page'], $next_page);
                $next_id = !empty($next_items_ids[$next_page][0]) ? $next_items_ids[$next_page][0] : 0;

                //store new ids
                foreach ($next_items_ids as $page => $items) {
                    $items_ids[$page] = $items;
                }
                $update_view = true;
            }
        }

        if (empty($prev_id) && ($prev_page > 0)) {
            if (!empty($items_ids[$prev_page])) {
                $prev_id = !empty($items_ids[$prev_page][count($items_ids[$prev_page]) - 1]) ? $items_ids[$prev_page][count($items_ids[$prev_page]) - 1] : 0;//last on previus page
            } else {
                $prev_items_ids = $this->_getAnotherPageIds($data['params'], $view_results['items_per_page'], $prev_page);
                $prev_id = !empty($prev_items_ids[$prev_page][count($prev_items_ids[$prev_page])-1]) ? $prev_items_ids[$prev_page][count($prev_items_ids[$prev_page])-1] : 0;

                //store new ids
                foreach ($prev_items_ids as $page => $items) {
                    $items_ids[$page] = $items;
                }
                $update_view = true;
            }
        }

        if ($update_view) {
            $updated_results = array(
                'items_ids' => $items_ids,
                'total_pages' => $view_results['total_pages'],
                'items_per_page' => $view_results['items_per_page'],
                'total_items' => $view_results['total_items'],
            );
            $this->_updateCurrentView(array('view_results' => serialize($updated_results)));
        }

        $this ->_setViewTools($current_pos, $next_id, $prev_id, $view_results['total_items']);

        return true;
    }

    /**
     * Sets view tools
     *
     * @param  int     $current_pos Current position
     * @param  int     $next_id     Next element id
     * @param  int     $prev_id     Previous element id
     * @param  int     $total_items Total items
     * @return boolean Always true
     */
    protected function _setViewTools($current_pos, $next_id, $prev_id, $total_items)
    {
        $view_tools = array(
            'prev_id' => $prev_id,
            'next_id' => $next_id,
            'total' => $total_items,
            'current' => $current_pos,
            'prev_url' => fn_link_attach(Registry::get('config.current_url'), $this->_schema['item_id'] . '=' . $prev_id),
            'next_url' => fn_link_attach(Registry::get('config.current_url'), $this->_schema['item_id'] . '=' . $next_id),
        );

        if (!empty($this->_schema['show_item_id'])) {
            $view_tools['show_item_id'] = $this->_schema['show_item_id'];
        }
        if (!empty($this->_schema['links_label'])) {
            $view_tools['links_label'] = __($this->_schema['links_label']);
        }

        Registry::get('view')->assign('view_tools', $view_tools);

        return true;
    }

    /**
     * Saves current search results
     *
     * @param array @params Request params
     * @return boolean Always true
     */
    public function _saveViewResults($params)
    {
        if (!empty($params['save_view_results'])) {
            $view_results = Registry::get('view_results.' . $this->_schema['func']);

            $view = $this->_getCurrentView();
            if (!empty($view_results)) {
                $stored_items_ids = array();
                if (!empty($view['view_results'])) {
                    $stored_data = unserialize($view['view_results']);
                    $stored_items_ids = $stored_data['items_ids'];
                }
                foreach ($view_results['items_ids'] as $page => $items) {
                    $stored_items_ids[$page] = $items;
                }
                $updated_data = $view;
                $updated_data['params'] = serialize($view_results['params']);
                $updated_data['view_results'] = array(
                        'items_ids' => $stored_items_ids,
                        'total_pages' => $view_results['total_pages'],
                        'items_per_page' => $view_results['items_per_page'],
                        'total_items' => $view_results['total_items'],
                );
                $updated_data['view_results'] = serialize($updated_data['view_results']);
                $this->_updateCurrentView($updated_data);
            }
        }

        return true;
    }

    /**
    * Processes search results
    *
    * @param string $func Func Name
    * @param array $items Search result items
    * @param array $params Search params
    * @return array Array of the parsed data
    */
    public function processResults($func, $items, $params)
    {
        fn_set_hook('view_process_results_pre', $func, $items, $params);

        if (!empty($params['save_view_results']) && !empty($params['page'])) {
            $id = $params['save_view_results'];
            $pagination = fn_generate_pagination($params);

            if (empty($pagination)) {
                return false;
            }

            $current_page = $pagination['current_page'];

            $view_results = array(
                'items_ids' => array(),
                'total_pages' => $pagination['total_pages'],
                'items_per_page' => $pagination['items_per_page'],
                'total_items' =>$pagination['total_items'],
                'params' => $params,
            );

            $items_ids = array();
            foreach ($items as $item) {
                $view_results['items_ids'][$current_page][] = $item[$id];
            }

            Registry::set('view_results.fn_get_' . $func, $view_results);
        }

    }

    /**
     * Gets next page item ids
     *
     * @param  array $params         Search parameters
     * @param  int   $items_per_page Items per page
     * @param  int   $page           Page number
     * @return array next page items ids
     */
    protected function _getAnotherPageIds($params, $items_per_page, $page)
    {
        $_ids = array();
        $params = unserialize($params);
        if (!empty($this->_schema['additional_data'])) {
            $params = fn_array_merge($params, $this->_schema['additional_data']);
        }
        $params = fn_array_merge($params, array('page' => $page));

        if (!empty($this->_schema['auth'])) {
            list($items, ) = $this->_schema['func']($params, $this->_auth, $items_per_page);
        } elseif (!empty($this->_schema['skip_param'])) {
            list($items, ) = $this->_schema['func']($params, array(), $items_per_page);
        } else {
            list($items, ) = $this->_schema['func']($params, $items_per_page);

        }
        foreach ($items as $v) {
            $_ids[$page][] = $v[$this->_schema['item_id']];
        }

//         Registry::get('view')->assign('pagination', array()); //Unset pagination
        return $_ids;
    }

}
