{if !"ULTIMATE:FREE"|fn_allowed_for}
    {assign var="usergroups" value="C"|fn_get_usergroups}
{/if}

<div id="content_qty_discounts" class="hidden">
    <table class="table table-middle" width="100%">
    <thead class="cm-first-sibling">
    <tr>
        <th width="5%">{__("quantity")}</th>
        <th width="20%">{__("value")}</th>
        <th width="25%">{__("type")}{include file="common/tooltip.tpl" tooltip=__("qty_discount_type_tooltip")}</th>
        {if !"ULTIMATE:FREE"|fn_allowed_for}
            <th width="25%">{__("usergroup")}</th>
        {/if}
        <th width="15%">&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    {foreach from=$product_data.prices item="price" key="_key" name="prod_prices"}
    <tr class="cm-row-item">
        <td width="5%" class="{$no_hide_input_if_shared_product}">
            {if $price.lower_limit == "1" && $price.usergroup_id == "0"}
                &nbsp;{$price.lower_limit}
            {else}
            <input type="text" name="product_data[prices][{$_key}][lower_limit]" value="{$price.lower_limit}" class="input-micro" />
            {/if}</td>
        <td width="20%" class="{$no_hide_input_if_shared_product}">
            {if $price.lower_limit == "1" && $price.usergroup_id == "0"}
                &nbsp;{if $price.percentage_discount == 0}{$price.price|default:"0.00"}{else}{$price.percentage_discount}{/if}
            {else}
            <input type="text" name="product_data[prices][{$_key}][price]" value="{if $price.percentage_discount == 0}{$price.price|default:"0.00"}{else}{$price.percentage_discount}{/if}" size="10" class="input-medium" />
            {/if}</td>
        <td width="25%" class="{$no_hide_input_if_shared_product}">
            {if $price.lower_limit == "1" && $price.usergroup_id == "0"}
                &nbsp;{if $price.percentage_discount == 0}{__("absolute")}{else}{__("percent")}{/if}
            {else}
            <select class="span3" name="product_data[prices][{$_key}][type]">
                <option value="A" {if $price.percentage_discount == 0}selected="selected"{/if}>{__("absolute")} ({$currencies.$primary_currency.symbol nofilter})</option>
                <option value="P" {if $price.percentage_discount != 0}selected="selected"{/if}>{__("percent")} (%)</option>
            </select>
            {/if}</td>
        {if !"ULTIMATE:FREE"|fn_allowed_for}
        <td width="25%" class="{$no_hide_input_if_shared_product}">
            {if $price.lower_limit == "1" && $price.usergroup_id == "0"}
                &nbsp;{__("all")}
            {else}
            <select id="usergroup_id" name="product_data[prices][{$_key}][usergroup_id]" class="span3">
                {foreach from=fn_get_default_usergroups() item="usergroup"}
                    {if $price.usergroup_id != $usergroup.usergroup_id}
                        <option value="{$usergroup.usergroup_id}">{$usergroup.usergroup}</option>
                    {else}
                        {*we should do this because there are no descriptions for default usergroups in database*}
                        {assign var="default_usergroup_name" value=$usergroup.usergroup}
                    {/if}
                {/foreach}
                {foreach from=$usergroups item="usergroup"}
                    {if $price.usergroup_id != $usergroup.usergroup_id}
                        <option value="{$usergroup.usergroup_id}">{$usergroup.usergroup}</option>
                    {/if}
                {/foreach}
                    <option value="{$price.usergroup_id}" selected="selected">{if $default_usergroup_name}{$default_usergroup_name}{else}{$price.usergroup_id|fn_get_usergroup_name}{/if}</option>
            </select>
            {include file="buttons/update_for_all.tpl" display=$show_update_for_all object_id="price_`$_key`" name="update_all_vendors[prices][`$_key`]"}
            {assign var="default_usergroup_name" value=""}
            {/if}</td>
        {/if}
        <td width="15%" class="nowrap {$no_hide_input_if_shared_product} right">
            {if $price.lower_limit == "1" && $price.usergroup_id == "0"}
            &nbsp;{else}
            {include file="buttons/clone_delete.tpl" microformats="cm-delete-row" no_confirm=true}
            {/if}
        </td>
    </tr>
    {/foreach}
    {math equation="x+1" x=$_key|default:0 assign="new_key"}
    <tr class="{cycle values="table-row , " reset=1}{$no_hide_input_if_shared_product}" id="box_add_qty_discount">
        <td width="5%">
            <input type="text" name="product_data[prices][{$new_key}][lower_limit]" value="" class="input-micro" /></td>
        <td width="20%">
            <input type="text" name="product_data[prices][{$new_key}][price]" value="0.00" size="10" class="input-medium" /></td>
        <td width="25%">
        <select class="span3" name="product_data[prices][{$new_key}][type]">
            <option value="A" selected="selected">{__("absolute")} ({$currencies.$primary_currency.symbol nofilter})</option>
            <option value="P">{__("percent")} (%)</option>
        </select></td>
        {if !"ULTIMATE:FREE"|fn_allowed_for}
        <td width="25%">
            <select id="usergroup_id" name="product_data[prices][{$new_key}][usergroup_id]" class="span3">
                {foreach from=fn_get_default_usergroups() item="usergroup"}
                    <option value="{$usergroup.usergroup_id}">{$usergroup.usergroup}</option>
                {/foreach}
                {foreach from=$usergroups item="usergroup"}
                    <option value="{$usergroup.usergroup_id}">{$usergroup.usergroup}</option>
                {/foreach}
            </select>
            {include file="buttons/update_for_all.tpl" display=$show_update_for_all object_id="price_`$new_key`" name="update_all_vendors[prices][`$new_key`]"}
        </td>
        {/if}
        <td width="15%" class="right">
            {include file="buttons/multiple_buttons.tpl" item_id="add_qty_discount"}
        </td>
    </tr>
    </tbody>
    </table>

</div>