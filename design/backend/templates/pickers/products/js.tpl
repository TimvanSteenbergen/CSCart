{if "ULTIMATE"|fn_allowed_for && $product_id && $runtime.company_id}
    {assign var="product_data" value=$product_id|fn_get_product_data:$smarty.session.auth:$smarty.const.CART_LANGUAGE:"?:products.company_id,?:product_descriptions.product":false:false:false:false:false:false:true}
    {if $product_data.company_id != $runtime.company_id}
        {assign var="product" value=$product_data.product|default:$product}
        {if $owner_company_id && $owner_company_id != $runtime.company_id}
            {assign var="show_only_name" value=true}
        {/if}
    {/if}
{/if}

{if $type == "options"}
<tr {if !$clone}id="{$root_id}_{$delete_id}" {/if}class="cm-js-item{if $clone} cm-clone hidden{/if}">
{if $position_field}<td><input type="text" name="{$input_name}[{$delete_id}]" value="{math equation="a*b" a=$position b=10}" size="3" class="input-text-short" {if $clone}disabled="disabled"{/if} /></td>{/if}
<td>
    {$product}{if $show_only_name}{include file="views/companies/components/company_name.tpl" object=$product_data}{/if}
    {if $options}
        <br>
        <small>{$options nofilter}</small>
    {/if}
    {if $options_array|is_array}
        {foreach from=$options_array item="option" key="option_id"}
        <input type="hidden" name="{$input_name}[product_options][{$option_id}]" value="{$option}"{if $clone} disabled="disabled"{/if} />
        {/foreach}
    {/if}
    {if $product_id}
        <input type="hidden" name="{$input_name}[product_id]" value="{$product_id}"{if $clone} disabled="disabled"{/if} />
    {/if}
    {if $amount_input == "hidden"}
        <input type="hidden" name="{$input_name}[amount]" value="{$amount}"{if $clone} disabled="disabled"{/if} />
    {/if}
</td>
    {if $amount_input == "text"}
<td class="center">
    {if $show_only_name}
        {$amount}
    {else}
        <input type="text" name="{$input_name}[amount]" value="{$amount}" size="3" class="input-micro"{if $clone} disabled="disabled"{/if} />
    {/if}
</td>
    {/if}

    {hook name="product_picker:table_column_options"}
    {/hook}
<td class="nowrap">
    {if !$hide_delete_button && !$show_only_name}
        {capture name="tools_list"}
            <li>{btn type="list" text=__("delete") onclick="Tygh.$.cePicker('delete_js_item', '{$root_id}', '{$delete_id}', 'p'); return false;"}</li>
        {/capture}
        <div class="hidden-tools">
            {dropdown content=$smarty.capture.tools_list}
        </div>
    {else}&nbsp;{/if}
</td>
</tr>

{elseif $type == "product"}
    <tr {if !$clone}id="{$root_id}_{$delete_id}" {/if}class="cm-js-item{if $clone} cm-clone hidden{/if}">
        {if $position_field}<td><input type="text" name="{$input_name}[{$delete_id}]" value="{math equation="a*b" a=$position b=10}" size="3" class="input-text-short" {if $clone}disabled="disabled"{/if} /></td>{/if}
        <td>{if !$show_only_name}<a href="{"products.update?product_id=`$delete_id`"|fn_url}">{$product nofilter}</a>{else}{$product nofilter} {include file="views/companies/components/company_name.tpl" object=$product_data}{/if}</td>
        <td>&nbsp;</td>
        <td class="nowrap">{if !$hide_delete_button && !$show_only_name}
            {capture name="tools_list"}
                <li>{btn type="list" text=__("edit") href="products.update?product_id=`$delete_id`"}</li>
                <li>{btn type="list" text=__("remove") onclick="Tygh.$.cePicker('delete_js_item', '{$root_id}', '{$delete_id}', 'p'); return false;"}</li>
            {/capture}
            <div class="hidden-tools">
                {dropdown content=$smarty.capture.tools_list}
            </div>
        {/if}</td>
    </tr>

{elseif $type == "single"}
<span {if !$clone}id="{$holder}_{$product_id}" {/if}class="cm-js-item {if $clone} cm-clone hidden{/if}">
    {if !$first_item && $single_line}<span class="cm-comma{if $clone} hidden{/if}">,&nbsp;&nbsp;</span>{/if}

    <div class="input-append">
    <input class="cm-picker-value-description {$extra_class}" type="text" value="{$product}" {if $display_input_id}id="{$display_input_id}"{/if} size="10" name="product_name" readonly="readonly" {$extra} id="appendedInputButton">

    {assign var="_but_text" value="<i class='icon-plus'></i>"}
    {assign var="_but_role" value="icon"}

    {include file="buttons/button.tpl" but_id="opener_picker_`$data_id`" but_href="products.picker?display=radio&company_ids=`$company_ids`&picker_for=`$picker_for`&extra=`$extra_var`&checkbox_name=`$checkbox_name`&except_id=`$except_id`&data_id=`$data_id``$extra_url`"|fn_url but_text=$_but_text but_role=$_but_role but_icon=$_but_icon but_target_id="content_`$data_id`" but_meta="`$but_meta` cm-dialog-opener add-on btn"}

    </div>
    </span>
{/if}
