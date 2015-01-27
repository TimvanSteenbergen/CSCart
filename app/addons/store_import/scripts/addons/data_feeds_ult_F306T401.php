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

$data_feeds = db_get_array("SELECT datafeed_id,export_options FROM ?:data_feeds");
if (!empty($data_feeds)) {
    foreach ($data_feeds as $key => $feed) {
        $feed['export_options'] = unserialize($feed['export_options']);
        $feed['export_options']['lang_code'] = array(strtolower($feed['export_options']['lang_code']));
        $feed['export_options'] = serialize($feed['export_options']);
        db_query("UPDATE ?:data_feeds SET export_options = ?s WHERE datafeed_id = ?i", $feed['export_options'], $feed['datafeed_id']);
    }
}
