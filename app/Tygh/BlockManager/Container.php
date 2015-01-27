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
 * Container class
 */
class Container
{
    /**
     * Gets list of containers
     *
     * @param  array $params input params
     * @return array Array of containers data as position => data
     */
    public static function getList($params = array())
    {
        $fields = array(
            'c.*'
        );

        $join = $condition = '';

        if (!empty($params['location_id'])) {
            $condition .= db_quote(" AND c.location_id = ?i", $params['location_id']);
        }

        if (!empty($params['container_id'])) {
            $condition .= db_quote(" AND c.container_id = ?i", $params['container_id']);
        }

        if (!empty($params['default_location'])) {
            $layout_id = db_get_field("SELECT layout_id FROM ?:bm_locations WHERE location_id = ?i", $params['default_location']);
            $join .= db_quote(" INNER JOIN ?:bm_locations as l ON c.location_id = l.location_id AND l.is_default = 1 AND l.layout_id = ?i", $layout_id);
            $condition .= db_quote(" AND c.position IN ('TOP_PANEL', 'HEADER', 'FOOTER')");

            $fields[] = db_quote('IF (c.location_id != ?i, 0, 1) as `default`', $params['default_location']);
        }

        $containers = db_get_hash_array(
            "SELECT " . implode(', ', $fields) . " FROM ?:bm_containers as c ?p WHERE 1 ?p",
            'position',
            $join,
            $condition
        );

        return $containers;
    }

    /**
     * Gets list of containers from the location with <i>$location_id</i> for admin area,
     * or top, header and footer containers from the default location and center
     * container from location with <i>$location_id</i> for customer area.
     *
     * @param  int    $location_id Location identifier
     * @param  string $area        Area ('A' for admin or 'C' for customer)
     * @return array  Array of containers data as position => data
     */
    public static function getListByArea($location_id, $area = AREA)
    {
        $containers = self::_overrideByDefault(
            self::getList(array(
                'location_id' => $location_id
            )),
            self::getList(array(
                'default_location' => $location_id
            )),
            $area
        );

        return $containers;
    }

    /**
     * Gets container data by id
     *
     * @param  int   $container_id Container identifier
     * @return array Array of container data
     */
    public static function getById($container_id)
    {
        $container = self::getList(array(
            'container_id' => $container_id
        ));

        return !empty($container) ? array_pop($container) : array();
    }

    /**
     * Gets identifiers of containers from array of containers data as position => data
     *
     * @param  array $containers Array of containers data as position => data
     * @return array Array of containers ids
     */
    public static function getIds($containers)
    {
        $container_ids = array();

        if (is_array($containers)) {
            foreach ($containers as $container) {
                $container_ids[] = $container['container_id'];
            }
        }

        return $container_ids;
    }

    /**
     * Creates or updates container.
     * <i>$container_data</i> must be array with this fields:
     * <pre>array (
     *  container_id,
     *  location_id,
     *  position (TOP_PANEL | HEADER | CONTENT | FOOTER),
     *  width (12 | 16)
     * )</pre>
     *
     * @param  array         $container_data array of container data
     * @return int|db_result Container id if new grid was created, DB result otherwise
     */
    public static function update($container_data)
    {
        return db_replace_into('bm_containers', $container_data);
    }

    /**
     * Performs a cleanup: removes container related data
     *
     * @return bool Always true
     */
    public static function removeMissing()
    {
        // Remove missing blocks
        db_remove_missing_records('bm_containers', 'location_id', 'bm_locations');

        return true;
    }

    /**
     * Copies containers/grids/snappings from one location to another
     * @param int $location_id     source location ID
     * @param int $new_location_id target location ID
     */
    public static function copy($location_id, $new_location_id)
    {
        $containers = self::getList(array(
            'location_id' => $location_id
        ));

        foreach ($containers as $container) {
            $container_id = $container['container_id'];
            unset($container['container_id']);

            $container['location_id'] = $new_location_id;
            $new_container_id = db_query("INSERT INTO ?:bm_containers ?e", $container);

            Grid::copy($container_id, $new_container_id);
        }
    }

    /**
     * Override top, header and footer containers with the ones from the default location in customer area; only for the default location in the admin area
     *
     * @param  array  $containers     Array of containers data as position => data
     * @param  array  $def_containers Array of containers data from default location as position => data
     * @param  string $area           Area ('A' for admin or 'C' for customer)
     * @return array  Array of containers data as position => data
     */
    private static function _overrideByDefault($containers, $def_containers, $area)
    {
        $_containers = array();

        foreach ($containers as $position => $container) {
            $_containers[$position] = $container;
            if ($area == 'C') {
                // Always override by default containers
                if (!empty($def_containers[$position])) {
                    $_containers[$position] = $def_containers[$position];
                }
            } elseif ($area == 'A') {
                // Override by default containers only for default page
                if (isset($def_containers[$position]['default']) && $def_containers[$position]['default'] == 1) {
                    $_containers[$position] = $def_containers[$position];
                }
            }
        }

        return $_containers;
    }
}
