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
 * Last view backend class
 */
class Backend extends ACommon
{
    /**
     * Prepares params for search
     *
     * @param array @params Request params
     * @return boolean Always true
     */
    public function prepare(&$params)
    {
        if (!empty($params['return_to_list']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $params['redirect_url'] = $this->_controller . '.' . (empty($this->_schema['list_mode']) ? 'manage' : $this->_schema['list_mode']) . '.last_view';
            if ($this->_controller == 'profiles' && !empty($params['user_type'])) {
                $params['redirect_url'] .= '&user_type=' . $params['user_type'];
            }
            if (!empty($this->_schema['selected_section'])) {
                $params['selected_section'] = $this->_schema['selected_section'];
            } elseif (!empty($this->_schema['update_mode']) && is_array($this->_schema['update_mode']) && isset($this->_schema['update_mode'][$this->_mode]) && !empty($this->_schema['update_mode'][$this->_mode]['selected_section'])) {
                $params['selected_section'] = $this->_schema['update_mode'][$this->_mode]['selected_section'];
            } else {
                unset($params['selected_section']);
            }

            return true;
        }

        if (isset($this->_schema) && ((!empty($this->_schema['list_mode']) && $this->_schema['list_mode'] == $this->_mode) || $this->_mode == 'manage') && (empty($this->_schema['update_mode']) || (!empty($this->_schema['update_mode']) && !is_array($this->_schema['update_mode']))) && isset($this->_schema['func'])) {
            $sort_data = array('sort_by' => '', 'sort_order' => '');
            if (Registry::get('runtime.action') == 'last_view' && empty($params['view_id'])) {

                $data = $this->_getCurrentView();
                if (!empty($data)) {
                    $data['active'] = 'N';
                    $this->_updateCurrentView($data);

                    $view_params = unserialize($data['params']);
                    if (!empty($params['sort_by']) && !empty($view_params['sort_by'])) {
                        $sort_data['sort_by'] = $view_params['sort_by'];
                        unset($view_params['sort_by']);
                    }
                    if (!empty($params['sort_order']) && !empty($view_params['sort_order'])) {
                        $sort_data['sort_order'] = $view_params['sort_order'];
                        unset($view_params['sort_order']);
                    }
                    if (!empty($view_params['dispatch'])) {
                        unset($params['dispatch']);
                    }
                    $params = fn_array_merge($view_params, $params);
                }

            }

            $sort_params = array('sort_by' => !empty($params['sort_by']) ? $params['sort_by'] : '', 'sort_order' => !empty($params['sort_order']) ? $params['sort_order'] : '');

            $_actions = array('save_view', 'delete_view');

            if (!in_array(Registry::get('runtime.action'), $_actions) && (!(Registry::get('runtime.action') == 'last_view' && empty($params['view_id'])) || (Registry::get('runtime.action') == 'last_view' && empty($params['view_id']) && $sort_data != $sort_params))) {
                $_params = $params;
                unset($_params['dispatch'], $_params['page']);
                $view = $this->_getCurrentView();

                if (empty($view)) {
                    $data = array (
                        'object' => 'lv_' . $this->_controller,
                        'params' => serialize($_params),
                        'view_results' => serialize(array('items_ids' => array(), 'total_pages' => 0, 'items_per_page' => 0, 'total_items' => 0)),
                        'user_id' => $this->_auth['user_id']
                    );
                    $this->_updateCurrentView($data);
                }

                if (!empty($view) && (serialize($_params) != $view['params'])) {
                    $data = array (
                        'params' => serialize($_params),
                        'view_results' => serialize(array('items_ids' => array(), 'total_pages' => 0, 'items_per_page' => 0, 'total_items' => 0)),
                    );
                    $this->_updateCurrentView($data);
                }

                $params['save_view_results'] = $this->_schema['item_id'];
            }
        }

        return true;
    }

    /**
     * Init search view params
     *
     * @param  string $object object to init view for
     * @param  array  $params request parameters
     * @return array  filtered params
    */
    public function update($object, $params)
    {
        if (!empty($params['skip_view'])) {
            return $params;
        }

        $this->_checkUpdateActions($object, $params);

        if (!empty($params['view_id'])) {
            $data = db_get_row("SELECT params, view_id FROM ?:views WHERE view_id = ?i", $params['view_id']);
            if (!empty($data)) {
                $result['view_id'] = $data['view_id'];
                $result = fn_array_merge($params, unserialize($data['params']));

                if (!empty($params['sort_by'])) {
                    $result['sort_by'] = $params['sort_by'];
                }

                if (!empty($params['sort_order'])) {
                    $result['sort_order'] = $params['sort_order'];
                }

                db_query("UPDATE ?:views SET active = IF(view_id = ?i, 'Y', 'N') WHERE user_id = ?i AND object = ?s", $data['view_id'], $this->_auth['user_id'], $object);

                return $result;
            }
        }

        return $params;
    }

    /**
     * Gets current view data
     *
     * @return array View data
     */
    protected function _getCurrentView()
    {
        return db_get_row("SELECT * FROM ?:views WHERE user_id = ?i AND object = ?s", $this->_auth['user_id'], 'lv_' . $this->_controller);
    }

    /**
     * Saves current view
     *
     * @param  array   $data View data
     * @return boolean Always true
     */
    protected function _updateCurrentView($data)
    {
        if (!empty($data['view_id'])) {
            db_query("UPDATE ?:views SET ?u WHERE view_id = ?i", $data, $data['view_id']);
        } else {
            db_query("INSERT INTO ?:views ?e", $data);
        }

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

            if (empty($this->_schema['update_mode'])) {
                if ($this->_mode == 'update') {
                    $result = true;
                }
            } elseif (!empty($this->_schema['update_mode']) && !is_array($this->_schema['update_mode']) && $this->_schema['update_mode'] == $this->_mode) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Processes view actions
     *
     * @param  string  $object object to init view for
     * @param  array   $params request parameters
     * @return boolean Always true
     */
    protected function _checkUpdateActions($object, $params)
    {
        // Save view
        if ($this->_action == 'save_view' && !empty($params['new_view'])) {
            $name = $params['new_view'];
            $update_view_id = empty($params['update_view_id']) ? 0 : $params['update_view_id'];
            unset($params['dispatch'], $params['page'], $params['new_view'], $params['update_view_id']);
            $data = array (
                'object' => $object,
                'name' => $name,
                'params' => serialize($params),
                'user_id' => $this->_auth['user_id']
            );

            if ($update_view_id) {
                db_query("UPDATE ?:views SET ?u WHERE view_id = ?i", $data, $update_view_id);
                $params['view_id'] = $update_view_id;
            } else {
                $params['view_id'] = db_query("REPLACE INTO ?:views ?e", $data);
            }

            fn_redirect(Registry::get('runtime.controller') . '.' . Registry::get('runtime.mode') . '?' . http_build_query($params));

        } elseif ($this->_action == 'delete_view' && !empty($params['view_id'])) {
            db_query("DELETE FROM ?:views WHERE view_id = ?i", $params['view_id']);

        } elseif ($this->_action == 'reset_view') {
            db_query("UPDATE ?:views SET active = 'N' WHERE user_id = ?i AND object = ?s", $this->_auth['user_id'], $object);
        }

        return true;
    }

}
