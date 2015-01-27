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

$join = 'LEFT JOIN ?:banners as b ON bd.banner_id = b.banner_id';

db_query("UPDATE ?:banner_descriptions as bd $join SET bd.url = b.url");
db_query("ALTER TABLE ?:banners DROP COLUMN `url`;");

// Update images links
$images_links = db_get_array("SELECT pair_id, object_id, image_id FROM ?:images_links WHERE object_type = 'promo'");
if (!empty($images_links)) {
    foreach ($images_links as $link) {
        $data_banner_image = array(
            'banner_id' => $link['object_id'],
            'lang_code' => DEFAULT_LANGUAGE
        );

        $banner_image_id = db_query("INSERT INTO ?:banner_images ?e", $data_banner_image);
        db_query('UPDATE ?:images_links SET object_id = ?i WHERE pair_id = ?i', $banner_image_id, $link['pair_id']);

        $lang_codes = db_get_fields("SELECT lang_code FROM ?:languages WHERE lang_code NOT IN (?s)", DEFAULT_LANGUAGE);

        if (!empty($lang_codes)) {
            foreach ($lang_codes as $lang_code) {
                $banner_image_id = db_query("INSERT INTO ?:banner_images (banner_id, lang_code) VALUE(?i, ?s)", $link['object_id'], $lang_code);
                fn_add_image_link($banner_image_id, $link['pair_id']);
            }
        }
    }
}
