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

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

use Tygh\Registry;
use Twigmo\Core\Functions\Order\TwigmoOrder;
use Twigmo\Core\Functions\Image\TwigmoImage;
use Twigmo\Core\Api;
use Twigmo\Core\TwigmoConnector;
use Tygh\Session;
use Tygh\Navigation\LastView;
use Twigmo\Core\Functions\Lang;
use Twigmo\Api\ApiData;

$format = !empty($_REQUEST['format']) ? $_REQUEST['format'] : TWG_DEFAULT_DATA_FORMAT;
$api_version = !empty($_REQUEST['api_version']) ? $_REQUEST['api_version'] : TWG_DEFAULT_API_VERSION;
$response = new ApiData($api_version, $format);

if (!empty($_REQUEST['callback'])) {
    $response->setCallback($_REQUEST['callback']);
}

$object = !empty($_REQUEST['object']) ? $_REQUEST['object'] : '';
$lang_code = DESCR_SL;

$trusted_actions = array('auth.svc', 'auth.app');
if (empty($auth['user_id']) && (empty($_REQUEST['action']) || !in_array($_REQUEST['action'], $trusted_actions))) {
    $response->addError('ERROR_ACCESS_DENIED', __('access_denied'));
    $response->returnResponse();
}

$data = '';

if (!empty($_REQUEST['data'])) {
    $data = ApiData::parseDocument(base64_decode(rawurldecode($_REQUEST['data'])), $format);
}

$update_actions = array('update', 'delete');

if (($_SERVER['REQUEST_METHOD'] == 'POST' || $format == 'jsonp') &&  in_array($_REQUEST['action'], $update_actions)) {

    if (empty($data)) {
        $response->addError('ERROR_WRONG_DATA', __('twgadmin_wrong_api_data'));
    }

    if ($mode == 'post') {
        if ($object == 'profile') {
            $user_data = fn_twg_get_api_data($response, $format);

            $user_data['ship_to_another'] = empty($user_data['copy_address']) ? 'Y' : '';
            if (empty($user_data['ship_to_another'])) {
                $profile_fields = fn_get_profile_fields('O');
                fn_fill_address($user_data, $profile_fields);
            }
            if (isset($user_data['fields']) && is_array($user_data['fields'])) {
                $user_data['fields'] = array_filter($user_data['fields'], 'fn_twg_filter_profile_fields');
            }
            $_auth = null;
            $user_data = array_merge(fn_get_user_info($user_data['user_id'], false), $user_data);

            $result = fn_update_user($user_data['user_id'], $user_data, $_auth, $user_data['ship_to_another'], false);

            if ($result) {
                fn_set_notification('N', __('information'), __('text_profile_is_updated'));
            } else {
                if (!fn_twg_set_internal_errors($response, 'ERROR_FAIL_CREATE_USER')) {
                    $response->addError('ERROR_FAIL_CREATE_USER', __('twgadmin_fail_create_user'));
                }
                $response->returnResponse();
            }
            $profile = fn_twg_get_user_info(array('user_id' => $user_data['user_id'], 'mode' => $mode));
            $response->setData($profile);
        }

        if ($object == 'shipments') {

            if ($_REQUEST['action'] == 'update') {

                $shipments = fn_check_shipment_data($data);

                if ($shipments) {
                    foreach ($shipments as $shipment) {
                        if (empty($shipment['shipment_id'])) {
                            $shipment_id = db_query(
                                "INSERT INTO ?:shipments ?e",
                                $shipment
                            );
                            foreach ($shipment['products'] as $product) {
                                if (!empty($product['amount'])) {
                                    $product['shipment_id'] = $shipment_id;
                                    db_query(
                                        "INSERT INTO ?:shipment_items ?e",
                                        $product
                                    );
                                }
                            }
                        } else {
                            db_query(
                                "UPDATE ?:shipments SET ?u WHERE shipment_id = ?i",
                                $shipment,
                                $shipment['shipment_id']
                            );
                            foreach ($shipment['products'] as $product) {
                                $product['shipment_id'] = $shipment['shipment_id'];
                                if (empty($product['amount'])) {
                                    db_query(
                                        "DELETE FROM ?:shipment_items WHERE item_id = ?i AND shipment_id = ?i",
                                        $product['item_id'],
                                        $product['shipment_id']
                                    );
                                } else {
                                    db_query(
                                        "REPLACE INTO ?:shipment_items ?e",
                                        $product
                                    );
                                }
                            }
                        }
                    }
                } else {
                    $response->addError(
                        'ERROR_WRONG_OBJECT_DATA',
                        str_replace(
                            '[object]',
                            'shipments',
                            __(
                                'twgadmin_wrong_api_object_data'
                            )
                        )
                    );
                }
            } elseif ($_REQUEST['action'] == 'delete') {
                $shipment_ids = array();
                foreach ($data as $shipment) {
                    if (empty($shipment['shipment_id'])) {
                        $response->addError(
                            'ERROR_WRONG_OBJECT_DATA',
                            str_replace(
                                '[object]',
                                'shipments',
                                __(
                                    'twgadmin_wrong_api_object_data'
                                )
                            )
                        );
                    }
                    $shipment_ids[] = $shipment['shipment_id'];
                }

                if (!empty($shipment_ids)) {
                    db_query(
                        'DELETE FROM ?:shipments WHERE shipment_id IN (?a)',
                        $shipment_ids
                    );
                    db_query(
                        'DELETE FROM ?:shipment_items WHERE shipment_id IN (?a)',
                        $shipment_ids
                    );
                }
            }
        }

        if ($object == 'orders') {
            if ($_REQUEST['action'] == 'update') {
                if (!empty($data['order_id'])) {
                    TwigmoOrder::apiUpdateOrder($data, $response);
                } else {
                    $response->addError('ERROR_WRONG_OBJECT_DATA', str_replace('[object]', 'orders', __('twgadmin_wrong_api_object_data')));
                }
            }
        }

        if ($object == 'products') {
            if ($_REQUEST['action'] == 'update') {
                foreach ($data as $product) {
                    if (!empty($product['product_id'])) {
                        $_REQUEST['update_all_vendors'] = $product['update_all_vendors'];
                        fn_update_product($product, $product['product_id'], $lang_code);
                    } else {
                        $response->addError(
                            'ERROR_WRONG_OBJECT_DATA',
                            str_replace(
                                '[object]',
                                'products',
                                __(
                                    'twgadmin_wrong_api_object_data'
                                )
                            )
                        );
                    }
                }
            }
        }

        if ($object == 'categories') {
            if ($_REQUEST['action'] == 'update') {

                foreach ($data as $category) {
                    // allow to update only order status
                    if (!empty($category['category_id'])) {

                        if (!fn_update_category(
                                $category,
                                $category['category_id'],
                                $lang_code
                            )) {
                            $msg = str_replace(
                                '[object_id]',
                                $category['category_id'],
                                __(
                                    'twgadmin_wrong_api_object_data'
                                )
                            );
                            $response->addError(
                                'ERROR_OBJECT_UPDATE',
                                str_replace(
                                    '[object]',
                                    'categories',
                                    __(
                                        'twgadmin_wrong_api_object_data'
                                    )
                                )
                            );
                        } elseif (!empty($category['icon'])) {
                            TwigmoImage::updateIconsByApiData(
                                $category['icon'],
                                $category['category_id'],
                                'category'
                            );
                        }

                    } else {
                        $response->addError(
                            'ERROR_WRONG_OBJECT_DATA',
                            str_replace(
                                '[object]',
                                'categories',
                                __(
                                    'twgadmin_wrong_api_object_data'
                                )
                            )
                        );
                    }
                }
            }
        }

        if ($object == 'images') {
            if ($_REQUEST['action'] == 'delete') {
                foreach ($data as $image) {
                    if (empty($image['pair_id'])) {
                        $response->addError('ERROR_WRONG_OBJECT_DATA', str_replace('[object]', 'images', __('twgadmin_wrong_api_object_data')));
                        continue;
                    }
                    fn_delete_image_pair($image['pair_id'], 'product');
                }
            }
        }

        $response->returnResponse();
    }

}

if ($mode == 'post') {
    if ($_REQUEST['action'] == 'auth.svc') {
        $connector = new TwigmoConnector();
        $request = $connector->parseResponse($_REQUEST['data']);
        if (!$connector->responseIsOk($request) || empty($request['data']['user_login']) || empty($request['data']['password'])) {
            $connector->onError();
        }

        $_POST = $_REQUEST = array_merge($_REQUEST, $request['data']);

        list($status, $user_data, $user_login, $password, $salt) = fn_twg_api_auth_routines($_REQUEST['user_login'], $_REQUEST['password']);

        $is_ok = !empty($user_data) && !empty($password) && fn_generate_salted_password($password, $salt) == $user_data['password'];

        if ($status === false || !$is_ok || $user_data['is_root'] !== 'Y') {
            $connector->onError();
        }
        $connector->respond();
    } elseif ($_REQUEST['action'] == 'auth.app') {
        $_POST['password'] = $_REQUEST['password'];
        list($status, $user_data, $user_login, $password, $salt) = fn_twg_api_auth_routines($_REQUEST['user_login'], $_REQUEST['password']);

        $is_ok = !empty($user_data) && !empty($password) && fn_generate_salted_password($password, $salt) == $user_data['password'];

        if ($status === false || !$is_ok || $user_data['is_root'] !== 'Y') {
            $response->addError('ERROR_ACCESS_DENIED', __('access_denied'));
            $response->returnResponse();
        }

        // Regenerate session_id for security reasons
        Session::regenerateId();
        fn_login_user($user_data['user_id']);
        fn_set_session_data(AREA . '_user_id', $user_data['user_id'], COOKIE_ALIVE_TIME);
        fn_set_session_data(AREA . '_password', $user_data['password'], COOKIE_ALIVE_TIME);
        // Set last login time
        db_query("UPDATE ?:users SET ?u WHERE user_id = ?i", array('last_login' => TIME), $user_data['user_id']);

        $_SESSION['auth']['this_login'] = TIME;
        $_SESSION['auth']['ip'] = $_SERVER['REMOTE_ADDR'];

        // Log user successful login
        fn_log_event('users', 'session', array(
            'user_id' => $user_data['user_id'],
            'company_id' => fn_get_company_id('users', 'user_id', $user_data['user_id']),
        ));
        $response->setData(array('status' => 'ok'));
        $response->setData(array('settings' => fn_twg_get_admin_settings()));
        $response->returnResponse();
    } elseif ($_REQUEST['action'] == 'get') {
        $object_name = '';
        $condition = array();
        $options = array(
            'lang_code' => $lang_code
        );
        $sortings = array();
        $result = array();
        $is_paginate = false;
        $total_items = 0;
        $items_per_page =
            !empty($_REQUEST['items_per_page'])
            ? $_REQUEST['items_per_page']
            : TWG_RESPONSE_ITEMS_LIMIT;

        if ($object == 'timeline') {
            list($logs, $pagination_params) = fn_twg_get_logs($_REQUEST);
            $response->setData($logs);
            fn_twg_set_response_pagination($response, $pagination_params);
        } elseif ($object == 'dashboard') {
            list($logs, $pagination_params) = fn_twg_get_logs();
            $data = array(
                'products' => reset(fn_twg_api_get_products(array('amount_to' => 10), 7, $lang_code)),
                'orders' => fn_twg_get_latest_orders($lang_code),
                'summary_stats' => fn_twg_get_summary_stats(),
                'products_stats' => fn_twg_get_product_stats(),
                'users_stats' => fn_twg_get_user_stats(),
                'timeline' => array($logs, $pagination_params)
            );
            $response->setData($data);
            $is_paginate = true;
        } elseif ($object == 'users') {
            $auth = null;
            $_REQUEST['user_type'] = 'C';

            if (empty($_REQUEST['page'])) {
                $_REQUEST['page'] = 1;
            }

            list($users, $search) = fn_get_users($_REQUEST, $auth, $items_per_page);

            $total_items = $search['total_items'];

            $u_ids = array();
            foreach ($users as $k => $v) {
                $u_ids[] = $v['user_id'];
            }

            if (empty($users)) {
                $response->returnResponse();
            }

            $response->setResponseList(Api::getAsList($object, $users));
            $is_paginate = true;

        } elseif ($object == 'orders') {
            $_REQUEST['compact'] = 'Y';
            if (!empty($_REQUEST['sname'])) {
                $_REQUEST['cname'] = $_REQUEST['email'] = $_REQUEST['order_id'] = $_REQUEST['sname'];
            }

            if (!empty($_REQUEST['status'])) {
                $_REQUEST['status'] = unserialize($_REQUEST['status']);
            }

            list($orders, $search) = fn_get_orders($_REQUEST, $items_per_page);
            $total_items = $search['total_items'];

            if (empty($orders)) {
                $response->returnResponse();
            }

            $response->setResponseList(TwigmoOrder::getOrdersAsApiList($orders, $lang_code));
            $is_paginate = true;

        } elseif ($object == 'products') {
            fn_twg_set_response_products(
                $response,
                $_REQUEST,
                $items_per_page,
                $lang_code
            );

        } elseif ($object == 'categories' || $object == 'categories_paginated') {

            if ($object == 'categories') {
                fn_twg_set_response_categories($response, $_REQUEST, 0, $lang_code);
            } elseif ($object == 'categories_paginated') {
                fn_twg_set_response_categories(
                    $response,
                    $_REQUEST,
                    $items_per_page,
                    $lang_code
                );
            }

        } elseif ($object == 'shipments') {

            $_REQUEST['advanced_info'] = true;
            list($shipments, $search, $totals) = fn_get_shipments_info(
                $_REQUEST,
                $items_per_page
            );

            if (!empty($_REQUEST['order_id'])) {
                $items_amount = db_get_row(
                    "SELECT SUM(?:order_details.amount) as amount,
                    SUM(?:shipment_items.amount) as shipped_amount
                    FROM ?:order_details
                    LEFT JOIN ?:shipment_items
                    ON ?:shipment_items.item_id = ?:order_details.item_id
                    WHERE ?:order_details.order_id = ?i
                    GROUP BY ?:order_details.order_id",
                    $_REQUEST['order_id']
                );
                $not_shipped = $items_amount['amount'] - $items_amount['shipped_amount'];
                $need_shipment = ($not_shipped > 0) ? 'Y' : 'N';
                $response->setMeta(
                    $need_shipment,
                    'need_shipment'
                );

            }

            $response->setResponseList(Api::getAsList($object, $shipments));
            $is_paginate = true;

        } else {
            if (!empty($search) && !empty($search['sort_by'])) {
                $sortings = array (
                    'sort_by' => $search['sort_by'],
                    'sort_order' =>
                    (!empty($search['sort_order']) && $search['sort_order'] == 'desc') ? 'desc' : 'asc'
                );
            }

            $response->setResponseList(
                Api::getApiSchemaData(
                    $object,
                    $condition,
                    array(),
                    $options,
                    $sortings
                )
            );
        }
        if (empty($pagination_params)) {
            $pagination_params = array(
                'items_per_page' => !empty($items_per_page)? $items_per_page : TWG_RESPONSE_ITEMS_LIMIT,
                'page' => !empty($_REQUEST['page'])? $_REQUEST['page'] : 1,
                'total_items' => !empty($total_items)? $total_items : 0
            );
        }

        if ($is_paginate) {
            fn_twg_set_response_pagination($response, $pagination_params);
        }

        $response->returnResponse($object);
    }

    if ($_REQUEST['action'] == 'details') {

         if ($object == 'shipment_data') {
            $shippings = db_get_array(
                "SELECT a.shipping_id,
                 b.shipping
                 FROM ?:shippings as a
                 LEFT JOIN ?:shipping_descriptions as b
                 ON a.shipping_id = b.shipping_id AND b.lang_code = ?s
                 WHERE a.status = ?s
                 ORDER BY a.position",
                $lang_code,
                'A'
            );

            $carriers_data = fn_twg_get_carriers();

            $carriers = array();
            foreach ($carriers_data as $k => $v) {
                $carriers[] = array (
                    'carrier_id' => $k,
                    'carrier' => $v
                );
            }

            $result = array (
                'shippings' => Api::getAsList('shippings', $shippings),
                'carriers' => Api::getAsList('carriers', $carriers)
            );

            $response->setData($result);
            $response->returnResponse($object);
        }

        if (empty($_REQUEST['id'])) {
            $response->addError(
                'ERROR_WRONG_OBJECT_DATA',
                str_replace(
                    '[object]',
                    $object,
                    __(
                        'twgadmin_wrong_api_object_data'
                    )
                )
            );
            $response->returnResponse();
        }

        if ($object == 'orders') {
            $order = TwigmoOrder::getOrderInfo($_REQUEST['id']);
            if (empty($order)) {
                $response->addError('ERROR_OBJECT_WAS_NOT_FOUND', str_replace('[object]', $object, __('twgadmin_object_was_not_found')));
                $response->returnResponse();
            }

            $response->setData($order);
            $response->returnResponse('order');

        } elseif ($object == 'products') {

            $product = fn_twg_get_api_product_data($_REQUEST['id'], $lang_code);

            if (empty($product)) {
                $response->addError(
                    'ERROR_OBJECT_WAS_NOT_FOUND',
                    str_replace(
                        '[object]',
                        $object,
                        __(
                            'twgadmin_object_was_not_found'
                        )
                    )
                );
                $response->returnResponse();
            }

            $response->setData($product);
            $response->returnResponse('product');

        } elseif ($object == 'categories') {

            $category = fn_twg_get_api_category_data($_REQUEST['id'], $lang_code);

            if (empty($category)) {
                $response->addError(
                    'ERROR_OBJECT_WAS_NOT_FOUND',
                    str_replace(
                        '[object]',
                        $object,
                        __(
                            'twgadmin_object_was_not_found'
                        )
                    )
                );
                $response->returnResponse();
            }

            $response->setData($category);
            $response->returnResponse('category');

        }  elseif ($object == 'users') {
            $user_data = fn_twg_get_user_info(array('user_id' => $_REQUEST['id'], 'mode' => $mode));
            $response->setData($user_data);
            $response->returnResponse();
        } else {
            // get object data by scheme where id is a primary
            // key in database and scheme
            fn_twg_api_get_object($response, $object, $_REQUEST);
        }
    }
    if ($_REQUEST['action'] == 'edit_css') {
        $_SESSION['current_path'] = '/' . TwigmoSettings::get('base_theme') . '/templates/addons/twigmo/';
        fn_redirect(Registry::get('config.admin_index') . '?dispatch=template_editor.manage', true);
    }
}

function fn_check_shipment_data($data)
{
    $shipments = array();

    foreach ($data as $k => $v) {

        if (!empty($v['shipment_id'])) {

            $shipment_info =  db_get_row(
                "SELECT ?:shipments.*, ?:shipment_items.order_id
                 FROM ?:shipments
                 LEFT JOIN ?:shipment_items
                 ON ?:shipments.shipment_id = ?:shipment_items.shipment_id
                 WHERE ?:shipments.shipment_id = ?i",
                $v['shipment_id']
            );

            if (!$shipment_info) {
                return false;
            }
            $shipment_items =  db_get_hash_single_array(
                "SELECT item_id, amount
                 FROM ?:shipment_items
                 WHERE shipment_id = ?i",
                array('item_id', 'amount'),
                $v['shipment_id']
            );

            $v = array_merge($shipment_info, $v);

        } elseif (empty($v['is_complete_data'])) {
             return false;
        }

        $order_info = fn_get_order_info($v['order_id'], false, true, true);

        if (!empty($v['shipment_id']) && !empty($shipment_items)) {
            foreach ($shipment_items as $item_id => $amount) {
                if (!isset($order_info['items'][$item_id])) {
                    return false;
                }
                $order_info['items'][$item_id]['shipped_amount'] -= $amount;
            }
        }

        if (empty($order_info)) {
            return false;
        }

        if (empty($v['shipping_id'])) {
            $v['shipping_id'] = $order_info['shipping_ids'];
        }

        if (empty($v['timestamp'])) {
            $v['timestamp'] = TIME;
        }

        $items = array();

        foreach ($v['products'] as $product) {
            if (!$product['is_complete_data']) {
                return false;
            }

            $item_id = $product['item_id'];

            if (!isset($order_info['items'][$item_id])) {
                return false;
            }

            $amount = intval($product['amount']);

            if ($amount > ($order_info['items'][$item_id]['amount']
                             - $order_info['items'][$item_id]['shipped_amount'])
                ) {
                return false;
            }

            $items[] = array (
                'item_id' => $item_id,
                'order_id' => $v['order_id'],
                'product_id' => $order_info['items'][$item_id]['product_id'],
                'amount' => $amount
            );
        }

        unset($v['products']);
        $v['products'] = $items;
        $shipments[] = $v;
    }

    return $shipments;
}

function fn_twg_get_logs($params = array())
{
    $items_per_page = TWG_RESPONSE_ITEMS_LIMIT;

    $page = empty($params['page']) ? 1 : $params['page'];

    $condition = db_quote(" WHERE type IN('users',  'products',  'orders') AND action != 'session'");
    $limit = '';
    if (!empty($items_per_page)) {
        $total = db_get_field("SELECT COUNT(DISTINCT(?:logs.log_id)) FROM ?:logs ?p", $condition);
        $limit = db_paginate($page, $items_per_page);
    }

    $data = db_get_array("SELECT * FROM ?:logs ?p ORDER BY log_id desc $limit", $condition);

    foreach ($data as $k => $v) {
        $data[$k]['backtrace'] = !empty($v['backtrace']) ? unserialize($v['backtrace']) : array();
        $data[$k]['content'] = !empty($v['content']) ? unserialize($v['content']) : array();
    }

    $params = array(
        'page' => $page,
        'items_per_page' => $items_per_page,
        'total_items' => $total,
        'total_pages' => ceil((int)$total / $items_per_page)
    );

    return array($data, $params);
}

function fn_twg_get_summary_stats()
{
    $periods = array(
        'day' => array(
            'periods' => array(),
            'totals' => array('current' => 0, 'previous' => 0),
            'percentage' => 0,
            'intervals' => 6,
        ),
        'week' => array(
            'periods' => array(),
            'totals' => array('current' => 0, 'previous' => 0),
            'percentage' => 0,
            'intervals' => 7,
        ),
        'month' => array(
            'periods' => array(),
            'totals' => array('current' => 0, 'previous' => 0),
            'percentage' => 0,
            'intervals' => 5,
        ),
    );

    $shifts = array(
        'day' => 14400,   // 4 hours
        'week' => 86400,  // 1 day
        'month' => 604800 // 1 week
    );

    $boundaries = array(
        'day' => array(
            'current' => strtotime('midnight', TIME),
            'previous' => strtotime('yesterday midnight', TIME)
        ),
        'week' => array(
            'current' => strtotime('this week midnight', TIME),
            'previous' => strtotime('previous week midnight', TIME)
        ),
        'month' => array(
            'current' => strtotime('first day of this month midnight', TIME),
            'previous' => strtotime('first day of previous month midnight', TIME)
        )
    );

    $query = "SELECT SUM(IF(status IN('C', 'P'), total, 0)) as total_paid, "
        . "SUM(total) as total, COUNT(order_id) as order_amount "
        . "FROM ?:orders WHERE timestamp >= ?i AND timestamp <= ?i";
    if (isset($_REQUEST['s_company']) && $_REQUEST['s_company'] != 'all') {
        $query .= db_quote(" AND company_id = ?i", $_REQUEST['s_company']);
    }
    foreach ($periods as $period_name => &$period_data) {
        for ($i = 0; $i < $period_data['intervals']; $i++) {
            $current = db_get_row($query, $boundaries[$period_name]['current'], $boundaries[$period_name]['current'] + $shifts[$period_name]);
            $previous = db_get_row($query, $boundaries[$period_name]['previous'], $boundaries[$period_name]['previous'] + $shifts[$period_name]);

            $boundaries[$period_name]['current'] += $shifts[$period_name];
            $boundaries[$period_name]['previous'] += $shifts[$period_name];

            $period_data['periods'][$i] = array(
                'current' => $current,
                'previous' => $previous
            );
            $period_data['totals']['current'] += $current['total_paid'];
            $period_data['totals']['previous'] += $previous['total_paid'];
        }

        $totals = $period_data['totals'];
        if ($totals['current'] == 0 && $totals['previous'] == 0) {
            continue;
        }

        $negative = $totals['previous'] > $totals['current'];
        if ($negative) {
            $max = $totals['previous'];
            $min = $totals['current'];
        } else {
            $min = $totals['previous'];
            $max = $totals['current'];
        }

        if ($min == 0) {
            $period_data['percentage'] = $negative ? '-100' : '100';
        } else {
            $percentage = 100 - ($max * 100 / $min);
            if (($negative && $percentage > 0) || (!$negative && $percentage < 0)) {
                $percentage = -$percentage;
            }
            $period_data['percentage'] = "$percentage";
        }

    }

    return $periods;
}

function fn_twg_get_product_stats()
{
    $product_stats = array();
    $company_condition = fn_get_company_condition('?:products.company_id');
    $product_stats['total'] = db_get_field("SELECT COUNT(*) as amount FROM ?:products WHERE 1" . $company_condition);
    $product_stats['status'] = db_get_hash_single_array(
        "SELECT status, COUNT(*) as amount FROM ?:products WHERE 1" . $company_condition . " GROUP BY status",
        array('status', 'amount')
    );

    $product_stats['configurable'] = db_get_field("SELECT COUNT(*) FROM ?:products WHERE product_type = 'C'" . $company_condition);
    $product_stats['downloadable'] = db_get_field("SELECT COUNT(*) FROM ?:products WHERE is_edp = 'Y'" . $company_condition);
    $product_stats['free_shipping'] = db_get_field("SELECT COUNT(*) FROM ?:products WHERE free_shipping = 'Y'" . $company_condition);
    return $product_stats;
}

function fn_twg_get_user_stats()
{
    $users_stats = array(
        'total' => db_get_hash_single_array("SELECT user_type, COUNT(*) as total FROM ?:users GROUP BY user_type", array('user_type', 'total')),
        'total_all' => db_get_field("SELECT COUNT(*) FROM ?:users"),
        'not_approved' => db_get_field("SELECT COUNT(*) FROM ?:users WHERE status = 'D'"),
    );
    return $users_stats;
}

function fn_twg_get_latest_orders($lang_code)
{
    list($orders) = fn_get_orders(array('sort_by' => 'date', 'sort_order' => 'desc'), 7, true);
    $orders = TwigmoOrder::getOrdersAsApiList($orders, $lang_code);
    return $orders;
}

function fn_twg_get_searches()
{
    $query = "SELECT * FROM ?:views WHERE object IN ('orders', 'products', 'users') AND user_id = ?i";
    $objects = db_get_hash_multi_array($query, array('object', 'view_id'), $_SESSION['auth']['user_id']);
    # Saved searches
    foreach ($objects as &$views) {
        foreach ($views as &$view) {
            $view['params'] = unserialize($view['params']);
        }
        $views = array_values($views);
    }

    // Add default filters
    // orders
    if (!isset($objects['orders'])) {
        $objects['orders'] = array();
    }

    array_unshift($objects['orders'], array('name' => __('all'), 'params' => array('period' => '')));

    // products
    if (!isset($objects['products'])) {
        $objects['products'] = array();
    }
    array_unshift($objects['products'], array('name' => __('all'), 'params' => array('amount_to' => '')));
    array_push($objects['products'], array('name' => __('twapp_low_stock'), 'params' => array('amount_to' => '10')));

    // users
    if (!isset($objects['users'])) {
        $objects['users'] = array();
    }
    array_unshift($objects['users'], array('name' => __('all')));
    return $objects;
}

function fn_twg_get_statuses()
{
    $status_types = array(
        'orders' =>     fn_get_statuses(STATUSES_ORDER),
        'products' =>   fn_twg_api_get_base_statuses(true),
        'categories' => fn_twg_api_get_base_statuses(true),
        'users' =>      fn_twg_api_get_base_statuses(false)
    );

    foreach ($status_types as &$status_type) {
        foreach ($status_type as &$status) {
            if (isset($status['color'])) {
                $color = $status['color'];
            } elseif (isset($status['params']['color'])) {
                $color = str_replace('#', '', $status['params']['color']);
            } else {
                $color = '666666';
            }
            $status = array(
                'label' => $status['description'],
                'value' => $status['status'],
                'color' => $color
            );
        }
    }

    return $status_types;
}

function fn_twg_get_admin_settings()
{
    $settings = array();

    $needed_langvars = array('in_stock', 'uc_ok', 'sign_in', 'included', 'twg_msg_field_required', 'twgadmin_access_id', 'select_country', 'select_state', 'twg_lbl_copy_from_billing', 'twg_billing_is_the_same', 'no_users_found', 'text_no_products_found', 'text_no_orders', 'users_statistics', 'product_inventory', 'latest_orders', 'view_all_orders', 'this_week', 'this_month', 'previous_week', 'previous_month', 'twg_msg_fill_required_fields', 'twg_msg_field_required', 'update', 'create', 'change', 'user', 'order', 'product', 'log_action_failed_login', 'manage_accounts', 'twg_lbl_out_of_stock', 'user_account_info', 'email', 'username', 'password', 'confirm_password', 'tracking_num', 'tracking_number', 'profile', 'date', 'order_id', 'tax_exempt', 'payment_surcharge', 'payment_method', 'free_shipping', 'shipping_cost', 'including_discount', 'order_discount', 'taxes', 'order_details', 'customer_info', 'customer_notes', 'staff_only_notes', 'order', 'name', 'product_code', 'account', 'email', 'login', 'email_invalid', 'digits_required', 'error_passwords_dont_match', 'yes', 'contact_information', 'billing_address', 'shipping_address', 'timeline', 'fax', 'zipcode', 'company', 'customers', 'billing', 'shipping', 'total', 'subtotal', 'discount', 'disabled', 'month', 'save', 'edit', 'create', 'update', 'products', 'store', 'cancel', 'password', 'delete', 'account', 'ok', 'back', 'stats', 'dashboard', 'add', 'all', 'new', 'currencySymbol', 'active', 'search', 'by', 'orders', 'week', 'day', 'address', 'information', 'firstName', 'lastName', 'email', 'phone', 'today', 'yesterday', 'configurable', 'downloadable', 'loading', 'title', 'code', 'category', 'quantity', 'price', 'status', 'city', 'state', 'zip', 'country');

    $settings['lang'] = array();
    foreach ($needed_langvars as $needed_langvar) {
        $settings['lang'][$needed_langvar] = __($needed_langvar);
    }
    $settings['lang'] = array_merge($settings['lang'], Lang::getLangVarsByPrefix('twapp'));
    $settings['lang'] = fn_twg_process_langvars($settings['lang']);

    $settings['statuses'] = fn_twg_get_statuses();

    $settings['profileFields'] = fn_twg_prepare_profile_fields(fn_get_profile_fields('O'), false);

    list($settings['countries']) = fn_get_countries(array('only_avail' => true));
    $settings['states'] = fn_twg_get_states();
    $settings['titles'] = array();
    $settings['saved_searches'] = fn_twg_get_searches();
    $settings = array_merge($settings, fn_twg_get_checkout_settings());
    $settings['use_email_as_login'] = Registry::get('settings.General.use_email_as_login');
    $settings['currency'] = Registry::get('currencies.' . CART_PRIMARY_CURRENCY);
    $settings['use_email_as_login'] = Registry::get('settings.General.use_email_as_login');
    $settings['time_format'] = Registry::get('settings.Appearance.time_format');
    $settings['date_format'] = Registry::get('settings.Appearance.date_format');
    $settings['languages'] = fn_twg_get_languages();
    $settings['cart_language'] = CART_LANGUAGE;
    $settings['descr_sl'] = DESCR_SL;

    return $settings;

}
