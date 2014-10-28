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

$section_id = db_query("INSERT INTO ?:settings_sections (`parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('0', 'ROOT', 'vendor_data_premoderation', '0', 'ADDON')");
$tab_section_id = db_query("INSERT INTO ?:settings_sections (`parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('$section_id', 'ROOT', 'general', '0', 'TAB')");

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT', 'products_prior_approval', $section_id, $tab_section_id, 'S', 'none', 0, 'N', '')"
);
db_query("INSERT INTO ?:settings_variants (`object_id`, `name`, `position`) VALUES "
    . "($object_id, 'none', 0),"
    . "($object_id, 'custom', 10),"
    . "($object_id, 'all', 20)"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "($object_id, 'O', 'en', 'Products require prior approval', '\"None\" - this option is disabled for all vendors; \"Custom\" - the option is enabled on a vendor details page (the Add-ons tab); \"All vendors\" - the option is enabled for all vendors.')"
);
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT', 'products_updates_approval', $section_id, $tab_section_id, 'S', 'none', 10, 'N', '')"
);
db_query("INSERT INTO ?:settings_variants (`object_id`, `name`, `position`) VALUES "
    . "($object_id, 'none', 0),"
    . "($object_id, 'custom', 10),"
    . "($object_id, 'all', 20)"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "($object_id, 'O', 'en', 'Approve product info updates', '\"None\" - this option is disabled for all vendors; \"Custom\" - the option is enabled on a vendor details page (the Add-ons tab); \"All vendors\" - the option is enabled for all vendors.')"
);
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT', 'vendor_profile_updates_approval', $section_id, $tab_section_id, 'S', 'none', 20, 'N', '')"
);
db_query("INSERT INTO ?:settings_variants (`object_id`, `name`, `position`) VALUES "
    . "($object_id, 'none', 0),"
    . "($object_id, 'custom', 10),"
    . "($object_id, 'all', 20)"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "($object_id, 'O', 'en', 'Approve vendor profile updates', '\"None\" - this option is disabled for all vendors; \"Custom\" - the option is enabled on a vendor details page (the Add-ons tab); \"All vendors\" - the option is enabled for all vendors.')"
);
