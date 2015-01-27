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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

require_once "hybrid_auth_functions.php";

$addon_hybrid_auth = db_get_row("SELECT * FROM ?:addons WHERE addon = 'hybrid_auth'");
if (!empty($addon_hybrid_auth)) {
    createTables();
    transferSettingsToTable();
    moveUsersToTables();

    db_query("ALTER TABLE ?:users DROP COLUMN  identifier;");
    $settings_section = db_get_row("SELECT section_id FROM ?:settings_sections WHERE type = 'ADDON' AND name = 'hybrid_auth'");
    deleteSettingsBySection($settings_section['section_id']);
    $tab_id = getTabId($settings_section);
    $settings = array(
        array(
            'name' => 'icons_pack',
            'section_id' => $settings_section['section_id'],
            'section_tab_id' => $tab_id,
            'type' => 'S',
            'position' => 10,
            'value' => 'flat_32x32'            ),
        array(
            'name' => 'autogen_email',
            'section_id' => $settings_section['section_id'],
            'section_tab_id' => $tab_id,
            'type' => 'C',
            'position' => 20,
            'value' => 'N'
        )
    );

    $descriptions = array(
        'Icons pack',
        'Autogeneration email'
    );

    setSettings($settings, $descriptions);

    if (!hasField("?:hybrid_auth_providers", "app_params")) {
        db_query("ALTER TABLE ?:hybrid_auth_providers ADD COLUMN app_params TEXT DEFAULT '';");
    }
}
