<?php

namespace Twigmo\Core\Functions;

use Tygh\Languages\Values as LanguageValues;

class Lang
{
    private static $needed_lang_vars = array(
        'account_name',
        'add_to_cart',
        'address',
        'address_2',
        'apply_for_vendor_account',

        'billing_address',
        'billing_shipping_address',

        'cannot_proccess_checkout_without_payment_methods',
        'card_name',
        'card_number',
        'cardholder_name',
        'cart',
        'cart_contents',
        'cart_is_empty',
        'catalog',
        'checkout',
        'checkout_as_guest',
        'checkout_terms_n_conditions',
        'checkout_terms_n_conditions_alert',
        'city',
        'company',
        'confirm_password',
        'contact_information',
        'contact_us_for_price',
        'continue',
        'country',
        'coupon',
        'credit_card',

        'date',
        'date_of_birth',
        'deleted',
        'description',
        'details',
        'discount',

        'email',
        'enter_your_price',
        'error_passwords_dont_match',
        'error_validator_message',

        'fax',
        'features',
        'files',
        'first_name',
        'free',
        'free_shipping',

        'gift_certificate',

        'home',

        'in_stock',
        'inc_tax',
        'including_tax',
        'included',
        'including_discount',

        'language',
        'last_name',
        'loading',

        'my_points',

        'na',
        'no',
        'no_items',
        'notes',

        'options',
        'or_use',
        'order',
        'order_discount',
        'order_id',
        'order_info',
        'orders',

        'password',
        'payment_information',
        'payment_method',
        'payment_surcharge',
        'phone',
        'place_order',
        'points',
        'points_in_use',
        'price',
        'price_in_points',
        'product',
        'product_coming_soon',
        'product_coming_soon_add',
        'products',
        'profile',
        'promo_code',
        'promo_code_or_certificate',

        'quantity',

        'reason',
        'register',
        'reward_points',
        'reward_points_log',

        'search',
        'select_country',
        'select_state',
        'shipping',
        'shipping_address',
        'shipping_cost',
        'shipping_method',
        'shipping_methods',
        'sign_in',
        'sign_out',
        'sku',
        'state',
        'status',
        'submit',
        'subtotal',
        'successful_login',
        'summary',

        'tax',
        'tax_exempt',
        'taxes',
        'text_cart_min_qty',
        'text_combination_out_of_stock',
        'text_decrease_points_in_use',
        'text_email_sent',
        'text_fill_the_mandatory_fields',
        'text_min_order_amount_required',
        'text_min_products_amount_required',
        'text_no_matching_results_found',
        'text_no_orders',
        'text_no_payments_needed',
        'text_no_products',
        'text_no_shipping_methods',
        'text_order_backordered',
        'text_out_of_stock',
        'text_point_in_account',
        'text_points_in_order',
        'text_profile_is_created',
        'text_qty_discounts',
        'title',
        'total',

        'update_profile',
        'update_profile_notification',
        'url',
        'user_account_info',
        'username',

        'vendor',
        'view_cart',

        'yes',

        'zip_postal_code'
    );

    public static function getCustomerLangVars($lang_code = CART_LANGUAGE)
    {
        $lang_vars = array_diff(self::getAllLangVars($lang_code), self::getAdminLangVars($lang_code));
        // We have to remove the twg_ prefix for these langvars
        $remove_prefix_for = array('is_logged_in', 'review_and_place_order');
        $prefix = 'twg_';
        foreach ($remove_prefix_for as $lang_var) {
            $with_prefix = $prefix . $lang_var;
            if (!isset($lang_vars[$with_prefix])) {
                continue;
            }
            $lang_vars[$lang_var] = $lang_vars[$with_prefix];
            unset($lang_vars[$with_prefix]);
        }
        return $lang_vars;
    }

    public static function getNeededLangvars()
    {
        return self::$needed_lang_vars;
    }

    /**
    * Returns only active languages list (as lang_code => array(name, lang_code, status)
    *
    * @param bool $include_hidden if true get hiddenlanguages too
    * @return array Languages list
    */
    public static function getLanguages($include_hidden = false)
    {
        $language_condition =
            $include_hidden ?
                "WHERE status <> 'D'" :
                "WHERE status = 'A'";

        return db_get_hash_array(
            "SELECT lang_code, name FROM ?:languages ?p",
            'lang_code',
            $language_condition
        );
    }

    public static function getAllLangVars($lang_code = CART_LANGUAGE)
    {
        return self::getLangvarsByPrefix('twg', $lang_code);
    }

    private static function getAdminLangVars($lang_code = CART_LANGUAGE)
    {
        return self::getLangvarsByPrefix('twgadmin', $lang_code);
    }

    public static function getLangvarsByPrefix($prefix, $lang_code = CART_LANGUAGE)
    {
        if (class_exists('Tygh\Languages\Values')) {
            return LanguageValues::getLangVarsByPrefix($prefix, $lang_code);
        } else {
            return fn_get_lang_vars_by_prefix($prefix, $lang_code);
        }
    }
}
