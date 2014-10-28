{if $filter_id}
    {assign var="filter" value=$filter_id|fn_get_product_filter_name|default:"`$ldelim`filter`$rdelim`"}
{else}
    {assign var="filter" value=$default_name}
{/if}

{if $multiple}
<tr {if !$clone}id="{$holder}_{$filter_id}" {/if}class="cm-js-item{if $clone} cm-clone hidden{/if}">
    {if $position_field}<td><input type="text" name="{$input_name}[{$filter_id}]" value="{math equation="a*b" a=$position b=10}" size="3" class="input-text-short"{if $clone} disabled="disabled"{/if} /></td>{/if}
    <td><a href="{"product_filters.update?filter_id=`$filter_id`"|fn_url}">{$filter}</a></td>
    <td>
        <div class="hidden-tools">
        {if !$hide_delete_button && !$view_only}
            {capture name="tools_list"}
                <li>{btn type="list" text=__("remove") onclick="Tygh.$.cePicker('delete_js_item', '{$holder}', '{$filter_id}', 'f'); return false;"}</li>
            {/capture}
            {dropdown content=$smarty.capture.tools_list}
        {/if}
        </div>
    </td>
    {if !$hide_input}
        <input {if $input_id}id="{$input_id}"{/if} type="hidden" name="{$input_name}" value="{$filter_id}" />
    {/if}
</tr>
{else}
    <span {if !$clone}id="{$holder}_{$filter_id}" {/if}class="cm-js-item no-margin{if $clone} cm-clone hidden{/if}">
    {if !$first_item && $single_line}<span class="cm-comma{if $clone} hidden{/if}">,&nbsp;&nbsp;</span>{/if}
    <input class="cm-picker-value-description {$extra_class}" type="text" value="{$filter}" {if $display_input_id}id="{$display_input_id}"{/if} size="10" name="filter_name" readonly="readonly" {$extra}>&nbsp;
    </span>
{/if}