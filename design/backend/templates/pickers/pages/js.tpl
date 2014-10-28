{if $page_id}
    {assign var="page" value=$page_id|fn_get_page_name|default:"`$ldelim`page`$rdelim`"}
{else}
    {assign var="page" value=$default_name}
{/if}

{if $multiple}
<tr {if !$clone}id="{$holder}_{$page_id}" {/if}class="cm-js-item{if $clone} cm-clone hidden{/if}">
    {if $position_field}<td><input type="text" name="{$input_name}[{$page_id}]" value="{math equation="a*b" a=$position b=10}" size="3" class="input-text-short"{if $clone} disabled="disabled"{/if} /></td>{/if}
    
    <td><a href="{"pages.update?page_id=`$page_id`"|fn_url}">{$page}</a></td>

    <td>
        <div class="hidden-tools">
        {if !$hide_delete_button && !$view_only}
            {capture name="tools_list"}
                <li>{btn type="list" text=__("remove") onclick="Tygh.$.cePicker('delete_js_item', '{$holder}', '{$page_id}', 'a'); return false;"}</li>
            {/capture}
            {dropdown content=$smarty.capture.tools_list}
        {/if}
        </div>
    </td>
    {if !$hide_input}
        <input {if $input_id}id="{$input_id}"{/if} type="hidden" name="{$input_name}" value="{$page_id}" />
    {/if}
</tr>
{else}
    <span {if !$clone}id="{$holder}_{$page_id}" {/if}class="cm-js-item no-margin{if $clone} cm-clone hidden{/if}">
    {if !$first_item && $single_line}<span class="cm-comma{if $clone} hidden{/if}">,&nbsp;&nbsp;</span>{/if}
    <input class="cm-picker-value-description {$extra_class}" type="text" value="{$page}" {if $display_input_id}id="{$display_input_id}"{/if} size="10" name="page_name" readonly="readonly" {$extra}>&nbsp;
    </span>
{/if}