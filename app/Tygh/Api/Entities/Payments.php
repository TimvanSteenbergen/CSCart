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

use Tygh\Registry;
use Tygh\Api\AEntity;
use Tygh\Api\Response;

class Payments extends AEntity
{

    public function index($id = 0, $params = array())
    {
        $lang_code = $this->safeGet($params, 'lang_code', DEFAULT_LANGUAGE);

        if (!empty($id)) {
            $data = fn_get_payment_method_data($id, $lang_code);

            if (empty($data)) {
                $status = Response::STATUS_NOT_FOUND;
            } else {
                $status = Response::STATUS_OK;
            }

        } else {
            $items_per_page = $this->safeGet($params, 'items_per_page', Registry::get('settings.Appearance.admin_products_per_page'));
            $page = $this->safeGet($params, 'page', 1);

            $data = fn_get_payments($lang_code);

            if ($items_per_page) {
                $data = array_slice($data, ($page - 1) * $items_per_page, $items_per_page);
            }

            $data = array(
                'payments' => $data,
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

        $params['company_id'] = Registry::get('runtime.company_id');

        if (!empty($params['payment']) && $params['company_id'] != 0) {

            $payment_id = fn_update_payment($params, 0);

            if ($payment_id) {
                $status = Response::STATUS_CREATED;
                $data = array(
                    'payment_id' => $payment_id,
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

        if (isset($params['processor_params']['certificate_filename']) && !$params['processor_params']['certificate_filename']) {
            fn_rm(Registry::get('config.dir.certificates') . $id);
        }

        $payment_id = fn_update_payment($params, $id, $lang_code);

        if ($payment_id) {
            $status = Response::STATUS_OK;
            $data = array(
                'payment_id' => $payment_id
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

        if (fn_delete_payment($id)) {
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
            'create' => 'manage_payments',
            'update' => 'manage_payments',
            'delete' => 'manage_payments',
            'index'  => 'view_payments'
        );
    }

}
