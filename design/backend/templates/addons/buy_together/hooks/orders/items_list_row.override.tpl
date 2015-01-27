{if $oi.extra.buy_together}
    {assign var="conf_price" value=$oi.price|default:"0"}
    {assign var="conf_subtotal" value=$oi.display_subtotal|default:"0"}
    {assign var="conf_discount" value=$oi.extra.discount|default:"0"}
    {assign var="conf_tax" value=$oi.tax_value|default:"0"}


    {assign var="_colspan" value=4}
    {assign var="c_oi" value=$oi}
    {foreach from=$order_info.products item="sub_oi"}
        {if $sub_oi.extra.parent.buy_together && $sub_oi.extra.parent.buy_together == $oi.cart_id}
            {capture name="is_conf"}1{/capture}
            {math equation="item_price * amount + conf_price" item_price=$sub_oi.price|default:"0" amount=$sub_oi.extra.min_qty|default:"1" conf_price=$conf_price assign="conf_price"}
            {math equation="discount + conf_discount" discount=$sub_oi.extra.discount|default:"0" conf_discount=$conf_discount assign="conf_discount"}
            {math equation="tax + conf_tax" tax=$sub_oi.tax_value|default:"0" conf_tax=$conf_tax assign="conf_tax"}
            {math equation="subtotal + conf_subtotal" subtotal=$sub_oi.display_subtotal conf_subtotal=$conf_subtotal|default:$oi.display_subtotal assign="conf_subtotal"}
        {/if}
    {/foreach}

    {assign var="product_key" value="gc_"|uniqid}

    <tr valign="top">
        <td>
            <div class="pull-left">
                <i id="on_{$product_key}" class="hand cm-combination exicon-expand"></i>
                <i title="{__("collapse_sublist_of_items")}" id="off_{$product_key}" class="hand cm-combination hidden exicon-collapse"></i>
            </div>
            <a href="{"products.update?product_id=`$oi.product_id`"|fn_url}">{$oi.product}</a>
            {hook name="orders:product_info"}
            {if $oi.product_code}</p>{__("sku")}:&nbsp;{$oi.product_code}</p>{/if}
            {/hook}

            {if $oi.product_options}<div class="options-info">{include file="common/options_info.tpl" product_options=$oi.product_options}</div>{/if}
        </td>
        <td class="nowrap">{include file="common/price.tpl" value=$conf_price|default:0}</td>
        <td class="center">{$oi.amount}
            {if $settings.General.use_shipments == "Y" && $oi.shipped_amount > 0}
                <p><span class="small-note">(<strong>{$oi.shipped_amount}</strong>&nbsp;{__("shipped")})</span></p>
            {/if}
        </td>
        {if $order_info.use_discount}
        {assign var="_colspan" value=$_colspan+1}
        <td class="right nowrap">
            {include file="common/price.tpl" value=$conf_discount|default:0}</td>
        {/if}
        {if $order_info.taxes && $settings.General.tax_calculation != "subtotal"}
        {assign var="_colspan" value=$_colspan+1}
        <td class="nowrap">
            {include file="common/price.tpl" value=$conf_tax|default:0}</td>
        {/if}
        <td class="right"><strong>{include file="common/price.tpl" value=$conf_subtotal|default:0}</strong></td>
    </tr>
    {if $smarty.capture.is_conf}
        <tr class="row-more row-gray hidden" id="{$product_key}" valign="top">
            <td colspan="{$_colspan}">
                <p>{__("buy_together")}:</p>
                <table width="100%" class="table-condensed">
                {foreach from=$order_info.products item="oi" key="sub_key"}
                    {if $oi.extra.parent.buy_together && $oi.extra.parent.buy_together == $c_oi.cart_id}
                    <tr>
                        <td width="50%">
                            <a href="{"products.update?product_id=`$oi.product_id`"|fn_url}">{$oi.product|truncate:50:"...":true}</a>&nbsp;
                            {if $oi.product_code}
                                <p>{__("sku")}:&nbsp;{$oi.product_code}</p>
                            {/if}
                            {hook name="orders:product_info"}
                            {if $oi.product_options}<div style="padding-top: 1px; padding-bottom: 2px;">&nbsp;{include file="common/options_info.tpl" product_options=$oi.product_options}</div>{/if}
                            {/hook}
                        </td>
                        <td width="10%" class="center nowrap">
                            {include file="common/price.tpl" value=$oi.price}</td>
                        <td width="10%" class="center nowrap">
                            {$oi.amount}
                            {if $settings.General.use_shipments == "Y" && $oi.shipped_amount}
                                <p><span class="small-note">(<strong>{$oi.shipped_amount}</strong>&nbsp;{__("shipped")})</span></p>
                            {/if}
                        </td>
                        {if $order_info.use_discount}
                            <td width="5%" class="right nowrap">
                                {if $oi.extra.discount|floatval}{include file="common/price.tpl" value=$oi.extra.discount}{else}-{/if}</td>
                        {/if}
                        {if $order_info.taxes && $settings.General.tax_calculation != "subtotal"}
                            <td width="10%" class="center nowrap">
                                {include file="common/price.tpl" value=$oi.tax_value}</td>
                        {/if}
                        <td width="10%" class="right nowrap">
                            {include file="common/price.tpl" value=$oi.display_subtotal}</td>
                    </tr>
                    {/if}
                {/foreach}
                </table>
            </td>
        </tr>
    </tr>
    {/if}
{/if}