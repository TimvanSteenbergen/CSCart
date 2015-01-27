{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}

{if $search.sort_order == "asc"}
{assign var="sort_sign" value="<i class=\"ty-icon-down-dir\"></i>"}
{else}
{assign var="sort_sign" value="<i class=\"ty-icon-up-dir\"></i>"}
{/if}
{include file="common/pagination.tpl"}
<table class="ty-reward-points-userlog ty-table">
<thead>
    <tr>
        <th class="ty-reward-points-userlog__date"><a class="cm-ajax" href="{"`$c_url`&sort_by=timestamp&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("date")}</a>{if $search.sort_by == "timestamp"}{$sort_sign nofilter}{/if}</th>
        <th class="ty-reward-points-userlog__points"><a class="cm-ajax" href="{"`$c_url`&sort_by=amount&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("points")}</a>{if $search.sort_by == "amount"}{$sort_sign nofilter}{/if}</th>
        <th class="ty-reward-points-userlog__reason">{__("reason")}</th>
    </tr>
</thead>
{foreach from=$userlog item="ul"}
<tr>
    <td>{$ul.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>
    <td>{$ul.amount}</td>
    <td>
        {if $ul.action == $smarty.const.CHANGE_DUE_ORDER}
            {assign var="statuses" value=$smarty.const.STATUSES_ORDER|fn_get_simple_statuses:true:true}
            {assign var="reason" value=$ul.reason|unserialize}
            {assign var="order_exist" value=$reason.order_id|fn_get_order_name}
            {__("order")}&nbsp;{if $order_exist}<a href="{"orders.details?order_id=`$reason.order_id`"|fn_url}" class="underlined">{/if}<strong>#{$reason.order_id}</strong>{if $order_exist}</a>{/if}:&nbsp;{$statuses[$reason.from]}&nbsp;&#8212;&#8250;&nbsp;{$statuses[$reason.to]}{if $reason.text}&nbsp;({__($reason.text) nofilter}){/if}
        {elseif $ul.action == $smarty.const.CHANGE_DUE_USE}
            {assign var="order_exist" value=$ul.reason|fn_get_order_name}
            {__("text_points_used_in_order")}: {if $order_exist}<a href="{"orders.details?order_id=`$ul.reason`"|fn_url}">{/if}<strong>#{$ul.reason}</strong>{if $order_exist}</a>{/if}
        {elseif $ul.action == $smarty.const.CHANGE_DUE_ORDER_DELETE}
            {assign var="reason" value=$ul.reason|unserialize}
            {__("order")} <strong>#{$reason.order_id}</strong>: {__("deleted")}
        {elseif $ul.action == $smarty.const.CHANGE_DUE_ORDER_PLACE}
            {assign var="reason" value=$ul.reason|unserialize}
            {assign var="order_exist" value=$reason.order_id|fn_get_order_name}
            {__("order")} {if $order_exist}<a href="{"orders.details?order_id=`$reason.order_id`"|fn_url}" class="underlined">{/if}<strong>#{$reason.order_id}</strong>{if $order_exist}</a>{/if}: {__("placed")}
        {else}
            {hook name="reward_points:userlog"}
            {$ul.reason}
            {/hook}
        {/if}
    </td>
</tr>
{foreachelse}
<tr class="ty-table__no-items">
    <td colspan="3"><p class="ty-no-items">{__("no_items")}</p></td>
</tr>
{/foreach}
</table>
{include file="common/pagination.tpl"}
{** / userlog description section **}

{capture name="mainbox_title"}{__("reward_points_log")}{/capture}
