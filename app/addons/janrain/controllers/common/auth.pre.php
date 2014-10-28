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

use Tygh\Http;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'login') {
        $redirect_url = '';

        if (!empty($_REQUEST['token'])) {
            $auth = &$auth;
            $_request = array();
            $_request['apiKey'] = Registry::get('addons.janrain.apikey');
            $_request['token'] = $_REQUEST['token'];

            $_result = Http::post('https://rpxnow.com/api/v2/auth_info', $_request);

            $data = json_decode($_result, true);

            if (isset($data['stat']) && $data['stat'] == 'ok') {
                $user_data = array();
                $condition = db_quote(" AND janrain_identifier = ?s", md5($data['profile']['identifier']));

                if (fn_allowed_for('ULTIMATE')) {
                    if (Registry::get('settings.Stores.share_users') == 'N' && AREA != 'A') {
                        $condition .= fn_get_company_condition('?:users.company_id');
                    }
                }

                $user_data = db_get_row("SELECT user_id, password FROM ?:users WHERE 1 $condition");

                if (empty($user_data['user_id'])) {
                    Registry::get('settings.General.address_position') == 'billing_first' ? $address_zone = 'b' : $address_zone = 's';
                    $user_data = array();
                    $user_data['janrain_identifier'] = md5($data['profile']['identifier']);
                    $user_data['email'] = (!empty($data['profile']['verifiedEmail'])) ? $data['profile']['verifiedEmail'] : ((!empty($data['profile']['email'])) ? $data['profile']['email'] : $data['profile']['displayName'] . '@' . $data['profile']['preferredUsername'] . '.com');
                    $user_data['user_login'] = (!empty($data['profile']['verifiedEmail'])) ? $data['profile']['verifiedEmail'] : ((!empty($data['profile']['email'])) ? $data['profile']['email'] : $data['profile']['displayName'] . '@' . $data['profile']['preferredUsername'] . '.com');
                    $user_data['user_type'] = 'C';
                    $user_data['is_root'] = 'N';
                    $user_data['password1'] = $user_data['password2'] = '';
                    $user_data['title'] = (!empty($data['profile']['honorificPrefix']) ? $data['profile']['honorificPrefix'] : 'mr');
                    $user_data[$address_zone . '_firstname'] = (!empty($data['profile']['name']['givenName'])) ? $data['profile']['name']['givenName'] : $data['profile']['displayName'];
                    $user_data[$address_zone . '_lastname'] = (!empty($data['profile']['name']['familyName'])) ? $data['profile']['name']['familyName'] : '';
                    list($user_data['user_id'], $profile_id) = fn_update_user('', $user_data, $auth, true, true, false);
                }
                $user_status = (empty($user_data['user_id'])) ? LOGIN_STATUS_USER_NOT_FOUND : fn_login_user($user_data['user_id']);

                if ($user_status == LOGIN_STATUS_OK) {
                    if (empty($user_data['password'])) {
                        fn_set_notification('W', __('warning'), __('janrain_need_update_profile'));
                        $redirect_url = 'profiles.update';
                    } else {
                        $redirect_url = (!empty($_REQUEST['return_url'])) ? $_REQUEST['return_url'] : fn_url();
                    }
                } elseif ($user_status == LOGIN_STATUS_USER_DISABLED) {
                    fn_set_notification('E', __('error'), __('error_account_disabled'));
                    $redirect_url = (!empty($_REQUEST['return_url'])) ? $_REQUEST['return_url'] : fn_url();
                } elseif ($user_status == LOGIN_STATUS_USER_NOT_FOUND) {
                    fn_delete_notification('user_exist');
                    fn_set_notification('W', __('warning'), __('janrain_cant_create_profile'));
                    $redirect_url = (!empty($_REQUEST['return_url'])) ? $_REQUEST['return_url'] : fn_url();
                }
            }
            unset($_REQUEST['token']);
        } elseif (empty($_REQUEST['user_login']) || empty($_REQUEST['password'])) {
            $redirect_url = (!empty($_REQUEST['return_url'])) ? $_REQUEST['return_url'] : fn_url();
        }

        if (!empty($redirect_url)) {
            return array(CONTROLLER_STATUS_REDIRECT, !empty($redirect_url) ? $redirect_url : fn_url());
        }
    }
}
