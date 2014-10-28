{if $order_info.gift_certificates}
{foreach from=$order_info.gift_certificates item="gift" key="gift_key"}
<tr>
    <td>
        <div class="float-right">{include file="buttons/button.tpl" but_href="gift_certificates.print?order_id=`$order_info.order_id`&gift_cert_cart_id=`$gift_key`" but_text=__("print_card") but_role="text" but_meta="cm-new-window"}</div>
        <span class="product-title">{__("gift_certificate")}</span>
        {if $gift.gift_cert_code}
        <p class="code">{__("code")}:<a href="{"gift_certificates.verify?verify_code=`$gift.gift_cert_code`"|fn_url}">{$gift.gift_cert_code}</a></p>
        {/if}
        <div class="details-block">
            <a id="sw_options_{$gift_key}" class="cm-combination details-link">{__("text_click_here")}</a>
            <div id="options_{$gift_key}" class="details-block-box hidden">
                <span class="caret-info alt"> <span class="caret-outer"></span> <span class="caret-inner"></span></span>
                <div class="details-block-field">
                    <label>{__("gift_cert_to")}:</label>
                    <span>{$gift.recipient}</span>
                </div>
                <div class="details-block-field">
                    <label>{__("gift_cert_from")}:</label>
                    <span>{$gift.sender}</span>
                </div>
                <div class="details-block-field">
                    <label>{__("amount")}:</label>
                    <span>{include file="common/price.tpl" value=$gift.amount}</span>
                </div>
                <div class="details-block-field">
                    <label>{__("send_via")}:</label>
                    <span>{if $gift.send_via == "E"}{__("email")}{else}{__("postal_mail")}{/if}</span>
                </div>
            </div>
        </div>
    </td>
    
    <td class="right nowrap">{if !$gift.extra.exclude_from_calculate}{include file="common/price.tpl" value=$gift.display_subtotal}{else}{__("free")}{/if}</td>
    <td class="center">&nbsp;1</td>
    <td class="right nowrap">{if !$gift.extra.exclude_from_calculate}{include file="common/price.tpl" value=$gift.display_subtotal}{else}{__("free")}{/if}</td>
</tr>    
{/foreach}

{/if}