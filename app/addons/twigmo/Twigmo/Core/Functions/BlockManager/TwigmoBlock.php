<?php

namespace Twigmo\Core\Functions\BlockManager;

use Tygh\BlockManager\Location;
use Tygh\BlockManager\Container;
use Tygh\BlockManager\Grid;
use Tygh\BlockManager\Block;
use Tygh\BlockManager\SchemesManager;
use Tygh\BlockManager\RenderManager;
use Twigmo\Core\Api;
use Twigmo\Core\Functions\Image\TwigmoImage;
use Twigmo\Core\TwigmoSettings;

class TwigmoBlock
{
    /**
     * Get blocks for the twigmo homepage
     * @param  string $dispatch        Dispatch of needed location
     * @param  array  $allowed_objects - array of blocks types
     * @return array  blocks
     */
    final public static function getBlocksForLocation($dispatch, $allowed_objects)
    {
        $allowed_page_types = array('T', 'L', 'F');
        $blocks = array();
        $location = Location::instance(fn_twg_get_default_layout_id())->get($dispatch);
        if (!$location) {
            return $blocks;
        }
        $get_cont_params = array('location_id' => $location['location_id']);
        $container = Container::getList($get_cont_params);
        if (!$container or !$container['CONTENT']) {
            return $blocks;
        }
        $grids_params = array('container_ids' => $container['CONTENT']['container_id'], );
        $grids = Grid::getList($grids_params);
        if (!$grids) {
            return $blocks;
        }
        $block_grids = Block::instance()->getList(
            array('?:bm_snapping.*','?:bm_blocks.*', '?:bm_blocks_descriptions.*'),
            Grid::getIds($grids)
        );
        $image_params = TwigmoSettings::get('images.catalog');

        foreach ($block_grids as $block_grid) {
            foreach ($block_grid as $block) {
                if ($block['status'] != 'A' or !in_array($block['type'], $allowed_objects)) {
                    continue;
                }
                $block_data = array(
                    'block_id' => $block['block_id'],
                    'title' => $block['name'],
                    'hide_header' => isset($block['properties']['hide_header']) ?
                        $block['properties']['hide_header'] :
                        'N',
                    'user_class' => $block['user_class']
                );
                $block_scheme = SchemesManager::getBlockScheme($block['type'], array());
                if ($block['type'] == 'html_block') {
                    // Html block
                    if (
                        isset($block['content']['content'])
                        and fn_string_not_empty($block['content']['content'])
                    ) {
                        $block_data['html'] = $block['content']['content'];
                    }
                } elseif (
                    !empty($block_scheme['content'])
                    and !empty($block_scheme['content']['items'])
                ) {
                    // Products and categories: get items
                    $template_variable = 'items';
                    $field = $block_scheme['content']['items'];
                    fn_set_hook(
                        'render_block_content_pre',
                        $template_variable,
                        $field,
                        $block_scheme,
                        $block
                    );
                    $items = RenderManager::getValue(
                        $template_variable,
                        $field,
                        $block_scheme,
                        $block
                    );
                    // Filter pages - only texts, links and forms posible
                    if ($block['type'] == 'pages') {
                        foreach ($items as $item_id => $item) {
                            if (!in_array($item['page_type'], $allowed_page_types)) {
                                unset($items[$item_id]);
                            }
                        }
                    }
                    if (empty($items)) {
                        continue;
                    }
                    $block_data['total_items'] = count($items);
                    // Images
                    if ($block['type'] == 'products' or $block['type'] == 'categories') {
                        $object_type = $block['type'] == 'products' ? 'product' : 'category';
                        foreach ($items as $items_id => $item) {
                            if (!empty($item['main_pair'])) {
                                $main_pair = $item['main_pair'];
                            } else {
                                $main_pair = fn_get_image_pairs($item[$object_type . '_id'], $object_type, 'M', true, true);
                            }
                            if (!empty($main_pair)) {
                                $items[$items_id]['icon'] =
                                    TwigmoImage::getApiImageData(
                                        $main_pair,
                                        $object_type,
                                        'icon',
                                        $image_params
                                    );
                            }
                        }
                    }
                    // Banners properties
                    if ($block['type'] == 'banners') {
                        $rotation =
                            $block['properties']['template'] == 'addons/banners/blocks/carousel.tpl' ?
                                'Y' :
                                'N';
                        $block_data['delay'] =
                            $rotation == 'Y' ?
                                $block['properties']['delay'] :
                                0;
                        $block_data['hide_navigation'] = (isset($block['properties']['navigation']) && $block['properties']['navigation'] == 'N') ? 'Y' : 'N';
                    }
                    $block_data[$block['type']] =
                        Api::getAsList($block['type'], $items);
                }
                $blocks[$block['block_id']] = $block_data;
            }
        }

        return $blocks;
    }

    /**
     * Get block by id
     * @param  array $params
     * @return array $block
     */
    final public static function getBlock($params)
    {
        if (!empty($params['block_id'])) {
            $block_id =  $params['block_id'];
            $snapping_id = !empty($params['snapping_id']) ? $params['snapping_id'] : 0;

            $dispatch =
                isset($_REQUEST['object']) ?
                    $_REQUEST['object'] . '.view' :
                    'index.index';

            $area = !empty($params['area']) ?  $params['area'] : AREA;

            if (!empty($params['dynamic_object'])) {
                $dynamic_object = $params['dynamic_object'];
            } elseif (!empty($_REQUEST['dynamic_object']) && $area != 'C') {
                $dynamic_object = $_REQUEST['dynamic_object'];
            } else {
                    $dynamic_obj_schema = SchemesManager::getDynamicObject(
                        $dispatch,
                        $area
                    );
                    $twg_request = array(
                        'dispatch' => $dispatch,
                        $dynamic_obj_schema['key'] => $_REQUEST['id']
                    );
                if (
                    !empty($dynamic_obj_schema)
                    && !empty($twg_request[$dynamic_obj_schema['key']])
                ) {
                    $dynamic_object['object_type'] = $dynamic_obj_schema['object_type'];
                    $dynamic_object['object_id'] = $twg_request[$dynamic_obj_schema['key']];
                } else {
                    $dynamic_object = array();
                }
            }
            $block =
                Block::instance()->getById(
                    $block_id,
                    $snapping_id,
                    $dynamic_object,
                    DESCR_SL
                );

            return $block;
        }
    }
}
