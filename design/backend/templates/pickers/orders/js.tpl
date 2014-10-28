{if $view_mode == "simple"}
<span {if !$clone}id="{$holder}_{$order_id}" {/if}class="cm-js-item{if $clone} cm-clone{/if}{if $clone || $hidden} hidden{/if}">{if !$first_item}<span class="cm-comma{if $clone} hidden{/if}">, </span>{/if}#{$order_id}</span>
{else}
<tr {if !$clone}id="{$holder}_{$order_id}" {/if}class="cm-js-item{if $clone} cm-clone hidden{/if}">
    <td>
        <a href="{"orders.details?order_id=`$order_id`"|fn_url}">&nbsp;<span>#{$order_id}</span>&nbsp;</a></td>
    <td>{if $clone}{$status}{else}{include file="common/status.tpl" status=$status display="view" name="order_statuses[`$order_id`]"}{/if}</td>
    <td>{$customer}</td>
    <td>
        <a href="{"orders.details?order_id=`$order_id`"|fn_url}" class="underlined">{if $clone}{$timestamp}{else}{$timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}{/if}</a></td>
    <td class="right">
        {if $clone}{$total}{else}{include file="common/price.tpl" value=$total}{/if}</td>
    {if !$view_only}
    <td class="nowrap">
        <div class="hidden-tools">
            {capture name="tools_list"}
                <li>{btn type="list" text=__("edit") href="orders.details?order_id=`$order_id`"}</li>
                <li>{btn type="list" text=__("remove") onclick="Tygh.$.cePicker('delete_js_item', '{$holder}', '{$order_id}', 'o'); return false;"}</li>
            {/capture}
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
    {/if}
</tr>
{/if}
