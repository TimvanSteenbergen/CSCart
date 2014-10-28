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

/**
 * Grid class
 */
class Grid
{
    /**
     * Gets list of grids
     * @static
     * @param  array $params input params
     * @return array Array of grids as container_id => array(grid_id => block data)
     */
    public static function getList($params = array())
    {
        /**
         * Prepares params for SQL query before getting grids
         * @param array $params input params
         * @param string $lang_code 2 letter language code
         */
        fn_set_hook('get_grids_pre', $params);

        $condition = $join = '';

        $fields = array(
            '*'
        );

        if (!empty($params['container_ids'])) {
            $condition .= db_quote(" AND g.container_id IN (?n)", $params['container_ids']);
        }

        if (!empty($params['simple'])) {
            $fields = array(
                'g.grid_id',
                'g.container_id'
            );
        }

        $grids = db_get_hash_multi_array(
            "SELECT " . implode(', ', $fields) . " FROM ?:bm_containers as c "
            . "LEFT JOIN ?:bm_grids as g ON g.container_id = c.container_id ?p"
            . "WHERE 1 ?p ORDER BY g.order, g.parent_id, g.grid_id ASC",
            array('container_id', 'grid_id'),
            $join,
            $condition
        );

        /**
         * Processes grids list after getting it
         * @param array $grids Array of grids data
         */
        fn_set_hook('get_grids_post', $grids);

        return $grids;
    }

    /**
     * Gets grid data by id
     * @static
     * @param  int    $grid_id   Grid identifier
     * @param  string $lang_code 2 letter language code
     * @return array  Grid data
     */
    public static function getById($grid_id, $lang_code = CART_LANGUAGE)
    {
        /**
         * Prepares params for SQL query before getting grid
         * @param int $grid_id Grid identifier
         * @param string $lang_code 2 letter language code
         */
        fn_set_hook('get_grid_pre', $grid_id, $lang_code);

        $grid = db_get_row('SELECT * FROM ?:bm_grids WHERE grid_id = ?i ORDER BY ?:bm_grids.order', $grid_id);

        /**
         * Processes grid data after getting it
         * @param array $grid Array of grid data
         * @param string $lang_code 2 letter language code
         */
        fn_set_hook('get_grid_post', $grid, $lang_code);

        return $grid;
    }

    /**
     * Gets identifiers of grids from array of grids as container_id => array(grid_id => block data)
     * @static
     * @param  array $grids Array of grids as container_id => array(grid_id => block data)
     * @return array Grid identifiers
     */
    public static function getIds($grids)
    {
        $grids_ids = array();
        foreach ($grids as $container) {
            $grids_ids = array_merge($grids_ids, array_keys($container));
        }

        return $grids_ids;
    }

    /**
     * Creates or updates grid
     * <i>$grid_data</i> must be array in this format
     * <pre>array (
     *  grid_id
     *  container_id
     *  parent_id
     *  order
     *  width - grid 960 param
     *  suffix - grid 960 param
     *  prefix - grid 960 param
     *  omega - grid 960 param
     *  alpha - grid 960 param
     *  wrapper - path to wrapper template relative to "templates" directory
     *  content_align - LEFT|RIGHT|FULL_WIDTH, blocks in this grid will be placed as float left, float right or with width 100% in case.
     *  html_element - name of html element of this grid (div, ul, li, p, etc.)
     *  clear - If 1 then after this grid will be clear div on rendered page
     *  user_class
     * )</pre>
     * @static
     * @param  array         $grid_data Array of grid data
     * @return int|db_result Grid id if new grid was created, DB result otherwise
     */
    public static function update($grid_data)
    {
        /**
         * Processes grid data before update it
         * @param int $grid_data Array of grid data
         */
        fn_set_hook('update_grid', $grid_data);

        $grid_id = db_replace_into('bm_grids', $grid_data);

        return $grid_id;
    }

    /**
     * Removes grid
     * @param  int  $grid_id Grid identifier
     * @return bool True in success, false otherwise
     */
    public static function remove($grid_id)
    {
        $grids = db_get_hash_array('SELECT b.grid_id, b.parent_id FROM ?:bm_grids as a LEFT JOIN ?:bm_grids as b ON a.container_id = b.container_id WHERE a.grid_id = ?i ORDER BY b.parent_id, b.grid_id ASC', 'grid_id', $grid_id);

        if (!empty($grids)) {
            $grids = fn_build_hierarchic_tree($grids, 'grid_id');

            foreach ($grids as $grid) {
                self::_remove($grid_id, $grid);
            }

            self::removeMissing();

            return true;
        } else {
            return false;
        }
    }

    /**
     * Performs a cleanup: removes grid related data
     * @static
     * @return bool Always true
     */
    public static function removeMissing()
    {
        // Remove missing grids
        db_remove_missing_records('bm_grids', 'container_id', 'bm_containers');

        // Remove missing snappings
        db_remove_missing_records('bm_snapping', 'grid_id', 'bm_grids');

        return true;
    }

    /**
     * Copies grids/snappings from one container to another
     * @param int $container_id     source container ID
     * @param int $new_container_id target container ID
     */
    public static function copy($container_id, $new_container_id)
    {
        $clone_blocks = false;

        if (fn_allowed_for('ULTIMATE')) {
            $company_ids = db_get_hash_single_array(
                "SELECT lay.company_id, con.container_id  FROM ?:bm_layouts as lay " .
                "LEFT JOIN ?:bm_locations as loc ON loc.layout_id = lay.layout_id " .
                "LEFT JOIN ?:bm_containers as con ON con.location_id = loc.location_id " .
                "WHERE con.container_id IN (?n)",
            array('container_id', 'company_id'),
            array($container_id, $new_container_id));

            if ($company_ids[$container_id] != $company_ids[$new_container_id]) {
                $clone_blocks = true;
            }
        }

        $links = array();
        $grids = db_get_hash_array("SELECT * FROM ?:bm_grids WHERE container_id = ?i ORDER BY grid_id", 'grid_id', $container_id); //FIXME: order should not be by grid ID
        foreach ($grids as $grid_id => $grid) {
            unset($grid['grid_id']);
            $grid['container_id'] = $new_container_id;

            $new_grid_id = db_query("INSERT INTO ?:bm_grids ?e", $grid);
            $links[$grid_id] = $new_grid_id;
        }

        $new_snapping_ids = array();
        foreach ($links as $old_grid_id => $new_grid_id) {
            db_query("UPDATE ?:bm_grids SET parent_id = ?i WHERE parent_id = ?i AND grid_id IN (?n)", $new_grid_id, $old_grid_id, $links);

            $snappings = db_get_hash_array("SELECT * FROM ?:bm_snapping WHERE grid_id = ?i", 'snapping_id', $old_grid_id);
            foreach ($snappings as $snapping_id => $snapping) {
                unset($snapping['snapping_id']);
                $snapping['grid_id'] = $new_grid_id;
                $new_snapping_id = db_query("INSERT INTO ?:bm_snapping ?e", $snapping);
                $new_snapping_ids[$snapping_id] = $new_snapping_id;

                $statuses = db_get_array("SELECT * FROM ?:bm_block_statuses WHERE snapping_id = ?i", $snapping_id);
                foreach ($statuses as $status) {
                    $status['snapping_id'] = $new_snapping_id;
                    db_query("INSERT INTO ?:bm_block_statuses ?e", $status);
                }
            }
        }

        if ($clone_blocks == true) {
            Block::instance($company_ids[$container_id])->copy($new_snapping_ids, $company_ids[$new_container_id]);
        }
    }

    /**
     * Sets the <i>clear<i/> param as 1 on the grids that must have a clear div after them.
     * @static
     * @param  array $clear_divs_data
     * @return bool  Always true
     */
    public static function setClearDivs($clear_divs_data)
    {
        if (!empty($clear_divs_data['containers'])) {
            db_query('UPDATE ?:bm_grids SET clear = 0 WHERE container_id IN (?a)', array_keys($clear_divs_data['containers']));
        }

        if (!empty($clear_divs_data['grids'])) {
            db_query('UPDATE ?:bm_grids SET clear = 1 WHERE grid_id IN (?a)', array_keys($clear_divs_data['grids']));
        }

        return true;
    }

    /**
     * @static
     * @param $start_grid_id
     * @param $grid
     * @param bool $delete_grids
     */
    private static function _remove($start_grid_id, $grid, $delete_grids = false)
    {
        if (isset($grid['grid_id']) && $start_grid_id == $grid['grid_id']) {
            $delete_grids = true;
        }

        if ($delete_grids) {
            /**
             * Action before remove grid
             * @param int $grid_id Grid identifier
             */
            $grid_id = $grid['grid_id'];
            fn_set_hook('remove_grid', $grid_id);

            db_query('DELETE FROM ?:bm_grids WHERE grid_id = ?i', $grid['grid_id']);
        }

        if (!empty($grid['children']) && is_array($grid['children'])) {
            foreach ($grid['children'] as $_grid) {
                self::_remove($start_grid_id, $_grid, $delete_grids);
            }
        }
    }
}
