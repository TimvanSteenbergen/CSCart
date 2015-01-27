{math equation="rand()" assign="rnd"}
{assign var="data_id" value="`$data_id`_`$rnd`"}
{assign var="view_mode" value=$view_mode|default:"mixed"}
{assign var="start_pos" value=$start_pos|default:0}
{script src="js/tygh/picker.js"}

{if $item_ids && !$item_ids|is_array && $type != "table"}
        {assign var="item_ids" value=","|explode:$item_ids}
{/if}

{if $view_mode != "list"}
    {if $placement == 'right'}
        <div class="clearfix">
            <div class="pull-right">
    {/if}

    {if $type != "single"}
    <a data-ca-external-click-id="opener_picker_{$data_id}" class="cm-external-click btn {$meta}"><i class="icon-plus"></i> {__("add_product")}</a>
    {/if}

    {if $placement == 'right'}
            </div>
        </div>
    {/if}
{/if}

{if $view_mode != "button"}
{if $type == "links"}
    <input type="hidden" id="p{$data_id}_ids" name="{$input_name}" value="{if $item_ids}{","|implode:$item_ids}{/if}" />
    {capture name="products_list"}
    <table class="table table-middle">
    <thead>
    <tr>
        {if $positions}<th>{__("position_short")}</th>{/if}
        <th width="100%">{__("name")}</th>
        <th>&nbsp;</th>
        <th>&nbsp;</th>
    </tr>
    </thead>
    <tbody id="{$data_id}" class="{if !$item_ids}hidden{/if} cm-picker-product">
    {include file="pickers/products/js.tpl" clone=true product="`$ldelim`product`$rdelim`" root_id=$data_id delete_id="`$ldelim`delete_id`$rdelim`" type="product" position_field=$positions position="0"}
    {if $item_ids}
    {foreach from=$item_ids item="product" name="items"}
        {include file="pickers/products/js.tpl" product_id=$product product=$product|fn_get_product_name|default:__("deleted_product") root_id=$data_id delete_id=$product type="product" first_item=$smarty.foreach.items.first position_field=$positions position=$smarty.foreach.items.iteration+$start_pos}
    {/foreach}
    {/if}
    </tbody>
    <tbody id="{$data_id}_no_item"{if $item_ids} class="hidden"{/if}>
    <tr class="no-items">
        <td colspan="{if $positions}4{else}3{/if}"><p>{$no_item_text|default:__("no_items") nofilter}</p></td>
    </tr>
    </tbody>
    </table>
    {/capture}
    {if $picker_view}
        <div class="shift-button">
        {include file="common/popupbox.tpl" id="inner_`$data_id`" link_text=$item_ids|count act="link" content=$smarty.capture.products_list text=__("editing_defined_products") picker_meta="cm-bg-close" method="GET" no_icon_link=true}{__("defined_items")}
        </div>
    {else}
        {$smarty.capture.products_list nofilter}
    {/if}
{elseif $type == "table"}
    {if !isset($display)}
        {assign var="display" value="options"}
    {/if}
    <table class="table table-middle">
    <thead>
    <tr>
        <th width="80%">{__("name")}</th>
        <th class="center">{__("quantity")}</th>
        {hook name="product_picker:table_header"}
        {/hook}
        <th>&nbsp;</th>
    </tr>
    </thead>
    <tbody id="{$data_id}" class="{if !$item_ids}hidden {/if}cm-picker{if $display}-options{/if}">
    {hook name="product_picker:table_rows"}
    {if $item_ids}
    {foreach from=$item_ids item="product" key="product_id"}
        {if $display}
            {capture name="product_options"}
                {assign var="prod_opts" value=$product.product_id|fn_get_product_options}
                {if $prod_opts && !$product.product_options}
                    <span>{__("options")}: </span>&nbsp;{__("any_option_combinations")}
                {elseif $product.product_options}
                    {if $product.product_options_value}
                        {include file="common/options_info.tpl" product_options=$product.product_options_value}
                    {else}
                        {include file="common/options_info.tpl" product_options=$product.product_options|fn_get_selected_product_options_info}
                    {/if}
                {/if}
            {/capture}
        {/if}
        {if $product.product}
            {assign var="product_name" value=$product.product}
        {else}
            {assign var="product_name" value=$product.product_id|fn_get_product_name|default:__("deleted_product")}
        {/if}
        {include file="pickers/products/js.tpl" product=$product_name root_id=$data_id delete_id=$product_id input_name="`$input_name`[`$product_id`]" amount=$product.amount amount_input="text" type="options" options=$smarty.capture.product_options options_array=$product.product_options product_id=$product.product_id product_info=$product}
    {/foreach}
    {/if}
    {/hook}
    {include file="pickers/products/js.tpl" clone=true product="`$ldelim`product`$rdelim`" root_id=$data_id delete_id="`$ldelim`delete_id`$rdelim`" input_name="`$input_name`[`$ldelim`product_id`$rdelim`]" amount="1" amount_input="text" type="options" options="`$ldelim`options`$rdelim`" product_id=""}
    </tbody>
    <tbody id="{$data_id}_no_item"{if $item_ids} class="hidden"{/if}>
    <tr class="no-items">
        <td colspan="{$colspan|default:"3"}"><p>{$no_item_text|default:__("no_items") nofilter}</p></td>
    </tr>
    </tbody>
    </table>
{elseif $type == "single"}
<div class="cm-display-radio" id="{$data_id}">
    <input id="{if $input_id}{$input_id}{else}c{$data_id}_ids{/if}" type="hidden" class="cm-picker-value" name="{$input_name}" value="{if $item_ids|is_array}{","|implode:$item_ids}{/if}" />
    <div class="input-append choose-input">
        {include file="pickers/products/js.tpl" product_id="" holder=$data_id hide_input=$hide_input input_name=$input_name hide_link=$hide_link hide_delete_button=$hide_delete_button type="single"}
        {$smarty.capture.add_buttons nofilter}
    </div>
</div>
{/if}
{/if}
{if $view_mode != "list"}
    <div class="hidden">
        {if $extra_var}
            {assign var="extra_var" value=$extra_var|escape:url}
        {/if}
        {if !$no_container}<div class="buttons-container">{/if}{if $picker_view}[{/if}
            {include file="buttons/button.tpl" but_id="opener_picker_`$data_id`" but_href="products.picker?display=`$display`&company_id=`$company_id`&company_ids=`$company_ids`&picker_for=`$picker_for`&extra=`$extra_var`&checkbox_name=`$checkbox_name`&aoc=`$aoc`&data_id=`$data_id`"|fn_url but_text=$but_text|default:__("add_products") but_role="add" but_target_id="content_`$data_id`" but_meta="cm-dialog-opener"}
        {if $picker_view}]{/if}{if !$no_container}</div>{/if}
        <div class="hidden" id="content_{$data_id}" title="{$but_text|default:__("add_products")}">
        </div>
    </div>
{/if}