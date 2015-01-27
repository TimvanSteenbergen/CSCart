{math equation="rand()" assign="rnd"}
{assign var="data_id" value="`$data_id`_`$rnd`"}
{assign var="view_mode" value=$view_mode|default:"mixed"}

{if $view_mode == "simple"}
    {assign var="display" value="simple"}
    {assign var="max_displayed_qty" value="50"}
{/if}

{script src="js/tygh/picker.js"}

{if $item_ids && !$item_ids|is_array}
    {assign var="item_ids" value=","|explode:$item_ids}
{/if}

{if $view_mode != "list"}
    <div class="clearfix">
        {if $extra_var}
            {assign var="extra_var" value=$extra_var|escape:url}
        {/if}

        {if !$no_container}<div class="buttons-container pull-right">{/if}{if $picker_view}[{/if}

            {include file="buttons/button.tpl" but_id="opener_picker_`$data_id`" but_href="orders.picker?display=`$display`&picker_for=`$picker_for`&extra=`$extra_var`&checkbox_name=`$checkbox_name`&aoc=`$aoc`&data_id=`$data_id`&max_displayed_qty=`$max_displayed_qty`"|fn_url but_text=$but_text|default:__("add_orders") but_role="add" but_target_id="content_`$data_id`" but_meta="btn cm-dialog-opener" but_icon="icon-plus"}           
        
            {if $view_mode == "simple"}
                <span id="{$data_id}_clear" class="reload-container{if !$item_ids} hidden{/if}">
                    {include file="buttons/button.tpl" but_id="opener_picker_`$data_id`" but_onclick="Tygh.$.cePicker('delete_js_item', '`$data_id`', 'delete_all', 'o'); Tygh.$.cePicker('check_items_qty', '`$data_id`', '{"orders.manage?order_id="|fn_url}', {$max_displayed_qty}); return false;" but_text=$but_text|default:__("clear") but_role="action" but_icon="icon-repeat" but_target_id="content_`$data_id`"}
                </span>
            {/if}

        {if $picker_view}]{/if}{if !$no_container}</div>{/if}
        <div class="hidden" id="content_{$data_id}" title="{$but_text|default:__("add_orders")}">
        </div>
    </div>
{/if}

{if $view_mode == "simple"}
    <input id="o{$data_id}_ids" type="hidden" name="{$input_name}" value="{if $item_ids}{","|implode:$item_ids}{/if}" />
    <span id="{$data_id}"{if !$item_ids} class="hidden"{/if}>
        {include file="pickers/orders/js.tpl" order_id="`$ldelim`order_id`$rdelim`" clone=true}
        {if $item_ids}
        {foreach from=$item_ids item="o" name="items"}
            {if $smarty.foreach.items.iteration <= $max_displayed_qty}
            {include file="pickers/orders/js.tpl" order_id=$o first_item=$smarty.foreach.items.first holder=$data_id}
            {else}
            {include file="pickers/orders/js.tpl" order_id=$o first_item=$smarty.foreach.items.first holder=$data_id hidden=true}
            {/if}
        {/foreach}
        {/if}
    </span>
    <span id="{$data_id}_details"{if $item_ids|count <= $max_displayed_qty} class="hidden"{/if}><a href="{"orders.manage?order_id="|fn_url}{if $item_ids}{','|implode:$item_ids}{/if}">..</a></span>
    <span id="{$data_id}_no_item"{if $item_ids} class="hidden"{/if}>{$no_item_text nofilter}</span>

{elseif $view_mode != "button"}
    
    <input id="o{$data_id}_ids" type="hidden" name="{$input_name}" value="{if $item_ids}{","|implode:$item_ids}{/if}" />
    <table class="table table-middle">
        <thead>
            <tr>
                <th width="10%">{__("id")}</th>
                <th width="15%">{__("status")}</th>
                <th width="25%">{__("customer")}</th>
                <th width="25%">{__("date")}</th>
                <th width="24%" class="right">{__("total")}</th>
                {if !$view_only}<th>&nbsp;</th>{/if}
            </tr>
        </thead>
        <tbody id="{$data_id}"{if !$item_ids} class="hidden"{/if}>
        {include file="pickers/orders/js.tpl" order_id="`$ldelim`order_id`$rdelim`" status="`$ldelim`status`$rdelim`" customer="`$ldelim`customer`$rdelim`" timestamp="`$ldelim`timestamp`$rdelim`" total="`$ldelim`total`$rdelim`" holder=$data_id clone=true}
        {foreach from=$item_ids item="o"}
            {assign var="order_info" value=$o|fn_get_order_short_info}
            {include file="pickers/orders/js.tpl" order_id=$o status=$order_info.status customer="`$order_info.firstname` `$order_info.lastname`" timestamp=$order_info.timestamp total=$order_info.total holder=$data_id}
        {/foreach}
        <tr class="no-items {if $item_ids}hidden{/if}" id="{$data_id}_no_item">
            <td colspan="{if !$view_only}6{else}5{/if}">{$no_item_text nofilter}</td>
        </tr>
        </tbody>
    </table>
{/if}