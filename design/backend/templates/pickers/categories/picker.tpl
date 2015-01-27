{assign var="data_id" value=$data_id|default:"categories_list"}
{if !$rnd}{math equation="rand()" assign="rnd"}{/if}
{assign var="data_id" value="`$data_id`_`$rnd`"}
{assign var="view_mode" value=$view_mode|default:"mixed"}
{assign var="start_pos" value=$start_pos|default:0}
{assign var="but_meta" value=$but_meta}

{script src="js/tygh/picker.js"}

{if $item_ids == ""}
    {assign var="item_ids" value=null}
{/if}

{if $item_ids && !$item_ids|is_array}
    {assign var="item_ids" value=","|explode:$item_ids}
{/if}

{if $view_mode != "blocks"}
    {capture name="add_buttons"}
        {if $view_mode != "list"}

            {if $multiple == true}
                {assign var="display" value="checkbox"}
                {else}
                {assign var="display" value="radio"}
            {/if}

            {if !$extra_url}
                {assign var="extra_url" value="&get_tree=multi_level"}
            {/if}

            {if $extra_var}
                {assign var="extra_var" value=$extra_var|escape:url}
            {/if}

            {if !$runtime.company_id || $runtime.controller != "companies"}
        
                {if $multiple}
                    {assign var="_but_text" value=$but_text|default:__("add_categories")}
                    {assign var="_but_role" value="add"}
                    {assign var="_but_icon" value="icon-plus"}
                    {else}
                    {assign var="_but_text" value="<i class='icon-plus'></i>"}
                    {assign var="_but_role" value="icon"}
                {/if}
                
                {if $_but_role != "icon"}
                    {if $placement == 'right'}
                    <div class="clearfix">
                        <div class="pull-right">
                    {/if}
                        {include file="buttons/button.tpl" but_id="opener_picker_`$data_id`" but_href="categories.picker?display=`$display`&company_ids=`$company_ids`&picker_for=`$picker_for`&extra=`$extra_var`&checkbox_name=`$checkbox_name`&root=`$default_name`&except_id=`$except_id`&data_id=`$data_id``$extra_url`"|fn_url but_text=$_but_text but_role=$_but_role but_icon=$_but_icon but_target_id="content_`$data_id`" but_meta="`$but_meta` btn cm-dialog-opener"}
                    {if $placement == 'right'}
                    </div>
                        </div>
                    {/if}
                {/if}
                <div class="hidden" id="content_{$data_id}" title="{$but_text|default:__("add_categories")}"></div>
            {/if}

        {else}

            {assign var="display" value="checkbox"}

            {if !$extra_url}
                {assign var="extra_url" value="&get_tree=multi_level"}
            {/if}

            {if $extra_var}
                {assign var="extra_var" value=$extra_var|escape:url}
            {/if}

            {if !$runtime.company_id || $runtime.controller != "companies"}
                {assign var="_but_text" value=$but_text|default:__("add_categories")}
                {assign var="_but_role" value="add"}
                {assign var="_but_icon" value="icon-plus"}
            {include file="buttons/button.tpl" but_id="opener_picker_`$data_id`" but_href="categories.picker?display=`$display`&data_id=`$data_id`&company_ids=`$company_ids``$extra_url`"|fn_url but_text=$_but_text but_role=$_but_role but_icon=$_but_icon but_meta=$but_ but_target_id="content_`$data_id`" but_meta="`$but_meta` cm-dialog-opener"}

            {/if}
            <div class="hidden" id="content_{$data_id}" title="{$but_text|default:__("add_categories")}"></div>
        {/if}
    {/capture}
    
    {if !$prepend}
        {$smarty.capture.add_buttons nofilter}
        {capture name="add_buttons"}{/capture}
    {/if}
    
{/if}

{if !$extra_var && $view_mode != "button"}
    {if $multiple}
        <table  width="100%" class="table table-middle">
        <thead>
        <tr>
            {if $positions}<th>{__("position_short")}</th>{/if}
            <th>{__("name")}</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody id="{$data_id}"{if !$item_ids} class="hidden"{/if}>
    {else}
        <div id="{$data_id}" class="{if $multiple && !$item_ids}hidden{elseif !$multiple}{if $view_mode != "list"}cm-display-radio{/if}{/if} choose-category">
    {/if}
    {if $multiple}
        <tr class="hidden">
            <td colspan="{if $positions}3{else}2{/if}">
    {/if}
            <input id="{if $input_id}{$input_id}{else}c{$data_id}_ids{/if}" type="hidden" class="cm-picker-value" name="{$input_name}" value="{if $item_ids|is_array}{","|implode:$item_ids}{/if}" {$extra} />
    {if $multiple}
            </td>
        </tr>
    {/if}
        {if $multiple}        
            {include file="pickers/categories/js.tpl" category_id="`$ldelim`category_id`$rdelim`" holder=$data_id hide_input=$hide_input input_name=$input_name radio_input_name=$radio_input_name clone=true hide_link=$hide_link hide_delete_button=$hide_delete_button position_field=$positions position="0"}
        {/if}
        {if $view_mode == "list"}
            {foreach from=$item_ids item="c_id" name="items"}
                {include file="pickers/categories/js.tpl" main_category=$main_category category_id=$c_id holder=$data_id hide_input=$hide_input input_name=$input_name clone=true hide_link=$hide_link first_item=$smarty.foreach.items.first view_mode="list"}
            {foreachelse}
                {include file="pickers/categories/js.tpl" category_id="" holder=$data_id hide_input=$hide_input input_name=$input_name clone=true hide_link=$hide_link view_mode="list"}
            {/foreach}
        {else}
            {foreach from=$item_ids item="c_id" name="items"}
                {if !$multiple}<div class="input-append choose-input">{/if}
                {include file="pickers/categories/js.tpl" category_id=$c_id holder=$data_id hide_input=$hide_input input_name=$input_name hide_link=$hide_link hide_delete_button=$hide_delete_button first_item=$smarty.foreach.items.first position_field=$positions position=$smarty.foreach.items.iteration+$start_pos}
                {if !$multiple}</div>{/if}<!-- /.choose-input -->
            {foreachelse}
                {if !$multiple}
                <div class="input-append choose-input">
                    {include file="pickers/categories/js.tpl" category_id="" holder=$data_id hide_input=$hide_input input_name=$input_name hide_link=$hide_link hide_delete_button=$hide_delete_button}
                    {$smarty.capture.add_buttons nofilter}
                </div>
                {/if}
            {/foreach}
        {/if}
    {if $multiple}
        </tbody>
        <tbody id="{$data_id}_no_item"{if $item_ids} class="hidden"{/if}>
        <tr class="no-items">
            <td colspan="{if $positions}3{else}2{/if}"><p>{$no_item_text|default:__("no_items") nofilter}</p></td>
        </tr>
        </tbody>
    </table>
    {else}</div>{/if}
{/if}