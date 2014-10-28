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

class Block extends CompanySingleton
{
    /**
     * Gets all unique blocks
     *
     * @param  string     $lang_code 2 letter language code
     * @return array|bool
     */
    public function getAllUnique($lang_code = CART_LANGUAGE)
    {
        $join = '';
        $condition = '';

        /**
         * Prepares params for SQL query before getting unique blocks
         * @param string $join Query join; it is treated as a JOIN clause
         * @param string $condition Query condition; it is treated as a WHERE clause
         * @param string $lang_code 2 letter language code
         */
        fn_set_hook('get_unique_blocks_pre', $join, $condition, $lang_code);

        $blocks = db_get_hash_array(
            "SELECT * FROM ?:bm_blocks AS b LEFT JOIN ?:bm_blocks_descriptions AS d ON b.block_id = d.block_id ?p"
            . "WHERE lang_code = ?s ?p ORDER BY d.name",
            'block_id',
            $join,
            $lang_code,
            $condition . $this->getCompanyCondition('b.company_id')
        );

        foreach ($blocks as $block_id => $block_data) {
            if (!empty($blocks[$block_id]['properties'])) {
                $blocks[$block_id]['properties'] = unserialize($block_data['properties']);
            }
        }

        /**
         * Prepares params for SQL query before getting unique blocks
         * @param string $lang_code 2 letter language code
         */
        fn_set_hook('get_unique_blocks_post', $blocks, $lang_code);

        return $blocks;
    }

    /**
     * Gets block data by id
     *
     * @param  int    $block_id       Block identifier
     * @param  int    $snapping_id    Snapping identifier
     * @param  array  $dynamic_object Array of dynamic object data
     * @param  string $lang_code      2 letter language code
     * @return array
     */
    public function getById($block_id, $snapping_id = 0, $dynamic_object = array(), $lang_code = CART_LANGUAGE)
    {
        /**
         * Prepares params for SQL query before getting block data
         * @param int $block_id Block identifier
         * @param int $snapping_id Snapping identifier
         * @param string $lang_code 2 letter language code
         */
        fn_set_hook('get_block_pre', $block_id, $snapping_id, $lang_code);

        $block = db_get_row (
            "SELECT b.*, d.*, c.* FROM ?:bm_blocks as b "
            . "LEFT JOIN ?:bm_blocks_descriptions as d ON b.block_id = d.block_id "
            . "LEFT JOIN ?:bm_blocks_content as c ON b.block_id = c.block_id AND d.lang_code=c.lang_code "
            . "WHERE b.block_id = ?i AND d.lang_code=?s ?p ORDER BY snapping_id DESC LIMIT 1",
            $block_id,
            $lang_code,
            $this->getCompanyCondition('b.company_id') . $this->_generateContentCondition($dynamic_object)
        );

        if ($snapping_id > 0) {
            $snapping_data = db_get_row ("SELECT * FROM ?:bm_snapping WHERE snapping_id=?i", $snapping_id);

            $block = array_merge($block, $snapping_data);

            if (!empty($dynamic_object['object_type'])) {
                $object_ids = db_get_field (
                    "SELECT object_ids FROM ?:bm_block_statuses WHERE snapping_id=?i AND object_type=?s",
                    $snapping_id, $dynamic_object['object_type']
                );

                $block['object_ids'] = $object_ids;
            }
        }

        $block['object_id'] = !empty($dynamic_object['object_id']) ? $dynamic_object['object_id'] : $block['object_id'];
        $block['object_type'] = !empty($dynamic_object['object_type']) ? $dynamic_object['object_type'] : $block['object_type'];

        if (!empty($block['properties'])) {
            $block['properties'] = @unserialize($block['properties']);

            if (empty($block['properties'])) {
                $block['properties'] = array();
            }
        }

        if (!empty($block['content'])) {
            $block['content'] = @unserialize($block['content']);

            if (empty($block['content'])) {
                $block['content'] = array();
            }
        }

        /**
         * Processes block data after getting it
         *
         * @param $block Array of block data
         * @param int $snapping_id Snapping identifier
         * @param string $lang_code
         */
        fn_set_hook('get_block_post', $block, $snapping_id, $lang_code);

        return $block;
    }

    /**
     * Generates SQL condition for getting the proper block content
     *
     * @param $dynamic_object Array of dynamic object data
     * @return string SQL condition
     */
    private function _generateContentCondition($dynamic_object)
    {
        $condition = '';

        if (isset($dynamic_object['object_id']) && isset($dynamic_object['object_type'])) {
            $condition = db_quote(
                " AND ((c.object_id = ?i AND c.object_type like ?s) OR (c.object_id = 0 AND c.object_type like '')) ",
                $dynamic_object['object_id'], $dynamic_object['object_type']
            );
        }

        return $condition;
    }

    /**
     * Gets block description for all languages by block id and snapping id (optional)
     * @param  int   $block_id    Block identifier
     * @param  int   $snapping_id Snapping identifier
     * @return array Array of block descriptions as lang_code => description
     */
    public function getFullDescription($block_id, $snapping_id = 0)
    {
        $block_descriptions = array();

        foreach (fn_get_translation_languages() as $lang_code => $v) {
            $block_descriptions[$lang_code] = $this->getById($block_id, $snapping_id, array(), $lang_code);
        }

        return $block_descriptions;
    }

    /**
     * Gets list of blocks
     * <i>$dynamic_object</i> must be array in this format
     * <pre>array (
     *   object_ids - dynamic object id
     *   object_type - dynamic object type from dynamic_objects scheme
     * )</pre>
     *
     * @param  array  $fields         array of table column names to be returned
     * @param  array  $grids_ids      Identifiers of grids containing the needed blocks
     * @param  array  $dynamic_object Dynamic object data
     * @param  string $join           Query join; it is treated as a JOIN clause
     * @param  string $condition      Query condition; it is treated as a WHERE clause
     * @param  string $lang_code      2 letter language code
     * @return array  Array of blocks as grid_id => array(block_id => block data)
     */
    public function getList($fields, $grids_ids, $dynamic_object = array(), $join = '', $condition = '', $lang_code = CART_LANGUAGE)
    {
        /**
         * Prepares params for SQL query before getting blocks
         * @param array $fields Array of table column names to be returned
         * @param array $grids_ids Identifiers of grids containing the needed blocks
         * @param array $dynamic_object Dynamic object data
         * @param string $join Query join; it is treated as a JOIN clause
         * @param string $condition Query condition; it is treated as a WHERE clause
         * @param string $lang_code 2 letter language code
         */
        fn_set_hook('get_blocks_pre', $fields, $grids_ids, $dynamic_object, $join, $condition, $lang_code);
        $_fields = array(
            "?:bm_snapping.grid_id as grid_id",
            "?:bm_snapping.block_id as block_id",
            "IFNULL(dynamic_object_content.content, default_content.content) as content",
            "?:bm_block_statuses.object_ids as object_ids"
        );
        $_fields = array_merge($_fields, $fields);

        $condition .= $this->getCompanyCondition('?:bm_blocks.company_id');

        $blocks = db_get_hash_multi_array(
            "SELECT ?p "
            . "FROM ?:bm_snapping "
                . "LEFT JOIN ?:bm_blocks "
                    . "ON ?:bm_blocks.block_id = ?:bm_snapping.block_id "
                . "LEFT JOIN ?:bm_block_statuses "
                    . "ON ?:bm_snapping.snapping_id = ?:bm_block_statuses.snapping_id "
                    . "AND ?:bm_block_statuses.object_type LIKE ?s "
                . "LEFT JOIN ?:bm_blocks_descriptions "
                    . "ON ?:bm_blocks.block_id = ?:bm_blocks_descriptions.block_id ?p "
                . "LEFT JOIN ?:bm_blocks_content AS default_content "
                    . "ON ?:bm_blocks.block_id = default_content.block_id "
                    . "AND ?:bm_blocks_descriptions.lang_code = default_content.lang_code "
                    . "AND default_content.snapping_id = 0 "
                    . "AND default_content.object_id = 0 "
                    . "AND default_content.object_type like '' "
                . "LEFT JOIN ?:bm_blocks_content AS dynamic_object_content "
                    . "ON ?:bm_blocks.block_id = dynamic_object_content.block_id "
                    . "AND ?:bm_blocks_descriptions.lang_code = dynamic_object_content.lang_code "
                    . "AND dynamic_object_content.object_id = ?i "
                    . "AND dynamic_object_content.object_type like ?s "
            . "WHERE ?:bm_snapping.grid_id IN (?n)  "
                . "AND ?:bm_blocks_descriptions.lang_code=?s ?p "
            . "ORDER BY ?:bm_snapping.order, ?:bm_snapping.block_id ",
            array('grid_id', 'block_id'),
            implode(',', $_fields),
            !empty($dynamic_object['object_type']) ? $dynamic_object['object_type'] : '',
            $join,
            !empty($dynamic_object['object_id']) ? $dynamic_object['object_id'] : 0,
            !empty($dynamic_object['object_type']) ? $dynamic_object['object_type'] : '',
            $grids_ids,
            $lang_code,
            $condition
        );

        foreach ($blocks as $grid_id => $blocks_list) {
            foreach ($blocks_list as $block_id => $block) {
                if (!empty($block['properties'])) {
                    $blocks[$grid_id][$block_id]['properties'] = unserialize($block['properties']);
                }
                if (!empty($block['content'])) {
                    $blocks[$grid_id][$block_id]['content'] = unserialize($block['content']);
                }
                if (!empty($block['object_ids'])) {
                    $blocks[$grid_id][$block_id]['items_array'] = explode(',', $block['object_ids']);
                } else {
                    $blocks[$grid_id][$block_id]['items_array'] = array();
                }
                $blocks[$grid_id][$block_id]['items_count'] = count($blocks[$grid_id][$block_id]['items_array']);
                $blocks[$grid_id][$block_id]['object_id'] = !empty($dynamic_object['object_id']) ? $dynamic_object['object_id'] : 0;
                $blocks[$grid_id][$block_id]['object_type'] = !empty($dynamic_object['object_type']) ? $dynamic_object['object_type'] : '';
            }
        }

        /**
         * Processes blocks list after getting it
         * @param array $blocks List of blocks data
         * @param array $grids_ids Identifiers of grids containing the needed blocks
         * @param array $dynamic_object Dynamic object data
         * @param string $join Query join; it is treated as a JOIN clause
         * @param string $condition Query condition; it is treated as a WHERE clause
         * @param string $lang_code 2 letter language code
         */
        fn_set_hook('get_blocks_post', $blocks, $grids_ids, $dynamic_object, $lang_code);

        return $blocks;
    }

    /**
     * Creates or updates block.
     * <i>$block_data</i> must be array in this format:
     * <pre>array(
     *   block_id - If does not exist a new record will be created
     *   type - Block type
     *   properties - Array of  block properties (will be serialized)
     *   user_class - User CSS class
     * )</pre>
     *
     * @param  array         $block_data  Array of block data
     * @param  array         $description Array of block description data @see Bm_Block::updateDescription
     * @return int|db_result Block id if new block was created, DB result otherwise
     */
    public function update($block_data, $description = array())
    {
        if (!isset($block_data['company_id']) && $this->_company_id) {
            $block_data['company_id'] = $this->_company_id;
        }

        if (isset($block_data['properties'])) {
            $block_data['properties'] = $this->_serialize($block_data['properties']);
        }

        /**
         * Prepares block data before updating it
         * @param array $block_data Array of block data
         */
        fn_set_hook('update_block_pre', $block_data);
        $db_result = db_replace_into('bm_blocks', $block_data);

        if (!empty($block_data['block_id'])) {
            // Update record
            $block_id = intval($block_data['block_id']);
            $this->_updateDescription($block_id, $description);

            // If this block type have no multilanguage content we must update it for all languages
            if (isset($block_data['type']) && !empty($block_data['content_data'])) {
                if (!empty($block_data['apply_to_all_langs']) && $block_data['apply_to_all_langs'] == 'Y') {
                    foreach (fn_get_translation_languages() as $block_data['content_data']['lang_code'] => $v) {
                        $this->_updateContent($block_id, $block_data['type'], $block_data['content_data']);
                    }
                } else {
                    $this->_updateContent($block_id, $block_data['type'], $block_data['content_data']);
                }
            }

            /**
             * Actions to be performed after the block is updated
             * @param int $block_id Block identifier
             */
            fn_set_hook('block_updated', $block_id);
        } else {
            // Create new record
            $block_id = $db_result;
            $this->_updateAllDescriptions($block_id, $description);

            $this->_updateAllContent($block_id, $block_data['type'], $block_data['content_data']);

            /**
             * Actions to be performed after the new block is added
             * @param int $block_id Block identifier
             */
            fn_set_hook('block_created', $block_id);
        }

        return $block_id;
    }

    /**
     * Updates block content
     * <i>$content_data</i> must be array in this format:
     * <pre>array(
     *  snapping_id
     *  block_id
     *  lang_code
     *  content Array of block content data (will be serialized)
     * )</pre>
     * <i>(snapping_id, block_id, lang_code)</i>-triplet is used as PRIMARY key
     *
     * @param  int    $block_id     Block identifier
     * @param  string $block_type   Block type from scheme
     * @param  array  $content_data Array of content data
     * @return bool   True in case of success, false otherwise
     */
    private function _updateContent($block_id, $block_type, $content_data)
    {
        if (!empty($block_type)) {
            if (isset($content_data['override_by_this']) && $content_data['override_by_this'] == 'Y') {
                db_query('DELETE FROM ?:bm_blocks_content WHERE block_id = ?i AND lang_code=?s', $block_id, $content_data['lang_code']);
                // Remove dynamic object data for default
                if (isset($content_data['object_type'])) {
                    unset($content_data['object_type']);
                }
                if (isset($content_data['object_id'])) {
                    unset($content_data['object_id']);
                }
            }

            if (isset($content_data['content']) && is_array($content_data['content'])) {
                $content_data['content'] = $this->_serialize($content_data['content']);
            } else {
                $content_data['content'] = '';
            }
            $content_data['block_id'] = $block_id;

            // Now content must be the same for all snappings
            if (isset($content_data['snapping_id'])) {
                unset($content_data['snapping_id']);
            }

            db_replace_into('bm_blocks_content', $content_data);

            return true;
        }

        return false;
    }

    /**
     * Updates content for all languages
     *
     * @param  int    $block_id     Block identifier
     * @param  string $block_type   Block type
     * @param  array  $content_data Array of content data @see Bm_Block::update_content
     * @return bool   True in case of success, false otherwise
     */
    private function _updateAllContent($block_id, $block_type, $content_data)
    {
        $result = true;

        foreach (fn_get_translation_languages() as $content_data['lang_code'] => $v) {
            $result = $result & $this->_updateContent($block_id, $block_type, $content_data);
        }

        return $result;
    }

    /**
     * Serializes item if it is array
     *
     * @param  mixed  $array object to _serialize
     * @return string String with serialized array
     */
    private function _serialize($array)
    {
        if (is_array($array)) {
            $array = serialize($array);
        }

        return $array;
    }

    /**
     * Updates block description
     * <i>$description</i> must be array with this fields:
     * <pre>array (
     *   lang_code (required)
     *   name
     *   content
     *   object_ids - dynamic object id
     *   object_type - dynamic object type from dynamic_objects scheme
     * )</pre>
     *
     * @param  int   $block_id    Block identifier
     * @param  array $description Array of description data
     * @return bool  True in case of success, false otherwise
     */
    private function _updateDescription($block_id, $description)
    {
        if (!empty($block_id) && !empty($description['lang_code'])) {
            $description['block_id'] = $block_id;

            return db_replace_into('bm_blocks_descriptions', $description);
        } else {
            return false;
        }
    }

    /**
     * Updates block descriptions for all languages
     * @param  int   $block_id    Block identifier
     * @param  array $description Array of description data @see Bm_Block::updateDescription
     * @return bool  True in case of success, false otherwise
     */
    private function _updateAllDescriptions($block_id, $description)
    {
        $result = true;

        foreach (fn_get_translation_languages() as $description['lang_code'] => $v) {
            $result = $result & $this->_updateDescription($block_id, $description);
        }

        return $result;
    }

    /**
     * Removes block from DB by block_id
     *
     * @param  int  $block_id Block identifier
     * @return bool True in case of success, false otherwise
     */
    public function remove($block_id)
    {
        if (!empty($block_id)) {
            db_query('DELETE FROM ?:bm_blocks WHERE block_id = ?i', $block_id);
            $this->removeMissing();

            return true;
        } else {
            return false;
        }
    }

    /**
     * Gets snapping data by block_id
     *
     * @param  array $fields      Array of fields to be returned in result
     * @param  int   $snapping_id Snapping identifier
     * @return array Array of snapping data from db
     */
    public function getSnappingData($fields, $snapping_id)
    {
        return db_get_row(
            "SELECT ?p FROM ?:bm_snapping LEFT JOIN ?:bm_blocks ON ?:bm_blocks.block_id = ?:bm_snapping.block_id WHERE snapping_id = ?i",
            implode(',', $fields), $snapping_id
        );
    }

    /**
     * Updates block snappings, block dynamic items and descriptions
     * <i>$snapping_data</i> must be array with these fields:
     * <pre>array (
     *   snapping_id - if not exists will be created new record
     *   grid_id (required)
     *   block_id (required)
     *   order - position of block in this grid
     *   object_ids - dynamic object id
     *   object_type - dynamic object type from dynamic_objects scheme
     *   items - coma delimited list of object items
     *   disbled (1 | 0)
     *   description array with block description data @see Bm_Block::update_description
     * )</pre>
     *
     * @param  array $snapping_data Array of snapping data
     * @return bool  True in case of success, false otherwise
     */
    public function updateSnapping($snapping_data)
    {
        if (!empty($snapping_data['snapping_id']) || (!empty($snapping_data['block_id']) && !empty($snapping_data['grid_id']))) {
            // Updates block descriptions for dynamic objects
            if (isset($snapping_data['object_ids']) && isset($snapping_data['object_type']) && !empty($snapping_data['block_id'])
                && isset($snapping_data['description']) && isset($snapping_data['description']['lang_code'])
            ) {
                $this->_updateDescription($snapping_data['block_id'], $snapping_data['description']);
            }

            // Remove description if it is sets because it is from another table
            if (isset($snapping_data['description'])) {
                unset ($snapping_data['description']);
            }
            // Remove action if it is sets because it is not needed here
            if (isset($snapping_data['action'])) {
                unset ($snapping_data['action']);
            }

            $snapping_id = db_replace_into('bm_snapping', $snapping_data);

            if (!empty($snapping_id)) {
                $snapping_data['snapping_id'] = $snapping_id;
            }

            if (!empty($snapping_data['snapping_id']) && !empty($snapping_data['object_type'])) {
                db_replace_into('bm_block_statuses', $snapping_data);
            }

            return $snapping_id;
        } else {
            return false;
        }
    }

    /**
     * Removes snapping data
     *
     * @param  int  $snapping_id Snapping identifier
     * @return bool True in case of success, false otherwise
     */
    public function removeSnapping($snapping_id)
    {
        if (!empty($snapping_id)) {
            db_query('DELETE FROM ?:bm_snapping WHERE snapping_id = ?i', $snapping_id);
            $this->removeMissing();

            return true;
        } else {
            return false;
        }
    }

    /**
     * Updates block statuses
     * <i>$status_data</i> must be array with these fields:
     * <pre>array (
     *   snapping_id - if not exists will be created new record
     *   status - block status 'A', 'D'
     *   object_ids - dynamic object id
     *   object_type - dynamic object type from dynamic_objects scheme
     * )</pre>
     *
     * @param  array       $status_data Array of status data
     * @return string|bool Status value on success, false otherwise
     */
    public function updateStatus($status_data)
    {
        if (!empty($status_data['snapping_id']) && !empty($status_data['status'])) {
            if (!empty($status_data['object_type']) && !empty($status_data['object_id']) && $status_data['object_id'] > 0) {
                // If it's status update for dynamic object
                $block = $this->getById(null, $status_data['snapping_id'], $status_data, DESCR_SL);

                $object_ids = explode(',', $block['object_ids']);

                $key = array_search($status_data['object_id'], $object_ids);

                if ($status_data['status'] == $block['status'] && isset($object_ids[$key])) {
                    unset($object_ids[$key]);
                } elseif ($status_data['status'] != $block['status']) {
                    $object_ids[] = $status_data['object_id'];
                }

                foreach ($object_ids as $k => $v) {
                    if (empty($v)) {
                        unset($object_ids[$k]);
                    }
                }

                $status_data['object_ids'] = implode(',', $object_ids);

                if (empty($status_data['object_ids'])) {
                    db_query('DELETE FROM ?:bm_block_statuses WHERE object_type=?s and snapping_id=?i', $status_data['object_type'], $status_data['snapping_id']);
                } else {
                    $this->updateStatuses($status_data);
                }
            } else {
                // If it's simple status update just do it
                $this->updateSnapping(array(
                    'snapping_id' => $status_data['snapping_id'],
                    'status' => $status_data['status'],
                    'object_ids' => '',
                    'object_type' => ''
                ));
            }

            return $status_data['status'];
        } else {
            return false;
        }
    }

    /**
     * Updates statuses for more that one dynamic object
     *
     * @param $status_data
     * @return mixed db_result on success, false otherwise
     */
    public function updateStatuses($status_data)
    {
        if (!empty($status_data['snapping_id']) && !empty($status_data['object_type'])) {
            return db_replace_into('bm_block_statuses', $status_data);
        } else {
            return false;
        }
    }

    /**
     * Performs a cleanup: removes block related data
     *
     * @return bool Always true
     */
    public function removeMissing()
    {
        // Remove missing contents
        db_remove_missing_records('bm_blocks_content', 'block_id', 'bm_blocks');

        // Remove missing descriptions
        db_remove_missing_records('bm_blocks_descriptions', 'block_id', 'bm_blocks');

        // Remove missing snapping
        db_remove_missing_records('bm_snapping', 'block_id', 'bm_blocks');

        return true;
    }

    /**
     * Gets items from block content
     *
     * @param  string $item_name    Name of current content variable
     * @param  array  $block        Array of block data
     * @param  array  $block_scheme Array of block scheme data generated by Block Schemes Manager
     * @return array  Array of block items
     */
    public function getItems($item_name, $block, $block_scheme)
    {
        $params = $items = $bulk_modifier = array();

        if (!empty($block['content'][$item_name])) {
            $filling_params = $block['content'][$item_name];
        } else {
            $filling_params = array();
        }

        if (isset($block['content'][$item_name]['filling'])) {
            $filling = $block['content'][$item_name]['filling'];
            unset($filling_params['filling']);
        } else {
            $filling = current($block_scheme['content'][$item_name]['fillings']);
        }

        $field_scheme = $block_scheme['content'][$item_name]['fillings'][$filling];
        // Params from scheme
        if (isset($field_scheme['params'])) {
            $params = $field_scheme['params'];
        }

        // Params from content
        $params = array_merge($params, $block['content']);

        // Assign additional template params
        if (isset($block_scheme['templates'][$block['properties']['template']]['params'])) {
            $params = fn_array_merge($params, $block_scheme['templates'][$block['properties']['template']]['params']);
        }

        // Collect data from $_REQUEST
        if (!empty($params['request'])) {
            foreach ($params['request'] as $param => $val) {
                $val = fn_strtolower(str_replace('%', '', $val));
                if (isset($_REQUEST[$val])) {
                    $params[$param] = $_REQUEST[$val];
                }
            }
            unset($params['request']);
        }

        // Collect data from $_SESSION !!! FIXME, merge with $_REQUEST
        if (!empty($params['session'])) {
            foreach ($params['session'] as $param => $val) {
                $val = fn_strtolower(str_replace('%', '', $val));
                if (isset($_SESSION[$val])) {
                    $params[$param] = $_SESSION[$val];
                }
            }
            unset($params['session']);
        }

        // Collect data from $auth !!! FIXME, merge with $_REQUEST
        if (!empty($params['auth'])) {
            foreach ($params['auth'] as $param => $val) {
                $val = fn_strtolower(str_replace('%', '', $val));
                if (isset($_SESSION['auth'][$val])) {
                    $params[$param] = $_SESSION['auth'][$val];
                }
            }
            unset($params['auth']);
        }

        if ($filling == 'manually') {
            // Check items list
            if (empty($params[$item_name]['item_ids'])) {
                if (empty($params['process_empty_items'])) {
                    return array();
                }
            } else {
                $params['item_ids'] = $params[$item_name]['item_ids'];
            }
        }

        $_params = $block['properties'];
        unset($params[$item_name], $_params['content_type'], $_params['template'], $_params['order'], $_params['positions']);

        if (!empty($_params)) {
            $params = fn_array_merge($params, $_params);
        }

        if (!empty($filling_params)) {
            foreach ($filling_params as $param => $value) {
                if (!empty($field_scheme['settings'][$param]) && !empty($field_scheme['settings'][$param]['unset_empty']) && empty($value)) {
                    unset($filling_params[$param]);
                }
            }

            $params = fn_array_merge($params, $filling_params);
        }

        if (isset($block_scheme['content'][$item_name]['items_function'])) {
            $callable = $block_scheme['content'][$item_name]['items_function'];
            $params['block_data'] = $block;
        } else {
            $callable = 'fn_get_' . $block['type'];
        }

        if (is_callable($callable)) {
            @list($items, ) = call_user_func($callable, $params);
        }

        // If in template issets bulk modifer set it
        if (isset($block_scheme['templates'][$block['properties']['template']]['bulk_modifier'])) {
            $bulk_modifier = $block_scheme['templates'][$block['properties']['template']]['bulk_modifier'];
        }

        // Picker values
        if (!empty($items)) {
            if (!empty($bulk_modifier)) {
                // global modifier
                if (!empty($bulk_modifier)) {
                    foreach ($bulk_modifier as $_func => $_param) {
                        $__params = array();
                        foreach ($_param as $v) {
                            if (is_string($v) && $v == '#this') {
                                $__params[] = &$items;
                            } else {
                                $__params[] = $v;
                            }
                        }
                        call_user_func_array($_func, $__params);
                    }
                }
            }
        }

        return $items;
    }

    /**
     * Returns all contents belongs to block with $block_id
     *
     * @param  int   $block_id             Block identifier
     * @param  bool  $with_dynamic_objects If true contents on dynamic objects will be returned too
     * @return array List of contnts data
     */
    public function getAllContents($block_id, $with_dynamic_objects = false)
    {
        $condition = "";

        if (!$with_dynamic_objects) {
            $condition = " AND object_type = ''";
        }

        $contents = db_get_array("SELECT * FROM ?:bm_blocks_content WHERE block_id = ?i ?p", $block_id, $condition);

        foreach ($contents as $key => $content) {
            if (!empty($content['content'])) {
                $contents[$key]['content'] = @unserialize($content['content']);

                if (empty($contents[$key]['content'])) {
                    $contents[$key]['content'] = array();
                }
            }
        }

        return $contents;
    }

    /**
     * Returns list of dynamic object types with count of different
     * contents belongs to some block or to specified block if block_id > 0
     *
     * @param  int    $block_id  Block identifier
     * @param  bool   $with_ids  Include object ids
     * @param  string $lang_code 2 letters language code
     * @return array
     */
    public function getChangedContentsCount($block_id = 0, $with_ids = false, $lang_code = DESCR_SL)
    {
        $condition = db_quote(" WHERE lang_code = ?s", $lang_code);

        if ($block_id > 0) {
            $condition .= db_quote(" AND block_id = ?i", $block_id);
        }

        $contents = db_get_array("SELECT block_id, object_type, count(*) as contents_count FROM ?:bm_blocks_content $condition GROUP BY block_id, object_type");

        if ($with_ids) {
            foreach ($contents as $key => $content) {
                $contents[$key]['object_ids'] = $this->getChangedContentsIds($content['object_type'], $content['block_id']);
            }
        }

        return $contents;
    }

    /**
     * Returns string of comma delimited object ids belongs to some object type and block_id
     *
     * @param  string $object_type Dynamic object type from scheme
     * @param  int    $block_id    Block identifier
     * @param  string $lang_code   2 letters language code
     * @return string
     */
    public function getChangedContentsIds($object_type, $block_id = 0, $lang_code = DESCR_SL)
    {
        $condition = db_quote(" AND lang_code = ?s", $lang_code);

        if ($block_id > 0) {
            $condition .= db_quote(" AND block_id = ?i", $block_id);
        }

        return implode(',', db_get_fields("SELECT object_id FROM ?:bm_blocks_content WHERE object_id > 0 AND object_type LIKE ?s ?p", $object_type, $condition));
    }

    /**
     * Removes dynamic object data
     *
     * @param  string $object_type Object type in DB
     * @param  int    $object_id   Object identifier to remove it's data
     * @return bool   Always true
     */
    public function removeDynamicObjectData($object_type, $object_id)
    {
        db_query("DELETE FROM ?:bm_blocks_content WHERE object_type=?s AND object_id=?i",$object_type, $object_id);

        $statuses = db_get_array(
            "SELECT * FROM ?:bm_block_statuses WHERE object_type = ?s AND FIND_IN_SET(?i, object_ids)",
            $object_type, $object_id
        );

        foreach ($statuses as $status) {
            $object_ids = explode(',', $status['object_ids']);

            $key = array_search($object_id, $object_ids);
            if (isset($object_ids[$key]) && $key !== false) {
                unset($object_ids[$key]);
            }

            db_query(
                "UPDATE ?:bm_block_statuses SET object_ids = ?s WHERE snapping_id = ?i AND object_type = ?s",
                implode(",", $object_ids), $status['snapping_id'], $status['object_type']
            );
        }

        return true;
    }

    /**
     * Clones dynamic object data
     *
     * @param  string $object_type   Object type in DB
     * @param  int    $old_object_id Object identifier to get data from
     * @param  int    $new_object_id Object identifier to clone
     * @return bool   Always true
     */
    public function cloneDynamicObjectData($object_type, $old_object_id, $new_object_id)
    {
        $data = db_get_array("SELECT * FROM ?:bm_blocks_content WHERE object_type=?s AND object_id=?i", $object_type, $old_object_id);
        foreach ($data as $fields) {
            $fields['object_id'] = $new_object_id;
            db_replace_into("bm_blocks_content", $fields);
        }

        $data = db_get_array("SELECT * FROM ?:bm_block_statuses WHERE object_type=?s AND FIND_IN_SET(?i, object_ids)", $object_type, $old_object_id);

        foreach ($data as $fields) {
            $fields['object_ids'] .= ',' . $new_object_id;
            db_replace_into("bm_block_statuses", $fields);
        }

        return true;
    }

    /**
     * Checks is there are at least one active block of given type on current location
     *
     * @param  string $block_type Type of block
     * @return bool   True, if block of given type is active, false otherwise.
     */
    public function isBlockTypeActiveOnCurrentLocation($block_type)
    {
        $dispatch = !empty($_REQUEST['dispatch']) ? $_REQUEST['dispatch'] : 'index.index';

        $dynamic_object = array();
        $dynamic_object_scheme = SchemesManager::getDynamicObject($dispatch, AREA);
        if (!empty($dynamic_object_scheme) && !empty($_REQUEST[$dynamic_object_scheme['key']])) {
            $dynamic_object['object_type'] = $dynamic_object_scheme['object_type'];
            $dynamic_object['object_id'] = $_REQUEST[$dynamic_object_scheme['key']];
        }

        $current_location = Location::instance()->get($dispatch, $dynamic_object);

        if (!empty($current_location['location_id'])) {
            $blocks = $this->getBlocksByTypeForLocation($block_type, $current_location['location_id']);

            if (!empty($blocks)) {
                if (!empty($dynamic_object['object_id']) && !empty($dynamic_object['object_type'])) {
                    $dynamic_object_statuses = db_get_hash_array(
                        'SELECT * FROM ?:bm_block_statuses WHERE object_type = ?s AND FIND_IN_SET(?i, object_ids)',
                        'snapping_id', $dynamic_object['object_type'], $dynamic_object['object_id']
                    );

                    foreach (array_keys($dynamic_object_statuses) as $snapping_id) {
                        if (isset($blocks[$snapping_id])) {
                            // reverse block status
                            $blocks[$snapping_id] = ($blocks[$snapping_id] == 'A') ? 'D' : 'A';
                        }
                    }
                }

                foreach ($blocks as $status) {
                    if ($status == 'A') {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Get blocks by type from given location_id, returns snapping_id and block status
     *
     * @param  string     $block_type  Type of block
     * @param  int        $location_id Location Id
     * @return array|bool Array with snapping_id => block_status
     */
    public function getBlocksByTypeForLocation($block_type, $location_id)
    {
        $blocks = false;

        if (!empty($location_id)) {
            $containers = Container::getListByArea($location_id, 'C');
            $grids = Grid::getList(array(
                'container_ids' => Container::getIds($containers),
                'simple' => true
            ));

            $condition = db_quote(' AND ?:bm_blocks.type = ?s', $block_type);
            $condition .= $this->getCompanyCondition('?:bm_blocks.company_id');

            $blocks = db_get_hash_single_array(
                "SELECT ?:bm_snapping.snapping_id, ?:bm_snapping.status "
                    . "FROM ?:bm_snapping "
                    . "LEFT JOIN ?:bm_blocks "
                    . "ON ?:bm_blocks.block_id = ?:bm_snapping.block_id "
                    . "WHERE ?:bm_snapping.grid_id IN (?n) ?p",
                array('snapping_id', 'status'),
                Grid::getIds($grids),
                $condition
            );
        }

        return $blocks;
    }

    /**
     * Copy blocks from one company to another
     * @param $array snapping IDs of the company blocks are copied to
     * @param int $company_id company ID to copy blocks to
     */
    public function copy($snapping_ids, $company_id)
    {
        static $_unique_blocks = array();

        $exim = Exim::instance($company_id);
        $block_matches = array();

        $blocks = db_get_hash_array("SELECT ?:bm_blocks.* FROM ?:bm_blocks LEFT JOIN ?:bm_snapping ON ?:bm_snapping.block_id = ?:bm_blocks.block_id WHERE ?:bm_snapping.snapping_id IN (?n)", 'block_id', array_keys($snapping_ids));


        foreach ($blocks as $block_id => $block) {
            $descriptions = db_get_hash_array("SELECT * FROM ?:bm_blocks_descriptions WHERE block_id = ?i", 'lang_code', $block_id);

            // Get unique block key
            $unique_key = $exim->getUniqueBlockKey($block['type'], $block['properties'], $descriptions[CART_LANGUAGE]['name']);
            if (isset($_unique_blocks[$company_id][$unique_key])) {
                $new_block_id = $_unique_blocks[$company_id][$unique_key];
            } else {
                $block['company_id'] = $company_id;
                unset($block['block_id']);
                $new_block_id = db_query("INSERT INTO ?:bm_blocks ?e", $block);

                foreach ($descriptions as $description) {
                    $description['block_id'] = $new_block_id;
                    db_query("INSERT INTO ?:bm_blocks_descriptions ?e", $description);
                }

                $block_content = db_get_array("SELECT * FROM ?:bm_blocks_content WHERE block_id = ?i AND snapping_id = 0 AND object_id = 0 AND object_type = ''", $block_id);
                foreach ($block_content as $content) {
                    $content['block_id'] = $new_block_id;
                    db_query("INSERT INTO ?:bm_blocks_content ?e", $content);
                }

                $_unique_blocks[$company_id][$unique_key] = $new_block_id;
            }

            $block_matches[$block_id] = $new_block_id;
        }

        //update snappings
        foreach ($block_matches as $old_block_id => $new_block_id) {
            db_query("UPDATE ?:bm_snapping SET block_id = ?i WHERE block_id = ?i AND snapping_id IN (?n)", $new_block_id, $old_block_id, $snapping_ids);
        }
    }
}
