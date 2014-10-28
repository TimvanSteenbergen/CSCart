{if $category_id == "0"}
    {assign var="category" value=$default_name}
{else}
    {assign var="category" value=$category_id|fn_get_category_name|default:"`$ldelim`category`$rdelim`"}
{/if}
<{if $single_line}span{else}p{/if} {if !$clone}id="{$holder}_{$category_id}" {/if}class="cm-js-item ty-p-none{if $clone} cm-clone hidden{/if}">
{if !$first_item && $single_line}<span class="cm-comma{if $clone} hidden{/if}">,&nbsp;&nbsp;</span>{/if}
{if $multiple}
    {if !$hide_link}
    {if $position_field}<input type="text" name="{$input_name}[{$category_id}]" value="{math equation="a*b" a=$position b=10}" size="3" class="ty-input-text-short{if $clone} disabled{/if}"{if $clone} disabled="disabled"{/if} />&nbsp;{/if}<a href="{"categories.update?category_id=`$category_id`"|fn_url}">{$category}</a>
    {else}
    <strong>{$category}</strong>
    {/if}
    {if !$hide_delete_button && !$view_only}
        <a onclick="Tygh.$.cePicker('delete_js_item', '{$holder}', '{$category_id}', 'c'); return false;" class="ty-delete-big" title="{__("remove")}"><i class="ty-delete-big__icon ty-icon-cancel-circle"></i></a>
    {/if}
{else}
    <input class="ty-input-text cm-picker-value-description" type="text" value="{$category}" {if $display_input_id}id="{$display_input_id}"{/if} size="10" name="category_name" readonly="readonly" {$extra} />
{/if}
</{if $single_line}span{else}p{/if}>
