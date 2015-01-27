{if $news_id}
    {assign var="news" value=$news_id|fn_get_news_name|default:"`$ldelim`news`$rdelim`"}
{else}
    {assign var="news" value=$default_name}
{/if}

<tr {if !$clone}id="{$holder}_{$news_id}" {/if}class="cm-js-item{if $clone} cm-clone hidden{/if}">
    {if $position_field}<td><input type="text" name="{$input_name}[{$news_id}]" value="{math equation="a*b" a=$position b=10}" size="3" class="input-text-short" {if $clone}disabled="disabled"{/if} /></td>{/if}
    <td>
        <a href="{"news.update?news_id=`$news_id`"|fn_url}">{$news}</a>
        {if !$hide_input}
            <input {if $input_id}id="{$input_id}"{/if} type="hidden" name="{$input_name}" value="{$news_id}" />
        {/if}
    </td>
    <td class="right">
        {capture name="tools_list"}
            {if !$hide_delete_button && !$view_only}
                <li><a onclick="Tygh.$.cePicker('delete_js_item', '{$holder}', '{$news_id}', 'n'); return false;" title="{__("delete")}">{__("delete")}</a></li>
            {/if}
        {/capture}
        <div class="hidden-tools">
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
</tr>
