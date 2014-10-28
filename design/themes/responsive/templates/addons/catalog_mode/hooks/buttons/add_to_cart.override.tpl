{if ''|fn_catalog_mode_enabled == 'Y'}
    {if $product.buy_now_url != ''}
        {include file="buttons/button.tpl" but_id=$but_id but_text=__("buy_now") but_href=$product.buy_now_url but_role=$but_role|default:"text" but_meta="ty-btn__primary" but_name=""}
    {elseif $addons.catalog_mode.add_to_cart_empty_buy_now_url != 'Y'}
		&nbsp;
    {/if}
{/if}
