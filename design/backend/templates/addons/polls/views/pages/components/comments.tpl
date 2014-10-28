{if $smarty.request.answer_id}
    {assign var="suffix" value="_a_`$smarty.request.answer_id`"}
{elseif $smarty.request.item_id}
    {assign var="suffix" value="_q_`$smarty.request.item_id`"}
{/if}

<div id="content_poll_statistics_comments{$suffix}">
{if $comments}

{include file="common/pagination.tpl" div_id="pagination_comments_`$suffix`"}
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="table">
<tr>
    <th>{__("date")}</th>
    <th>{__("comment")}</th>
    <th width="100%">&nbsp;</th>
</tr>
{foreach from=$comments item="comment"}
<tr {cycle values="class=\"table-row\","}>
       <td class="nowrap">{$comment.time|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>
       <td class="nowrap" width="350">{$comment.comment}</td>
       <td width="100%">&nbsp;</td>
</tr>
{/foreach}
</table>
{include file="common/pagination.tpl" div_id="pagination_comments_`$suffix`"}

{/if}
<!--content_poll_statistics_comments{$suffix}--></div>