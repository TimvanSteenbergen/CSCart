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

if ($mode == 'options') {
    define('GET_OPTIONS', true);
    $_auth = $auth;

    if (empty($_REQUEST['product_data']) && empty($_REQUEST['cart_products'])) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    // Apply the specific block template settings
    if (!empty($_REQUEST['appearance'])) {
        foreach ($_REQUEST['appearance'] as $setting => $value) {
            Registry::get('view')->assign($setting, $value);
        }
    }

    Registry::get('view')->assign('show_images', true);
    Registry::get('view')->assign('no_capture', true);

    if (!empty($_REQUEST['product_data'])) {
        // Product data
        unset($_REQUEST['product_data']['custom_files']);
        $product_data = $_REQUEST;
        list($product_id, $_data) = each($product_data['product_data']);

        $product_id = isset($_data['product_id']) ? $_data['product_id'] : $product_id;
        $selected_options = empty($_data['product_options']) ? array() : $_data['product_options'];
        unset($selected_options['AOC']);

        if (isset($product_data['additional_info']['info_type']) && $product_data['additional_info']['info_type'] == 'D') {
            $product = fn_get_product_data($product_id, $_auth, CART_LANGUAGE, '', true, true, true, true, ($auth['area'] == 'A'));
        } else {
            $params['pid'] = $product_id;

            list($product) = fn_get_products($params);
            $product = reset($product);
        }

        if (empty($product)) {
            return array(CONTROLLER_STATUS_NO_PAGE);
        }

        $product['changed_option'] = isset($product_data['changed_option']) ? reset($product_data['changed_option']) : '';
        $product['selected_options'] = $selected_options;
        if (!empty($_data['amount'])) {
            $product['selected_amount'] = $_data['amount'];
        }

        // Get specific settings
        $params = array(
            'get_icon' => isset($_REQUEST['additional_info']['get_icon']) ? $_REQUEST['additional_info']['get_icon'] : false,
            'get_detailed' => isset($_REQUEST['additional_info']['get_detailed']) ? $_REQUEST['additional_info']['get_detailed'] : false,
            'get_options' => isset($_REQUEST['additional_info']['get_options']) ? $_REQUEST['additional_info']['get_options'] : true,
            'get_discounts' => isset($_REQUEST['additional_info']['get_discounts']) ? $_REQUEST['additional_info']['get_discounts'] : true,
            'get_features' => isset($_REQUEST['additional_info']['get_features']) ? $_REQUEST['additional_info']['get_features'] : false,
        );

        fn_set_hook('get_additional_information', $product, $product_data);

        fn_gather_additional_product_data($product, $params['get_icon'], $params['get_detailed'], $params['get_options'], $params['get_discounts'], $params['get_features']);
        if (isset($product['inventory_amount'])) {
            $product['amount'] = $product['inventory_amount'];
        }

        if (!empty($_REQUEST['extra_id'])) {
            $product['product_id'] = $_REQUEST['extra_id'];
        }

        Registry::get('view')->assign('product', $product);

        // Update the images in the list/grid templates
        if (!empty($_REQUEST['image'])) {
            foreach ($_REQUEST['image'] as $div_id => $value) {
                list($obj_id, $width, $height, $type) = explode(',', $value['data']);
                $images_data[$div_id] = array(
                    'obj_id' => $obj_id,
                    'width' => $width,
                    'height' => $height,
                    'type' => $type,
                    'link' => isset($value['link']) ? $value['link'] : '',
                );
            }

            Registry::get('view')->assign('images', $images_data);
        }

        if (AREA == 'C') {
            if (!empty($_REQUEST['appearance']['quick_view'])) {
                $display_tpl = 'views/products/quick_view.tpl';
            } elseif (!empty($_REQUEST['appearance']['details_page'])) {
                $display_tpl = 'views/products/view.tpl';
            } else {
                $display_tpl = 'common/product_data.tpl';
            }
        } else {
            $display_tpl = 'views/products/components/select_product_options.tpl';
            Registry::get('view')->assign('product_options', $product['product_options']);
        }

    } else {
        // Cart data
        fn_enable_checkout_mode();

        unset($_REQUEST['cart_products']['custom_files']);
        $cart_products = $_REQUEST['cart_products'];
        if (!empty($cart_products)) {
            foreach ($cart_products as $cart_id => $product) {
                if (!empty($product['object_id'])) {
                    unset($cart_products[$cart_id]);
                    $cart_products[$product['object_id']] = $product;
                }
            }
        }

        $_cart = $_SESSION['cart'];

        if (AREA == 'A') {
            $_auth = $_SESSION['customer_auth'];
            if (empty($_auth)) {
                $_auth = fn_fill_auth(array(), array(), false, 'C');
            }
        }

        foreach ($cart_products as $cart_id => $item) {
            if (isset($_cart['products'][$cart_id])) {
                $amount = isset($item['amount']) ? $item['amount'] : 1;
                $product_data = fn_get_product_data($item['product_id'], $auth, CART_LANGUAGE, '', false, false, false, false, false, false, false);

                if ($product_data['options_type'] == 'S' && isset($item['product_options']) && isset($_REQUEST['changed_option'][$cart_id])) {
                    $item['product_options'] = fn_fill_sequential_options($item, $_REQUEST['changed_option'][$cart_id]);
                    unset($_REQUEST['changed_option']);
                }

                $product_options = isset($item['product_options']) ? $item['product_options'] : array();
                $amount = fn_check_amount_in_stock($item['product_id'], $amount, $product_options, $cart_id, $_cart['products'][$cart_id]['is_edp'], 0, $_cart);

                if ($amount === false) {
                    unset($_cart['products'][$cart_id]);
                    continue;
                }

                $_cart['products'][$cart_id]['amount'] = $amount;
                $_cart['products'][$cart_id]['product_options'] = isset($item['product_options']) ? $item['product_options'] : array();

                if (!empty($_cart['products'][$cart_id]['extra']['saved_options_key'])) {
                    $_cart['saved_product_options'][$_cart['products'][$cart_id]['extra']['saved_options_key']] = $_cart['products'][$cart_id]['product_options'];
                }

                if (!empty($item['object_id'])) {
                    $_cart['products'][$cart_id]['object_id'] = $item['object_id'];

                    if (!empty($_cart['products'][$cart_id]['extra']['saved_options_key'])) {
                        // Product from promotion. Save object_id for this product
                        $_cart['saved_object_ids'][$_cart['products'][$cart_id]['extra']['saved_options_key']] = $item['object_id'];
                    }
                }
            }
        }

        fn_set_hook('calculate_options', $cart_products, $_cart, $auth);

        $exclude_products = array();
        foreach ($_cart['products'] as $cart_id => $product) {
            if (!empty($product['extra']['exclude_from_calculate'])) {
                $exclude_products[$cart_id] = true;
            }
        }

        list ($cart_products) = fn_calculate_cart_content($_cart, $_auth, 'S', true, 'F', true);

        fn_gather_additional_products_data($cart_products, array('get_icon' => true, 'get_detailed' => true, 'get_options' => true, 'get_discounts' => false));

        $changed_options = false;
        foreach ($cart_products as $item_id => $product) {
            $cart_id = !empty($product['object_id']) ? $product['object_id'] : $item_id;

            if ($_cart['products'][$cart_id]['product_options'] != $product['selected_options']) {
                $_cart['products'][$cart_id]['product_options'] = $product['selected_options'];
                $changed_options = true;
            }
        }

        if ($changed_options) {
            list ($cart_products) = fn_calculate_cart_content($_cart, $_auth, 'S', true, 'F', true);

            fn_gather_additional_products_data($cart_products, array('get_icon' => true, 'get_detailed' => true, 'get_options' => true, 'get_discounts' => false));
        }

        if (count($_SESSION['cart']['products']) != count($_cart['products'])) {
            $_recalculate = false;
            foreach ($_SESSION['cart']['products'] as $cart_id => $product) {
                if (!isset($_cart['products'][$cart_id]) && !isset($exclude_products[$cart_id])) {
                    $_recalculate = true;
                    break;
                }
            }

            if ($_recalculate) {
                $_cart = $_SESSION['cart'];
                list ($cart_products) = fn_calculate_cart_content($_cart, $_auth, 'S', true, 'F', true);
            }
        }

        // Restore the cart_id
        if (!empty($cart_products)) {
            foreach ($cart_products as $k => $product) {
                if (!empty($product['object_id'])) {
                    $c_product = !empty($_cart['products'][$k]) ? $_cart['products'][$k] : array();
                    unset($cart_products[$k], $_cart['products'][$k]);
                    $_cart['products'][$product['object_id']] = $c_product;
                    $cart_products[$product['object_id']] = $product;
                    $k = $product['object_id'];
                }

                $cart_products[$k]['changed_option'] = isset($product['object_id']) ? isset($_REQUEST['changed_option'][$product['object_id']]) ? $_REQUEST['changed_option'][$product['object_id']] : '' : isset($_REQUEST['changed_option'][$k]) ? $_REQUEST['changed_option'][$k] : '' ;
            }
        }

        Registry::set('navigation', array());
        Registry::get('view')->assign('cart_products', $cart_products);
        Registry::get('view')->assign('cart', $_cart);

        if (AREA == 'C') {
            $display_tpl = 'views/checkout/components/cart_items.tpl';
        } else {
            $display_tpl = 'views/order_management/components/products.tpl';
        }
    }

    $data = isset($product_data) ? $product_data : $cart_products;
    fn_set_hook('after_options_calculation', $mode, $data);

    Registry::get('view')->display($display_tpl);

    exit;
}

if ($mode == 'picker') {

    $params = $_REQUEST;
    $params['extend'] = array('description');
    $params['skip_view'] = 'Y';

    list($products, $search) = fn_get_products($params, AREA == 'C' ? Registry::get('settings.Appearance.products_per_page') : Registry::get('settings.Appearance.admin_products_per_page'));

    if (!empty($_REQUEST['display']) || (AREA == 'C' && !defined('EVENT_OWNER'))) {
        fn_gather_additional_products_data($products, array('get_icon' => true, 'get_detailed' => true, 'get_options' => true, 'get_discounts' => true));
    }

    if (!empty($products)) {
        foreach ($products as $product_id => $product_data) {
            $products[$product_id]['options'] = fn_get_product_options($product_data['product_id'], DESCR_SL, true, false, true);
            if (!fn_allowed_for('ULTIMATE:FREE')) {
                $products[$product_id]['exceptions'] = fn_get_product_exceptions($product_data['product_id']);
                if (!empty($products[$product_id]['exceptions'])) {
                    foreach ($products[$product_id]['exceptions'] as $exceptions_data) {
                        $products[$product_id]['exception_combinations'][fn_get_options_combination($exceptions_data['combination'])] = '';
                    }
                }
            }
        }
    }

    Registry::get('view')->assign('products', $products);
    Registry::get('view')->assign('search', $search);

    if (isset($_REQUEST['company_id'])) {
        Registry::get('view')->assign('picker_selected_company', $_REQUEST['company_id']);
    }
    if (!empty($_REQUEST['company_ids'])) {
        Registry::get('view')->assign('picker_selected_companies', $_REQUEST['company_ids']);
    }

    Registry::get('view')->display('pickers/products/picker_contents.tpl');
    exit;

}

/**
 * Fills sequential options with default values. Necessary for cart total calculation
 *
 * @param array $item Cart item
 * @param int $changed_option Changed option identifier
 * @return array New options list
 */
function fn_fill_sequential_options($item, $changed_option)
{
    $params['pid'] = $item['product_id'];
    list($product) = fn_get_products($params);
    $product = reset($product);

    $product['changed_option'] = $changed_option;
    $product['selected_options'] = $item['product_options'];

    fn_gather_additional_product_data($product, false, false, true, false, false);

    if (count($item['product_options']) != count($product['selected_options'])) {
        foreach ($item['product_options'] as $option_id => $variant_id) {
            if (isset($product['selected_options'][$option_id]) || (in_array($product['product_options'][$option_id]['option_type'], array('I', 'T', 'F')))) {
                continue;
            }

            if (!empty($product['product_options'][$option_id]['variants'])) {
                reset($product['product_options'][$option_id]['variants']);
                $variant_id = key($product['product_options'][$option_id]['variants']);
            } else {
                $variant_id = '';
            }

            $product['selected_options'][$option_id] = $variant_id;
            $product['changed_option'] = $option_id;

            fn_gather_additional_product_data($product, false, false, true, false, false);
        }
    }

    return $product['selected_options'];
}
