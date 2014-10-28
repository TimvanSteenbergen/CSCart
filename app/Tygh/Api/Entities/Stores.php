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
use Tygh\Settings;
use Tygh\Api\AEntity;
use Tygh\Api\Response;

class Stores extends AEntity
{
    protected $setting_names = array(
        'company_name',
        'company_address',
        'company_city',
        'company_country',
        'company_state',
        'company_state',
        'company_zipcode',
        'company_phone',
        'company_phone_2',
        'company_fax',
        'company_website',
        'company_start_year',
        'company_users_department',
        'company_site_administrator',
        'company_orders_department',
        'company_support_department',
        'company_newsletter_email'
    );

    protected function updateCompanySettings($params, $company_id)
    {
        $settings = array();

        foreach ($this->setting_names as $setting_name) {
            if (isset($params[$setting_name])) {
                Settings::instance()->updateValue($setting_name, $params[$setting_name], '', false, $company_id);
            }
        }

    }

    protected function checkRequiredParams($params, $mode = 'update')
    {
        $result = array(
            'valid_params' => true,
            'message' => '',
        );

        if ($mode == 'add') {
            if (empty($params['company'])) {
                $result['message'] = __('api_required_field', array(
                    '[field]' => 'company'
                ));
                $result['valid_params'] = false;
            }

            if (empty($params['storefront'])) {
                $result['message'] = __('api_required_field', array(
                    '[field]' => 'storefront'
                ));
                $result['valid_params'] = false;
            }            
        }

        return array($result['valid_params'], $result['message']);
    }

    public function index($id = 0, $params = array())
    {
        $status = Response::STATUS_BAD_REQUEST;

        if (!empty($this->auth['company_id'])) {
            $params['company_id'] = $this->auth['company_id'];
        }

        $lang_code = $this->safeGet($params, 'lang_code', DEFAULT_LANGUAGE);

        if (!empty($id)) {

            if (!empty($this->auth['company_id']) && $this->auth['company_id'] != $id) {
                $status = Response::STATUS_FORBIDDEN;
                $data = array();

            } else {

                $params = array(
                    'company_id' => $id
                );
                list($data) = fn_get_companies($params, $this->auth, 0, $lang_code);

                if (empty($data)) {
                    $status = Response::STATUS_NOT_FOUND;
                } else {
                    $status = Response::STATUS_OK;
                }

                $data = reset($data);
                Registry::set('runtime.company_id', $id);
            }

        } else {
            $items_per_page = $this->safeGet($params, 'items_per_page', Registry::get('settings.Appearance.admin_products_per_page'));
            list($data, $params) = fn_get_companies(array(),$this->auth, $items_per_page, $lang_code);
            $pathes = explode('\\', get_class($this));
            $key = strtolower(array_pop($pathes));
            $data = array(
                $key => $data,
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

        unset($params['company_id']);

        if (Registry::get('runtime.simple_ultimate')) {
            Registry::set('runtime.simple_ultimate', false);
        }

        list($valid_params, $data['message']) = $this->checkRequiredParams($params, 'add');

        if ($valid_params) {
            $company_id = fn_update_company($params);

            if ($company_id) {
                $status = Response::STATUS_OK;
                $data = array(
                    'store_id' => $company_id,
                );
            }
        }

        return array(
            'status' => $status,
            'data' => $data,
        );
    }

    public function update($id, $params)
    {
        $data = array();
        $valid_params = true;
        $status = Response::STATUS_BAD_REQUEST;

        unset($params['company_id']);

        $company_data = fn_get_company_data($id);

        if (empty($company_data)) {
            $message = __('api_need_correct_company_id');
            $valid_params = false;
        } else {
            list($valid_params, $message) = $this->checkRequiredParams($params);
        }

        if ($valid_params) {
            if (!empty($this->auth['company_id']) && $this->auth['company_id'] != $id) {
                $status = Response::STATUS_FORBIDDEN;
                $data = array();
            } else {
                $lang_code = $this->safeGet($params, 'lang_code', DEFAULT_LANGUAGE);

                $company_id = fn_update_company($params, $id, $lang_code);

                if ($company_id) {
                    $this->updateCompanySettings($params, $id);

                    $status = Response::STATUS_OK;
                    $data['store_id'] = $company_id;
                }
            }
        }

        if (!empty($message)) {
            $data['message'] = $message;
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function delete($id)
    {
        $data = array();
        $status = Response::STATUS_BAD_REQUEST;

        if (fn_delete_company($id)) {
            $status = Response::STATUS_OK;
            $data['message'] = 'Ok';
        } elseif (!fn_notification_exists('extra', 'company_has_orders')) {
            $status = Response::STATUS_NOT_FOUND;
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function privileges()
    {
        $privileges = array(
            'create' => 'manage_stores',
            'update' => 'manage_stores',
            'delete' => 'manage_stores',
            'index'  => 'view_stores'
        );

        return $privileges;
    }

    public function childEntities()
    {
        return array(
            'products',
            'categories',
            'languages',
        );
    }
}
