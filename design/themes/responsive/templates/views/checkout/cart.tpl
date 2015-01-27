{script src="js/tygh/exceptions.js"}

{if !$cart|fn_cart_is_empty}
    {include file="views/checkout/components/cart_content.tpl"}
{else}
    <p class="ty-no-items">{__("text_cart_empty")}</p>

    <div class="buttons-container wrap">
        {include file="buttons/continue_shopping.tpl" but_href=$continue_url|fn_url but_role="submit"}
    </div>
{/if}