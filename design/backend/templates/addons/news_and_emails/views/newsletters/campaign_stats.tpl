<div id="content_campaign_stats_{$campaign.campaign_id}">
{if $campaign_stats}
<table width="100%" class="table table-middle">
<thead>
    <tr>
        <th>{__("title")}</th>
        <th>{__("clicks")}</th>
    </tr>
</thead>
<tbody>
{foreach from=$campaign_stats item="newsletter"}
<tr>
    <td>{$newsletter.newsletter}</td>
    <td>{$newsletter.clicks|default:0}</td>
</tr>
{/foreach}
</tbody>
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}
<!--content_campaign_stats_{$campaign.campaign_id}--></div>