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

$general_section_id = db_get_row("SELECT section_id FROM ?:settings_sections WHERE name = 'ebay'");
$general_section_id = $general_section_id['section_id'];
if ($general_section_id) {
    db_query("INSERT INTO ?:settings_sections (parent_id, edition_type, name, position, type) VALUES($general_section_id, 'ROOT,ULT:VENDOR', 'license_info', 10, 'TAB')");
    $license_section_id = db_get_row("SELECT section_id FROM ?:settings_sections WHERE name = 'license_info' AND parent_id = $general_section_id");
    $license_section_id = $license_section_id['section_id'];
    db_query("UPDATE ?:settings_objects SET section_tab_id = $license_section_id WHERE name = 'ebay_license_number'");
    db_query("INSERT INTO ?:settings_objects (edition_type, name, section_id, section_tab_id, type, value, position, is_global, handler, parent_id) VALUES ('ROOT,ULT:VENDOR', 'license_notice', $general_section_id, $license_section_id, 'O', '', 0, 'N', 'fn_ebay_get_license_notice', 0)");
}

db_query("INSERT INTO ?:privileges SET privilege = 'view_ebay_templates', is_default = 'Y', section_id = 'addons' ON DUPLICATE KEY UPDATE privilege = VALUES(privilege)");
db_query("INSERT INTO ?:privileges SET privilege = 'manage_ebay_templates', is_default = 'Y', section_id = 'addons' ON DUPLICATE KEY UPDATE privilege = VALUES(privilege)");
