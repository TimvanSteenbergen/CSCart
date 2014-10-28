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

class News extends AEntity
{

    public function index($id = 0, $params = array())
    {
        $lang_code = $this->_safeGet($params, 'lang_code', DEFAULT_LANGUAGE);

        if (empty($id)) {
            list($news, $search) = fn_get_news($params, Registry::get('settings.Appearance.admin_elements_per_page'), $lang_code);
            $data['news'] = $news;
            $data['params'] = $search;

        } else {
            $data = fn_get_news_data($id, $lang_code);
        }

        if (empty($data)) {
            $status = Response::STATUS_NOT_FOUND;
        } else {
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
        $lang_code = $this->_safeGet($params, 'lang_code', DEFAULT_LANGUAGE);

        unset($params['news_id']);

        $new_id = fn_update_news(0, $params, $lang_code);

        if ($new_id) {
            $status = Response::STATUS_CREATED;
            $data = array(
                'news_id' => $new_id,
            );
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
        unset($params['news_id']);

        $lang_code = $this->_safeGet($params, 'lang_code', DEFAULT_LANGUAGE);
        $new_id = fn_update_news($id, $params, $lang_code);

        if ($new_id) {
            $status = Response::STATUS_OK;
            $data = array(
                'new_id' => $new_id
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
        $status = Response::STATUS_BAD_REQUEST;

        if (fn_delete_news($id)) {
            $status = Response::STATUS_OK;
            $data['message'] = 'Ok';
            $data['status'] = $status;
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function privileges()
    {
        return array(
            'create' => 'manage_news',
            'update' => 'manage_news',
            'delete' => 'manage_news',
            'index'  => 'view_news'
        );
    }

}
