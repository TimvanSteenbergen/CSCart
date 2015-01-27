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

class Taxes extends AEntity
{

    public function index($id = 0, $params = array())
    {
        $lang_code = $this->safeGet($params, 'lang_code', DEFAULT_LANGUAGE);

        if (!empty($id)) {
            $data = fn_get_tax($id, $lang_code);

            if (empty($data)) {
                $status = Response::STATUS_NOT_FOUND;
            } else {
                $status = Response::STATUS_OK;
            }

        } else {
            $items_per_page = $this->safeGet($params, 'items_per_page', Registry::get('settings.Appearance.admin_products_per_page'));
            $page = $this->safeGet($params, 'page', 1);

            $data = fn_get_taxes($lang_code);
            $data = array_values($data);

            if ($items_per_page) {
                $data = array_slice($data, ($page - 1) * $items_per_page, $items_per_page);
            }

            $data = array(
                'taxes' => $data,
                'params' => array(
                    'items_per_page' => $items_per_page,
                    'page' => $page,
                    'total_items' => count($data),
                ),
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

        if (empty($params['tax'])) {
            $data['message'] = __('api_required_field', array(
                '[field]' => 'tax'
            ));
            $valid_params = false;
        }

        if ($valid_params) {
            $tax_id = fn_update_tax($params, 0);

            if ($tax_id) {
                $status = Response::STATUS_CREATED;
                $data = array(
                    'tax_id' => $tax_id,
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
        $status = Response::STATUS_BAD_REQUEST;
        $data = array();

        $lang_code = $this->safeGet($params, 'lang_code', DEFAULT_LANGUAGE);
        $tax_id = fn_update_tax($params, $id, $lang_code);

        if ($tax_id) {
            $status = Response::STATUS_OK;
            $data = array(
                'tax_id' => $tax_id
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

        if (fn_delete_tax($id)) {
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
            'create' => 'manage_taxes',
            'update' => 'manage_taxes',
            'delete' => 'manage_taxes',
            'index'  => 'view_taxes'
        );
    }

}
