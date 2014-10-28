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
use Tygh\CompanySingleton;
use Tygh\Themes\Styles;
use Tygh\Settings;

class Layout extends CompanySingleton
{
    /**
     * Gets layout by ID
     * @param  int   $layout_id layout ID
     * @return array layout data
     */
    public function get($layout_id = 0)
    {
        $condition = "";
        if (fn_allowed_for('ULTIMATE')) {
            $condition = $this->getCompanyCondition('?:bm_layouts.company_id');
        }

        if (!empty($layout_id)) {
            $condition .= db_quote(" AND layout_id = ?i", $layout_id);
        } else {
            $condition .= db_quote(" AND is_default = 1");
        }

        return db_get_row("SELECT * FROM ?:bm_layouts WHERE 1 ?p", $condition);
    }

    public function getDefault($theme_name = '')
    {
        $condition = '';

        if (empty($theme_name)) {
            $theme_name = Settings::instance()->getValue('theme_name', '');
        }

        if (fn_allowed_for('ULTIMATE')) {
            $condition = $this->getCompanyCondition('?:bm_layouts.company_id');
        }

        $condition .= db_quote(" AND is_default = 1 AND theme_name = ?s", $theme_name);

        /**
         * Modifies the way to get default layout
         *
         * @param object  $this Layout object
         * @param string  $theme_name theme name
         * @param string  $condition part of SQL condition
         */
        fn_set_hook('layout_get_default', $this, $theme_name, $condition);

        $layout =  db_get_row("SELECT * FROM ?:bm_layouts WHERE 1 ?p", $condition);

        return $layout;
    }

    /**
     * Changes default layout for the theme
     *
     * @param  int  $layout_id Layout identifier
     * @return bool true
     */
    public function setDefault($layout_id)
    {
        $condition = '';

        if (fn_allowed_for('ULTIMATE')) {
            $condition .= db_quote(" AND company_id = ?i", $this->_company_id);
        }

        /**
         * Changes the way how layout is set as default
         *
         * @param object  $this Layout object
         * @param integer $layout_id layout ID
         * @param string  $condition part of SQL condition
         */
        fn_set_hook('layout_set_default', $this, $layout_id, $condition);

        $theme_name = db_get_field('SELECT theme_name FROM ?:bm_layouts WHERE layout_id = ?i', $layout_id);
        db_query('UPDATE ?:bm_layouts SET is_default = IF(layout_id = ?i, 1, 0) WHERE theme_name = ?s ?p', $layout_id, $theme_name, $condition);

        return true;
    }

    /**
     * Gets layouts list
     *
     * @param $array input params
     * @return array layouts list
     */
    public function getList($params = array())
    {
        $condition = '';
        if (fn_allowed_for('ULTIMATE')) {
            $condition = $this->getCompanyCondition('?:bm_layouts.company_id');
        }

        if (!empty($params['theme_name'])) {
            $condition .= db_quote(" AND theme_name = ?s", $params['theme_name']);
        }

        if (!empty($params['style_id'])) {
            $condition .= db_quote(" AND style_id = ?s", $params['style_id']);
        }

        /**
         * Modifies layouts list
         *
         * @param object  $this Layout object
         * @param array   $params search params
         * @param string  $condition part of SQL condition
         */
        fn_set_hook('layout_get_list', $this, $params, $condition);

        return db_get_hash_array("SELECT * FROM ?:bm_layouts WHERE 1 ?p", 'layout_id', $condition);
    }

    /**
     * Updates or creates layout
     * @param  array $layout_data layout data
     * @param  int   $layout_id   layout ID to update, zero to create
     * @return int   ID of updated/created layout
     */
    public function update($layout_data, $layout_id = 0)
    {
        $create = empty($layout_id);

        if (fn_allowed_for('ULTIMATE')) {
            if (empty($layout_data['company_id'])) {
                $layout_data['company_id'] = $this->_company_id;
            }
        }

        $theme_name = empty($layout_data['theme_name']) ? fn_get_theme_path('[theme]', 'C', $this->_company_id, false) : $layout_data['theme_name'];

        $available_styles = Styles::factory($theme_name)->getList(array(
            'short_info' => true
        ));

        /**
         * Performs actions before updating layout
         *
         * @param object  $this Layout object
         * @param integer $layout_id layout ID
         * @param array   $layout_data layout data
         * @param boolean $create create/update flag
         */
        fn_set_hook('layout_update_pre', $this, $layout_id, $layout_data, $create);

        if (empty($layout_id)) {
            if (!empty($layout_data['from_layout_id'])) {
                $layout_data['style_id'] = Styles::factory($theme_name)->getStyle($layout_data['from_layout_id']);

            }

            if (!empty($layout_data['style_id']) && !isset($available_styles[$layout_data['style_id']])) {
                unset($layout_data['style_id']);
            }

            if (empty($layout_data['style_id'])) {
                $layout_data['style_id'] = Styles::factory($theme_name)->getDefault();
            }

            $layout_id = db_query("INSERT INTO ?:bm_layouts ?e", $layout_data);

            // Create logos
            $types = fn_get_logo_types();
            foreach ($types as $type => $data) {
                if (!empty($data['for_layout'])) {
                    $object_ids[$type] = fn_create_logo(array(
                        'type' => $type,
                        'layout_id' => $layout_id
                    ), !empty($layout_data['company_id']) ? $layout_data['company_id'] : 0);
                }
            }

        } else {
            if (isset($layout_data['style_id']) && !isset($available_styles[$layout_data['style_id']])) {
                $layout_data['style_id'] = Styles::factory($theme_name)->getDefault();
            }

            db_query("UPDATE ?:bm_layouts SET ?u WHERE layout_id = ?i", $layout_data, $layout_id);
        }

        if (!empty($layout_data['is_default'])) {
            $this->setDefault($layout_id);
        }

        if (!empty($layout_data['from_layout_id'])) {
            $this->copyById($layout_data['from_layout_id'], $layout_id);
        }

        return $layout_id;
    }

    /**
     * Deletes layout and assigned data (logos)
     * @param  int     $layout_id layout ID
     * @return boolean always true
     */
    public function delete($layout_id)
    {
        // Delete locations, containers, grids and snappings
        $location_ids = db_get_fields("SELECT location_id FROM ?:bm_locations WHERE layout_id = ?i", $layout_id);
        if (!empty($location_ids)) {
            foreach ($location_ids as $location_id) {
                Location::instance($layout_id)->remove($location_id, true);
            }
        }

        db_query("DELETE FROM ?:bm_layouts WHERE layout_id = ?i", $layout_id);

        // Delete logos
        $logo_ids = db_get_fields("SELECT logo_id FROM ?:logos WHERE layout_id = ?i", $layout_id);
        if (!empty($logo_ids)) {
            foreach ($logo_ids as $logo_id) {
                fn_delete_image_pairs($logo_id, 'logos');
            }

            db_query("DELETE FROM ?:logos WHERE logo_id IN (?n)", $logo_ids);
        }

        return true;
    }

    /**
     * Copy all layouts from one company to another
     * @param  integer $to_company_id target company ID
     * @return mixed   true on success, false - otherwise
     */
    public function copy($to_company_id)
    {
        $from_layout = $this->getList();
        if (empty($from_layout)) {
            return false;
        }

        foreach ($from_layout as $layout) {
            $original_layout_id = $layout['layout_id'];
            unset($layout['layout_id'], $layout['company_id']);
            $layout['name'] .= ' (' . __('clone') . ')';
            $layout['company_id'] = $to_company_id;

            $new_layout_id = Layout::instance($to_company_id)->update($layout, 0);

            $this->copyById($original_layout_id, $new_layout_id);
        }

        return true;
    }

    /**
     * Copy all layout data from one layout to another by ID
     * @param  integer $from_layout_id source layout ID
     * @param  integer $to_layout_id   target layout ID
     * @return boolean true on success, false - otherwise
     */
    public function copyById($from_layout_id, $to_layout_id)
    {
        $from_layout = $this->get($from_layout_id);
        if (empty($from_layout)) {
            return false;
        }

        $object_ids = array();
        $location = Location::instance($from_layout_id)->copy($to_layout_id);

        $target_company_id = 0;
        if (fn_allowed_for('ULTIMATE')) {
            $target_company_id = db_get_field("SELECT company_id FROM ?:bm_layouts WHERE layout_id = ?i", $to_layout_id);
        }

        // Copy logos
        $types = fn_get_logo_types();
        foreach ($types as $type => $data) {
            if (!empty($data['for_layout'])) {
                $object_ids[$type] = fn_create_logo(array(
                    'type' => $type,
                    'layout_id' => $to_layout_id
                ), $target_company_id);
            }
        }

        $logo_ids = db_get_hash_single_array("SELECT logo_id, type FROM ?:logos WHERE layout_id = ?i AND type IN (?a)", array('type', 'logo_id'), $from_layout_id, array_keys($object_ids));
        foreach ($logo_ids as $type => $logo_id) {
            fn_clone_image_pairs($object_ids[$type], $logo_id, 'logos');
        }

        return true;
    }
}
