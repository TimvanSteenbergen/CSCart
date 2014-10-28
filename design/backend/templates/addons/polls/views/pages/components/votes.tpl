{if $smarty.request.answer_id}
    {assign var="suffix" value="a_`$smarty.request.answer_id`"}
{elseif $smarty.request.item_id}
    {assign var="suffix" value="q_`$smarty.request.item_id`"}
{elseif $smarty.request.completed == "Y"}
    {assign var="suffix" value="completed"}
{else}
    {assign var="suffix" value="total"}
{/if}

<div id="content_poll_statistics_votes_{$suffix}">

{include file="common/pagination.tpl" div_id="pagination_contents_`$suffix`"}
{if $votes}
<table class="table">
<thead>
  <tr>
      <th>{__("date")}</th>
      <th>{__("user")}</th>
      <th>{__("ip")}</th>
      <th>{__("completed")}</th>
      <th>&nbsp;</th>
  </tr>
</thead>
<tbody>
{foreach from=$votes item="vote"}
<tr class="cm-row-item">
       <td class="nowrap">{$vote.time|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>
       <td>{if $vote.user_id}{$vote.lastname}{if $vote.lastname && $vote.firstname}&nbsp;{/if}{$vote.firstname}{else}{__("anonymous")}{/if}</td>
       <td>{$vote.ip_address}</td>
       <td>{if $vote.type == "C"}{__("yes")}{else}{__("no")}{/if}</td>
       <td>{include file="buttons/clone_delete.tpl" href_delete="pages.delete_vote?vote_id=`$vote.vote_id`"}</td>
</tr>
{/foreach}
</tbody>

</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}
{include file="common/pagination.tpl" div_id="pagination_contents_`$suffix`"}

<!--content_poll_statistics_votes_{$suffix}--></div>