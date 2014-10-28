{if $cart.points_info.in_use}
    <li>
        {assign var="_redirect_url" value=$config.current_url|escape:url}
            {if $use_ajax}{assign var="_class" value="cm-ajax"}{/if}
        <span class="checkout-item-title">{__("points_in_use")}&nbsp;({$cart.points_info.in_use.points}&nbsp;{__("points")}){if $settings.General.checkout_style != "multi_page"}{include file="buttons/button.tpl" but_href="checkout.delete_points_in_use?redirect_url=`$_redirect_url`" but_meta="delete-icon" but_role="delete" but_target_id="checkout_totals,subtotal_price_in_points,checkout_steps`$additional_ids`"}{/if}</span>
    </li>
{/if}

{if $cart.points_info.reward}
    <li>
        <span class="checkout-item-title">{__("points")}</span>
        <span class="checkout-item-value discount-price">+{$cart.points_info.reward}</span>
    </li>
{/if}