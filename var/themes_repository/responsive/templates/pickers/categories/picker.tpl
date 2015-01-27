{assign var="data_id" value=$data_id|default:"categories_list"}
{math equation="rand()" assign="rnd"}
{assign var="data_id" value="`$data_id`_`$rnd`"}
{assign var="view_mode" value=$view_mode|default:"mixed"}

{script src="js/tygh/picker.js"}

{if $item_ids == ""}
    {assign var="item_ids" value=null}
{/if}

{if $item_ids && $multiple && !$item_ids|is_array}
    {assign var="item_ids" value=","|explode:$item_ids}
{/if}

{if !$extra_var && $view_mode != "button"}
    {if $multiple}
    <p id="{$data_id}_no_item" class="ty-no-items{if $item_ids} hidden{/if}">{$no_item_text|default:__("no_items") nofilter}</p>
    {/if}

    <div id="{$data_id}" class="{if $multiple && !$item_ids}hidden{elseif !$multiple}ty-search-form__inline-input cm-display-radio ty-float-left{/if}">
    <input id="{if $input_id}{$input_id}{else}c{$data_id}_ids{/if}" type="hidden" class="cm-picker-value" name="{$input_name}" value="{if $item_ids|is_array}{","|implode:$item_ids}{else}{$item_ids}{/if}" />
        {if $multiple}
        {include file="pickers/categories/js.tpl" category_id="`$ldelim`category_id`$rdelim`" holder=$data_id input_name=$input_name clone=true hide_link=$hide_link hide_delete_button=$hide_delete_button position_field=$positions position="0"}
        {/if}

        {foreach from=$item_ids item="c_id" name="items"}
            {include file="pickers/categories/js.tpl" category_id=$c_id holder=$data_id input_name=$input_name hide_link=$hide_link hide_delete_button=$hide_delete_button first_item=$smarty.foreach.items.first position_field=$positions position=$smarty.foreach.items.iteration}
        {foreachelse}
            {if !$multiple}
                {include file="pickers/categories/js.tpl" category_id="" holder=$data_id input_name=$input_name hide_link=$hide_link hide_delete_button=$hide_delete_button}
            {/if}
        {/foreach}
    </div>
{/if}

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

    {if !$no_container}<div class="{if !$multiple}clearfix{else}buttons-container picker{/if}">{/if}
        {if $multiple}
            {assign var="_but_text" value=$but_text|default:__("add_categories")}
            {assign var="_but_role" value="add"}
        {else}
            {assign var="_but_text" value=__("choose_category")}
            {assign var="_but_role" value="submit"}
        {/if}

        {include file="buttons/button.tpl" but_id="opener_picker_`$data_id`" but_href="categories.picker?display=`$display`&picker_for=`$picker_for`&extra=`$extra_var`&checkbox_name=`$checkbox_name`&root=`$default_name`&except_id=`$except_id`&data_id=`$data_id``$extra_url`" but_text=$_but_text but_role=$_but_role but_target_id="content_`$data_id`" but_meta="ty-btn__secondary cm-dialog-opener" but_rel="nofollow"}

    {if !$no_container}</div>{/if}

    <div class="hidden" id="content_{$data_id}" title="{$but_text|default:__("add_categories")}">
    </div>

{/if}