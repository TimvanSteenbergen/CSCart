{if $cart.points_info.reward}
    <div class="ty-reward-points__info clearfix">
        <span>{__("points")}</span>
        <span class="ty-float-right">{$cart.points_info.reward}</span>
    </div>
{/if}

{if $runtime.mode == "checkout" && $cart_products && $cart.points_info.total_price && $user_info.points > 0}
    <form class="cm-ajax cm-ajax-full-render" name="point_payment_form" action="{""|fn_url}" method="post">
        <input type="hidden" name="redirect_mode" value="{$location}" />
        <input type="hidden" name="result_ids" value="checkout*,cart_status*" />

        <div class="ty-discount-coupon__control-group ty-reward-points__coupon ty-input-append ty-inline-block">
            <input type="text" class="ty-input-text ty-valign cm-hint" name="points_to_use" size="40" value="{__("points_to_use")}" />
            {include file="buttons/go.tpl" but_name="checkout.point_payment" alt=__("apply")}
            <input type="submit" class="hidden" name="dispatch[checkout.point_payment]" value="" />
        </div>
    </form>

    {if $user_info.points}
        <div class="ty-discount-info">
            <span class="ty-caret-info"><span class="ty-caret-outer"></span><span class="ty-caret-inner"></span></span>
            <span class="ty-reward-points__txt-point">{__("text_point_in_account")}&nbsp;{$user_info.points}&nbsp;{__("points_lower")}.</span>
            
            {if $cart.points_info.in_use.points}
                {assign var="_redirect_url" value=$config.current_url|escape:url}
                {if $use_ajax}{assign var="_class" value="cm-ajax"}{/if}
                <span class="ty-reward-points__points-in-use">
                        {$cart.points_info.in_use.points}
                        {__("points_in_use_lower")}.
                        ({include file="common/price.tpl" value=$cart.points_info.in_use.cost})
                        {include file="buttons/button.tpl" but_href="checkout.delete_points_in_use?redirect_url=`$_redirect_url`" but_meta="ty-reward-points__delete-icon" but_role="delete" but_target_id="checkout*,cart_status*,subtotal_price_in_points"}
                </span>
            {/if}
        </div>
    {/if}
{/if}