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

use Tygh\Registry;
use Tygh\CompanySingleton;
use Tygh\Addons\SchemesManager as AddonSchemesManager;

/**
 * ProductTabs class
 */
class ProductTabs extends CompanySingleton
{
    /**
     * Return list of product tabs
     *
     * @param  string $condition  Query condition; it is treated as a WHERE clause
     * @param  int    $product_id Product identifier
     * @param  string $lang_code  2 letter language code
     * @return array  Array of product tabs sorted by position and tab_id
     */
    public function getList($condition = '', $product_id = 0, $lang_code = DESCR_SL)
    {
        $join = '';

        $fields = array('*');

        /**
         * Prepares params for SQL query before getting product tabs
         * @param array $fields array of table column names to be returned
         * @param string $condition Query condition; it is treated as a WHERE clause
         * @param string $lang_code 2 letter language code
         */
        fn_set_hook('get_product_tabs_pre', $fields, $join, $condition, $lang_code);

        $fields = array_merge(
            $fields,
            array(
                '?:product_tabs.status as status',
                '?:product_tabs.block_id as block_id',
                '?:product_tabs.company_id as company_id',
                '?:product_tabs_descriptions.name as name '
            )
        );

        $tabs = db_get_hash_array(
            "SELECT ?p FROM ?:product_tabs "
                . "LEFT JOIN ?:product_tabs_descriptions "
                    . "ON ?:product_tabs.tab_id = ?:product_tabs_descriptions.tab_id "
                . "LEFT JOIN ?:bm_blocks "
                    . "ON ?:bm_blocks.block_id = ?:product_tabs.block_id "
                . "?p "
            . "WHERE ?:product_tabs_descriptions.lang_code = ?s ?p ?p ORDER BY position",
            'tab_id',
            implode (',', $fields),
            $join,
            $lang_code,
            $this->getCompanyCondition('?:product_tabs.company_id'),
            $condition
        );

        foreach ($tabs as $tab_id => $tab) {
            if (!empty($tab['addon'])) {
                $addons = Registry::get('addons');
                if ($addons[$tab['addon']]['status'] != 'A') {
                    unset($tabs[$tab_id]);

                    continue;
                }
            }

            $tabs[$tab_id]['items_ids'] = explode(',', $tab['product_ids']);
            $tabs[$tab_id]['items_count'] = count($tabs[$tab_id]['items_ids']);
            if ($product_id > 0) {
                if (array_search($product_id, $tabs[$tab_id]['items_ids']) !== false) {
                    if ($tab['status'] == 'A') {
                        $tabs[$tab_id]['status'] = 'D';
                    } else {
                        $tabs[$tab_id]['status'] = 'A';
                    }
                }
            }
        }

        /**
         * Processes product tabs list after getting it
         * @param array $tabs Array of product tabs data
         * @param string $lang_code 2 letter language code
         */
        fn_set_hook('get_product_tabs_post', $tabs, $lang_code);

        return $tabs;
    }

    /**
     * Deletes product tab with related descriptions
     *
     * @param  int  $tab_id Product tab identifier
     * @param  bool $force  Delete tab if it is primary or no
     * @return bool True in case of success, false otherwise
     */
    public function delete($tab_id, $force = false)
    {
        if (!empty($tab_id) && (fn_check_company_id('product_tabs', 'tab_id', $tab_id) || !$this->_company_id)) {
            /**
             * Before delete product tab
             * @param int $tab_id Id of product tab for delete
             */
            fn_set_hook('delete_product_tab_pre', $tab_id);

            if (!$this->isPrimary($tab_id) || $force) {
                db_query("DELETE FROM ?:product_tabs WHERE tab_id = ?i", $tab_id);
                db_query("DELETE FROM ?:product_tabs_descriptions WHERE tab_id = ?i", $tab_id);

                /**
                 * After delete product tab
                 * @param int $tab_id Id of product tab for delete
                 */
                fn_set_hook('delete_product_tab_post', $tab_id);

                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if tab is primary
     *
     * @param  int  $tab_id Product tab identifier
     * @return bool true if tab is primary, false otherwise
     */
    public function isPrimary($tab_id)
    {
        $is_primary = db_get_field('SELECT is_primary FROM ?:product_tabs WHERE tab_id = ?i', $tab_id);

        return ($is_primary == 'Y') ? true : false;
    }

    /**
     * Creates or updates product tab data.
     * $tab_data must be array in this format:
     * array(
     *   tab_id - if not exists will be created new record
     *   tab_type - 'T' for template content or 'B' for block content
     *   template - path to template if tab_type = 'T'
     *   block_id - id of block from Block Manager if tab_type = 'B' @see Bm_Block for additiona information
     *   addon - addon name that created this tab
     *   position - position
     * 	 status - 'A' (active) or 'D' (disabled)
     *   company_id
     *   name
     *   lang_code
     * )
     *
     *
     * @param  array         $tab_data Array of product tab data
     * @return int|db_result Product tab id if new tab was created, DB result otherwise
     */
    public function update($tab_data)
    {
        if (!isset($tab_data['company_id']) && $this->_company_id) {
            $tab_data['company_id'] = $this->_company_id;
        }

        if (empty($tab_data['position']) && isset($tab_data['position']) && $tab_data['position'] !== 0) {
            $tab_data['position'] = $this->getMaxPosition() + 1;
        }

        /**
         * Actions before update product tab
         * @param int $tab_id Id of product tab for delete
         */
        fn_set_hook('update_product_tab_pre', $tab_id);

        $db_result = db_replace_into('product_tabs', $tab_data);

        if (!empty($tab_data['tab_id'])) {
            // Update record
            $tab_id = $tab_data['tab_id'];

            if (!empty($tab_data['name']) && !empty($tab_data['lang_code'])) {
                $this->_updateDescription($tab_id, array(
                    'lang_code' => $tab_data['lang_code'],
                    'name' => $tab_data['name'],
                ));
            }

            /**
             * Actions after product tab was updated
             * @param int $tab_id Identifier of tab
             */
            fn_set_hook('product_tab_updated', $tab_id);
        } else {
            // Create new record
            $tab_id = $db_result;

            if (!empty($tab_data['name']) && !empty($tab_data['lang_code'])) {
                foreach (fn_get_translation_languages() as $tab_data['lang_code'] => $v) {
                    $this->_updateDescription($tab_id, array(
                        'lang_code' => $tab_data['lang_code'],
                        'name' => $tab_data['name'],
                    ));
                }
            }

            /**
             * Actions after new product tab was created
             * @param int $tab_id Identifier of new tab
             */
            fn_set_hook('product_tab_created', $tab_id);
        }

        return $tab_id;
    }

    /**
     * Updates product tab description
     * $description must be array in this format:
     * array (
     *   lang_code (required)
     *   name (required)
     * )
     *
     * @param  int           $tab_id      Product tab identifier
     * @param  array         $description Array of product tab description data
     * @return int|db_result Product tab id if new tab was created, DB result otherwise
     */
    private function _updateDescription($tab_id, $description)
    {
        if (!empty($tab_id) && !empty($description['lang_code'])) {
            $description['tab_id'] = $tab_id;

            /**
             * Actions before updating product tab description
             * @param int $tab_id Product tab identifier
             * @param array $description Array of product tab description data
             */
            fn_set_hook('update_product_tab_description', $tab_id, $description);
            db_replace_into('product_tabs_descriptions', $description);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Create default product tabs
     *
     * @return bool Always true
     */
    public function createDefaultTabs()
    {
        $templates = $this->getTemplates(true, false);

        foreach ($templates as $template => $name) {
            $tab_data = array(
                'name' => $name,
                'tab_type' => 'T',
                'position' => $this->getMaxPosition() + 1,
                'status' => 'A',
                'template' => $template,
                'is_primary' => 'Y',
                'lang_code' => DESCR_SL,
                'company_id' =>  $this->_company_id
            );

            $this->update($tab_data);
        }

        // Now get tabs blocks from addons
        foreach (Registry::get('addons') as $addon => $v) {
            $scheme = AddonSchemesManager::getScheme($addon);

            if ($scheme != null) {
                $this->createAddonTabs($addon, $scheme->getTabOrder());
                $this->updateAddonTabStatus($addon, $v['status']);
            }
        }

        return true;
    }

    /**
     * Return list with all product tab templates
     *
     * @param  bool  $from_themes_repository get templates from themes repository or from installed theme folder
     * @param  bool  $with_addons            If true addons templates will be added to result list
     * @return array Array of templates of product tabs
     */
    public function getTemplates($from_themes_repository = false, $with_addons = true)
    {
        if ($from_themes_repository) {
            $base_dir = fn_get_theme_path('[repo]/[theme]/templates/', 'C');
        } else {
            $base_dir = fn_get_theme_path('[themes]/[theme]/templates/', 'C');
        }

        $tabs_blocks = fn_get_dir_contents($base_dir . 'blocks/product_tabs', false, true, '.tpl', 'blocks/product_tabs/');
        if ($with_addons) {
            // Now get tabs blocks from addons
            foreach (Registry::get('addons') as $addon => $v) {
                if ($v['status'] == 'A') {
                    $_tabs_blocks = fn_get_dir_contents($base_dir . 'addons/' . $addon . '/blocks/product_tabs', false, true, '.tpl', 'addons/' . $addon . '/blocks/product_tabs/');
                    if (!empty($_tabs_blocks)) {
                        $tabs_blocks = fn_array_merge($tabs_blocks, $_tabs_blocks, false);
                    }
                }
            }
        }

        $templates = array();
        foreach ($tabs_blocks as $template) {
            $name = SchemesManager::generateTemplateName($template, $base_dir);
            $templates[$template] = $name;
        }

        return $templates;
    }

    /**
     * Get max value of product tabs position
     *
     * @return int Max value of product tabs position
     */
    public function getMaxPosition()
    {
        return db_get_field("SELECT MAX(position) FROM ?:product_tabs WHERE 1 ?p", $this->getCompanyCondition('company_id'));
    }

    /**
     * Create product tabs for addon
     *
     * @param  string $addon     Add-on name
     * @param  string $tab_order Tab order prepend|append
     * @param  string $lang_code 2 letter language code
     * @return bool
     */
    public function createAddonTabs($addon, $tab_order = 'append', $lang_code = DESCR_SL)
    {
        $repo_path = fn_get_theme_path('[repo]/[theme]/templates/', 'C', $this->_company_id);
        $themes_path = fn_get_theme_path('[themes]/[theme]/templates/', 'C', $this->_company_id);
        $addon_path = 'addons/' . $addon . '/blocks/product_tabs';

        $repo_tabs_blocks = fn_get_dir_contents($repo_path . $addon_path, false, true, '.tpl', $repo_path . $addon_path . '/');
        $theme_tabs_blocks = fn_get_dir_contents($themes_path . $addon_path, false, true, '.tpl', $themes_path . $addon_path . '/');
        $tabs_blocks = array_merge($repo_tabs_blocks, $theme_tabs_blocks);
        $created_tabs = array();

        if (!empty($tabs_blocks)) {
            foreach ($tabs_blocks as $template) {
                $addon_template = $addon_path . '/' . fn_basename($template);
                if (!isset($created_tabs[$addon_template])) {
                    $name = fn_get_file_description($template, 'block-description', true);

                    $position =	$this->getMaxPosition() + 1;

                    if ($tab_order == 'prepend') {
                        $position = 0;
                    }
                    $tab_data = array(
                        'name' => __($name),
                        'tab_type' => 'T',
                        'position' => $position,
                        'status' => 'D',
                        'addon'	=> $addon,
                        'template' => $addon_template,
                        'is_primary' => 'Y',
                        'lang_code' => $lang_code,
                        'company_id' => $this->_company_id
                    );

                    $tab_id = $this->update($tab_data);

                    foreach (fn_get_translation_languages() as $lang_code => $v) {
                        $this->_updateDescription($tab_id, array(
                            'lang_code' => $lang_code,
                            'name' => __($name, array(), $lang_code),
                        ));
                    }

                    $created_tabs[$addon_template] = 1;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Deletes product tabs that defined by this addon
     *
     * @param  string $addon Addon name
     * @return bool   True on success, false otherwise
     */
    public function deleteAddonTabs($addon)
    {
        $tabs_ids = db_get_fields("SELECT tab_id FROM ?:product_tabs WHERE addon = ?s", $addon);

        if (!empty($tabs_ids)) {
            foreach ($tabs_ids as $tabs_id) {
                $this->delete($tabs_id, true);
            }

            return true;
        }

        return false;
    }

    /**
     * Updates tabs statuses for addon
     *
     * @param  string $addon  Add-on name
     * @param  string $status Tab status (A - active, D - disabled)
     * @return bool   Always true
     */
    public function updateAddonTabStatus($addon, $status)
    {
        db_query('UPDATE ?:product_tabs SET status = ?s WHERE addon LIKE ?s', $status, $addon);

        return true;
    }

    /**
     * Removes missing tabs data
     *
     * @return bool Always true
     */
    public function removeMissing()
    {
        // Remove missing tabs
        db_remove_missing_records('product_tabs', 'block_id', 'bm_blocks');

        db_remove_missing_records('product_tabs_descriptions', 'block_id', 'bm_blocks');

        return true;
    }

    /**
     * Clones tabs statuses from one product to other
     * used for products cloning
     *
     * @param  int  $target_product_id Identifier of product to which statuses will be cloned
     * @param  int  $product_id        Identifier of product from which statuses are taken
     * @return bool Always true
     */
    public function cloneStatuses($target_product_id, $product_id)
    {
        $tabs = $this->getList('', $product_id, DESCR_SL);
        foreach ($tabs as $tab) {
            if (array_search($product_id, $tab['items_ids']) !== false) {
                $object_ids = $tab['items_ids'];
                $object_ids[] = $target_product_id;

                $this->update(array(
                    'tab_id' => $tab['tab_id'],
                    'product_ids' => implode(',', $object_ids)
                ));
            }
        }

        return true;
    }
}
