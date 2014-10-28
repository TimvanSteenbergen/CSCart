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

$section_id = db_query("INSERT INTO ?:settings_sections (`parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('0', 'ROOT', 'access_restrictions', '0', 'ADDON')");
$tab_section_id = db_query("INSERT INTO ?:settings_sections (`parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('$section_id', 'ROOT', 'general', '0', 'TAB')");

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('ROOT', 'elm_administrator_area_settings', '$section_id', '$tab_section_id', 'H', '', 0, 'N', '')");
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('$object_id', 'O', 'EN', 'Administrator area settings', '')");

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('ROOT', 'admin_reverse_ip_access', '$section_id', '$tab_section_id', 'N', '', 0, 'N', '')");
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('$object_id', 'O', 'EN', 'Allow login to the admin area from specified IPs only', '')");

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('ROOT', 'unsuccessful_attempts_login', '$section_id', '$tab_section_id', 'N', '', 0, 'N', '')");
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('$object_id', 'O', 'EN', 'Block IP after a number of unsuccessful attempts', '')");

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('ROOT', 'number_unsuccessful_attempts', '$section_id', '$tab_section_id', 'N', '', 0, 'N', '')");
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('$object_id', 'O', 'EN', 'Number of unsuccessful attempts', '')");

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('ROOT', 'login_intervals', '$section_id', '$tab_section_id', 'H', '', 0, 'N', '')");
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('$object_id', 'O', 'EN', 'Time between unsuccessful login attempts (seconds)', '')");

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('ROOT', 'time_block', '$section_id', '$tab_section_id', 'H', '', 0, 'N', '')");
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('$object_id', 'O', 'EN', 'Time for which the IP should be blocked (hours)', '')");

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('ROOT', 'elm_customer_and_affiliate_area_settings', '$section_id', '$tab_section_id', 'H', '', 0, 'N', '')");
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('$object_id', 'O', 'EN', 'Customer and Affiliate area settings', '')");

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('ROOT', 'unsuccessful_attempts_login_customer', '$section_id', '$tab_section_id', 'C', '', 0, 'N', '')");
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('$object_id', 'O', 'EN', 'Block IP after a number of unsuccessful attempts', '')");

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('ROOT', 'number_unsuccessful_attempts_customer', '$section_id', '$tab_section_id', 'I', '', 0, 'N', '')");
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('$object_id', 'O', 'EN', 'Number of unsuccessful attempts', '')");

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('ROOT', 'login_intervals_customer', '$section_id', '$tab_section_id', 'I', '', 0, 'N', '')");
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('$object_id', 'O', 'EN', 'Time between unsuccessful login attempts (seconds)', '')");

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES ('ROOT', 'time_block_customer', '$section_id', '$tab_section_id', 'I', '', 0, 'N', '')");
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('$object_id', 'O', 'EN', 'Time for which the IP should be blocked (hours)', '')");
