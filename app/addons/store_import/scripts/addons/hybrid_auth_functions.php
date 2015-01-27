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

function createTables()
{
    db_query(
        "CREATE TABLE `?:hybrid_auth_users` ("
        . "`user_id` mediumint(8) unsigned NOT NULL auto_increment,"
        . "`provider_id` mediumint(8) unsigned NOT NULL,"
        . "`identifier` varchar(128) NOT NULL default '',"
        . "`timestamp` int(11) unsigned NOT NULL default '0',"
        . "PRIMARY KEY  (`user_id`, `provider_id`)"
        . ") Engine=MyISAM DEFAULT CHARSET UTF8;"
    );

    db_query(
        "CREATE TABLE `?:hybrid_auth_providers` ("
        . "`provider_id` mediumint(8) unsigned NOT NULL auto_increment,"
        . "`company_id` int(11) unsigned NOT NULL default '0',"
        . "`provider` varchar(32) NOT NULL,"
        . "`position` smallint NOT NULL default '0',"
        . "`app_id` varchar(255) NOT NULL default '',"
        . "`app_secret_key` varchar(255) default '',"
        . "`app_public_key` varchar(255) default '',"
        . "`status` char default 'D',"
        . "PRIMARY KEY  (`provider_id`)"
        . ") Engine=MyISAM DEFAULT CHARSET UTF8;"
    );
}

function moveUsersToTables()
{
    $users_data = db_get_array("SELECT user_id, identifier, timestamp FROM ?:users");

    foreach ($users_data as $user_data) {
        if (!empty($user_data['identifier'])) {
            db_query(
                "INSERT INTO ?:hybrid_auth_users SET "
                . "user_id = " .  $user_data['user_id']
                . ", provider_id = 0"
                . ", identifier = '" . $user_data['identifier'] . "'"
                . ", timestamp = '" . $user_data['timestamp'] . "'"
                . " ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), provider_id = VALUES(provider_id)"
            );
        }
    }
}

function moveUsersToProfiles()
{
    db_query("ALTER TABLE ?:users ADD COLUMN  identifier VARCHAR(128) DEFAULT '';");

    $users_data = db_get_array("SELECT user_id, identifier FROM ?:hybrid_auth_users WHERE provider_id = 0");

    foreach ($users_data as $user_data) {
        if (!empty($user_data['identifier'])) {
            db_query("UPDATE ?:users SET identifier = " . $user_data['identifier'] . " WHERE user_id = " . $user_data['user_id']);
        }
    }
}

function transferSettingsToTable()
{
    $settings_section = db_get_row("SELECT section_id FROM ?:settings_sections WHERE type = 'ADDON' AND name = 'hybrid_auth'");
    $settings = db_get_array("SELECT * FROM ?:settings_objects WHERE section_id = " . $settings_section['section_id']);

    $companies = db_get_array("SELECT company_id FROM ?:companies;");

    $_settings = array();
    foreach ($settings as $setting) {
        $_settings[$setting['name']] = $setting;
    }
    $settings = $_settings;

    if (!empty($settings)) {
        foreach ($settings as $setting) {
            if (strpos($setting['name'], '_status')) {
                $provider_id = str_replace('_status', '', $setting['name']);

                foreach ($companies as $company_data) {
                    list($status, $is_vendor) = getStatus($provider_id, $settings, $company_data['company_id']);
                    if ($status == 'Y') {
                        $app_id = getSetting($provider_id, 'id', $settings, $company_data['company_id'], $is_vendor);
                        if (empty($app_id)) {
                            $app_id = getSetting($provider_id, 'key', $settings, $company_data['company_id'], $is_vendor);
                        }
                        $secret_key = getSetting($provider_id, 'secret', $settings, $company_data['company_id'], $is_vendor);
                        $public_key = getSetting($provider_id, 'public', $settings, $company_data['company_id'], $is_vendor);

                        db_query(
                            "INSERT INTO ?:hybrid_auth_providers SET "
                            . "provider = '" .  $provider_id . "'"
                            . ", company_id = " . $company_data['company_id']
                            . ", app_id = '" . $app_id . "'"
                            . ", app_secret_key = '" . $secret_key . "'"
                            . ", app_public_key = '" . $public_key . "'"
                            . ", status = 'A'"
                            . " ON DUPLICATE KEY UPDATE provider_id = VALUES(provider_id)"
                        );
                    }
                }
            }
        }
    }
}

function getStatus($provider_name, $settings, $company_id)
{
    $value = '';
    $is_vendor = false;
    $name = $provider_name . '_status';
    if (isset($settings[$name])) {
        $object_id = $settings[$name]['object_id'];
        $value = $settings[$name]['value'];
        $_value = db_get_row("SELECT `value` FROM ?:settings_vendor_values WHERE object_id = $object_id AND company_id = $company_id");

        if (!empty($_value['value'])) {
            $value = $_value['value'];
            $is_vendor = true;
        }
    }

    return array($value, $is_vendor);
}

function getSetting($provider_name, $field_name, $settings, $company_id, $is_vendor)
{
    $value = '';
    $name = $provider_name . '_' . $field_name;
    if (isset($settings[$name])) {
        $object_id = $settings[$name]['object_id'];
        if ($is_vendor) {
            $_value = db_get_row("SELECT `value` FROM ?:settings_vendor_values WHERE object_id = $object_id AND company_id = $company_id");
            $value = $_value['value'];
        } else {
            $value = $settings[$name]['value'];
        }
    }

    return $value;
}

function setSettings($settings, $descriptions, $settings_vendor = array())
{
    $companies = db_get_array("SELECT company_id FROM ?:companies;");

    foreach ($settings as $id => $setting) {
        $name = (isset($setting['name']) ? $setting['name'] : '');
        $section_id = (isset($setting['section_id']) ? $setting['section_id'] : 0);
        $section_tab_id = (isset($setting['section_tab_id']) ? $setting['section_tab_id'] : 0);

        $result = db_get_row("SELECT object_id FROM ?:settings_objects WHERE section_id = $section_id AND section_tab_id = $section_tab_id AND name = '$name'");

        if (empty($result)) {
            $values =
                "edition_type = '" . (isset($setting['edition_type']) ? $setting['edition_type'] : 'ROOT,ULT:VENDOR') . "'"
                . ", name = '" . (isset($setting['name']) ? $setting['name'] : '') . "'"
                . ", section_id = " . (isset($setting['section_id']) ? $setting['section_id'] : 0)
                . ", section_tab_id = " . (isset($setting['section_tab_id']) ? $setting['section_tab_id'] : 0)
                . ", type = '" . (isset($setting['type']) ? $setting['type'] : 'I') . "'"
                . ", value = '" . (isset($setting['value']) ? $setting['value'] : '') . "'"
                . ", position = " . (isset($setting['position']) ? $setting['position'] : 0)
                . ", is_global = '" . (isset($setting['is_global']) ? $setting['is_global'] : 'N') . "'"
                . ", handler = '" . (isset($setting['handler']) ? $setting['handler'] : '') . "'"
                . ", parent_id = " . (isset($setting['parent_id']) ? $setting['parent_id'] : 0);

            db_query("INSERT INTO {$pr}settings_objects SET $values");

            $object = db_get_row("SELECT object_id FROM ?:settings_objects WHERE section_id = $section_id AND section_tab_id = $section_tab_id AND name = '$name'");
            $object_id = $object['object_id'];
            $decription =
                "object_id = " . $object_id
                . ", object_type = 'O'"
                . ", lang_code = 'en'"
                . ", value = '" . $descriptions[$id] . "'";

            db_query("INSERT INTO ?:settings_descriptions SET $decription");

        } else {
            $object_id = $result['object_id'];
        }

        if (!empty($settings_vendor) && !empty($object_id)) {
            foreach ($companies as $company_data) {
                $values =
                    "object_id = " . $object_id
                    . ", company_id = " . $company_data['company_id']
                    . ", value = '" . (isset($settings_vendor[$company_data['company_id']][$id]) ? $settings_vendor[$company_data['company_id']][$id] : '') . "'";

                db_query("INSERT INTO ?:settings_vendor_values SET $values ON DUPLICATE KEY UPDATE object_id = VALUES(object_id), company_id = VALUES(company_id)");
            }
        }
    }
}

function getTabId($settings_section)
{
    $section_tab = db_get_row("SELECT section_id FROM ?:settings_sections WHERE parent_id = " . $settings_section['section_id'] . " AND type = 'TAB' AND name = 'general'");

    $tab_id = 0;
    if (!empty($section_tab)) {
        $tab_id = $section_tab['section_id'];
    }

    return $tab_id;
}

function getShemaProvider()
{
    $schema_provider = array(
        'openid' => array(
            'status' => array(
                'type' => 'C',
                'db_field' => 'status',
                'default_value' => 'N'
            )
        ),
        'yahoo' => array(
            'status' => array(
                'type' => 'C',
                'db_field' => 'status',
                'default_value' => 'N'
            ),
            'key' => array(
                'type' => 'I',
                'db_field' => 'app_id'
            ),
            'secret' => array(
                'type' => 'I',
                'db_field' => 'app_secret_key'
            ),
        ),
        'google' => array(
            'status' => array(
                'type' => 'C',
                'db_field' => 'status',
                'default_value' => 'N'
            ),
            'id' => array(
                'type' => 'I',
                'db_field' => 'app_id'
            ),
            'secret' => array(
                'type' => 'I',
                'db_field' => 'app_secret_key'
            ),
            'google_callback_url' => array(
                'type' => 'O',
                'handler' => 'fn_hybrid_auth_google_callback_url'
            )
        ),
        'facebook' => array(
            'status' => array(
                'type' => 'C',
                'db_field' => 'status',
                'default_value' => 'N'
            ),
            'id' => array(
                'type' => 'I',
                'db_field' => 'app_id'
            ),
            'secret' => array(
                'type' => 'I',
                'db_field' => 'app_secret_key'
            ),
        ),
        'twitter' => array(
            'status' => array(
                'type' => 'C',
                'db_field' => 'status',
                'default_value' => 'N'
            ),
            'key' => array(
                'type' => 'I',
                'db_field' => 'app_id',
             ),
             'secret' => array(
                 'type' => 'I',
                 'db_field' => 'app_secret_key'
             ),
         ),
         'myspace' => array(
             'status' => array(
                 'type' => 'C',
                 'db_field' => 'status',
                 'default_value' => 'N'
             ),
             'key' => array(
                 'type' => 'I',
                 'db_field' => 'app_id'
             ),
             'secret' => array(
                 'type' => 'I',
                 'db_field' => 'app_secret_key'
             ),
         ),
         'linkedin' => array(
             'status' => array(
                 'type' => 'C',
                 'db_field' => 'status',
                 'default_value' => 'N'
             ),
             'key' => array(
                 'type' => 'I',
                 'db_field' => 'app_id'
             ),
             'secret' => array(
                 'type' => 'I',
                 'db_field' => 'app_secret_key'
             ),
         ),
     );

    return $schema_provider;
}

function deleteSettingsBySection($section_id)
{
    $object_ids = db_get_array("SELECT object_id FROM ?:settings_objects WHERE section_id = " . $section_id);
    foreach ($object_ids as $object) {
        db_query("DELETE FROM ?:settings_descriptions WHERE object_id = " . $object['object_id']);
        db_query("DELETE FROM ?:settings_vendor_values WHERE object_id = " . $object['object_id']);
    }
    db_query("DELETE FROM ?:settings_objects WHERE section_id = " . $section_id);
}

function hasField($table, $field)
{
    $db = Registry::get('config.db_name');
    $result = db_get_row("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = '{$table}' AND COLUMN_NAME = '{$field}'");

    return !empty($result);
}
