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

/* WARNING: DO NOT MODIFY THIS FILE TO AVOID PROBLEMS WITH THE CART FUNCTIONALITY */

namespace Tygh;

use Tygh\Http;
/**
 *
 * Helpdesk connector class
 *
 */
class Helpdesk
{
    /**
     * Returns current license status
     * @param  string $license_key
     * @param  string $host_name   If host_name was specified, license will be checked
     * @return bool
     */
    public static function getLicenseInformation($license_number = '', $extra_fields = array())
    {
        if (empty($license_number)) {
            $uc_settings = Settings::instance()->getValues('Upgrade_center');
            $license_number = $uc_settings['license_number'];
        }

        if (empty($license_number)) {
            return 'LICENSE_IS_INVALID';
        }

        $store_ip = fn_get_ip();
        $store_ip = $store_ip['host'];

        $request = array(
            'license_number' => $license_number,
            'ver' => PRODUCT_VERSION,
            'product_status' => PRODUCT_STATUS,
            'product_build' => strtoupper(PRODUCT_BUILD),
            'edition' => isset($extra_fields['edition']) ? $extra_fields['edition'] : PRODUCT_EDITION,
            'lang' => strtoupper(CART_LANGUAGE),
            'store_uri' => fn_url('', 'C', 'http'),
            'secure_store_uri' => fn_url('', 'C', 'https'),
            'https_enabled' => (Registry::get('settings.Security.secure_checkout') == 'Y' || Registry::get('settings.Security.secure_admin') == 'Y' || Registry::get('settings.Security.secure_auth') == 'Y') ? 'Y' : 'N',
            'admin_uri' => fn_url('', 'A', 'http'),
            'store_ip' => $store_ip,
        );

        $request = array(
            'Request@action=check_license@api=3' => array_merge($extra_fields, $request),
        );

        $request = '<?xml version="1.0" encoding="UTF-8"?>' . fn_array_to_xml($request);

        $data = Http::get(Registry::get('config.resources.updates_server') . '/index.php?dispatch=product_updates.check_available', array('request' => $request), array(
            'timeout' => 10
        ));

        if (empty($data)) {
            $data = fn_get_contents(Registry::get('config.resources.updates_server') . '/index.php?dispatch=product_updates.check_available&request=' . urlencode($request));
        }

        return $data;
    }

    /**
     * Set/Get token auth key
     * @param  string $generate If generate value is equal to "true", new token will be generated
     * @return string token value
     */
    public static function token($generate = false)
    {
        if ($generate) {
            $token = fn_crc32(microtime());
            fn_set_storage_data('hd_request_code', $token);
        } else {
            $token = fn_get_storage_data('hd_request_code');
        }

        return $token;
    }

    /**
     * Get store auth key
     *
     * @return string store key
     */
    public static function getStoreKey()
    {
        $key = Registry::get('settings.store_key');
        $host_path = Registry::get('config.http_host') . Registry::get('config.http_path');

        if (!empty($key)) {
            list($token, $host) = explode(';', $key);
            if ($host != $host_path) {
                unset($key);
            }
        }

        if (empty($key)) {
            // Generate new value
            $key = fn_crc32(microtime());
            $key .= ';' . $host_path;
            Settings::instance()->updateValue('store_key', $key);
        }

        return $key;
    }

    public static function auth()
    {
        $_SESSION['last_status'] = 'INIT';

        self::initHelpdeskRequest();

        return true;
    }

    public static function initHelpdeskRequest($area = AREA)
    {
        if ($area != 'C') {
            $protocol = defined('HTTPS') ? 'https' : 'http';

            $_SESSION['stats'][] = '<img src="' . fn_url('helpdesk_connector.auth', 'A', $protocol) . '" alt="" style="display:none" />';
        }
    }

    public static function parseLicenseInformation($data, $auth, $process_messages = true)
    {
        $updates = $messages = $license = '';

        if (!empty($data)) {
            // Check if we can parse server response
            if (strpos($data, '<?xml') !== false) {
                $xml = simplexml_load_string($data);
                $updates = (string) $xml->Updates;
                $messages = $xml->Messages;
                $license = (string) $xml->License;
            } else {
                $license = $data;
            }
        }

        if (!empty($auth)) {
            if (Registry::get('settings.General.auto_check_updates') == 'Y' && fn_check_user_access($auth['user_id'], 'upgrade_store')) {
                // If upgrades are available
                if ($updates == 'AVAILABLE') {
                    fn_set_notification('W', __('notice'), __('text_upgrade_available', array(
                        '[product]' => PRODUCT_NAME,
                        '[link]' => fn_url('upgrade_center.manage')
                    )), 'S', 'upgrade_center');
                }
            }

            if (!empty($data)) {
                $_SESSION['last_status'] = $license;
            }
        }

        $messages = self::processMessages($messages, $process_messages);

        return array($license, $updates, $messages);
    }

    public static function processMessages($messages, $process_messages = true)
    {
        $messages_queue = array();

        if (!empty($messages)) {
            if ($process_messages) {
                $messages_queue = fn_get_storage_data('hd_messages');
            }

            if (empty($messages_queue)) {
                $messages_queue = array();
            } else {
                $messages_queue = unserialize($messages_queue);
            }

            foreach ($messages->Message as $message) {
                $message_id = empty($message->Id) ? intval(fn_crc32(microtime()) / 2) : (string) $message->Id;
                $message = array(
                    'type' => empty($message->Type) ? 'W' : (string) $message->Type,
                    'title' => (empty($message->Title)) ? __('notice') : (string) $message->Title,
                    'text' => (string) $message->Text,
                );

                $messages_queue[$message_id] = $message;
            }

            if ($process_messages) {
                fn_set_storage_data('hd_messages', serialize($messages_queue));
            }
        }

        return $messages_queue;
    }

    public static function registerLicense($license_data)
    {
        $request = array(
            'Request@action=registerLicense@api=2' => array(
                'product_type' => PRODUCT_EDITION,
                'domain' => Registry::get('config.http_host'),
                'first_name' => $license_data['first_name'],
                'last_name' => $license_data['last_name'],
                'email' => $license_data['email'],
            ),
        );

        $request = '<?xml version="1.0" encoding="UTF-8"?>' . fn_array_to_xml($request);

        $data = Http::get(Registry::get('config.resources.updates_server') . '/index.php?dispatch=licenses_remote.add', array('request' => $request), array(
            'timeout' => 10
        ));

        if (empty($data)) {
            $data = fn_get_contents(Registry::get('config.resources.updates_server') . '/index.php?dispatch=licenses_remote.create&request=' . urlencode($request));
        }

        $result = $messages = $license = '';

        if (!empty($data)) {
            // Check if we can parse server response
            if (strpos($data, '<?xml') !== false) {
                $xml = simplexml_load_string($data);
                $result = (string) $xml->Result;
                $messages = $xml->Messages;
                $license = (array) $xml->License;
            }
        }

        self::processMessages($messages, true);

        return array($result, $license, $messages);
    }

    public static function checkStoreImportAvailability($license_number, $version, $edition = PRODUCT_EDITION)
    {
        $request = array(
            'dispatch' => 'product_updates.check_storeimport_available',
            'license_key' => $license_number,
            'ver' => $version,
            'edition' => $edition,
        );

        $data = Http::get(Registry::get('config.resources.updates_server'), $request, array(
            'timeout' => 10
        ));

        if (empty($data)) {
            $data = fn_get_contents(Registry::get('config.resources.updates_server') . '/index.php?' . http_build_query($request));
        }

        $result = false;

        if (!empty($data)) {
            // Check if we can parse server response
            if (strpos($data, '<?xml') !== false) {
                $xml = simplexml_load_string($data);
                $result = ((string) $xml == 'Y') ? true : false;
            }
        }

        return $result;
    }
}
