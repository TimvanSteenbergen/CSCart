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
use Tygh\Settings;
use Tygh\BlockManager\Layout;
use Tygh\Themes\Styles;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $suffix = '';

    // Define trusted variables that shouldn't be stripped
    fn_trusted_vars(
        'company_data'
    );

    //
    // Processing additon of new company
    //
    if ($mode == 'add') {

        $suffix = '.add';

        if (!empty($_REQUEST['company_data']['company'])) {  // Checking for required fields for new company

            if (Registry::get('runtime.simple_ultimate')) {
                Registry::set('runtime.simple_ultimate', false);
            }

            if (isset($_REQUEST['company_data']['is_create_vendor_admin']) && $_REQUEST['company_data']['is_create_vendor_admin'] == 'Y') {
                if (!empty($_REQUEST['company_data']['admin_username']) && db_get_field("SELECT COUNT(*) FROM ?:users WHERE user_login = ?s", $_REQUEST['company_data']['admin_username']) > 0) {
                    fn_set_notification('E', __('error'), __('error_admin_not_created_name_already_used'));
                    fn_save_post_data('company_data', 'update'); // company data and settings
                    $suffix = '.add';
                } else {
                    // Adding company record
                    $company_id = fn_update_company($_REQUEST['company_data']);

                    if (!empty($company_id)) {
                        $suffix = ".update?company_id=$company_id";
                        if (isset($_REQUEST['company_data']['is_create_vendor_admin']) && $_REQUEST['company_data']['is_create_vendor_admin'] == 'Y') {

                            if (db_get_field("SELECT COUNT(*) FROM ?:users WHERE email = ?s", $_REQUEST['company_data']['email']) > 0) {
                                fn_set_notification('E', __('error'), __('error_admin_not_created_email_already_used'));
                            } else {

                                // Add company's administrator
                                if (fn_is_restricted_admin($_REQUEST) == true) {
                                    return array(CONTROLLER_STATUS_DENIED);
                                }

                                $user_data['fields'] = isset($_REQUEST['user_data']['fields']) ? $_REQUEST['user_data']['fields'] : '';

                                if (!empty($_REQUEST['company_data']['admin_username'])) {
                                    $user_data['user_login'] = $_REQUEST['company_data']['admin_username'];
                                } else {
                                    $user_data['user_login'] = $_REQUEST['company_data']['email'];
                                }

                                $user_data['user_type'] = 'V';
                                $user_data['password1'] = fn_generate_password();
                                $user_data['password2'] = $user_data['password1'];
                                $user_data['status'] = $_REQUEST['company_data']['status'];
                                $user_data['company_id'] = $company_id;
                                $user_data['email'] = $_REQUEST['company_data']['email'];
                                $user_data['company'] = $_REQUEST['company_data']['company'];
                                $user_data['last_login'] = 0;
                                $user_data['lang_code'] = $_REQUEST['company_data']['lang_code'];
                                $user_data['password_change_timestamp'] = 0;
                                $user_data['is_root'] = 'N';

                                // Copy vendor admin billing and shipping addresses from the company's credentials
                                $user_data['firstname'] = $user_data['b_firstname'] = $user_data['s_firstname'] = (!empty($_REQUEST['company_data']['admin_firstname'])) ? $_REQUEST['company_data']['admin_firstname'] : '';
                                $user_data['lastname'] = $user_data['b_lastname'] = $user_data['s_lastname'] = (!empty($_REQUEST['company_data']['admin_lastname'])) ? $_REQUEST['company_data']['admin_lastname'] : '';

                                $user_data['b_address'] = $user_data['s_address'] = $_REQUEST['company_data']['address'];
                                $user_data['b_city'] = $user_data['s_city'] = $_REQUEST['company_data']['city'];
                                $user_data['b_country'] = $user_data['s_country'] = $_REQUEST['company_data']['country'];
                                $user_data['b_state'] = $user_data['s_state'] = $_REQUEST['company_data']['state'];
                                $user_data['b_zipcode'] = $user_data['s_zipcode'] = $_REQUEST['company_data']['zipcode'];

                                // Create new user, avoiding switching to the vendor admin's session ($null as the 3rd argument)
                                list($added_user_id, $null) = fn_update_user(0, $user_data, $null, false, true, true);
                                if ($added_user_id) {
                                    $msg = __('new_administrator_account_created') . '<a href="' . fn_url('profiles.update?user_id=' . $added_user_id) . '">' . __('you_can_edit_account_details') . '</a>';
                                    fn_set_notification('N', __('notice'), $msg, 'K');
                                }
                            }
                        }
                    } else {
                        fn_save_post_data('company_data', 'update');
                    }
                }
            } else {
                $company_id = fn_update_company($_REQUEST['company_data']);
            }

            if (!empty($company_id)) {
                $suffix = ".update?company_id=$company_id";

                $redirect_url = empty($_REQUEST['redirect_url']) ? 'companies' . $suffix : $_REQUEST['redirect_url'];

                if (defined('AJAX_REQUEST')) {
                    Registry::get('ajax')->assign('non_ajax_notifications', true);
                    Registry::get('ajax')->assign('force_redirection', fn_url($redirect_url));

                    exit();
                }
            } else {
                fn_save_post_data('company_data', 'update');
            }
        }

        if (fn_allowed_for('ULTIMATE') && !empty($company_id)) {
            fn_ult_set_company_settings_information($_REQUEST['update'], $company_id);
        }
    }

    //
    // Processing updating of company element
    //
    if ($mode == 'update') {

        if (!empty($_REQUEST['company_data']['company'])) {
            if (!empty($_REQUEST['company_id']) && Registry::get('runtime.company_id') && Registry::get('runtime.company_id') != $_REQUEST['company_id']) {
                fn_company_access_denied_notification();
                fn_save_post_data('company_data', 'update');
            } else {
                // Updating company record
                fn_update_company($_REQUEST['company_data'], $_REQUEST['company_id'], DESCR_SL);
            }

            if (fn_allowed_for('ULTIMATE') && !empty($_REQUEST['company_id'])) {
                fn_ult_set_company_settings_information($_REQUEST['update'], $_REQUEST['company_id']);

                fn_clear_cache('registry'); // clean up block cache to re-generate storefront urls
            }
        }

        $suffix = ".update?company_id=$_REQUEST[company_id]";
    }

    if ($mode == 'm_delete') {

        if (!empty($_REQUEST['company_ids'])) {
            foreach ($_REQUEST['company_ids'] as $v) {
                fn_delete_company($v);
            }
        }

        return array(CONTROLLER_STATUS_OK, "companies.manage");
    }

    if (fn_allowed_for('MULTIVENDOR')) {
        if ($mode == 'merge') {
            if (!isset($_SESSION['auth']['is_root']) || $_SESSION['auth']['is_root'] != 'Y' || Registry::get('runtime.company_id')) {
                return array(CONTROLLER_STATUS_DENIED);
            }

            if (isset($_REQUEST['from_company_id']) && isset($_REQUEST['to_company_id']) && fn_chown_company($_REQUEST['from_company_id'], $_REQUEST['to_company_id'])) {
                fn_delete_company($_REQUEST['from_company_id']);
            }

            return array(CONTROLLER_STATUS_REDIRECT, "companies.manage");
        }

        if ($mode == 'm_delete_payouts' && !Registry::get('runtime.company_id')) {
            if (!empty($_REQUEST['payout_ids'])) {
                fn_companies_delete_payout($_REQUEST['payout_ids']);
            }

            $suffix = '.balance';
        }

        if ($mode == 'payouts_add' && !Registry::get('runtime.company_id')) {
            if (!empty($_REQUEST['payment']['amount'])) {
                fn_companies_add_payout($_REQUEST['payment']);
            }

            $suffix = '.balance';
        }

        if ($mode == 'update_payout_comments' && !Registry::get('runtime.company_id')) {
            if (!empty($_REQUEST['payout_comments'])) {
                foreach ($_REQUEST['payout_comments'] as $payout_id => $comment) {
                    db_query('UPDATE ?:vendor_payouts SET comments = ?s WHERE payout_id = ?i', $comment, $payout_id);
                }
            }
        }

        if ($mode == 'm_activate' || $mode == 'm_disable') {
            if ($mode == 'm_activate') {
                $status = 'A';
                $reason = !empty($_REQUEST['action_reason_activate']) ? $_REQUEST['action_reason_activate'] : '';
                $msg = __('text_companies_activated');
            } else {
                $status = 'D';
                $reason = !empty($_REQUEST['action_reason_disable']) ? $_REQUEST['action_reason_disable'] : '';
                $msg = __('text_companies_disabled');
            }

            $notification = !empty($_REQUEST['action_notification']) && $_REQUEST['action_notification'] == 'Y';

            $result = array();
            foreach ($_REQUEST['company_ids'] as $company_id) {
                $status_from = '';
                $res = fn_companies_change_status($company_id, $status, $reason, $status_from, false, $notification);
                if ($res) {
                    $result[] = $company_id;
                }
            }

            if ($result) {
                fn_set_notification('N', __('notice'), $msg);
            } else {
                fn_set_notification('E', __('error'), __('error_status_not_changed'), 'I');
            }

            return array(CONTROLLER_STATUS_REDIRECT, "companies.manage");
        }
    }

    return array(CONTROLLER_STATUS_OK, "companies$suffix");
}

if ($mode == 'payout_delete' && !Registry::get('runtime.company_id')) {
    fn_companies_delete_payout($_REQUEST['payout_id']);
}

if ($mode == 'manage') {

    list($companies, $search) = fn_get_companies($_REQUEST, $auth, Registry::get('settings.Appearance.admin_elements_per_page'));

    Registry::get('view')->assign('companies', $companies);
    Registry::get('view')->assign('search', $search);

    Registry::get('view')->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));
    Registry::get('view')->assign('states', fn_get_all_states());

} elseif ($mode == 'delete') {

    fn_delete_company($_REQUEST['company_id']);

    return array(CONTROLLER_STATUS_REDIRECT);

} elseif ($mode == 'update' || $mode == 'add') {

    $company_id = !empty($_REQUEST['company_id']) ? $_REQUEST['company_id'] : 0;
    $company_data = !empty($company_id) ? fn_get_company_data($company_id) : array();

    if ($mode == 'update' && empty($company_data)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    if (fn_allowed_for('MULTIVENDOR')) {
        if (!empty($company_id)) {
            $company_data['logos'] = fn_get_logos($company_id);
        }

        Registry::get('view')->assign('logo_types', fn_get_logo_types(true));
    }

    $restored_company_data = fn_restore_post_data('company_data');
    if (!empty($restored_company_data) && $mode == 'add') {
        if (!empty($restored_company_data['shippings'])) {
            $restored_company_data['shippings'] = implode(',', $restored_company_data['shippings']);
        }
        $company_data = fn_array_merge($company_data, $restored_company_data);
    }

    if (fn_allowed_for('ULTIMATE')) {

        if ($mode == 'update') {
            $available_themes = fn_get_available_themes(fn_get_theme_path('[theme]', 'C', $company_id));

            $theme_name = fn_get_theme_path('[theme]', 'C', $company_id);
            $layout = Layout::instance($company_id)->getDefault($theme_name);

            $style = Styles::factory($theme_name)->get($layout['style_id']);

            Registry::get('view')->assign('current_style', $style);
            Registry::get('view')->assign('theme_info', $available_themes['current']);
        }

        $countries_list = fn_get_simple_countries();

        if (!empty($company_data['countries_list'])) {
            if (!is_array($company_data['countries_list'])) {
                $company_countries = explode(',', $company_data['countries_list']);
            } else {
                $company_countries = $company_data['countries_list'];
            }
            $_countries = array();

            foreach ($company_countries as $code) {
                if (isset($countries_list[$code])) {
                    $_countries[$code] = $countries_list[$code];
                    unset($countries_list[$code]);
                }
            }

            $company_data['countries_list'] = $_countries;
            unset($_countries, $company_countries);
        }

        Registry::get('view')->assign('countries_list', $countries_list);

        if ($mode == 'add') {
            $schema = fn_init_clone_schemas();
            Registry::get('view')->assign('clone_schema', $schema);
        }

        // Get "Company" settings from the DB
        $settings_values = fn_restore_post_data('update');
        $section = Settings::instance()->getSectionByName('Company');
        $settings_data = Settings::instance()->getList($section['section_id'], 0, false, $company_id, CART_LANGUAGE);
        foreach ($settings_data['main'] as $field_id => &$field_data) {
            unset($field_data['update_for_all']);
            if (!empty($settings_values) && !empty($settings_values[$field_id])) {
                $field_data['value'] = $settings_values[$field_id];
            } elseif ($mode == 'add') {
                unset($field_data['value']);
            }
        }

        Registry::get('view')->assign('company_settings', $settings_data['main']);
        unset($settings_data, $section);
    }

    Registry::get('view')->assign('company_data', $company_data);
    Registry::get('view')->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));
    Registry::get('view')->assign('states', fn_get_all_states());

    $profile_fields = fn_get_profile_fields('A', array(), CART_LANGUAGE, array('get_custom' => true, 'get_profile_required' => true));
    Registry::get('view')->assign('profile_fields', $profile_fields);

    $tabs['detailed'] = array (
        'title' => __('general'),
        'js' => true
    );
    $tabs['addons'] = array (
        'title' => __('addons'),
        'js' => true
    );

    if (fn_allowed_for('MULTIVENDOR')) {
        $tabs['description'] = array (
            'title' => __('description'),
            'js' => true
        );
        $tabs['logos'] = array (
            'title' => __('logos'),
            'js' => true
        );
        $tabs['categories'] = array (
            'title' => __('categories'),
            'js' => true
        );

    } elseif (fn_allowed_for('ULTIMATE')) {
        $tabs['regions'] = array (
            'title' => __('regions'),
            'js' => true
        );
    }

    if (!Registry::get('runtime.company_id')) {
        $shippings = db_get_hash_array("SELECT a.shipping_id, a.status, b.shipping FROM ?:shippings as a LEFT JOIN ?:shipping_descriptions as b ON a.shipping_id = b.shipping_id AND b.lang_code = ?s WHERE a.company_id = 0 AND a.status = 'A' ORDER BY a.position", 'shipping_id', DESCR_SL);
        Registry::get('view')->assign('shippings', $shippings);

        if (!fn_allowed_for('ULTIMATE')) {
            $tabs['shipping_methods'] = array (
                'title' => __('shipping_methods'),
                'js' => true
            );
        }
    }

    Registry::set('navigation.tabs', $tabs);

} elseif ($mode == 'update_status') {

    $notification = !empty($_REQUEST['notify_user']) && $_REQUEST['notify_user'] == 'Y';

    if (fn_companies_change_status($_REQUEST['id'], $_REQUEST['status'], '', $status_from, false, $notification)) {
        fn_set_notification('N', __('notice'), __('status_changed'));
    } else {
        fn_set_notification('E', __('error'), __('error_status_not_changed'));
        Registry::get('ajax')->assign('return_status', $status_from);
    }

    exit;

} elseif ($mode == 'picker') {
    list($companies, $search) = fn_get_companies($_REQUEST, $auth, Registry::get('settings.Appearance.admin_elements_per_page'));

    Registry::get('view')->assign('companies', $companies);
    Registry::get('view')->assign('search', $search);

    Registry::get('view')->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));
    Registry::get('view')->assign('states', fn_get_all_states());

    Registry::get('view')->display('pickers/companies/picker_contents.tpl');
    exit;
}

if (fn_allowed_for('MULTIVENDOR')) {
    if ($mode == 'merge') {

        if (!isset($_SESSION['auth']['is_root']) || $_SESSION['auth']['is_root'] != 'Y' || Registry::get('runtime.company_id')) {
            return array(CONTROLLER_STATUS_DENIED);
        }

        if (empty($_REQUEST['company_id'])) {
            return array(CONTROLLER_STATUS_NO_PAGE);
        }

        $company_id = $_REQUEST['company_id'];
        unset ($_REQUEST['company_id']);
        $company_data = !empty($company_id) ? fn_get_company_data($company_id) : array();

        if (empty($company_data)) {
            return array(CONTROLLER_STATUS_NO_PAGE);
        }

        $_REQUEST['exclude_company_id'] = $company_id;

        list($companies, $search) = fn_get_companies($_REQUEST, $auth, Registry::get('settings.Appearance.admin_elements_per_page'));

        Registry::get('view')->assign('company_id', $company_id);
        Registry::get('view')->assign('company_name', $company_data['company']);
        Registry::get('view')->assign('companies', $companies);
        Registry::get('view')->assign('search', $search);
        Registry::get('view')->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));
        Registry::get('view')->assign('states', fn_get_all_states());

    } elseif ($mode == 'balance') {

        list($payouts, $search, $total) = fn_companies_get_payouts($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'));

        Registry::get('view')->assign('payouts', $payouts);
        Registry::get('view')->assign('search', $search);
        Registry::get('view')->assign('total', $total);
    }
}

if (fn_allowed_for('ULTIMATE')) {
    if ($mode == 'get_object_share') {
        $sharing_schema = fn_get_schema('sharing', 'schema');
        $view = Registry::get('view');

        if (!empty($_REQUEST['object_id']) && !empty($_REQUEST['object'])) {
            $schema = $sharing_schema[$_REQUEST['object']];

            $view->assign('selected_companies', fn_ult_get_object_shared_companies($_REQUEST['object'], $_REQUEST['object_id']));
            $owner = db_get_row('SELECT * FROM ?:' . $schema['table']['name'] . ' WHERE ' . $schema['table']['key_field'] . ' = ?s', $_REQUEST['object_id']);
            $owner_id = isset($owner['company_id']) ? $owner['company_id'] : '';

            $view->assign('result_ids', $_REQUEST['result_ids']);
            $view->assign('object_id', $_REQUEST['object_id']);
            $view->assign('owner_id', $owner_id);
            $view->assign('object', $_REQUEST['object']);
            $view->assign('schema', $schema);

            if (!empty($schema['no_item_text'])) {
                $view->assign('no_item_text', __($schema['no_item_text']));
            }

            $view->display('views/companies/components/share_object.tpl');
        }

        exit;
    }
}
