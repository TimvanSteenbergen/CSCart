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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'update' && $_REQUEST['category_id']) {
        $category = db_get_row("SELECT * FROM ?:categories WHERE category_id = ?i", $_REQUEST['category_id']);

        $age_verification = !empty($_REQUEST['category_data']['age_verification']) ? $_REQUEST['category_data']['age_verification'] : 'N';
        $age_limit = !empty($_REQUEST['category_data']['age_limit']) ? $_REQUEST['category_data']['age_limit'] : 0;
        $update = false;

        if ($category['age_verification'] != $age_verification) {
            $update = true;
        }

        if ($category['age_limit'] != $age_limit) {
            $update = true;
        }

        if ($update && $age_verification == 'Y' || $category['parent_age_verification'] == 'Y') {
            $age_verification = 'Y';
        }

        $age_limit = $age_limit > $category['parent_age_limit'] ? $age_limit : $category['parent_age_limit'];

        if ($update) {
            fn_age_verification_update_parent_data($_REQUEST['category_id'], $age_verification, $age_limit);
        }
    }

    if ($mode == 'add' && !empty($_REQUEST['category_data']['parent_id'])) {
        $category = db_get_row("SELECT * FROM ?:categories WHERE category_id = ?i", $_REQUEST['category_data']['parent_id']);

        $age_verification = 'Y';

        if ($category['age_verification'] == 'Y' && $category['parent_age_verification'] == 'Y') {
            $age_limit = max($category['age_limit'], $category['parent_age_limit']);
        } elseif ($category['age_verification'] == 'Y') {
            $age_limit = $category['age_limit'];
        } elseif ($category['parent_age_verification'] == 'Y') {
            $age_limit = $category['parent_age_limit'];
        } else {
            $age_limit = 0;
            $age_verification = 'N';
        }

        $_REQUEST['category_data']['parent_age_limit'] = $age_limit;
        $_REQUEST['category_data']['parent_age_verification'] = $age_verification;
    }

    return;
}

function fn_age_verification_update_parent_data($category_id, $age_verification, $age_limit)
{
    $data = db_get_array("SELECT category_id, age_verification, age_limit, parent_age_verification, parent_age_limit FROM ?:categories WHERE parent_id = ?i", $category_id);

    db_query("UPDATE ?:categories SET parent_age_verification = ?s, parent_age_limit = ?i WHERE parent_id = ?i", $age_verification, $age_limit, $category_id);

    foreach ($data as $key => $entry) {
        $update = false;

        if ($entry['age_verification'] == 'N' && $age_verification == 'Y') {
            $update = true;
        }

        if ($entry['age_limit'] < $age_limit || ($entry['age_verification'] == 'N' && $age_verification == 'N' && $age_limit = 0)) {
            $update = true;
        }

        if ($update) {
            fn_age_verification_update_parent_data($entry['category_id'], $age_verification, $age_limit);
        } elseif ($entry['age_verification'] == 'Y' && $entry['age_limit'] > $age_limit) {
            fn_age_verification_update_parent_data($entry['category_id'], $entry['age_verification'], $entry['age_limit']);
        }

    }
}
