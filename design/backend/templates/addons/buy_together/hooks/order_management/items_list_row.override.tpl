{if $cp.extra.buy_together}
<tr {if $cp.product_options}class="no-border"{/if}>
    <td class="center">
        <input type="checkbox" name="cart_ids[]" value="{$key}" class="checkbox cm-item" /></td>
    <td>
        <a href="{"products.update?product_id=`$cp.product_id`"|fn_url}">{$cp.product nofilter}</a></td>
    <td class="no-padding">
    {if $cp.exclude_from_calculate}
        {__("free")}
    {else}
        <table cellpadding="0" cellspacing="0" border="0" class="table-fixed" width="135">
        <col width="35" />
        <col width="100" />
        <tr>
            <td>
            <input type="hidden" name="cart_products[{$key}][stored_price]" value="N" />
            <input type="checkbox" name="cart_products[{$key}][stored_price]" value="Y" {if $cp.stored_price == "Y"}checked="checked"{/if} onclick="Tygh.$('#db_price_{$key},#manual_price_{$key}').toggle();" class="checkbox" />
            </td>
            <td class="data-block" valign="middle">
            <span {if $cp.stored_price == "Y"}class="hidden"{/if} id="db_price_{$key}">{include file="common/price.tpl" value=$cp.original_price}</span>
            <span {if $cp.stored_price != "Y"}class="hidden"{/if} id="manual_price_{$key}">{$currencies.$primary_currency.symbol nofilter}&nbsp;<input type="text" class="input-text" size="5" name="cart_products[{$key}][price]" value="{$cp.base_price}" /></span>
            </td>
        </tr>
        </table>
    {/if}
    </td>
    {if $cart.use_discount}
    <td class="no-padding">
    {if $cp.exclude_from_calculate}
        {include file="common/price.tpl" value=""}
    {else}
        {if $cart.order_id}
        <input type="hidden" name="cart_products[{$key}][stored_discount]" value="Y" />
        {$currencies.$primary_currency.symbol nofilter}&nbsp;<input type="text" class="input-text" size="5" name="cart_products[{$key}][discount]" value="{$cp.discount}" />
        {else}
        {include file="common/price.tpl" value=$cp.discount}
        {/if}
    {/if}
    </td>
    {/if}
    <td class="center">
        <input type="hidden" name="cart_products[{$key}][product_id]" value="{$cp.product_id}" />
        {if $cp.exclude_from_calculate}
        <input type="hidden" size="3" name="cart_products[{$key}][amount]" value="{$cp.amount}" />
        {/if}
        <input class="input-text" type="text" size="3" name="cart_products[{$key}][amount]" value="{$cp.amount}" {if $cp.exclude_from_calculate}disabled="disabled"{/if} /></td>
    <td class="nowrap">
        {capture name="tools_items"}
        <li><a class="cm-confirm" href="{"order_management.delete?cart_id=`$key`"|fn_url}">{__("delete")}</a></li>
        {/capture}
        {include file="common/table_tools_list.tpl" prefix=$cp.product_id tools_list=$smarty.capture.tools_items href="products.update?product_id=`$cp.product_id`"}
    </td>
</tr>
{if $cp.product_options}
<tr>
    <td>&nbsp;</td>
    <td colspan="{if $cart.use_discount}5{else}4{/if}">
        <div class="float-left">{include file="views/products/components/select_product_options.tpl" product_options=$cp.product_options name="cart_products" id=$key use_exceptions="Y" product=$cp additional_class="option-item"}</div>
        <div id="warning_{$key}" class="float-left notification-title-e hidden">&nbsp;&nbsp;&nbsp;{__("nocombination")}</div>

    </td>
</tr>
{/if}

<tr>
    <td>&nbsp;</td>
    <td colspan="{if $cart.use_discount}5{else}4{/if}">
        <strong>{__("buy_together")}</strong>
    </td>
</tr>

{foreach from=$cart_products item="_product" key="k"}
    {if $_product.extra.parent.buy_together == $key}
        <tr class="no-border">
            <td>
                <input type="hidden" name="cart_products[{$k}][stored_price]" value="Y" />
                <input type="hidden" name="cart_products[{$k}][product_id]" value="{$_product.product_id}" />
                <input type="hidden" name="cart_products[{$k}][price]" value="{$_product.price}" />
                {if $_product.exclude_from_calculate}
                    <input type="hidden" name="cart_products[{$k}][amount]" value="{$_product.amount}" />
                {/if}
            </td>
            <td colspan="{if $cart.use_discount}2{else}1{/if}">
                {$_product.product}
            </td>
            <td>
                <table cellpadding="0" cellspacing="0" border="0" class="table-fixed" width="135">
                <col width="35" />
                <col width="100" />
                <tr>
                    <td>
                    <input type="hidden" name="cart_products[{$k}][stored_price]" value="N" />
                    <input type="checkbox" name="cart_products[{$k}][stored_price]" value="Y" {if $_product.stored_price == "Y"}checked="checked"{/if} onclick="Tygh.$('#db_price_{$k},#manual_price_{$k}').toggle();" class="checkbox" />
                    </td>
                    <td class="data-block" valign="middle">
                    <span {if $_product.stored_price == "Y"}class="hidden"{/if} id="db_price_{$k}">{include file="common/price.tpl" value=$_product.original_price}</span>
                    <span {if $_product.stored_price != "Y"}class="hidden"{/if} id="manual_price_{$k}">{$currencies.$primary_currency.symbol nofilter}&nbsp;<input type="text" class="input-text" size="5" name="cart_products[{$k}][price]" value="{$_product.base_price}" /></span>
                    </td>
                </tr>
                </table>
            </td>
            <td colspan="2">
                {$_product.amount}
            </td>
        </tr>
        {if $_product.product_options}
        <tr>
            <td>&nbsp;</td>
            <td colspan="{if $cart.use_discount}5{else}4{/if}">
                <div class="float-left">{include file="views/products/components/select_product_options.tpl" product_options=$_product.product_options name="cart_products" id=$k use_exceptions="Y" product=$_product additional_class="option-item"}</div>
                <div id="warning_{$k}" class="float-left notification-title-e hidden">&nbsp;&nbsp;&nbsp;{__("nocombination")}</div>
            </td>
        </tr>
        {/if}
    {/if}
{/foreach}

{elseif $cp.extra.parent.buy_together}
    &nbsp;
{/if}