{if $order_info.gift_certificates}
{foreach from=$order_info.gift_certificates item="gift" key="gift_key"}
<tr {$_class}>
    <td>
        <div class="pull-left">
        <i id="on_gc_{$gift_key}" class="hand cm-combination exicon-expand"></i><i title="{__("collapse_sublist_of_items")}" id="off_gc_{$gift_key}" class="hand cm-combination hidden exicon-collapse"></i>
        </div>
        <div class="pull-left">
            {__("gift_certificate")}&nbsp;
            {include file="buttons/button.tpl" but_href="gift_certificates.print?order_id=`$order_info.order_id`&gift_cert_cart_id=`$gift_key`" but_text=__("print_card") but_role="text" but_meta="cm-new-window text-button-simple"}
            {if $gift.gift_cert_code}
            <p>{__("code")}:&nbsp;<a href="{"gift_certificates.update?gift_cert_id=`$gift.gift_cert_id`"|fn_url}">{$gift.gift_cert_code}</a></p>
            {/if}
        </div>
    </td>
    <td>{if !$gift.extra.exclude_from_calculate}{include file="common/price.tpl" value=$gift.display_subtotal}{else}{__("free")}{/if}</td>
    <td class="center">&nbsp;1</td>
    {if $order_info.use_discount}
    <td>-</td>
    {/if}
    {if $order_info.taxes && $settings.General.tax_calculation != "subtotal"}
    <td>-</td>
    {/if}
    <td class="right">&nbsp;<span>{if !$gift.extra.exclude_from_calculate}{include file="common/price.tpl" value=$gift.display_subtotal}{else}{__("free")}{/if}</span></td>
</tr>
{assign var="_colspan" value="4"}
<tr {$_class} class="row-more hidden">
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    {if $order_info.use_discount}
        {assign var="_colspan" value=$_colspan+1}
        <td>&nbsp;</td>
    {/if}
    {if $order_info.taxes && $settings.General.tax_calculation != "subtotal"}
        {assign var="_colspan" value=$_colspan+1}
        <td>&nbsp;</td>
    {/if}
    <td>&nbsp;</td>
</tr>

<tr id="gc_{$gift_key}" class="{$_class} hidden row-more row-gray">
    <td colspan="{$_colspan}">

        <table width="100%" class="table-condensed">
        <tr class="no-border">
            <td class="nowrap"><span>{__("gift_cert_to")}</span>:</td>
            <td>&nbsp;</td>
            <td class="nowrap" width="100%">{$gift.recipient}</td>
        </tr>
        <tr>
            <td class="nowrap"><span>{__("gift_cert_from")}</span>:</td>
            <td>&nbsp;</td>
            <td class="nowrap" width="100%">{$gift.sender}</td>
        </tr>
        <tr>
            <td class="nowrap"><span>{__("amount")}</span>:</td>
            <td>&nbsp;</td>
            <td class="nowrap" width="100%">{include file="common/price.tpl" value=$gift.amount}</td>
        </tr>
        <tr>
            <td class="nowrap"><span>{__("send_via")}</span>:</td>
            <td>&nbsp;</td>
            <td class="nowrap" width="100%">{if $gift.send_via == "E"}{__("email")}{else}{__("postal_mail")}{/if}</td>
        </tr>
        </table>
        {if $gift.products && $addons.gift_certificates.free_products_allow == "Y"}
        <table width="100%" class="table-condensed">
        <tr class="no-border">
            <th width="50%">{__("product")}</th>
            <th width="10%">{__("price")}</th>
            <th width="10%" class="center">{__("quantity")}</th>
            {if $order_info.use_discount}
            <th width="10%">{__("discount")}</th>
            {/if}
            {if $order_info.taxes && $settings.General.tax_calculation != "subtotal"}
            <th width="10%">{__("tax")}</th>
            {/if}
            <th class="right" width="10%">{__("subtotal")}</th>
        </tr>
        {foreach from=$order_info.products item="oi" key="sub_key"}
        {if $oi.extra.parent.certificate && $oi.extra.parent.certificate == $gift_key}
        <tr valign="top">
            <td>
                {if $oi.product}
                    {if !$oi.deleted_product}<a href="{"products.update?product_id=`$oi.product_id`"|fn_url}">{/if}{$oi.product|truncate:50:"...":true}{if !$oi.deleted_product}</a>{/if}
                {else}
                    {__("deleted_product")}
                {/if}
                {hook name="orders:product_info"}
                {if $oi.product_code}
                <p>{__("sku")}:&nbsp;{$oi.product_code}</p>
                {/if}
                {/hook}
                {if $oi.product_options}<div class="options-info">&nbsp;{include file="common/options_info.tpl" product_options=$oi.product_options}</div>{/if}
            </td>
            <td>
                {include file="common/price.tpl" value=$oi.original_price}</td>
            <td class="center">
                {$oi.amount}</td>
            {if $order_info.use_discount}
            <td>
                {if $oi.extra.discount|floatval}{include file="common/price.tpl" value=$oi.extra.discount}{else}-{/if}</td>
            {/if}
            {if $order_info.taxes && $settings.General.tax_calculation != "subtotal"}
            <td>
                {include file="common/price.tpl" value=$oi.tax_value}</td>
            {/if}
            <td class="nowrap right">
                {include file="common/price.tpl" value=$oi.display_subtotal}</td>
        </tr>
        {/if}
        {/foreach}
        </table>
        {/if}
    </td>
</tr>
{/foreach}

{/if}