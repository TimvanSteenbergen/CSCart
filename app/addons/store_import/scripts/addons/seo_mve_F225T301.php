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

$section_id = db_query("INSERT INTO ?:settings_sections (`parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('0', 'ROOT,ULT:VENDOR', 'seo', '0', 'ADDON')");
$tab_section_id = db_query("INSERT INTO ?:settings_sections (`parent_id`, `edition_type`, `name`, `position`, `type`) VALUES ('$section_id', 'ROOT,ULT:VENDOR', 'general', '0', 'TAB')");

$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT,ULT:VENDOR', 'seo_product_type', $section_id, $tab_section_id, 'S', 'category', 0, 'N', '')"
);
db_query("INSERT INTO ?:settings_variants (`object_id`, `name`, `position`) VALUES "
    . "($object_id, 'product_file', 0),"
    . "($object_id, 'product_category', 10)"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "($object_id, 'O', 'EN', 'Product/page SEF URL format', '')"
);
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT,ULT:VENDOR', 'seo_category_type', $section_id, $tab_section_id, 'S', 'category', 10, 'N', '')"
);
db_query("INSERT INTO ?:settings_variants (`object_id`, `name`, `position`) VALUES "
    . "($object_id, 'file', 0),"
    . "($object_id, 'category', 10),"
    . "($object_id, 'root_category', 20)"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "($object_id, 'O', 'EN', 'Categories SEF URL format', '')"
);
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT,ULT:VENDOR', 'single_url', $section_id, $tab_section_id, 'C', 'Y', 20, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "($object_id, 'O', 'EN', 'Use single URL for all languages', '')"
);
$object_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`) VALUES "
    . "('ROOT,ULT:VENDOR', 'seo_language', $section_id, $tab_section_id, 'C', 'N', 30, 'N', '')"
);
db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES "
    . "($object_id, 'O', 'EN', 'Show language in the URL', '')"
);
