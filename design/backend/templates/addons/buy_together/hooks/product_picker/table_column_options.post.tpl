{if ($runtime.controller == "buy_together" || $extra_mode == "buy_together") && $product_info}
    <td>
        <input type="hidden" id="item_price_bt_{$item.chain_id}_{$delete_id}" value="{$product_info.price|default:0}" />
        {include file="common/price.tpl" value=$product_info.price}
    </td>
    <td>
        <select name="{$input_name}[modifier_type]" class="input-slarge" id="item_modifier_type_bt_{$item.chain_id}_{$delete_id}">
            <option value="by_fixed" {if $product_info.modifier_type == "by_fixed"}selected="selected"{/if}>{__("by_fixed")}</option>
            <option value="to_fixed" {if $product_info.modifier_type == "to_fixed"}selected="selected"{/if}>{__("to_fixed")}</option>
            <option value="by_percentage" {if $product_info.modifier_type == "by_percentage"}selected="selected"{/if}>{__("by_percentage")}</option>
            <option value="to_percentage" {if $product_info.modifier_type == "to_percentage"}selected="selected"{/if}>{__("to_percentage")}</option>
        </select>
    </td>
    <td>
        <input type="hidden" class="cm-chain-{$item.chain_id}" value="{$delete_id}" />
        <input type="text" name="{$input_name}[modifier]" id="item_modifier_bt_{$item.chain_id}_{$delete_id}" size="4" value="{$product_info.modifier|default:0}" class="input-mini">
    </td>
    <td>
        {include file="common/price.tpl" value=$product_info.discounted_price span_id="item_discounted_price_bt_`$item.chain_id`_`$delete_id`_"}
    </td>
    
{elseif ($runtime.controller == "buy_together" || $extra_mode == "buy_together") && $clone}
    <td>
        <input type="text" class="hidden" id="item_price_bt_{$item.chain_id}_{$ldelim}bt_id{$rdelim}" value="{$ldelim}price{$rdelim}">
        {include file="common/price.tpl" span_id="item_display_price_bt_`$item.chain_id`_`$ldelim`bt_id`$rdelim`_"}
    </td>
    <td>
        <select name="{$input_name}[modifier_type]" class="input-slarge" id="item_modifier_type_bt_{$item.chain_id}_{$ldelim}bt_id{$rdelim}">
            <option value="by_fixed">{__("by_fixed")}</option>
            <option value="to_fixed">{__("to_fixed")}</option>
            <option value="by_percentage">{__("by_percentage")}</option>
            <option value="to_percentage">{__("to_percentage")}</option>
        </select>
    </td>
    <td>
        <input type="text" class="cm-chain-{$item.chain_id} hidden" value="{$ldelim}bt_id{$rdelim}" />
        <input type="text" class="hidden" id="{$ldelim}bt_id{$rdelim}" value="{$item.chain_id}" />
        <input type="text" name="{$input_name}[modifier]" id="item_modifier_bt_{$item.chain_id}_{$ldelim}bt_id{$rdelim}" size="4" value="0" class="input-mini">
    </td>
    <td>
        {include file="common/price.tpl" span_id="item_discounted_price_bt_`$item.chain_id`_`$ldelim`bt_id`$rdelim`_"}
    </td>
{/if}