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
$map = array(
    'min' => 'minimal_absolute',
    'max' => 'maximal_absolute',
);
$setting = db_get_row("SELECT object_id, value FROM ?:settings_objects_upg WHERE name = 'several_points_action'");
if ($setting) {
    $company_ids = db_get_fields("SELECT company_id FROM ?:companies");
    db_query("UPDATE ?:settings_objects_upg SET value = ?s WHERE name = 'several_points_action'", $map[$setting['value']]);
    foreach ($company_ids as $company_id) {
        db_query("UPDATE ?:settings_vendor_values_upg SET value = ?s WHERE object_id = ?i AND company_id = ?i", $map[$setting['value']], $setting['object_id'], $company_id);
    }
}
