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

class Auth extends AEntity
{
    public function index($id = '', $params = array())
    {
        return array(
            'status' => Response::STATUS_METHOD_NOT_ALLOWED,
            'data' => array()
        );
    }

    public function create($params)
    {
        $status = Response::STATUS_BAD_REQUEST;
        $data = array();

        $email = $this->safeGet($params, 'email', null);

        if ($email) {
            $status = Response::STATUS_NOT_FOUND;

            $notify = $this->safeGet($params, 'notify', false);
            $result = fn_recover_password_generate_key($email, $notify);
            if ($result) {
                $status = Response::STATUS_CREATED;
                
                if ($notify) {
                    $data = array(
                        'message' => __('text_password_recovery_instructions_sent'),
                    );   
                } else {
                    $link = 'auth.ekey_login?ekey=' . $result['key'] . '&company_id=' . $result['company_id'];

                    if ($redirect_url = $this->safeGet($params, 'redirect_url', '')) {
                        $link .= '&redirect_url=' . urlencode($redirect_url);
                    }

                    $data = array(
                        'key' => $result['key'],
                        'link' => fn_url($link, $result['user_type'], 'current', CART_LANGUAGE, true),
                    );
                }
            }
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function update($id, $params)
    {
        return array(
            'status' => Response::STATUS_METHOD_NOT_ALLOWED,
            'data' => array()
        );
    }

    public function delete($id)
    {
        return array(
            'status' => Response::STATUS_METHOD_NOT_ALLOWED,
            'data' => array()
        );
    }

    public function privileges()
    {
        return array(
            'index'  => false,
            'create' => 'manage_users',
            'update' => false,
            'delete' => false,
        );
    }

}
