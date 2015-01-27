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
use Tygh\Mailer;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'apply_for_vendor') {

        if (Registry::get('settings.Vendors.apply_for_vendor') != 'Y') {
            return array(CONTROLLER_STATUS_NO_PAGE);
        }

        if (fn_image_verification('use_for_apply_for_vendor_account', $_REQUEST) == false) {
            fn_save_post_data('user_data', 'company_data');

            return array(CONTROLLER_STATUS_REDIRECT, "companies.apply_for_vendor");
        }

        $data = $_REQUEST['company_data'];

        $data['timestamp'] = TIME;
        $data['status'] = 'N';
        $data['request_user_id'] = !empty($auth['user_id']) ? $auth['user_id'] : 0;

        $account_data = array();
        $account_data['fields'] = isset($_REQUEST['user_data']['fields']) ? $_REQUEST['user_data']['fields'] : '';
        $account_data['admin_firstname'] = isset($_REQUEST['company_data']['admin_firstname']) ? $_REQUEST['company_data']['admin_firstname'] : '';
        $account_data['admin_lastname'] = isset($_REQUEST['company_data']['admin_lastname']) ? $_REQUEST['company_data']['admin_lastname'] : '';
        $data['request_account_data'] = serialize($account_data);

        if (empty($data['request_user_id'])) {
            $login_condition = empty($data['request_account_name']) ? '' : db_quote(" OR user_login = ?s", $data['request_account_name']);
            $user_account_exists = db_get_field("SELECT user_id FROM ?:users WHERE email = ?s ?p", $data['email'], $login_condition);

            if ($user_account_exists) {
                fn_save_post_data('user_data', 'company_data');
                fn_set_notification('E', __('error'), __('error_user_exists'));

                return array(CONTROLLER_STATUS_REDIRECT, "companies.apply_for_vendor");
            }
        }

        $result = fn_update_company($data);

        if (!$result) {
            fn_save_post_data('user_data', 'company_data');
            fn_set_notification('E', __('error'), __('text_error_adding_request'));

            return array(CONTROLLER_STATUS_REDIRECT, "companies.apply_for_vendor");
        }

        
        $msg = Registry::get('view')->fetch('views/companies/components/apply_for_vendor.tpl');
        fn_set_notification('I', __('information'), $msg);


        // Notify user department on the new vendor application
        Mailer::sendMail(array(
            'to' => 'default_company_users_department',
            'from' => 'default_company_users_department',
            'data' => array(
                'company_id' => $result,
                'company' => $data
            ),
            'tpl' => 'companies/apply_for_vendor_notification.tpl',
        ), 'A', Registry::get('settings.Appearance.backend_default_language'));

        $return_url = !empty($_SESSION['apply_for_vendor']['return_url']) ? $_SESSION['apply_for_vendor']['return_url'] : fn_url('');
        unset($_SESSION['apply_for_vendor']['return_url']);

        return array(CONTROLLER_STATUS_REDIRECT, $return_url);
    }
}

if (fn_allowed_for('ULTIMATE')) {
    if ($mode == 'entry_page') {
        $countries = array();

        $companies_countries = db_get_array('SELECT storefront, countries_list FROM ?:companies');
        foreach ($companies_countries as $data) {
            if (empty($data['countries_list'])) {
                continue;
            }
            $_countries = explode(',', $data['countries_list']);
            foreach ($_countries as $code) {
                $countries[$code] = strpos($data['storefront'], 'http://') === false ? 'http://' . $data['storefront'] : $data['storefront'];
            }
        }

        $country_descriptions = fn_get_countries_name(array_keys($countries));

        $_SESSION['entry_page'] = true;

        Registry::get('view')->assign('countries', $countries);
        Registry::get('view')->assign('country_descriptions', $country_descriptions);
        Registry::get('view')->display('views/companies/components/entry_page.tpl');

        exit;
    }
}

if ($mode == 'view') {

    $company_data = !empty($_REQUEST['company_id']) ? fn_get_company_data($_REQUEST['company_id']) : array();

    if (empty($company_data) || empty($company_data['status']) || !empty($company_data['status']) && $company_data['status'] != 'A') {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    fn_add_breadcrumb(__('all_vendors'), 'companies.catalog');
    fn_add_breadcrumb($company_data['company']);

    $company_data['total_products'] = count(db_get_fields(fn_get_products(array(
        'get_query' => true,
        'company_id' => $_REQUEST['company_id']
    ))));

    $company_data['logos'] = fn_get_logos($company_data['company_id']);

    Registry::set('navigation.tabs', array(
        'description' => array(
            'title' => __('description'),
            'js' => true
        )
    ));

    $params = array(
        'company_id' => $_REQUEST['company_id'],
    );

    $categories = fn_get_product_counts_by_category($params);

    Registry::get('view')->assign('company_categories', $categories);
    Registry::get('view')->assign('company_data', $company_data);

} elseif ($mode == 'catalog') {

    fn_add_breadcrumb(__('all_vendors'));

    $params = $_REQUEST;
    $params['status'] = 'A';
    $params['get_description'] = 'Y';

    $vendors_per_page = Registry::get('settings.Vendors.vendors_per_page');
    list($companies, $search) = fn_get_companies($params, $auth, $vendors_per_page);

    foreach ($companies as &$company) {
        $company['logos'] = fn_get_logos($company['company_id']);
    }

    Registry::get('view')->assign('companies', $companies);
    Registry::get('view')->assign('search', $search);

} elseif ($mode == 'apply_for_vendor') {

    if (Registry::get('settings.Vendors.apply_for_vendor') != 'Y') {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    $restored_company_data = fn_restore_post_data('company_data');
    if ($restored_company_data) {
        Registry::get('view')->assign('company_data', $restored_company_data);
    }

    $restored_user_data = fn_restore_post_data('user_data');
    if ($restored_user_data) {
        Registry::get('view')->assign('user_data', $restored_user_data);
    }

    $profile_fields = fn_get_profile_fields('A', array(), CART_LANGUAGE, array('get_custom' => true, 'get_profile_required' => true));

    Registry::get('view')->assign('profile_fields', $profile_fields);
    Registry::get('view')->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));
    Registry::get('view')->assign('states', fn_get_all_states());

    fn_add_breadcrumb(__('apply_for_vendor_account'));

    $_SESSION['apply_for_vendor']['return_url'] = !empty($_REQUEST['return_previous_url']) ? $_REQUEST['return_previous_url'] : fn_url('');
}
