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

fn_define('GOOGLE_EXPORT_MAX_DESCR_LENGTH', 9999);

function fn_exim_google_export_format_description($product_descr, $max_length = GOOGLE_EXPORT_MAX_DESCR_LENGTH)
{
    $return = strip_tags($product_descr);
    if (strlen($return) > $max_length) {
        $return = substr($return, 0, $max_length);
    }

    return $return;
}

// Apply discounts to product price and format sale_price field
// Parameters:
// @product_price - original product price
// @product_id - current product id
function fn_exim_google_export_format_price($product_price, $product_id = 0)
{
    $auth = fn_fill_auth();
    $product = fn_get_product_data($product_id, $auth, CART_LANGUAGE, true, true, false, false, false);
    fn_promotion_apply('catalog', $product, $auth);

    $_discount = 0;
    if (!empty($product['discount'])) {
        $_discount = $product['discount'];
    }

    return fn_format_price($product_price - $_discount, CART_PRIMARY_CURRENCY, null, false);
}
