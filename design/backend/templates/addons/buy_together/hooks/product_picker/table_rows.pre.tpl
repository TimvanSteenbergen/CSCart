{if $runtime.controller == "buy_together" || $extra_mode == "buy_together"}

{if $product_data.min_qty == 0 || $item.min_qty == 0}
    {assign var="min_qty" value="1"}
{else}
    {assign var="min_qty" value=$product_data.min_qty|default:$item.min_qty}
{/if}

<tr>
    <td>{$item.product_name|default:$product_data.product}</td>
    <td>{$min_qty}</td>
    <td>
        <input type="hidden" id="item_price_bt_{$item.chain_id}_{$item.chain_id}" value="{$item.price|default:$product_data.price|default:"0"}" />
        <input type="hidden" name="item_data_bt_[amount]" id="item_amount_bt_{$item.chain_id}" value="{$min_qty}" />
        {include file="common/price.tpl" value=$item.price|default:$product_data.price}
    </td>
    <td>
        <select id="item_modifier_type_bt_{$item.chain_id}_{$item.chain_id}" class="input-slarge" name="item_data[modifier_type]">
            <option value="by_fixed" {if $item.modifier_type == "by_fixed"}selected="selected"{/if}>{__("by_fixed")}</option>
            <option value="to_fixed" {if $item.modifier_type == "to_fixed"}selected="selected"{/if}>{__("to_fixed")}</option>
            <option value="by_percentage" {if $item.modifier_type == "by_percentage"}selected="selected"{/if}>{__("by_percentage")}</option>
            <option value="to_percentage" {if $item.modifier_type == "to_percentage"}selected="selected"{/if}>{__("to_percentage")}</option>
        </select>
    </td>
    <td>
        <input type="hidden" class="cm-chain-{$item.chain_id}" value="{$item.chain_id}" />
        <input type="text" name="item_data[modifier]" id="item_modifier_bt_{$item.chain_id}_{$item.chain_id}" size="4" value="{$item.modifier|default:0|round:$currencies.$primary_currency.decimals}" class="input-mini">
    </td>
    <td>{include file="common/price.tpl" value=$item.discounted_price|default:$product_data.price|default:"0" span_id="item_discounted_price_bt_`$item.chain_id`_`$item.chain_id`_"}</td>
    <td>&nbsp;</td>
</tr>
{/if}