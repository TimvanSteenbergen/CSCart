{if $cart.gift_certificates}

{assign var="c_url" value=$config.current_url|escape:url}
{foreach from=$cart.gift_certificates item="gift" key="gift_key" name="f_gift_certificates"}
    <li>
        {if !$gift.extra.exclude_from_calculate}
            <a href="{"gift_certificates.update?gift_cert_id=`$gift_key`"|fn_url}" class="product-name">{__("gift_certificate")}</a>{include file="buttons/button.tpl" but_href="gift_certificates.delete?gift_cert_id=`$gift_key`&redirect_url=`$c_url`" but_meta="delete" but_target_id="cart_status*" but_role="delete" but_name="delete_cart_item"}
        {else}
            <strong>{__("gift_certificate")}</strong>
        {/if}
        <span class="product-price">{include file="common/price.tpl" value=$gift.display_subtotal}</span>
    </li>
{/foreach}
{/if}