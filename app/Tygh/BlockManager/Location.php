<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *																	        *
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

/**
 * Location class
 */
class Location
{
    private static $_instance;
    private $_layout_id = 0;

    /**
     * Gets list of locations
     *
     * @param  array  $params    input params
     * @param  string $lang_code 2 letter language code
     * @return array  Array of locations data
     */
    public function getList($params = array(), $lang_code = CART_LANGUAGE)
    {
        /**
         * Prepares params for SQL query before getting locations
         * @param array $params input params
         * @param string $lang_code 2 letter language code
         */
        fn_set_hook('get_locations_pre', $params, $lang_code);

        $sortings = array (
            'location_id' => 'l.location_id',
            'dispatch' => 'l.dispatch',
            'is_default' => 'l.is_default',
            'layout_id' => 'l.layout_id',
            'location' => array('l.is_default', 'd.name'),
            'object_ids' => 'l.object_ids',
            'position' => 'l.position'
        );

        $sorting = db_sort($params, $sortings, 'is_default', 'desc');

        $join = $condition = '';

        if (!empty($params['dispatch'])) {
            $condition .= db_quote(" AND l.dispatch = ?s", $params['dispatch']);
        }

        if (!empty($params['location_id'])) {
            $condition .= db_quote(" AND l.location_id = ?i", $params['location_id']);
        }

        if (!empty($params['is_default'])) {
            $condition .= db_quote(" AND l.is_default = 1");
        }

        if (!empty($params['dynamic_object']) && !empty($params['dispatch'])) {

            if (!empty($params['dynamic_object']['object_id'])) {
                $dynamic_object_scheme = SchemesManager::getDynamicObject($params['dispatch'], 'C');

                if (!empty($dynamic_object_scheme)) {
                    $condition .= db_quote(" AND (FIND_IN_SET(?i, l.object_ids) OR l.object_ids = '')", $params['dynamic_object']['object_id']);
                }
            }
        }

        $limit = '';
        if (!empty($params['limit'])) {
            $limit = db_quote(" LIMIT ?i", $params['limit']);
        }

        // Try to get location for this dispatch
        $locations = db_get_hash_array(
            "SELECT * FROM ?:bm_locations as l "
                    . "LEFT JOIN ?:bm_locations_descriptions as d ON d.location_id = l.location_id AND d.lang_code = ?s ?p"
                    . "WHERE l.layout_id = ?i ?p $sorting $limit",
            'location_id',
            $lang_code,
            $join,
            $this->_layout_id,
            $condition
        );

        /**
         * Processes locations list after getting it
         * @param array $locations Array of locations data
         * @param string $lang_code 2 letter language code
         */
        fn_set_hook('get_locations_post', $locations, $lang_code);

        return $locations;
    }

    /**
     * Gets location for current <i>dispatch</i> and <i>lang_code</i>
     *
     * @param  string $dispatch       URL dispatch (controller.mode.action)
     * @param  array  $dynamic_object Dynamic object data
     * @param  string $lang_code      2 letter language code
     * @return array  Array of location data
     */
    public function get($dispatch, $dynamic_object = array(), $lang_code = CART_LANGUAGE)
    {
        /**
         * Prepares params for SQL query before getting location
         * @param string $dispatch URL dispatch (controller.mode.action)
         * @param string $lang_code 2 letter language code
         */
        fn_set_hook('get_location_pre', $dispatch, $lang_code);

        $dispatch = explode('.', $dispatch);

        $location = array();
        while (count($dispatch) > 0) {
            // Try to get location for this dispatch
            $locations = $this->getList(array(
                'dispatch' => implode('.', $dispatch),
                'dynamic_object' => $dynamic_object,
                'sort_by' => 'object_ids',
                'sort_order' => 'desc',
                'limit' => 1
            ));

            if (!empty($locations)) {
                $location = array_pop($locations);
                break;
            } else {
                array_pop($dispatch);
            }
        }

        // Get default location if there is no location for this dispatch
        if (empty($location)) {
            $location = $this->getDefault($lang_code);
        }

        /**
         * Processes location data after getting it
         * @param array $location Location data
         * @param string $lang_code 2 letter language code
         */
        fn_set_hook('get_location_post', $location, $lang_code);

        return $location;
    }

    /**
     * Gets location data by id
     *
     * @param  int    $location_id Location identifier
     * @param  string $lang_code   2 letter language code
     * @return array  Array of locations data
     */
    public function getById($location_id, $lang_code = CART_LANGUAGE)
    {
        $locations = $this->getList(array(
            'location_id' => $location_id
        ), $lang_code);

        return !empty($locations[$location_id]) ? $locations[$location_id] : array();
    }

    /**
     * Gets default location data
     *
     * @param  string $lang_code 2 letter language code
     * @return array  Array of locations data
     */
    public function getDefault($lang_code = CART_LANGUAGE)
    {
        $locations = $this->getList(array(
            'is_default' => true
        ), $lang_code);

        return !empty($locations) ? array_pop($locations) : array();
    }

    /**
     * Sets location with <i>$location_id</i> as default if it exists.
     * Returns true in success or false if this location does not exist
     *
     * @param  int  $location_id Location identifier
     * @return bool True in success, false otherwise
     */
    public function setDefault($location_id)
    {
        $location = $this->getById($location_id);

        if (!empty($location)) {
            /**
             * Actions before setting location as default
             * @param array $location Location data
             */
            fn_set_hook('set_default_location', $location);

            db_query('UPDATE ?:bm_locations SET is_default = IF(location_id = ?i, 1, 0) WHERE layout_id = ?i', $location_id, $this->_layout_id);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Creates or updates location. Returns id of new location or false on fail.
     * <pre>array (
     *  location_id - if not exists will be created new record
     *  dispatch
     *  description - description data @see BM_Location::_update_description()
     * )</pre>
     *
     * @param  array  $location_data Array of location data
     * @param  string $lang_code language code
     * @return int    Location id if new location was created, DB result otherwise
     */
    public function update($location_data, $lang_code = DESCR_SL)
    {
        if (!empty($location_data['is_default']) && $location_data['is_default'] == 'Y') {
            $default = true;
        } else {
            $default = false;
        }

        // We cannot set the is_default flag
        if (isset($location_data['is_default'])) {
            unset($location_data['is_default']);
        }

        if (empty($location_data['location_id']) && (!isset($location_data['position']) || trim($location_data['position']) === '')) {
            // Add new location to the last position
            $location_data['position'] = db_get_field("SELECT max(position) FROM ?:bm_locations");
            $location_data['position'] = $location_data['position'] + 10;
        }

        $location_data['layout_id'] = $this->_layout_id;

        /**
         * Processes location data before updating it
         * @param int $location_data Array of location data
         */
        fn_set_hook('update_location', $location_data);

        $location_id = db_replace_into('bm_locations', $location_data);

        if (!empty($location_data['location_id'])) {
            // Updating location
            $location_id = intval($location_data['location_id']);
            $this->_updateDescription($location_id, $location_data, $lang_code);

            if (!empty($location_data['copy'])) {
                foreach ($location_data['copy'] as $field) {
                    db_query("UPDATE ?:bm_locations SET ?f = ?s WHERE layout_id = ?i", $field, $location_data[$field], $this->_layout_id);
                }
            }

            if (!empty($location_data['copy_translated'])) {
                foreach ($location_data['copy_translated'] as $field) {
                    db_query("UPDATE ?:bm_locations_descriptions LEFT JOIN ?:bm_locations ON ?:bm_locations.location_id = ?:bm_locations_descriptions.location_id SET ?f = ?s WHERE ?:bm_locations.layout_id = ?i AND lang_code = ?s", $field, $location_data[$field], $this->_layout_id, $lang_code);
                }
            }

            /**
             * Actions to be performed after the location is updated
             * @param int $location_id Location identifier
             */
            fn_set_hook('location_updated', $location_id);
        } else {
            // Creating location.  We have to create three default containers (top, header, content, footer) for this location
            $containers = array();
            foreach (array('TOP_PANEL', 'HEADER', 'CONTENT', 'FOOTER') as $position) {
                $containers[] = db_quote('(?i, ?s, ?i)', $location_id, $position, 16);
            }

            db_query('INSERT INTO ?:bm_containers (`location_id`, `position`, `width`) VALUES ' . implode(', ', $containers));

            foreach (fn_get_translation_languages() as $location_data['lang_code'] => $v) {
                $this->_updateDescription($location_id, $location_data);
            }

            /**
             * Actions to be performed after the location is created
             * @param array $location_id Location identifier
             */
            fn_set_hook('location_created', $location_id);
        }

        if ($default) {
            $this->setDefault($location_id);
        }

        return $location_id;
    }

    /**
     * Removes non-default location with containers and grids. Set <i>force_removing</i>
     * to true to remove default location.
     *
     * @param  int  $location_id    Location identifier
     * @param  bool $force_removing Disable default location check
     * @return bool True in success, false otherwise
     */
    public function remove($location_id, $force_removing = false)
    {
        if (!empty ($location_id)) {
            $location_data = $this->getById($location_id);
            if (!empty($location_data) && (!$location_data['is_default']) || $force_removing) {
                /**
                 * Actions before removing location
                 * @param int $location_id Location identifier
                 * @param bool $force_removing Disable default location check
                 * @param $description
                 */
                fn_set_hook('remove_location', $location_id, $force_removing);

                db_query('DELETE FROM ?:bm_locations WHERE location_id = ?i', $location_id);
                db_query('DELETE FROM ?:bm_locations_descriptions WHERE location_id = ?i', $location_id);

                Container::removeMissing();
                Grid::removeMissing();

                return true;
            }
        }

        return false;
    }

    /**
     * Removes non-default location with containers and grids by dispatch.
     *
     * @param  string $dispatch Location identifier
     * @return bool   True in success, false otherwise
     */
    public function removeByDispatch($dispatch)
    {
        if (!empty ($dispatch)) {
            $locations = $this->getList(array(
                'dispatch' => $dispatch
            ));

            if (!empty($locations)) {
                foreach ($locations as $location) {
                    $this->remove($location['location_id']);
                }
            }
        }

        return false;
    }

    /**
     * Returns descriptions for all languages
     *
     * @param  int   $location_id Location identifier
     * @return array list of descriptions
     */
    public function getAllDescriptions($location_id)
    {
        return db_get_array("SELECT * FROM ?:bm_locations_descriptions WHERE location_id = ?i", $location_id);
    }

    /**
     * Copies all locations from current layout to another one
     * @param int $new_layout_id target layout ID
     */
    public function copy($new_layout_id)
    {
        $locations = db_get_hash_array("SELECT * FROM ?:bm_locations WHERE layout_id = ?i", 'location_id', $this->_layout_id);

        foreach ($locations as $location_id => $location) {
            unset($location['location_id']);
            $location['layout_id'] = $new_layout_id;
            $new_location_id = db_query("INSERT INTO ?:bm_locations ?e", $location);

            $descriptions = db_get_array("SELECT * FROM ?:bm_locations_descriptions WHERE location_id = ?i", $location_id);
            foreach ($descriptions as $description) {
                $description['location_id'] = $new_location_id;
                db_query("INSERT INTO ?:bm_locations_descriptions ?e", $description);
            }

            Container::copy($location_id, $new_location_id);
        }
    }

    /**
     * Returns object instance if Location class or create it if not exists
     * @static
     * @param  int      $company_id Company identifier
     * @param  string   $class_name ClassName
     * @return Location
     */
    public static function instance($layout_id = 0)
    {
        if (empty(self::$_instance)) {
            self::$_instance = new Location();
        }

        if (empty($layout_id)) {
            $layout = Layout::instance()->get(Registry::get('runtime.layout.layout_id'));
            $layout_id = $layout['layout_id'];
        }

        self::$_instance->_layout_id = $layout_id;

        return self::$_instance;
    }

    /**
     * Updates description of the location with  <i>$location_id</i>
     * <i>$description</i> must be array with this keys:
     * <pre>array (
     *  lang_code, (requred)
     *  name, (requred)
     *  title,
     *  meta_description,
     *  meta_keywords,
     * )</pre>
     *
     * @param  int    $location_id Location identifier
     * @param  array  $description Array of description data
     * @param  string $lang_code language code
     * @return bool  True in success, false otherwise
     */
    private function _updateDescription($location_id, $description, $lang_code = DESCR_SL)
    {
        if (!empty($location_id) && isset($description['name'])) {
            if (!isset($description['lang_code'])) {
                $description['lang_code'] = $lang_code;
            }

            $description['location_id'] = $location_id;

            /**
             * Processes location description before updating it
             * @param $description
             */
            fn_set_hook('update_location_description', $location, $dispatch, $lang_code);

            db_replace_into('bm_locations_descriptions', $description);

            return true;
        } else {
            return false;
        }
    }
}
