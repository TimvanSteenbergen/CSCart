{if $cart.points_info.reward}
    <span class="">{__("points")}</span>
    <span class="float-right">{$cart.points_info.reward}</span>
{/if}

{if $runtime.mode == "checkout" && $cart_products && $cart.points_info.total_price && $user_info.points > 0}
    <form class="cm-ajax cm-ajax-full-render" name="point_payment_form" action="{""|fn_url}" method="post">
        <input type="hidden" name="redirect_mode" value="{$location}" />
        <input type="hidden" name="result_ids" value="checkout*,cart_status*" />

        <div class="control-group input-append reward-points">
            <input type="text" class="input-text valign cm-hint" name="points_to_use" size="40" value="{__("points_to_use")}" />
            {include file="buttons/go.tpl" but_name="checkout.point_payment" alt=__("apply")}
            <input type="submit" class="hidden" name="dispatch[checkout.point_payment]" value="" />
        </div>
    </form>

    {if $user_info.points}
        <div class="discount-info">
            <span class="caret-info"> <span class="caret-outer"></span> <span class="caret-inner"></span></span>
            <span class="block">{__("text_point_in_account")}&nbsp;{$user_info.points}&nbsp;{__("points_lower")}.</span>
            
            {if $cart.points_info.in_use.points}
                {assign var="_redirect_url" value=$config.current_url|escape:url}
                {if $use_ajax}{assign var="_class" value="cm-ajax"}{/if}
                <span class="points-in-use">
                    <span class="block">
                        {$cart.points_info.in_use.points}
                        {__("points_in_use_lower")}.
                        ({include file="common/price.tpl" value=$cart.points_info.in_use.cost})
                        {include file="buttons/button.tpl" but_href="checkout.delete_points_in_use?redirect_url=`$_redirect_url`" but_meta="delete-icon" but_role="delete" but_target_id="checkout*,cart_status*,subtotal_price_in_points"}
                    </span>
                </span>
            {/if}
        </div>
    {/if}
{/if}