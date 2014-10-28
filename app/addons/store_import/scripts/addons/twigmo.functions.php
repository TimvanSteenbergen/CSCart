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

function fn_twigmo_remove_tables()
{
    db_query("DROP TABLE IF EXISTS `?:twigmo_ua_stat`;");
}

function fn_twigmo_remove_bm()
{
    $location_ids = db_get_fields("SELECT `location_id` FROM `?:bm_locations` WHERE `dispatch` LIKE '%twigmo%';");

    if (!empty($location_ids)) {
        db_query("DELETE FROM `?:bm_locations` WHERE `location_id` IN (?a)", $location_ids);
        db_query("DELETE FROM `?:bm_locations_descriptions` WHERE `location_id` IN (?a)", $location_ids);
    }
}

function fn_twigmo_remove_langvars()
{
    db_query("DELETE FROM `?:language_values` WHERE `name` LIKE 'twg%'");
    db_query("DELETE FROM `?:language_values` WHERE `name` LIKE '%addons_twigmo%'");
}
