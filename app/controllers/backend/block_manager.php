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

use Tygh\Registry;
use Tygh\BlockManager\Block;
use Tygh\BlockManager\Layout;
use Tygh\BlockManager\Location;
use Tygh\BlockManager\Grid;
use Tygh\BlockManager\Container;
use Tygh\BlockManager\Exim;
use Tygh\BlockManager\SchemesManager;
use Tygh\Themes\Themes;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (defined('AJAX_REQUEST')) {
        $result = false;
        if ($mode == 'block') {
            if ($action == 'update' && isset($_REQUEST['block_data'])) {
                $result = Block::instance()->update($_REQUEST['block_data']);
            } elseif ($action == 'delete' && isset($_REQUEST['block_id'])) {
                $result = Block::instance()->remove($_REQUEST['block_id']);
            }
        }

        if ($mode == 'location') {
            if ($action == 'update' && isset($_REQUEST['location_data'])) {
                $result = Location::instance()->update($_REQUEST['location_data']);
            } elseif ($action == 'delete' && isset($_REQUEST['location_id'])) {
                $result = Location::instance()->remove($_REQUEST['location_id']);
            }
        }

        if ($mode == 'grid' && isset($_REQUEST['snappings']) && is_array($_REQUEST['snappings'])) {
            foreach ($_REQUEST['snappings'] as $snapping_data) {
                if (!empty($snapping_data['action'])) {

                    if ($snapping_data['action'] == 'update' || $snapping_data['action'] == 'add') {
                        $result = Grid::update($snapping_data['grid_data']);

                        if (is_numeric($result)) {
                            Registry::get('ajax')->assign('id', intval($result));
                        }
                    } elseif ($snapping_data['action'] == 'delete' && !empty($snapping_data['grid_data']['grid_id'])) {
                        $result = Grid::remove($snapping_data['grid_data']['grid_id']);
                    }
                }
            }
        }

        if ($mode == 'container') {
            if (
                $action == 'update'
                && isset($_REQUEST['container_data']['container_id'])
            ) {
                $result = Container::update($_REQUEST['container_data']);
            }
        }

        if ($mode == 'snapping' && isset($_REQUEST['snappings']) && is_array($_REQUEST['snappings'])) {
            foreach ($_REQUEST['snappings'] as $snapping_data) {
                if (!empty($snapping_data['action'])) {
                    if ($snapping_data['action'] == 'update' || $snapping_data['action'] == 'add') {
                        $snapping_id = Block::instance()->updateSnapping($snapping_data);

                        if ($snapping_data['action'] == 'add') {
                            $result = $snapping_id;
                        }
                    } elseif ($snapping_data['action'] == 'delete' && !empty($snapping_data['snapping_id'])) {
                        $result = Block::instance()->removeSnapping($snapping_data['snapping_id']);
                    }
                }
            }
        }

        if (!empty($_REQUEST['clears_data'])) {
            Grid::setClearDivs($_REQUEST['clears_data']);
        }
    }

    fn_trusted_vars(
        'block',
        'block_items',
        'block_data'
    );

    $suffix = '';

    if ($mode == 'update_layout') {

        $layout_data = $_REQUEST['layout_data'];
        $from_default_layout = array();

        if (empty($_REQUEST['layout_id'])) {
            $layout_data['theme_name'] = fn_get_theme_path('[theme]', 'C');

            if (!empty($layout_data['from_layout_id']) && !is_numeric($layout_data['from_layout_id'])) {
                list($from_default_layout['theme_name'], $from_default_layout['filename']) = explode(
                    '|', $layout_data['from_layout_id'], 2
                );
                unset($layout_data['from_layout_id']);
            }

        }

        $layout_id = Layout::instance()->update($layout_data, $_REQUEST['layout_id']);

        if (!empty($from_default_layout) && !empty($layout_id)) {

            $repo_dest = fn_get_theme_path('[themes]/' . $from_default_layout['theme_name'], 'C');
            $layout_path = fn_normalize_path($repo_dest . '/layouts/' . $from_default_layout['filename']);

            $exim = Exim::instance(
                Registry::get('runtime.company_id'), $layout_id, fn_get_theme_path('[theme]', 'C')
            );

            $structure = $exim->getStructure($layout_path);
            if (!empty($structure)) {
                foreach ($layout_data as $key => $val) {
                    if (!empty($structure->layout->{$key})) {
                        $structure->layout->{$key} = $val;
                    }
                }

                if (!isset($layout_data['is_default'])) {
                    $structure->layout->is_default = 0;
                }

                $exim->import($structure, array(
                    'import_style' => 'update'
                ));
            }

        }

        fn_clear_cache('statics', 'design/');

        return array(CONTROLLER_STATUS_OK, fn_url('block_manager.manage?s_layout=' . $layout_id));
    }

    if ($mode == 'update_block') {
        $description = array();
        if (!empty($_REQUEST['block_data']['description'])) {
            $_REQUEST['block_data']['description']['lang_code'] = DESCR_SL;
            $description = $_REQUEST['block_data']['description'];
        }

        if (!empty($_REQUEST['block_data']['content_data'])) {
            $_REQUEST['block_data']['content_data']['lang_code'] = DESCR_SL;
            if (isset($_REQUEST['block_data']['content'])) {
                $_REQUEST['block_data']['content_data']['content'] = $_REQUEST['block_data']['content'];
            }
        }

        if (!empty($_REQUEST['dynamic_object']['object_id']) && $_REQUEST['dynamic_object']['object_id'] > 0) {
            unset($_REQUEST['block_data']['properties']);
        }

        $block_id = Block::instance()->update($_REQUEST['block_data'], $description);

        if (!empty($_REQUEST['snapping_data'])) {
            // If block was newly created, and it must be snapped to grid, do it
            $snapping_data = $_REQUEST['snapping_data'];
            $snapping_data['block_id'] = $block_id;

            Block::instance()->updateSnapping($snapping_data);
        }

        if (defined('AJAX_REQUEST')) {
            if (!empty($_REQUEST['dynamic_object'])) {
                $dynamic_object = $_REQUEST['dynamic_object'];
            } else {
                $dynamic_object = array();
            }

            $block_data = Block::instance()->getById($block_id, 0, $dynamic_object, DESCR_SL);

            if (!empty($_REQUEST['assign_to'])) {
                Registry::get('view')->assign('block_data', $block_data);
                Registry::get('view')->assign('external_render', true);
                Registry::get('ajax')->assignHtml($_REQUEST['assign_to'], Registry::get('view')->fetch('views/block_manager/render/block.tpl'));
            }

            $result = $block_id;
        } else {
            if (!empty($_REQUEST['r_url'])) {
                return array(CONTROLLER_STATUS_OK, $_REQUEST['r_url']);
            }

            // Redirect to dynamic object edit page
            if (!empty($_REQUEST['dynamic_object']['object_id']) && !empty($_REQUEST['dynamic_object']['object_type'])) {
                $scheme = SchemesManager::getDynamicObjectByType($_REQUEST['dynamic_object']['object_type']);
                $return_url = $scheme['admin_dispatch'] .
                    '?' . $scheme['key'] . '=' . $_REQUEST['dynamic_object']['object_id'];

                if (!empty($_REQUEST['tab_redirect'])) {
                    $return_url .= '&selected_section=product_tabs';
                } else {
                    $return_url .= '&selected_section=blocks';
                }

                return array(CONTROLLER_STATUS_OK, $return_url);
            }

            $selected_location = fn_get_selected_location($_REQUEST);
            $suffix .= "&selected_location=" . $selected_location['location_id'];
        }
    }

    if ($mode == 'update_location') {

        fn_trusted_vars('location_data');

        $_REQUEST['location_data']['lang_code'] = DESCR_SL;
        $location_id = Location::instance()->update($_REQUEST['location_data']);

        $suffix .= "&selected_location=" . $location_id;
    }

    if ($mode == 'export_layout') {
        $location_ids = isset($_REQUEST['location_ids']) ? $_REQUEST['location_ids'] : array();
        $layout_id = Registry::get('runtime.layout.layout_id');

        $content = Exim::instance()->export($layout_id, $location_ids, $_REQUEST);

        $filename = empty($_REQUEST['filename']) ? date_format(TIME, "%m%d%Y") . 'xml' : $_REQUEST['filename'];

        if (Registry::get('runtime.company_id')) {
            $filename = Registry::get('runtime.company_id') . '/' . $filename;
        }

        fn_mkdir(dirname(Registry::get('config.dir.layouts') . $filename));

        fn_put_contents(Registry::get('config.dir.layouts') . $filename, $content);

        fn_set_notification('N', __('notice'), __('text_exim_data_exported'));

        // Direct download
        if ($_REQUEST['output'] == 'D') {
            return array(CONTROLLER_STATUS_REDIRECT, 'block_manager.manage?meta_redirect_url=block_manager.get_file%26filename=' . $_REQUEST['filename']);

        // Output to screen
        } elseif ($_REQUEST['output'] == 'C') {
            return array(CONTROLLER_STATUS_REDIRECT, 'block_manager.get_file?to_screen=Y&filename=' . $_REQUEST['filename']);
        }

    }

    if ($mode == 'import_layout') {
        $data = fn_filter_uploaded_data('filename');

        if (!empty($data[0]['path'])) {
            $result = Exim::instance()->importFromFile($data[0]['path'], $_REQUEST);

            if ($result) {
                fn_set_notification('N', __('notice'), __('text_exim_data_imported_clear'));
            }
        }
    }

    if (defined('AJAX_REQUEST')) {
        if ($result === true) {
            Registry::get('ajax')->assign('status', 'OK');
        } elseif (is_numeric($result)) {
            Registry::get('ajax')->assign('id', intval($result));
            Registry::get('ajax')->assign('status', 'OK');
        } else {
            Registry::get('ajax')->assign('status', 'FAIL');
        }

        Registry::get('ajax')->assign('mode', $mode);
        Registry::get('ajax')->assign('action', $action);

        fn_set_notification('N', __('notice'), __('text_changes_saved'));

        exit;
    }

    return array(CONTROLLER_STATUS_OK, "block_manager.manage" . $suffix);
}

if ($mode == 'manage' || $mode == 'manage_in_tab') {

    if (!(fn_allowed_for('MULTIVENDOR') && Registry::get('runtime.company_id')) && isset($_REQUEST['dynamic_object']['object_type'])) {
        $dynamic_object = SchemesManager::getDynamicObjectByType($_REQUEST['dynamic_object']['object_type']);
    } else {
        $dynamic_object = '';
    }

    $selected_location = fn_get_selected_location($_REQUEST);

    if (empty($selected_location) && !empty($_REQUEST['selected_location'])) {
        return array(CONTROLLER_STATUS_REDIRECT, 'block_manager.manage');
    }

    if (!empty($dynamic_object)) {
        // If it is some dynamic object, such as product with some id
        $dynamic_object_data = array();
        if (!empty($_REQUEST['dynamic_object'])) {
            $dynamic_object_data = $_REQUEST['dynamic_object'];
        }

        $selected_location = Location::instance()->get($dynamic_object['customer_dispatch'], $dynamic_object_data, DESCR_SL);
        Registry::get('view')->assign('location', $selected_location);

        Registry::get('view')->assign('dynamic_object', $dynamic_object_data);
        Registry::get('view')->assign('dynamic_object_scheme', $dynamic_object);
    } else {
        // If it is all another block manager
        $locations = Location::instance()->getList(array(
            'sort_by' => 'position',
            'sort_order' => 'asc'
        ), DESCR_SL);

        Registry::get('view')->assign('locations', $locations);

        // Set tabs content
        if (!empty($locations)) {
            foreach ($locations as $location => $_location) {
                Registry::set("navigation.tabs.location_". $_location['location_id'], array (
                    'title' => $_location['name'],
                    'href' => "block_manager.manage?selected_location=" . $_location['location_id']
                ));
            }
        }
    }

    Registry::get('view')->assign('location', $selected_location);

    if (!empty($_REQUEST['dynamic_object'])) {
        Registry::get('view')->assign('dynamic_object', $_REQUEST['dynamic_object']);
    }

    if (!empty($selected_location['dispatch'])) {
        Registry::get('view')->assign('dynamic_object_scheme', SchemesManager::getDynamicObject($selected_location['dispatch'], 'C'));
    }

    $theme = Themes::factory(fn_get_theme_path('[theme]', 'C'));

    Registry::get('view')->assign('theme_manifest', $theme->getManifest());

    $params = array(
        'theme_name' => $theme->getThemeName()
    );
    Registry::get('view')->assign('layouts', Layout::instance()->getList($params));
    Registry::get('view')->assign('all_layouts', Layout::instance()->getList());
    Registry::get('view')->assign('default_layouts_sources', fn_get_default_layouts_sources('responsive'));

    Registry::get('view')->assign('themes', fn_get_available_themes($theme->getThemeName()));

    $http_url = fn_url('', 'C', 'http');
    $https_url = fn_url('', 'C', 'https');
    Registry::get('view')->assign('widget_http_url', urlencode(substr($http_url, 0, strrpos($http_url, '/'))));
    Registry::get('view')->assign('widget_https_url', urlencode(substr($https_url, 0, strrpos($https_url, '/'))));

    if ($mode == 'manage') {
        Registry::get('view')->assign('layout_data', Layout::instance()->get(Registry::get('runtime.layout.layout_id')));
    } else {
        Registry::get('view')->display('views/block_manager/manage_in_tab.tpl');
        exit;
    }

} elseif ($mode == 'update_block') {
    $snapping_data = array();

    $editable_content = true;
    $editable_template_name = true;
    $editable_wrapper = false;

    if (!empty($_REQUEST['dynamic_object'])) {
        $dynamic_object = $_REQUEST['dynamic_object'];
        $editable_template_name = false;
    } else {
        $dynamic_object = array();
    }

    if (!empty($_REQUEST['snapping_data']['snapping_id'])) {
        $snapping_data = Block::instance()->getSnappingData(
            array('?:bm_blocks.type as type', '?:bm_blocks.block_id as block_id', '?:bm_snapping.*'),
            $_REQUEST['snapping_data']['snapping_id']
        );
        $type = isset($snapping_data['type']) ? $snapping_data['type'] : 'html_block';
        $block_id = isset($snapping_data['block_id']) ? $snapping_data['block_id'] : 0;
        $snapping_id = $_REQUEST['snapping_data']['snapping_id'];
    } else {
        $block_id = isset($_REQUEST['block_data']['block_id']) ? $_REQUEST['block_data']['block_id'] : 0;

        if (!empty($_REQUEST['snapping_data'])) {
            $snapping_data = $_REQUEST['snapping_data'];
        }
        $snapping_id = 0;
    }

    $content = array();

    if (!empty($_REQUEST['block_data']['content_data'])) {
        $content = $_REQUEST['block_data']['content_data'];
    }

    // If edit block
    if ($block_id > 0) {
        if (!empty($_REQUEST['snapping_data']['snapping_id'])) {
            $editable_wrapper = true;
        }

        $block_data = Block::instance()->getById($block_id, $snapping_id, $dynamic_object, DESCR_SL);

        if (!empty($block_data['content']) && empty($content['content'])) {
            $content['content'] = $block_data['content'];
        }

        $type = $block_data['type'];
        Registry::get('view')->assign('changed_content_stat', Block::instance()->getChangedContentsCount($block_id, true));
    } else {
        $type = isset($_REQUEST['block_data']['type']) ? $_REQUEST['block_data']['type'] : 'html_block';

        $block_data = array(
            'type' => $type,
            'block_id' => 0
        );
    }

    if (!empty($_REQUEST['block_data']['description']['name'])) {
        $block_data['name'] = $_REQUEST['block_data']['description']['name'];
    }

    if (!empty($_REQUEST['block_data']['properties'])) {
        $block_data['properties'] = $_REQUEST['block_data']['properties'];
    }

    if (!empty($_REQUEST['block_data']['content'])) {
        $block_data['content'] = $_REQUEST['block_data']['content'];
    }

    $block_scheme = SchemesManager::getBlockScheme($type, isset($_REQUEST['block_data']) ? $_REQUEST['block_data'] : $block_data, true);

    // Set template as first default from scheme
    if (empty($block_data['properties']['template']) && isset($block_scheme['templates'])) {
        if (is_array($block_scheme['templates'])) {
            $block_data['properties']['template'] = current(array_keys($block_scheme['templates']));
        } else {
            $block_data['properties']['template'] = $block_scheme['templates'];
        }
        $block_scheme['content'] = SchemesManager::prepareContent($block_scheme, $block_data);
    }

    // Set content_type as first default from scheme
    if (empty($block_data['properties']['content_type']) && !empty($block_scheme['content'])) {
        $block_data['properties']['content_type'] = current(array_keys($block_scheme['content']));
    }

    // Set filing as first default from scheme
    if (isset($block_scheme['content']) && is_array($block_scheme['content'])) {
        foreach ($block_scheme['content'] as $name => $scheme) {
            if (isset($scheme['type']) && $scheme['type'] == 'enum') {
                $fillings = array_keys($scheme['fillings']);
                if ((!empty($block_data['content'][$name]['filling']) && array_search($block_data['content'][$name]['filling'], $fillings) === FALSE) || empty($block_data['content'][$name]['filling'])) {
                    $block_data['content'][$name]['filling'] = current($fillings);
                }
            }
        }
    }

    $selected_location = fn_get_selected_location($_REQUEST);
    Registry::get('view')->assign('dynamic_object_scheme', SchemesManager::getDynamicObject($selected_location['dispatch'], 'C'));

    if (!empty($_REQUEST['hide_status'])) {
        Registry::get('view')->assign('hide_status', 1);
    }

    Registry::get('view')->assign('location', $selected_location);
    Registry::get('view')->assign('editable_content', $editable_content);
    Registry::get('view')->assign('editable_template_name', $editable_template_name);
    Registry::get('view')->assign('editable_wrapper', $editable_wrapper);

    Registry::get('view')->assign('block', $block_data);
    Registry::get('view')->assign('snapping_data', $snapping_data);
    Registry::get('view')->assign('block_scheme', $block_scheme);
    if (defined('AJAX_REQUEST')) {
        Registry::get('view')->display('views/block_manager/update_block.tpl');
        exit;
    }
} elseif ($mode == 'update_grid') {
    if (!empty($_REQUEST['grid_data']['grid_id'])) {
        // Update existing grid
        $grid = Grid::getById($_REQUEST['grid_data']['grid_id'], DESCR_SL);

        Registry::get('view')->assign('grid', $grid);
    }

    Registry::get('view')->assign('params', $_REQUEST['grid_data']);

} elseif ($mode == 'update_container') {
    if (!empty($_REQUEST['container_id'])) {
        // Update existing container
        $container = Container::getById($_REQUEST['container_id']);

        Registry::get('view')->assign('container', $container);
    }

} elseif ($mode == 'update_location') {

    $location_data = array(
        'dispatch' => ''
    );

    if (!empty($_REQUEST['location'])) {
        $location_data = Location::instance()->getById($_REQUEST['location'], DESCR_SL);
    }

    if (isset($_REQUEST['location_data']['dispatch'])) {
        $location_data['dispatch'] = $_REQUEST['location_data']['dispatch'];
        $location_data['object_ids'] = "";
    }

    Registry::get('view')->assign('location', $location_data);
    Registry::get('view')->assign('dynamic_object_scheme', SchemesManager::getDynamicObject($location_data['dispatch'], 'C'));
    Registry::get('view')->assign('dispatch_descriptions', SchemesManager::getDispatchDescriptions());

    if (defined('AJAX_REQUEST')) {
        Registry::get('view')->display('views/block_manager/update_location.tpl');
        exit;
    }
} elseif ($mode == 'delete_location' && !empty($_REQUEST['location_id'])) {
    Location::instance()->remove($_REQUEST['location_id']);

    return array(CONTROLLER_STATUS_OK, 'block_manager.manage');

} elseif ($mode == 'block_selection') {
    $selected_location = fn_get_selected_location($_REQUEST);

    if (!empty($_REQUEST['on_product_tabs'])) {
        $selected_location['dispatch'] = 'product_tabs';
    }

    $unique_blocks = SchemesManager::filterByLocation(Block::instance()->getAllUnique(DESCR_SL), $selected_location);
    $block_types = SchemesManager::filterByLocation(SchemesManager::getBlockTypes(DESCR_SL), $selected_location);

    if (!empty($_REQUEST['grid_id'])) {
        Registry::get('view')->assign('grid_id', $_REQUEST['grid_id']);
    }

    if (!empty($_REQUEST['extra_id'])) {
        Registry::get('view')->assign('extra_id', $_REQUEST['extra_id']);
    }

    Registry::get('view')->assign('block_types', $block_types);
    Registry::get('view')->assign('unique_blocks', $unique_blocks);

} elseif ($mode == 'export_layout') {
    $locations = Location::instance()->getList(array(), DESCR_SL);

    Registry::get('view')->assign('locations', $locations);

} elseif ($mode == 'get_file' && !empty($_REQUEST['filename'])) {
    $file = fn_basename($_REQUEST['filename']);
    if (Registry::get('runtime.company_id')) {
        $file = Registry::get('runtime.company_id') . '/' . $file;
    }

    if (!empty($_REQUEST['to_screen'])) {
        header("Content-type: text/xml");
        readfile(Registry::get('config.dir.layouts') . $file);
        exit;

    } else {
        fn_get_file(Registry::get('config.dir.layouts') . $file);
    }

} elseif ($mode == 'show_objects') {
    if (!empty($_REQUEST['object_type']) && !empty($_REQUEST['block_id'])) {
        Registry::get('view')->assign('object_type', $_REQUEST['object_type']);
        Registry::get('view')->assign('block_id', $_REQUEST['block_id']);
        Registry::get('view')->assign('object_ids', Block::instance()->getChangedContentsIds($_REQUEST['object_type'], $_REQUEST['block_id']));
        Registry::get('view')->assign('params', array('type' => 'links'));
        Registry::get('view')->assign('dynamic_object_scheme', SchemesManager::getDynamicObjectByType($_REQUEST['object_type']));
    }

} elseif ($mode == 'update_status') {

    $type = empty($_REQUEST['type']) ? 'block' : $_REQUEST['type'];

    if ($type == 'block') {
        Block::instance()->updateStatus($_REQUEST);

    } elseif ($type == 'grid') {
        Grid::update($_REQUEST);

    } elseif ($type == 'container') {
        Container::update($_REQUEST);
    }

    fn_set_notification('N', __('notice'), __('text_changes_saved'));

    exit;

} elseif ($mode == 'delete_layout') {
    Layout::instance()->delete($_REQUEST['layout_id']);

    return array(CONTROLLER_STATUS_OK, 'block_manager.manage');

} elseif ($mode == 'set_default_layout') {
    if (!empty($_REQUEST['layout_id'])) {
        Layout::instance()->setDefault($_REQUEST['layout_id']);

        fn_set_notification('N', __('notice'), __('text_changes_saved'));
    }

    return array(CONTROLLER_STATUS_OK, 'block_manager.manage');
}

function fn_get_selected_location($params)
{
    if (isset($params['selected_location']) && !empty($params['selected_location'])) {
        $selected_location = Location::instance()->getById($params['selected_location'], DESCR_SL);
    } else {
        $selected_location = Location::instance()->getDefault(DESCR_SL);
    }

    return $selected_location;
}

function fn_get_default_layouts_sources($theme_name = '', $themes_path = '')
{
    $layouts_sources = array();

    if (empty($themes_path)) {
        $themes_path = fn_get_theme_path('[themes]', 'C');
    }

    if (empty($theme_name)) {

        $installed_themes = fn_get_dir_contents($themes_path, true);

        foreach ($installed_themes as $theme_name) {
            $layouts_sources = array_merge($layouts_sources, fn_get_default_layouts_sources($theme_name, $themes_path));
        }

    } else {

        $layouts_path = $themes_path . '/' . $theme_name . '/layouts/';
        $layouts = fn_get_dir_contents($layouts_path, false, true, '.xml');

        foreach ($layouts as $layout_name) {

            $layout_path = fn_normalize_path($layouts_path . $layout_name);

            if (file_exists($layout_path)) {

                $layout_data = Exim::instance(
                    Registry::get('runtime.company_id'), 0, $theme_name
                )->getLayoutData($layout_path, false);

                if (!empty($layout_data)) {
                    $layout_data['theme_name'] = $theme_name;
                    $layout_data['filename'] = $layout_name;
                    $layouts_sources[] = $layout_data;
                }
            }
        }
    }

    return $layouts_sources;

}
