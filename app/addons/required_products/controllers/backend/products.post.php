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

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //
    // Update required products
    //

    if ($mode == 'update') {
        if (!empty($_REQUEST['product_id'])) {
            db_query('DELETE FROM ?:product_required_products WHERE product_id = ?i', $_REQUEST['product_id']);

            if (!empty($_REQUEST['required_products'])) {
                $required_products = explode(',', $_REQUEST['required_products']);

                $key = array_search($_REQUEST['product_id'], $required_products);

                if ($key !== false) {
                    unset($required_products[$key]);
                }

                $entry = array (
                    'product_id' => $_REQUEST['product_id']
                );

                foreach ($required_products as $entry['required_id']) {
                    if (empty($entry['required_id'])) {
                        continue;
                    }

                    db_query('INSERT INTO ?:product_required_products ?e', $entry);
                }
            }
        }
    }
}

if ($mode == 'update') {
    $product_id = empty($_REQUEST['product_id']) ? 0 : intval($_REQUEST['product_id']);

    Registry::set('navigation.tabs.required_products', array (
        'title' => __('required_products'),
        'js' => true
    ));

    $required_products = db_get_fields('SELECT required_id FROM ?:product_required_products WHERE product_id = ?i', $product_id);

    Registry::get('view')->assign('required_products', $required_products);
}
