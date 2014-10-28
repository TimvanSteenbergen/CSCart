{if $banner_id == "0"}
    {assign var="banner" value=$default_name}
{else}
    {assign var="banner" value=$banner_id|fn_get_banner_name|default:"`$ldelim`banner`$rdelim`"}
{/if}

<tr {if !$clone}id="{$holder}_{$banner_id}" {/if}class="cm-js-item{if $clone} cm-clone hidden{/if}">
    {if $position_field}
        <td>
            <input type="text" name="{$input_name}[{$banner_id}]" value="{math equation="a*b" a=$position b=10}" size="3" class="input-text-short" {if $clone}disabled="disabled"{/if} />
        </td>
    {/if}
    <td><a href="{"banners.update?banner_id=`$banner_id`"|fn_url}">{$banner}</a></td>
    <td>
        {capture name="tools_list"}
            {if !$hide_delete_button && !$view_only}
                <li><a onclick="Tygh.$.cePicker('delete_js_item', '{$holder}', '{$banner_id}', 'b'); return false;">{__("delete")}</a></li>
            {/if}
        {/capture}
        <div class="hidden-tools">
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
</tr>