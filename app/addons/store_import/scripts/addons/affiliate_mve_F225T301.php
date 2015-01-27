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

$section_id = db_query("INSERT INTO ?:settings_sections (`parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('0', 'ROOT', 'affiliate', '0', 'ADDON')");
$tab_section_id = db_query("INSERT INTO ?:settings_sections (`parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('$section_id', 'ROOT', 'general', '0', 'TAB')");

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT', 'show_affiliate_code', '$section_id', '$tab_section_id', 'C', 'Y', 0, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "('$object_id', 'O', 'EN', 'Show affiliate code at front-end', '')"
);

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT', 'payment_period', '$section_id', '$tab_section_id', 'S', '1w', 10, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "('$object_id', 'O', 'EN', 'Payment period', '')"
);
db_query("INSERT INTO ?:settings_variants (`object_id`, `name`, `position`) VALUES "
    . "($object_id, '1w', 0),"
    . "($object_id, '2w', 10),"
    . "($object_id, '1m', 20)"
);

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT', 'number_last_periods', '$section_id', '$tab_section_id', 'I', '10', 20, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "('$object_id', 'O', 'EN', 'Number of last periods', '')"
);

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT', 'automatic_approval_commissions', '$section_id', '$tab_section_id', 'C', '', 30, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "('$object_id', 'O', 'EN', 'Automatic approval of affiliate commissions', '')"
);

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT', 'elm_text_banner', '$section_id', '$tab_section_id', 'H', '', 40, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "('$object_id', 'O', 'EN', 'Text banner', '')"
);

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT', 'out_line_clor', '$section_id', '$tab_section_id', 'I', '#296DC1', 50, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "('$object_id', 'O', 'EN', 'Outline color', '')"
);

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT', 'title_text_color', '$section_id', '$tab_section_id', 'I', '#FDFDFD', 60, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "('$object_id', 'O', 'EN', 'Title text color', '')"
);

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT', 'text_color', '$section_id', '$tab_section_id', 'I', '#333333', 70, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "('$object_id', 'O', 'EN', 'Text color', '')"
);

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT', 'text_background_color', '$section_id', '$tab_section_id', 'I', '#F9F9FF', 80, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "('$object_id', 'O', 'EN', 'Text background color', '')"
);

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT', 'elm_discount_coupons', '$section_id', '$tab_section_id', 'H', '', 90, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "('$object_id', 'O', 'EN', 'Discount Coupons', '')"
);

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT', 'use_affiliate_id', '$section_id', '$tab_section_id', 'C', 'N', 100, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "('$object_id', 'O', 'EN', 'Affiliate ID is used as coupon prefix', '')"
);

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT', 'coupon_prefix_delim', '$section_id', '$tab_section_id', 'I', '-', 110, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "('$object_id', 'O', 'EN', 'Delimiter between prefix and coupon-code', '')"
);
