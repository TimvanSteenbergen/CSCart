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

function fn_wishlist_fill_user_fields(&$exclude)
{
    $exclude[] = 'wishlist';
}

//
//
//
function fn_wishlist_get_gift_certificate_info(&$_certificate, &$certificate, &$type)
{
    if ($type == 'W' && is_numeric($certificate)) {
        $_certificate = fn_array_merge($_SESSION['wishlist']['gift_certificates'][$certificate], array('gift_cert_wishlist_id' => $certificate));
    }
}

function fn_wishlist_user_init(&$auth, &$user_info, &$first_init)
{
    if ($first_init == true) {
        $user_id = $auth['user_id'];
        $user_type = 'R';
        if (empty($user_id) && fn_get_session_data('cu_id')) {
            $user_id = fn_get_session_data('cu_id');
            $user_type = 'U';
        }

        fn_extract_cart_content($_SESSION['wishlist'], $user_id, 'W', $user_type);

        return true;
    }

    return false;
}

function fn_wishlist_init_user_session_data(&$sess_data, &$user_id)
{
    if (AREA == 'C') {
        if (empty($_SESSION['wishlist'])) {
            $_SESSION['wishlist'] = array(
                'products' => array()
            );
        }
        fn_extract_cart_content($sess_data['wishlist'], $user_id, 'W');
        fn_save_cart_content($_SESSION['wishlist'], $user_id, 'W');
    }

    return true;
}

function fn_wishlist_sucess_user_login($udata, $auth)
{
    if (AREA == 'C') {
        if ($cu_id = fn_get_session_data('cu_id')) {
            fn_clear_cart($cart);
            fn_save_cart_content($cart, $cu_id, 'W', 'U');
        }
    }
}

function fn_wishlist_pre_add_to_cart(&$product_data, &$cart, &$auth, &$update)
{
    $wishlist = & $_SESSION['wishlist'];

    if (!empty($wishlist['products'])) {
    foreach ($wishlist['products'] as $key => $product) {
        if (!empty($product['extra']['custom_files'])) {
        foreach ($product['extra']['custom_files'] as $option_id => $files) {
            if (!empty($files)) {
            foreach ($files as $file) {
                $product_data['custom_files']['uploaded'][] = array(
                    'product_id' => $key,
                    'option_id' => $option_id,
                    'path' => $file['path'],
                    'name' => $file['name'],
                );
            }
            }
        }
        }
    }
    }
}

//
// Add possibility to retrieve the wishlist products form user_sessions_products
//
// @param array $type_restrictions allowed types
// @return no return value
//
function fn_wishlist_get_carts(&$type_restrictions)
{
    if (is_array($type_restrictions)) {
        $type_restrictions[] = 'W';
    }
}

function fn_wishlist_get_additional_information(&$product, &$products_data)
{
    $_product = reset($products_data['product_data']);
    if (isset($product['product_id']) && isset($_product['product_id']) && $product['product_id'] == $_product['product_id'] && isset($_product['object_id'])) {
        $product['product_id'] = $product['object_id'] = $_product['object_id'];
    }
}

/**
 * Gets wishlist items count
 *
 * @return int wishlist items count
 */
function fn_wishlist_get_count()
{
    $wishlist = array();
    $result = 0;

    if (!empty($_SESSION['wishlist'])) {
        $wishlist = & $_SESSION['wishlist'];
        $result = !empty($wishlist['products']) ? count($wishlist['products']) : 0;
    }

    /**
     * Changes wishlist items count
     *
     * @param array $wishlist wishlist data
     * @param int   $result   wishlist items count
     */
    fn_set_hook('wishlist_get_count_post', $wishlist, $result);

    return empty($result) ? -1 : $result;
}
