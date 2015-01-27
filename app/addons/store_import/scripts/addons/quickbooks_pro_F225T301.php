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

$section_id = db_query("INSERT INTO ?:settings_sections (`parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('0', 'ROOT', 'quickbooks', '0', 'ADDON')");
$tab_section_id = db_query("INSERT INTO ?:settings_sections (`parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('$section_id', 'ROOT', 'general', '0', 'TAB')");

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT', 'trns_class', $section_id, $tab_section_id, 'I', 'Website:Retail', 0, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "($object_id, 'O', 'EN', 'Transaction class name', '')"
);
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT', 'accnt_tax', $section_id, $tab_section_id, 'I', 'Website:Tax', 10, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "($object_id, 'O', 'EN', 'Account to track taxes. The type of this account should be INC.', '')"
);
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT', 'accnt_product', $section_id, $tab_section_id, 'I', 'Sales:Product', 20, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "($object_id, 'O', 'EN', 'Account to track product sales. The type of this account should be INC.', '')"
);
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT', 'accnt_shipping', $section_id, $tab_section_id, 'I', 'Sales:Shipping', 30, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "($object_id, 'O', 'EN', 'Account to track shipping cost. The type of this account should be INC.', '')"
);
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT', 'accnt_discount', $section_id, $tab_section_id, 'I', 'Sales:Discount', 40, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "($object_id, 'O', 'EN', 'Account to track discounts. The type of this account should be INC.', '')"
);
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT', 'accnt_surcharge', $section_id, $tab_section_id, 'I', 'Sales:Surcharge', 50, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "($object_id, 'O', 'EN', 'Account to track the payment surcharge of your items sales (where applicable). The type of this account should be INC.', '')"
);
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT', 'accnt_asset', $section_id, $tab_section_id, 'I', 'Inventory Asset', 60, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "($object_id, 'O', 'EN', 'Account to track the value of your inventory. The type of this account should be OASSET.', '')"
);
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT', 'accnt_cogs', $section_id, $tab_section_id, 'I', 'Cost of Goods Sold', 70, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "($object_id, 'O', 'EN', 'Account to track the cost of your items sales. The type of this account should be COGS.', '')"
);
