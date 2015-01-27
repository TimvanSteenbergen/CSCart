{if $location == "cart" && $cart.shipping_required == true && $settings.General.estimate_shipping_cost == "Y"}
    {capture name="shipping_estimation"}
        <i class="ty-cart-total__icon-estimation ty-icon-flight"></i><a id="opener_shipping_estimation_block" class="cm-dialog-opener cm-dialog-auto-size ty-cart-total__a-estimation" data-ca-target-id="shipping_estimation_block" href="{"checkout.cart"|fn_url}" rel="nofollow">{if $cart.shipping}{__("change")}{else}{__("calculate")}{/if}</a>
    {/capture}
    <div class="hidden" id="shipping_estimation_block" title="{__("calculate_shipping_cost")}">
        <div class="ty-cart-content__estimation">
            {include file="views/checkout/components/shipping_estimation.tpl" location="popup" result_ids="shipping_estimation_link"}
        </div>
    </div>
{/if}
<div class="ty-cart-total">
    <div class="ty-cart-total__wrapper clearfix" id="checkout_totals">
        {if $cart_products}
            <div class="ty-coupons__container">
                {if $cart.has_coupons}
                    {include file="views/checkout/components/promotion_coupon.tpl"}
                {/if}
                    
                {hook name="checkout:payment_extra"}
                {/hook}
                </div>
        {/if}
        
        {hook name="checkout:payment_options"}
        {/hook}
        
        {include file="views/checkout/components/checkout_totals_info.tpl"}
        <div class="clearfix"></div>
        <ul class="ty-cart-statistic__total-list">
            <li class="ty-cart-statistic__item ty-cart-statistic__total">
                <span class="ty-cart-statistic__total-title">{__("total_cost")}</span>
                <span class="ty-cart-statistic__total-value">{include file="common/price.tpl" value=$_total|default:$smarty.capture._total|default:$cart.total span_id="cart_total" class="ty-price"}</span>
            </li>
        </ul>
    <!--checkout_totals--></div>
</div>
