{assign var="view_mode" value=$view_mode|default:"mixed"}
{if !$display}
    {assign var="display" value="options"}
{/if}

{script src="js/tygh/picker.js"}

{if $view_mode != "button"}
{if $type == "table"}
    <p id="{$data_id}_no_item" class="no-items{if $item_ids} hidden{/if}">{$no_item_text|default:__("no_items") nofilter}</p>

    <table id="{$data_id}" class="table table-width{if !$item_ids} hidden{/if} cm-picker-options">
    <tr>
        <th style="width: 80%">{__("name")}</th>
        <th>{__("quantity")}</th>
    </tr>
    {include file="pickers/products/js.tpl" clone=true options="`$ldelim`options`$rdelim`" root_id=$data_id product="`$ldelim`product`$rdelim`" delete_id="`$ldelim`delete_id`$rdelim`" amount=1 amount_input="text" input_name="`$input_name`[`$ldelim`product_id`$rdelim`]"}
    {if $item_ids}
        {foreach from=$item_ids item="product" key="product_id"}
            {capture name="product_options"}
                {assign var="prod_opts" value=$product.product_id|fn_get_product_options}
                {if $prod_opts && !$product.product_options}
                    <strong>{__("options")}: </strong>&nbsp;{__("any_option_combinations")}
                {else}
                    {if $product.product_options_value}
                        {include file="common/options_info.tpl" product_options=$product.product_options_value}
                    {else}
                        {include file="common/options_info.tpl" product_options=$product.product_options|fn_get_selected_product_options_info}
                    {/if}
                {/if}
            {/capture}
            {if $product.product}
                {assign var="product_name" value=$product.product}
            {else}
                {assign var="product_name" value=$product.product_id|fn_get_product_name|escape|default:__("deleted_product")}
            {/if}
            {include file="pickers/products/js.tpl" options=$smarty.capture.product_options root_id=$data_id product=$product_name delete_id=$product_id product_id=$product.product_id amount=$product.amount amount_input="text" input_name="`$input_name`[`$product.product_id`]" options_array=$product.product_options}
        {/foreach}
    {/if}
    </table>
{/if}
{/if}

{if $view_mode != "list"}

    {if $extra_var}
        {assign var="extra_var" value=$extra_var|escape:url}
    {/if}

    {if !$no_container}<div class="buttons-container picker">{/if}{if $picker_view}[{/if}
        {if $icon_plus}<i class="icon-plus"></i>{/if}
        <div>
            {include file="buttons/button.tpl" but_id="opener_picker_`$data_id`" but_href="products.picker?display=`$display`&picker_for=`$picker_for`&extra=`$extra_var`&checkbox_name=`$checkbox_name`&aoc=`$aoc`&data_id=`$data_id`" but_text=$but_text|default:__("add_products") but_role=$but_role|default:"add" but_target_id="content_`$data_id`" but_meta="cm-dialog-opener" but_rel="nofollow"}
    {if $picker_view}]{/if}{if !$no_container}</div>{/if}
    </div>

    <div class="hidden" id="content_{$data_id}" title="{$but_text|default:__("add_products")}">
    </div>

{/if}