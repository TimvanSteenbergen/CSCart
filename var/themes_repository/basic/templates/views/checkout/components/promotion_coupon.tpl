<div class="code-input discount-coupon">
    {if $cart|fn_display_promotion_input_field}
    <div>
        <form class="cm-ajax cm-ajax-force cm-ajax-full-render" name="coupon_code_form{$position}" action="{""|fn_url}" method="post">
            <input type="hidden" name="result_ids" value="checkout*,cart_status*,cart_items,payment-methods" />
            <input type="hidden" name="redirect_url" value="{$config.current_url}" />

            {hook name="checkout:discount_coupons"}
                <div class="control-group input-append">
                    <label for="coupon_field{$position}" class="hidden cm-required">{__("promo_code")}</label>
                    <input type="text" class="input-text cm-hint" id="coupon_field{$position}" name="coupon_code" size="40" value="{__("promo_code")}" />
                    {include file="buttons/go.tpl" but_name="checkout.apply_coupon" alt=__("apply")}
                </div>
            {/hook}
        </form>
    </div>
    {/if}

    {hook name="checkout:applied_discount_coupons"}
        {capture name="promotion_info"}
            {hook name="checkout:applied_coupons_items"}
                {foreach from=$cart.coupons item="coupon" key="coupon_code"}
                <li>
                    <span>{__("coupon")} "{$coupon_code}"
                    {assign var="_redirect_url" value=$config.current_url|escape:url}
                    {assign var="coupon_code" value=$coupon_code|escape:url}
                    {include file="buttons/button.tpl" but_href="checkout.delete_coupon?coupon_code=`$coupon_code`&redirect_url=`$_redirect_url`" but_role="delete" but_meta="cm-ajax cm-ajax-full-render" but_target_id="checkout*,cart_status*,cart_items"}
                    </span>
                </li>
                {/foreach}
                {if $cart.applied_promotions}
                    <li>
                        {include file="views/checkout/components/applied_promotions.tpl"}
                    </li>
                {/if}
            {/hook}
        {/capture}

        {if $smarty.capture.promotion_info|trim}
            <ul class="coupon-items discount-info">
                <li class="caret-info"> <span class="caret-outer"></span> <span class="caret-inner"></span></li>
                {$smarty.capture.promotion_info nofilter}
            </ul>
        {/if}
    {/hook}
</div>
