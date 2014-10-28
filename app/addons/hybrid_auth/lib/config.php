<?php
/*!
* HybridAuth
* http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
* (c) 2009-2012, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
*/

// ----------------------------------------------------------------------------------------
//	HybridAuth Config file: http://hybridauth.sourceforge.net/userguide/Configuration.html
// ----------------------------------------------------------------------------------------

use \Tygh\Registry;

$config = array(
    'base_url' => fn_url('auth.process'),

    // if you want to enable logging, set 'debug_mode' to true  then provide a writable file by the web server on "debug_file"
    'debug_mode' => false,
    'debug_file' => Registry::get('config.dir.var') . 'oauth.log',
);

$providers_schema = fn_get_schema('hybrid_auth', 'providers');
$available_providers = fn_hybrid_auth_get_providers_list();

foreach ($available_providers as $provider_data) {
    $provider_name = $providers_schema[$provider_data['provider']]['provider'];
    $config['providers'][$provider_name] = array(
        'enabled' => $provider_data['status'] == 'A' ? true : false
    );

    if (isset($providers_schema[$provider_data['provider']])) {

        $provider_keys = isset($providers_schema[$provider_data['provider']]['keys']) ? $providers_schema[$provider_data['provider']]['keys'] : array();
        foreach ($provider_keys as $key => $key_data) {
            if (isset($key_data['db_field']) && isset($provider_data[$key_data['db_field']])) {
                $config['providers'][$provider_name]['keys'][$key] = $provider_data[$key_data['db_field']];
            }
        }

        $provider_params = isset($providers_schema[$provider_data['provider']]['params']) ? $providers_schema[$provider_data['provider']]['params'] : array();
        foreach ($provider_params as $param_id => $param_data) {
            if (isset($provider_data['params'][$param_id])) {
                $config['providers'][$provider_name][$param_id] = $provider_data['params'][$param_id];

            } elseif ($param_data['type'] == 'hidden' && !empty($param_data['value'])) {
                $config['providers'][$provider_name][$param_id] = $param_data['value'];
            }
        }
    }
}

return $config;
