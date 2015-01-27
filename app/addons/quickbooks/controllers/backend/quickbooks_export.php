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

    if ($mode == 'export_to_iif') {
        header('Content-type: text/csv');
        header('Content-disposition: attachment; filename=orders.iif');

        foreach ($_REQUEST['order_ids'] as $k => $v) {
            $orders[$k] = fn_get_order_info($v);
        }

        $order_users = $order_products = array();
        foreach ($orders as $k => $v) {
            $order_users[$v['user_id'] . '_' . $v['email']] = $v;
            foreach ($v['products'] as $key => $value) {
                $order_products[$value['cart_id']] = $value;
                if (!empty($value['product_options'])) {
                    $selected_options = '; ' . __('product_options') . ': ';
                    foreach ($value['product_options'] as $option) {
                        $selected_options .= "$option[option_name]: $option[variant_name];";
                    }
                    $order_products[$value['cart_id']]['selected_options'] = $selected_options;
                }
            }
        }

        $export = fn_quickbooks_export($orders, $order_users, $order_products);
        fn_echo($export);

        exit;
    }
}

function fn_quickbooks_export($orders, $order_users, $order_products)
{
    $export = array();
    fn_quickbooks_export_customers($order_users, $export);
    fn_quickbooks_export_products($orders, $order_products, $export);
    fn_quickbooks_export_orders($orders, $order_products, $export);
    fn_quickbooks_export_payments($orders, $export);

    return implode("\r\n", $export);
}

function fn_quickbooks_export_customers($order_users, &$export)
{
    $export[] = "!CUST\tNAME\tBADDR1\tBADDR2\tBADDR3\tBADDR4\tBADDR5\tSADDR1\tSADDR2\tSADDR3\tSADDR4\tSADDR5\tPHONE1\tFAXNUM\tEMAIL\tCONT1\tSALUTATION\tCOMPANYNAME\tFIRSTNAME\tLASTNAME";

    $cust = "CUST\t\"%s, %s\"\t%s %s\t%s %s\t%s\t\"%s, %s\"\t%s\t%s %s\t%s %s\t%s\t\"%s, %s\"\t%s\t%s\t%s\t%s\t\"%s, %s\"\t%s\t%s\t%s\t%s";
    foreach ($order_users as $order) {
        $order['title'] = !empty($order['title']) ? $order['title'] : '';

        $export[] = sprintf($cust, $order['lastname'], $order['firstname'], $order['b_firstname'], $order['b_lastname'], $order['b_address'], $order['b_address_2'],
            $order['b_city'], $order['b_state'], $order['b_zipcode'], $order['b_country_descr'], $order['s_firstname'], $order['s_lastname'], $order['s_address'],
            $order['s_address_2'], $order['s_city'], $order['s_state'], $order['s_zipcode'], $order['s_country_descr'], $order['phone'], $order['fax'], $order['email'],
            $order['lastname'], $order['b_firstname'], $order['title'], $order['company'], $order['firstname'], $order['lastname']
        );
    }
    $export[] = '';

    return true;
}

function fn_quickbooks_export_products($orders, $order_products, &$export)
{
    $export[] = "!INVITEM\tNAME\tINVITEMTYPE\tDESC\tPURCHASEDESC\tACCNT\tASSETACCNT\tCOGSACCNT\tPRICE\tCOST\tTAXABLE";

    $invitem = "INVITEM\t%s\tINVENTORY\t%s%s\t%s%s\t%s\t%s\t%s\t%01.2f\t0\tN";

    $accnt_product = Registry::get('addons.quickbooks.accnt_product');
    $accnt_asset = Registry::get('addons.quickbooks.accnt_asset');
    $accnt_cogs = Registry::get('addons.quickbooks.accnt_cogs');

    foreach ($order_products as $product) {
        $product_select_options = !empty($product['selected_options']) ? $product['selected_options'] : '';
        $product_name = !empty($product['product_code']) ? $product['product_code'] : $product['product_id'];

        $export[] = sprintf($invitem, $product_name, $product['product'], $product_select_options, $product['product'],
            $product_select_options, $accnt_product, $accnt_asset, $accnt_cogs, $product['price']
        );
    }

    fn_set_hook('quickbooks_export_items', $orders, $invitem, $export);

    $export[] = '';

    return true;
}

function fn_quickbooks_export_orders($orders, $order_products, &$export)
{
    $export[] = "!TRNS\tTRNSTYPE\tDATE\tACCNT\tNAME\tCLASS\tAMOUNT\tDOCNUM\tMEMO\tADDR1\tADDR2\tADDR3\tADDR4\tADDR5\tPAID\tSHIPVIA\tSADDR1\tSADDR2\tSADDR3\tSADDR4\tSADDR5\tTOPRINT";
    $export[] = "!SPL\tTRNSTYPE\tDATE\tACCNT\tNAME\tCLASS\tAMOUNT\tDOCNUM\tMEMO\tPRICE\tQNTY\tINVITEM\tTAXABLE\tEXTRA";
    $export[] = "!ENDTRNS\t";

    $trns = "TRNS\tINVOICE\t%s\tAccounts Receivable\t%s, %s\t%s\t%s\t%s\tWebsite Order: %s\t%s %s\t%s %s\t\"%s, %s %s\"\t%s\t\t%s\t\t%s %s\t%s %s\t\"%s, %s %s\"\t%s\t\tY";
    $spl = "SPL\tINVOICE\t%s\t%s\t\"%s, %s\"\t%s\t%01.2f\t%d\t%s%s\t%01.2f\t%d\t%s\tN\t%s";

    $accnt_product = Registry::get('addons.quickbooks.accnt_product');
    $accnt_tax = Registry::get('addons.quickbooks.accnt_tax');
    $accnt_shipping = Registry::get('addons.quickbooks.accnt_shipping');
    $accnt_discount = Registry::get('addons.quickbooks.accnt_discount');
    $accnt_surcharge = Registry::get('addons.quickbooks.accnt_surcharge');
    $trns_class = Registry::get('addons.quickbooks.trns_class');

    foreach ($orders as $order) {
        $order_details = str_replace(array("\r\n", "\n", "\r", "\t"), " ", $order['details']);
        $order_date = fn_date_format($order['timestamp'], "%m/%d/%Y");
        $product_subtotal = 0;

        if ($order['status'] == "P" || $order['status'] == "C") {
            $order_paid = 'Y';
        } else {
            $order_paid = 'N';
        }

        $order['s_countryname'] = $order['s_country'];
        $order['b_countryname'] = $order['b_country'];

        $export[] = sprintf($trns, $order_date, $order['b_lastname'], $order['b_firstname'],
            Registry::get('addons.quickbooks.trns_class'), $order['total'], $order['order_id'], $order_details,
            $order['b_firstname'], $order['b_lastname'], $order['b_address'], $order['b_address_2'], $order['b_city'], $order['b_state'],
            $order['b_zipcode'], $order['b_country_descr'], $order_paid,  $order['s_firstname'], $order['s_lastname'],
            $order['s_address'], $order['s_address_2'], $order['s_city'], $order['s_state'], $order['s_zipcode'], $order['s_country_descr']
        );

        // PRODUCTS
        foreach ($order['products'] as $product) {
            $product_id = $product['cart_id'];

            $product_subtotal = $product['price'] * $product['amount'];
            $product_select_options = !empty($order_products[$product_id]['selected_options']) ? $order_products[$product_id]['selected_options'] : '';

            if ($order_products[$product_id]['product_code']) {
                $product_code = $order_products[$product_id]['product_code'];
            } else {
                $product_code = $order_products[$product_id]['product_id'];
            }

            $export[] = sprintf($spl, $order_date, $accnt_product, $order['b_lastname'], $order['b_firstname'], $trns_class,
                -$product_subtotal, $order['order_id'], $order_products[$product_id]['product'],
                $product_select_options, $product['price'], -$product['amount'], $product_code, ''
            );
        }

        // *********  TAXES  **********
        foreach ($order['taxes'] as $tax_data) {
            if ($tax_data['price_includes_tax'] == 'N') {
                $export[] = sprintf($spl, $order_date, $accnt_tax,  $order['b_lastname'],
                    $order['b_firstname'], $trns_class, -$tax_data['tax_subtotal'], $order['order_id'],
                    $tax_data['description'], '', $tax_data['tax_subtotal'], -1, 'TAX', ''
                );
            }
        }

        fn_set_hook('quickbooks_export_order', $order, $order_products, $spl, $export);

        // **********  DISCOUNT  **********
        if ($order['subtotal_discount'] > 0) {
            $export[] = sprintf($spl, $order_date, $accnt_discount, $order['b_lastname'], $order['b_firstname'], $trns_class,
                $order['subtotal_discount'], $order['order_id'], 'DISCOUNT', '', -$order['subtotal_discount'], -1, 'DISCOUNT', '');
        }

        // *********  SHIPPING  **********
        if ($order['shipping_cost'] > 0) {
            $shipping_names = array();
            foreach ($order['shipping'] as $ship) {
                $shipping_names[] = $ship['shipping'];
            }

            $shipping_cost = fn_order_shipping_cost($order);
            $export[] = sprintf($spl, $order_date, $accnt_shipping, $order['b_lastname'], $order['b_firstname'], $trns_class,
                -$shipping_cost, $order['order_id'], implode('; ', $shipping_names), '', $shipping_cost, -1, 'SHIPPING', '');
        }

        // *********  SURCHARGE  **********}
        if ($order['payment_surcharge'] > 0) {
            $export[] = sprintf($spl, $order_date, $accnt_surcharge, $order['b_lastname'], $order['b_firstname'],
                $trns_class, -$order['payment_surcharge'], $order['order_id'], 'Payment processor surcharge', '', $order['payment_surcharge'], -1, 'SURCHARGE', '');
        }

        // ********** AUTO TAX  ************
        if (!$order['taxes']) {
            $export[] = sprintf($spl, $order_date, $accnt_tax, $order['b_lastname'], $order['b_firstname'], $trns_class, 0, $order['order_id'], 'TAX', '', '', '', '', 'AUTOSTAX');
        }

        $export[] = "ENDTRNS\t";
    }

    $export[] = '';

    return true;
}

function fn_quickbooks_export_payments($orders, &$export)
{
    $exists_order_complete = false;
    $payments = array();
    $payments[] = "!TRNS\tTRNSTYPE\tDATE\tACCNT\tNAME\tAMOUNT\tPAYMETH\tDOCNUM";
    $payments[] = "!SPL\tTRNSTYPE\tDATE\tACCNT\tNAME\tAMOUNT\tDOCNUM";
    $payments[] = "!ENDTRNS\t";

    $trns = "TRNS\tPAYMENT\t%s\tUndeposited Funds\t\"%s, %s\"\t%01.2f\t%s\t%d";
    $spl = "SPL\tPAYMENT\t%s\tAccounts Receivable\t\"%s, %s\"\t%01.2f\t%d";

    foreach ($orders as $order) {

        if ($order['status'] == 'P' || $order['status'] == 'C') {
            $order_date = fn_date_format($order['timestamp'], "%m/%d/%Y");

            $payments[] = sprintf($trns, $order_date, $order['b_lastname'], $order['b_firstname'], $order['total'], $order['payment_method']['payment'], $order['order_id']);
            $payments[] = sprintf($spl, $order_date, $order['b_lastname'], $order['b_firstname'], -$order['total'], $order['order_id']);
            $payments[] = "ENDTRNS\t";

            $exists_order_complete = true;
        }
    }

    if ($exists_order_complete) {
        $payments[] = '';
        $export = array_merge($export, $payments);
    }

    return true;
}
