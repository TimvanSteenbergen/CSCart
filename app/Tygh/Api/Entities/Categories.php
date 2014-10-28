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

namespace Tygh\Api\Entities;

use Tygh\Api\AEntity;
use Tygh\Api\Response;
use Tygh\Registry;

class Categories extends AEntity
{
    public function index($id = 0, $params = array())
    {
        $lang_code = $this->safeGet($params, 'lang_code', DEFAULT_LANGUAGE);

        if (!empty($id)) {
            $data = fn_get_category_data($id, $lang_code);

            if (empty($data)) {
                $status = Response::STATUS_NOT_FOUND;
            } else {
                $status = Response::STATUS_OK;
            }

        } else {
            if (!empty($params['category_ids']) && is_array($params['category_ids'])) {
                $params['item_ids'] = $params['category_ids'];
            }
            $params['plain'] = $this->safeGet($params, 'plain', true);
            $params['simple'] = $this->safeGet($params, 'simple', false);
            $params['group_by_level'] = $this->safeGet($params, 'group_by_level', false);

            $items_per_page = $this->safeGet($params, 'items_per_page', Registry::get('settings.Appearance.admin_products_per_page'));
            $page = $this->safeGet($params, 'page', 1);

            list($data, $params) = fn_get_categories($params, $lang_code);

            $params['items_per_page'] = $items_per_page;
            $params['page'] = $page;
            $params['total_items'] = count($data);

            if ($items_per_page) {
                $data = array_slice($data, ($page - 1) * $items_per_page, $items_per_page);
            }

            $data = array(
                'categories' => $data,
                'params' => $params,
            );
            $status = Response::STATUS_OK;
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function create($params)
    {
        $status = Response::STATUS_BAD_REQUEST;
        $data = array();
        $valid_params = true;

        unset($params['category_id']);

        if (empty($params['category'])) {
            $data['message'] = __('api_required_field', array(
                '[field]' => 'category'
            ));
            $valid_params = false;
        }

        if ($valid_params) {
            $category_id = fn_update_category($params, 0);

            if ($category_id) {
                $this->prepareImages($params, $category_id);
                fn_attach_image_pairs('category_main', 'category', $category_id, DESCR_SL);

                $status = Response::STATUS_CREATED;
                $data = array(
                    'category_id' => $category_id,
                );
            }
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function update($id, $params)
    {
        $data = array();
        $status = Response::STATUS_BAD_REQUEST;
        unset($params['category_id']);

        $lang_code = $this->safeGet($params, 'lang_code', DEFAULT_LANGUAGE);
        $category_id = fn_update_category($params, $id, $lang_code);
        $this->prepareImages($params, $id);
        $updated = fn_attach_image_pairs('category_main', 'category', $id, DESCR_SL);

        if ($category_id || $updated) {
            if ($updated && fn_notification_exists('extra', '404')) {
                fn_delete_notification('404');
            }

            $status = Response::STATUS_OK;
            $data = array(
                'category_id' => $id
            );
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function delete($id)
    {
        $data = array();
        $status = Response::STATUS_NOT_FOUND;

        if (fn_delete_category($id)) {
            $status = Response::STATUS_OK;
            $data['message'] = 'Ok';
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function privileges()
    {
        return array(
            'create' => 'manage_catalog',
            'update' => 'manage_catalog',
            'delete' => 'manage_catalog',
            'index'  => 'view_catalog'
        );
    }

    public function privilegesCustomer()
    {
        return array(
            'index' => true
        );
    }

    public function childEntities()
    {
        return array(
            'products'
        );
    }

    public function prepareImages($params, $category_id = 0)
    {
        if (isset($params['main_pair'])) {
            if (!empty($params['main_pair']['detailed']['image_path'])) {
                $_REQUEST['file_category_main_image_detailed'][] = $params['main_pair']['detailed']['image_path'];
                $_REQUEST['type_category_main_image_detailed'][] = (strpos($params['main_pair']['detailed']['image_path'], '://') === false) ? 'server' : 'url';
            }

            if (!empty($params['main_pair']['icon']['image_path'])) {
                $_REQUEST['file_category_main_image_icon'][] = $params['main_pair']['icon']['image_path'];
                $_REQUEST['type_category_main_image_icon'][] = (strpos($params['main_pair']['icon']['image_path'], '://') === false) ? 'server' : 'url';
            }

            $_REQUEST['category_main_image_data'][] = array(
                'pair_id' => 0,
                'type' => 'M',
                'object_id' => $category_id,
                'image_alt' => !empty($params['main_pair']['icon']['alt']) ? $params['main_pair']['icon']['alt'] : '',
                'detailed_alt' => !empty($params['main_pair']['detailed']['alt']) ? $params['main_pair']['detailed']['alt'] : '',
            );
        }

    }
}
