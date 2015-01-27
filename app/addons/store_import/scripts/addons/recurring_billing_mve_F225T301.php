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

$section_id = db_query("INSERT INTO ?:settings_sections (`parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('0', 'ROOT,ULT:VENDOR', 'recurring_billing', '0', 'ADDON')");

$tab_section_id = db_query("INSERT INTO ?:settings_sections (`parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('$section_id', 'ROOT,ULT:VENDOR', 'general', '0', 'TAB')");
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT,ULT:VENDOR', 'rb_initial_order_status', $section_id, $tab_section_id, 'S', '', 0, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "($object_id, 'O', 'EN', 'Initial status for recurring orders', '')"
);
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT,ULT:VENDOR', 'rb_fail_attempts', $section_id, $tab_section_id, 'I', '3', 10, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "($object_id, 'O', 'EN', 'Number of attempts after order failed', '')"
);
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT,ULT:VENDOR', 'rb_attempt_period', $section_id, $tab_section_id, 'I', '1', 20, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "($object_id, 'O', 'EN', 'Next attempt if order failed (days)', '')"
);

$tab_section_id = db_query("INSERT INTO ?:settings_sections (`parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('$section_id', 'ROOT,ULT:VENDOR', 'orders', '10', 'TAB')");
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT,ULT:VENDOR', 'additional_settings', $section_id, $tab_section_id, 'E', 'rb_order_status_settings.tpl', 0, 'N', '')"
);

$tab_section_id = db_query("INSERT INTO ?:settings_sections (`parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('$section_id', 'ROOT,ULT:VENDOR', 'rb_notification', '20', 'TAB')");
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT,ULT:VENDOR', 'additional_settings_lang', $section_id, $tab_section_id, 'E', 'rb_notification_settings_lang.tpl', 0, 'N', '')"
);
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT,ULT:VENDOR', 'rb_manual_pay_header', $section_id, $tab_section_id, 'H', '', 10, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "($object_id, 'O', 'EN', 'Notification of manual paying', '')"
);
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT,ULT:VENDOR', 'rb_manual_pay_duration', $section_id, $tab_section_id, 'I', '3', 20, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "($object_id, 'O', 'EN', 'Notification duration (days)', '')"
);
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT,ULT:VENDOR', 'additional_settings_manual', $section_id, $tab_section_id, 'E', 'rb_notification_settings_manual.tpl', 30, 'N', '')"
);
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT,ULT:VENDOR', 'rb_future_pay_header', $section_id, $tab_section_id, 'H', '', 40, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "($object_id, 'O', 'EN', 'Notification of future paying', '')"
);
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT,ULT:VENDOR', 'rb_future_pay_duration', $section_id, $tab_section_id, 'I', '3', 50, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "($object_id, 'O', 'EN', 'Notification duration (days)', '')"
);
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT,ULT:VENDOR', 'additional_settings_future', $section_id, $tab_section_id, 'E', 'rb_notification_settings_future.tpl', 60, 'N', '')"
);
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT,ULT:VENDOR', 'rb_changing_header', $section_id, $tab_section_id, 'H', '', 70, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "($object_id, 'O', 'EN', 'Notification of subscription changing', '')"
);
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT,ULT:VENDOR', 'additional_settings_changing', $section_id, $tab_section_id, 'E', 'rb_notification_settings_changing.tpl', 80, 'N', '')"
);
